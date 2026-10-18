#!/usr/bin/env bash
set -euo pipefail

echo "=== MicroHelium Zero-Host Setup ==="

if ! docker info >/dev/null 2>&1; then
    echo "ERRO: sem permissão no Docker. Rode 'sudo usermod -aG docker \$USER' e reabra a sessão (ou use sudo)." >&2
    exit 1
fi

# Suporte a flag --cloudflare via CLI ou variável de ambiente CLOUDFLARE
ENABLE_CLOUDFLARE="${CLOUDFLARE:-false}"
for arg in "$@"; do
    if [ "$arg" = "--cloudflare" ]; then
        ENABLE_CLOUDFLARE="true"
    fi
done

# 1. Configuração Idempotente do .env e Registro de UID/GID
if [ ! -f .env ]; then
    echo "Criando .env a partir de .env.docker..."
    cp .env.docker .env
fi

# Garantir DOCKER_UID e DOCKER_GID gravados no .env (respeitando SUDO_UID se executado com sudo)
CURRENT_UID="${SUDO_UID:-$(id -u)}"
CURRENT_GID="${SUDO_GID:-$(id -g)}"

if ! grep -q "^DOCKER_UID=" .env; then
    echo "DOCKER_UID=${CURRENT_UID}" >> .env
else
    sed -i "s/^DOCKER_UID=.*/DOCKER_UID=${CURRENT_UID}/" .env
fi

if ! grep -q "^DOCKER_GID=" .env; then
    echo "DOCKER_GID=${CURRENT_GID}" >> .env
else
    sed -i "s/^DOCKER_GID=.*/DOCKER_GID=${CURRENT_GID}/" .env
fi

export DOCKER_UID="${CURRENT_UID}"
export DOCKER_GID="${CURRENT_GID}"

# Carrega APP_PORT do .env se não estiver no ambiente
if [ -z "${APP_PORT:-}" ] && [ -f .env ]; then
    APP_PORT=$(grep -E '^APP_PORT=' .env | cut -d= -f2 | tr -d '\r"' || true)
fi
APP_PORT="${APP_PORT:-8000}"
export APP_PORT

# 2. Limpeza de resíduos de desenvolvimento anteriores
rm -f public/hot
mkdir -p .npm-cache node_modules storage/framework/views storage/framework/cache storage/framework/sessions storage/logs storage/app
if [ "$(id -u)" -eq 0 ]; then
    # Sob sudo, evita que o cache/node_modules/storage fiquem com dono root
    chown -R "${CURRENT_UID}:${CURRENT_GID}" .npm-cache node_modules storage bootstrap/cache
else
    # Sem sudo, caso haja resíduos de contêineres anteriores (ex.: testes) com dono root,
    # normaliza a posse para o usuário atual antes do build
    docker run --rm -v "$(pwd):/app" alpine chown -R "${CURRENT_UID}:${CURRENT_GID}" /app/storage /app/bootstrap/cache 2>/dev/null || true
fi
chmod -R u+rwX,go+rX storage bootstrap/cache 2>/dev/null || true

# 3. Trap de Limpeza dos Dockerfiles Temporários
cleanup() {
    rm -f Dockerfile.slim Dockerfile.dev.slim Dockerfile.judge.slim
}
trap cleanup EXIT INT TERM

# 4. Build Zero-Host com Containerização Pura do Python
JUDGE_PROFILE="${JUDGE_PROFILE:-maratona}"
if [ "$JUDGE_PROFILE" = "maratona" ] || [ "$JUDGE_PROFILE" = "minimal" ]; then
    echo "Processando Dockerfiles slim com container Python..."
    docker run --rm \
        --user "${CURRENT_UID}:${CURRENT_GID}" \
        -v "$(pwd):/app" \
        -w /app \
        python:3.12-alpine python3 docker/strip_builders.py

    DOCKERFILE_APP=Dockerfile.slim \
    DOCKERFILE_DEV=Dockerfile.dev.slim \
    DOCKERFILE_JUDGE=Dockerfile.judge.slim \
    JUDGE_PROFILE="$JUDGE_PROFILE" \
    docker compose build app autojudge
else
    JUDGE_PROFILE="$JUDGE_PROFILE" docker compose build app autojudge
fi

# 5. Instalação Determinística de Dependências PHP e Node
echo "Instalando dependências do Composer..."
docker compose run --rm -T composer install --optimize-autoloader

echo "Instalando dependências frontend e compilando assets Vite..."
NPM_FLAGS="--fetch-retries=5 --fetch-retry-mintimeout=20000 --fetch-retry-maxtimeout=120000 --fetch-timeout=300000 --prefer-offline --no-audit --no-fund"
for attempt in 1 2 3; do
    if docker compose run --rm -T --remove-orphans node sh -c "npm ci $NPM_FLAGS && npm run build"; then
        break
    fi
    if [ "$attempt" -eq 3 ]; then
        echo "Falha ao instalar dependências npm após 3 tentativas." >&2
        exit 1
    fi
    echo "Tentativa $attempt falhou (rede ou permissões?). Repetindo em 10s..."
    sleep 10
done

# 6. Inicialização com Espera Saudável do Banco de Dados
echo "Subindo MySQL e Redis com espera de healthcheck..."
docker compose up -d --wait --wait-timeout 120 db redis

# 7. Configuração da Aplicação Laravel
if ! grep -q "^APP_KEY=base64:[A-Za-z0-9+/=]\+" .env 2>/dev/null; then
    echo "Gerando nova APP_KEY..."
    docker compose run --rm -T artisan key:generate --force
else
    echo "APP_KEY existente preservada com sucesso."
fi

echo "Vinculando storage público..."
docker compose run --rm -T artisan storage:link --force

echo "Executando migrações do banco de dados..."
docker compose run --rm -T artisan migrate --force

# Views Blade compiladas corrompidas/obsoletas viram páginas em branco; com
# opcache.validate_timestamps=0 o php-fpm também as mantém em cache, então reinicia o app se estiver no ar.
echo "Limpando views compiladas..."
docker compose run --rm -T artisan view:clear
if [ -n "$(docker compose ps --status running -q app 2>/dev/null)" ]; then
    docker compose restart app >/dev/null
fi

if [ "${RUN_SEEDERS:-true}" = "true" ]; then
    echo "Populando dados iniciais e usuário administrador (Seed)..."
    docker compose run --rm -T artisan db:seed --force
else
    echo "Seeders completos ignorados (RUN_SEEDERS=false). Garantindo criação de admin..."
    docker compose run --rm -T artisan db:seed --class=AdminSeeder --force
fi

# 8. Inicialização da Stack Completa
echo "Iniciando todos os serviços..."
PROFILE_ARGS=""
if [ -n "${COMPOSE_PROFILE:-}" ]; then
    PROFILE_ARGS="--profile ${COMPOSE_PROFILE}"
fi
docker compose ${PROFILE_ARGS} up -d
docker compose restart webserver >/dev/null 2>&1 || true

# 9. Verificação de Fumaça com Retry
echo "Aguardando estabilização dos serviços web..."
for _ in {1..15}; do
    HTTP_STATUS=$(curl -s -o /dev/null -w "%{http_code}" "http://localhost:${APP_PORT}/up" || true)
    if [ "$HTTP_STATUS" = "200" ]; then
        break
    fi
    printf "."
    sleep 2
done
echo ""

echo "Executando testes de fumaça..."
SMOKE_PROD=0 ./scripts/smoke.sh

# 10. Inicialização Opcional e Resiliente do Cloudflare Tunnel
if [ "$ENABLE_CLOUDFLARE" = "true" ] || [ "$ENABLE_CLOUDFLARE" = "1" ] || [ "$ENABLE_CLOUDFLARE" = "yes" ]; then
    echo "Verificando Cloudflare tunnel..."
    if docker ps --filter "name=^cloudflared$" --filter "status=running" -q | grep -q .; then
        echo "🌐 Cloudflare tunnel (cloudflared) já está em execução."
    else
        TOKEN=$(grep -E '^TUNNEL_TOKEN=' .env 2>/dev/null | cut -d= -f2- | tr -d '\r"' || echo "${TUNNEL_TOKEN:-}")
        if [ -z "$TOKEN" ]; then
            echo "⚠️  AVISO: TUNNEL_TOKEN não configurado no .env. Cloudflare tunnel não foi iniciado."
        else
            echo "🌐 Iniciando Cloudflare tunnel (cloudflared)..."
            TUNNEL_TOKEN="$TOKEN" docker compose -f docker-compose.cloudflared.yml up -d || \
                echo "⚠️  Falha ao iniciar cloudflared. A aplicação continuará funcionando localmente."
        fi
    fi
fi

echo "=== Setup Zero-Host concluído com sucesso! Acesse http://localhost:${APP_PORT:-8000} ==="
