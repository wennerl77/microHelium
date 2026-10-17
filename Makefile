# MicroHelium Makefile
# Docker commands for development and production

.PHONY: help setup dev-up dev-up-cf dev-down prod-up prod-up-cf prod-down up-prod build up down restart dev logs shell migrate seed seed-admin backup-db restore-db fresh test lint format deploy rollback smoke composer npm artisan check-buildx dev-tools cloudflare-up cloudflare-down cloudflare-status

# Toolchain profile for compilers (maratona = ~2.1 GB, scripting = ~2.3 GB, funcional = ~4.5 GB, completo = ~9.4 GB)
JUDGE_PROFILE ?= maratona

# Flag para inicialização condicional do Cloudflare Tunnel (true/false)
CLOUDFLARE ?= false

# Execução padronizada do strip via container (Zero-Host)
STRIP_BUILDERS_CMD = docker run --rm --user "$$(id -u):$$(id -g)" -v "$(CURDIR):/app" -w /app python:3.12-alpine python3 docker/strip_builders.py

# Default target
help:
	@echo "MicroHelium Docker Commands"
	@echo ""
	@echo "Desenvolvimento (com Seeders):"
	@echo "  make dev-up      - Constrói e inicializa DEV no perfil maratona (migrações + seeders + Vite/Mailpit)"
	@echo "  make dev-up-cf   - Inicializa DEV junto com o Cloudflare Tunnel para acesso remoto"
	@echo "  make dev-down    - Para os contêineres de desenvolvimento"
	@echo ""
	@echo "Produção:"
	@echo "  make prod-up     - Constrói e inicializa PROD no perfil maratona (migrações + admin seeder apenas)"
	@echo "  make prod-up-cf  - Inicializa PROD junto com o Cloudflare Tunnel para acesso remoto"
	@echo "  make prod-down   - Para os contêineres de produção"
	@echo ""
	@echo "Banco de Dados & Backups:"
	@echo "  make seed-admin  - Cria o usuário administrador padrão (seguro para produção)"
	@echo "  make backup-db   - Realiza backup completo do banco de dados (.sql)"
	@echo "  make restore-db FILE=<path> - Restaura dump SQL completo no banco de dados"
	@echo ""
	@echo "Cloudflare Tunnel:"
	@echo "  make cloudflare-up     - Inicializa o túnel Cloudflare (caso não esteja ativo)"
	@echo "  make cloudflare-down   - Para o contêiner do Cloudflare tunnel"
	@echo "  make cloudflare-status - Verifica o status do túnel Cloudflare"
	@echo ""
	@echo "Build & Ferramentas:"
	@echo "  make build [JUDGE_PROFILE=...] - Compila as imagens Docker com perfil (default: maratona)"
	@echo "  make up          - Sobe os contêineres padrão"
	@echo "  make down        - Para os contêineres padrão"
	@echo "  make dev-tools   - Inicia phpMyAdmin e ferramentas adicionais"
	@echo "  make logs        - Exibe logs dos contêineres"
	@echo "  make shell       - Abre shell no contêiner da aplicação"
	@echo ""
	@echo "One-Off CLI (Zero-Host):"
	@echo "  make composer CMD=\"...\"  - Executa comandos do Composer"
	@echo "  make npm CMD=\"...\"       - Executa comandos do NPM"
	@echo "  make artisan CMD=\"...\"   - Executa comandos do Artisan"
	@echo ""
	@echo "Deploy & Testes:"
	@echo "  make deploy      - Constrói, migra e promove imagens para produção"
	@echo "  make rollback PREV=<tag> - Reverte para imagem anterior"
	@echo "  make smoke       - Executa testes de fumaça pós-deploy"
	@echo "  make test        - Executa testes PHPUnit"

# =============================================================================
# Desenvolvimento (com Seeders)
# =============================================================================

dev-up: check-buildx
	@JUDGE_PROFILE=$(JUDGE_PROFILE) RUN_SEEDERS=true COMPOSE_PROFILE=dev CLOUDFLARE=$(CLOUDFLARE) bash scripts/docker-setup.sh $(if $(filter true 1 yes,$(CLOUDFLARE)),--cloudflare)

dev-up-cf:
	@$(MAKE) dev-up CLOUDFLARE=true

dev-down:
	@docker compose --profile dev down
	@if [ "$(CLOUDFLARE)" = "true" ] || [ "$(CLOUDFLARE)" = "1" ] || [ "$(CLOUDFLARE)" = "yes" ]; then $(MAKE) cloudflare-down; fi

# Aliases de compatibilidade para desenvolvimento
setup: dev-up
dev: dev-up

# =============================================================================
# Build & Stack Padrão
# =============================================================================

check-buildx:
	@docker buildx version >/dev/null 2>&1 || (echo "ERRO: docker-buildx nao encontrado. Instale com: sudo pacman -S docker-buildx (ou equivalente na sua distribuicao)." && exit 1)

build: check-buildx
	@if [ "$(JUDGE_PROFILE)" = "maratona" ] || [ "$(JUDGE_PROFILE)" = "minimal" ]; then \
		echo "⚙️  Processando Dockerfiles slim via container Python..."; \
		trap 'rm -f Dockerfile.slim Dockerfile.dev.slim Dockerfile.judge.slim' EXIT INT TERM; \
		$(STRIP_BUILDERS_CMD); \
		DOCKERFILE_APP=Dockerfile.slim DOCKERFILE_DEV=Dockerfile.dev.slim DOCKERFILE_JUDGE=Dockerfile.judge.slim JUDGE_PROFILE=$(JUDGE_PROFILE) docker compose build app autojudge; \
	else \
		JUDGE_PROFILE=$(JUDGE_PROFILE) docker compose build app autojudge; \
	fi

up: check-buildx
	@if ! docker image inspect microhelium-app:latest >/dev/null 2>&1 || ! docker image inspect microhelium-judge:latest >/dev/null 2>&1; then \
		echo "📦 Imagens do MicroHelium não encontradas. Construindo no perfil $(JUDGE_PROFILE)..."; \
		$(MAKE) build JUDGE_PROFILE=$(JUDGE_PROFILE); \
	fi
	@docker compose up -d

down:
	@docker compose down

restart: down up

dev-tools:
	@docker compose --profile dev --profile tools up -d

logs:
	@docker compose logs -f

logs-app:
	@docker compose logs -f app

logs-queue:
	@docker compose logs -f queue

logs-judge:
	@docker compose logs -f autojudge

shell:
	@docker compose exec app sh

shell-root:
	@docker compose exec -u root app sh

shell-db:
	@docker compose exec db mysql -u microhelium -psecret microhelium

shell-redis:
	@docker compose exec redis redis-cli

# =============================================================================
# Laravel Commands
# =============================================================================

migrate:
	@docker compose exec app php artisan migrate

seed:
	@docker compose exec app php artisan db:seed

seed-admin:
	@docker compose exec app php artisan db:seed --class=AdminSeeder --force

backup-db:
	@mkdir -p backups
	@echo "Iniciando backup completo do banco de dados..."
	@if docker compose ps --filter "name=microhelium-app" --filter "status=running" -q | grep -q .; then \
		docker compose exec -T app php artisan db:backup; \
	elif [ -n "$$(docker ps --filter "name=microhelium-db" --filter "status=running" -q)" ]; then \
		docker compose run --rm -T artisan db:backup; \
	else \
		echo "ERRO: O container de banco de dados (db) não está em execução. Inicie com 'make up' ou 'make prod-up'."; \
		exit 1; \
	fi

restore-db:
ifndef FILE
	$(error Uso: make restore-db FILE=backups/microhelium_backup_YYYY-MM-DD_HHmmss.sql)
endif
	@if [ ! -f "$(FILE)" ]; then echo "ERRO: Arquivo '$(FILE)' não encontrado."; exit 1; fi
	@echo "Restaurando banco de dados a partir de $(FILE)..."
	@docker compose exec -T db sh -c 'exec mysql -u"$$MYSQL_USER" -p"$$MYSQL_PASSWORD" "$$MYSQL_DATABASE"' < "$(FILE)"
	@echo "✅ Banco de dados restaurado com sucesso a partir de $(FILE)."

fresh:
	@docker compose exec app php artisan migrate:fresh --seed

key:
	@docker compose exec app php artisan key:generate

cache:
	@docker compose exec app php artisan cache:clear
	@docker compose exec app php artisan config:clear
	@docker compose exec app php artisan route:clear
	@docker compose exec app php artisan view:clear

optimize:
	@docker compose exec app php artisan optimize
	@docker compose exec app php artisan view:cache
	@docker compose exec app php artisan event:cache

tinker:
	@docker compose exec app php artisan tinker

# =============================================================================
# Queue & Judge
# =============================================================================

# -u www because `docker compose exec` bypasses the image ENTRYPOINT, and
# so bypasses the privilege drop docker/php/entrypoint.sh does for the
# `queue` service (issue #136). Without it this target is the one
# remaining way to run the judging path -- queue:work dispatches
# JudgeRunJob, which compiles and executes submitted code -- as uid 0.
queue:
	@docker compose exec -u www app php artisan queue:work --verbose

queue-restart:
	@docker compose exec app php artisan queue:restart

# -u judge for the same reason, against the guarantee Dockerfile.judge's
# entrypoint has made since #86: `exec` starts a new process directly, so
# this one was running the autojudge loop as root even though the
# service's own main process is uid 1000.
judge:
	@docker compose exec -u judge autojudge php artisan autojudge:start --sleep=5

# =============================================================================
# Testing & Code Quality
# =============================================================================

test:
	@docker compose exec app php artisan test

test-coverage:
	@docker compose exec app php artisan test --coverage

lint:
	@docker compose exec app ./vendor/bin/pint --test

format:
	@docker compose exec app ./vendor/bin/pint

# =============================================================================
# CLI Tools (Zero-Host)
# =============================================================================

composer:
	@docker compose run --rm -T composer $(CMD)

npm:
	@docker compose run --rm -T node npm $(CMD)

artisan:
	@docker compose run --rm -T artisan $(CMD)

# =============================================================================
# Assets
# =============================================================================

npm-install:
	@docker compose exec node npm install

npm-dev:
	@docker compose exec node npm run dev

npm-build:
	@docker compose exec node npm run build

# =============================================================================
# Produção (sem Seeders)
# =============================================================================

# IMAGE_TAG defaults to :prod here (not :latest) so `make prod`/`prod-up`
# share the same tag universe as `make deploy`/`rollback` below -- otherwise
# `prod-up` would run :latest while `rollback` only knows about :prod/sha
# tags, and a rollback after a plain `prod-up` would silently swap in
# whatever :prod last happened to reference instead of anything related to
# what's actually running. Override with `make prod-up IMAGE_TAG=<tag>` if
# you need a specific tag.
IMAGE_TAG ?= prod
PROD_COMPOSE = docker compose -f docker-compose.yml -f docker-compose.prod.yml

prod: check-buildx
	@if [ "$(JUDGE_PROFILE)" = "maratona" ] || [ "$(JUDGE_PROFILE)" = "minimal" ]; then \
		echo "⚙️  Processando Dockerfiles slim para produção via container Python..."; \
		trap 'rm -f Dockerfile.slim Dockerfile.dev.slim Dockerfile.judge.slim' EXIT INT TERM; \
		$(STRIP_BUILDERS_CMD); \
		DOCKERFILE_APP=Dockerfile.slim DOCKERFILE_DEV=Dockerfile.dev.slim DOCKERFILE_JUDGE=Dockerfile.judge.slim IMAGE_TAG=$(IMAGE_TAG) JUDGE_PROFILE=$(JUDGE_PROFILE) $(PROD_COMPOSE) build app autojudge; \
	else \
		IMAGE_TAG=$(IMAGE_TAG) JUDGE_PROFILE=$(JUDGE_PROFILE) $(PROD_COMPOSE) build app autojudge; \
	fi

prod-up: check-buildx
	@rm -f public/hot
	@if ! docker image inspect microhelium-app:$(IMAGE_TAG) >/dev/null 2>&1 || ! docker image inspect microhelium-judge:$(IMAGE_TAG) >/dev/null 2>&1; then \
		echo "⚙️  Imagens de produção não encontradas. Construindo no perfil $(JUDGE_PROFILE)..."; \
		$(MAKE) prod IMAGE_TAG=$(IMAGE_TAG) JUDGE_PROFILE=$(JUDGE_PROFILE); \
	fi
	@echo "Subindo MySQL e Redis com espera de healthcheck..."
	@IMAGE_TAG=$(IMAGE_TAG) $(PROD_COMPOSE) up -d --wait --wait-timeout 120 db redis
	@echo "Executando migrações do banco de dados..."
	@IMAGE_TAG=$(IMAGE_TAG) $(PROD_COMPOSE) run --rm -T artisan migrate --force
	@echo "Garantindo usuário administrador padrão..."
	@IMAGE_TAG=$(IMAGE_TAG) $(PROD_COMPOSE) run --rm -T artisan db:seed --class=AdminSeeder --force
	@echo "Iniciando todos os serviços de produção..."
	@IMAGE_TAG=$(IMAGE_TAG) $(PROD_COMPOSE) up -d
	@if [ "$(CLOUDFLARE)" = "true" ] || [ "$(CLOUDFLARE)" = "1" ] || [ "$(CLOUDFLARE)" = "yes" ]; then \
		$(MAKE) cloudflare-up; \
	fi

prod-up-cf:
	@$(MAKE) prod-up CLOUDFLARE=true

prod-down:
	@rm -f public/hot
	@if [ "$(CLOUDFLARE)" = "true" ] || [ "$(CLOUDFLARE)" = "1" ] || [ "$(CLOUDFLARE)" = "yes" ]; then $(MAKE) cloudflare-down; fi
	@IMAGE_TAG=$(IMAGE_TAG) $(PROD_COMPOSE) down

# Aliases de conveniência para produção
up-prod: prod-up

# =============================================================================
# Cloudflare Tunnel
# =============================================================================

CLOUDFLARE_COMPOSE = docker compose -f docker-compose.cloudflared.yml

cloudflare-up:
	@if docker ps --filter "name=^cloudflared$$" --filter "status=running" -q | grep -q .; then \
		echo "🌐 Cloudflare tunnel (cloudflared) já está em execução."; \
	else \
		TOKEN=$$(grep -E '^TUNNEL_TOKEN=' .env 2>/dev/null | cut -d= -f2- | tr -d '\r"' || echo "$$TUNNEL_TOKEN"); \
		if [ -z "$$TOKEN" ]; then \
			echo "⚠️  AVISO: TUNNEL_TOKEN não configurado no .env. Cloudflare tunnel não foi iniciado."; \
		else \
			echo "🌐 Iniciando Cloudflare tunnel (cloudflared)..."; \
			TUNNEL_TOKEN="$$TOKEN" $(CLOUDFLARE_COMPOSE) up -d || echo "⚠️  Falha ao iniciar cloudflared. A aplicação continuará funcionando localmente."; \
		fi; \
	fi

cloudflare-down:
	@if docker ps -a --filter "name=^cloudflared$$" -q | grep -q .; then \
		echo "🛑 Parando Cloudflare tunnel..."; \
		$(CLOUDFLARE_COMPOSE) down; \
	fi

cloudflare-status:
	@echo "=== Status do Cloudflare Tunnel ==="
	@docker ps -a --filter "name=^cloudflared$$"

# =============================================================================
# Deploy / Rollback (issue #54)
#
# Every build is tagged with the git sha that produced it (microhelium-app,
# microhelium-judge), so a rollback is just re-pointing the ":prod" tag at a
# previously built image and restarting -- no rebuild, no migration replay.
# `docker images microhelium-app` / `microhelium-judge` lists what's
# available to roll back to.
# =============================================================================

# := (not ?=/lazy) so this is computed once per `make` invocation instead of
# re-running `git rev-parse` on every one of the several references below.
DEPLOY_TAG := $(shell git rev-parse --short HEAD)

deploy: check-buildx
	@echo "Building images tagged $(DEPLOY_TAG)..."
	@if [ "$(JUDGE_PROFILE)" = "maratona" ] || [ "$(JUDGE_PROFILE)" = "minimal" ]; then \
		echo "⚙️  Processando Dockerfiles slim para deploy..."; \
		trap 'rm -f Dockerfile.slim Dockerfile.dev.slim Dockerfile.judge.slim' EXIT INT TERM; \
		$(STRIP_BUILDERS_CMD); \
		DOCKERFILE_APP=Dockerfile.slim DOCKERFILE_DEV=Dockerfile.dev.slim DOCKERFILE_JUDGE=Dockerfile.judge.slim IMAGE_TAG=$(DEPLOY_TAG) JUDGE_PROFILE=$(JUDGE_PROFILE) $(PROD_COMPOSE) build app autojudge; \
	else \
		IMAGE_TAG=$(DEPLOY_TAG) JUDGE_PROFILE=$(JUDGE_PROFILE) $(PROD_COMPOSE) build app autojudge; \
	fi
	@echo "Promoting $(DEPLOY_TAG) to :prod..."
	@docker tag microhelium-app:$(DEPLOY_TAG) microhelium-app:prod
	@docker tag microhelium-judge:$(DEPLOY_TAG) microhelium-judge:prod
	@echo "Starting db/redis/app/webserver and running migrations before touching background workers..."
	@IMAGE_TAG=prod $(PROD_COMPOSE) up -d --wait --wait-timeout 120 db redis app webserver
	@$(PROD_COMPOSE) exec -T app php artisan migrate --force
	@echo "Migrations applied. Starting queue/scheduler/autojudge on the new image..."
	@IMAGE_TAG=prod $(PROD_COMPOSE) up -d --wait --wait-timeout 120 queue scheduler autojudge
	@echo "Deployed $(DEPLOY_TAG). Running smoke tests..."
	@$(MAKE) smoke || (echo "" && echo "Smoke tests FAILED after deploying $(DEPLOY_TAG)." && echo "Roll back with: make rollback PREV=<previous-tag>" && exit 1)
	@echo "Deploy of $(DEPLOY_TAG) verified healthy."

# NOTE: this re-points images and restarts containers only -- it does NOT
# revert database migrations. If the deploy being rolled back included a
# schema-breaking migration (a dropped/renamed column the old code still
# reads), rolling back the image alone will not restore compatibility; you
# must also manually run the down migration(s) for that deploy first.
rollback:
ifndef PREV
	$(error Usage: make rollback PREV=<git-sha-tag>. List available tags with: docker images microhelium-app)
endif
	@echo "Rolling back :prod to $(PREV) (no rebuild, migrations NOT reverted -- see Makefile comment)..."
	@docker tag microhelium-app:$(PREV) microhelium-app:prod
	@docker tag microhelium-judge:$(PREV) microhelium-judge:prod
	@IMAGE_TAG=prod $(PROD_COMPOSE) up -d --no-build --wait --wait-timeout 120
	@echo "Rolled back to $(PREV). Running smoke tests..."
	@$(MAKE) smoke
	@echo "Rollback to $(PREV) verified healthy."

smoke:
	@./scripts/smoke.sh

# =============================================================================
# Cleanup
# =============================================================================

clean:
	@docker compose down -v --remove-orphans
	@docker system prune -f

clean-all:
	@docker compose down -v --remove-orphans --rmi all
	@docker system prune -af --volumes
