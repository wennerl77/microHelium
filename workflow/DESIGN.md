---
name: Maratona de Programação Turing 2026
description: Sistema de design oficial para a Maratona de Programação das Instituições Públicas de Minas Gerais (IFMG).
colors:
  primary: "#079BD0"
  primary-hover: "#067FAE"
  accent: "#FFD900"
  accent-hover: "#E6C400"
  navy: "#091B4A"
  navy-dark: "#050E28"
  ink: "#201C19"
  black: "#000000"
  white: "#FFFFFF"
  background: "#FFFFFF"
  surface: "#F2F7FA"
  surface-hover: "#E7EEF4"
  border: "#D8E0E8"
  text-muted: "#475569"
  arena-bg: "#07152A"
  arena-line: "rgba(255, 255, 255, 0.09)"
  arena-muted: "#9BACC1"
  arena-cyan: "#5CDBFF"
  verdict-pass: "#79E2B5"
  verdict-pass-bg: "#143C36"
  verdict-fail: "#ED6B62"
  verdict-fail-bg: "#44272C"
typography:
  display:
    fontFamily: "Montserrat, Space Grotesk, system-ui, sans-serif"
    fontWeight: 900
    letterSpacing: "-0.04em"
  heading:
    fontFamily: "Space Grotesk, system-ui, sans-serif"
    fontWeight: 700
    letterSpacing: "-0.02em"
  body:
    fontFamily: "Inter, system-ui, sans-serif"
    fontSize: "16px"
    lineHeight: 1.6
    fontWeight: 400
  code:
    fontFamily: "JetBrains Mono, monospace"
    fontWeight: 500
  scale:
    2xs: "10px"
    xs: "11px"
    sm: "12px"
    md: "14px"
    base: "16px"
    lg: "18px"
    xl: "22px"
    2xl: "24px"
    3xl: "32px"
    4xl: "48px"
    5xl: "64px"
    display: "clamp(3.25rem, 13vw, 9.5rem)"
rounded:
  xs: "4px"
  sm: "8px"
  md: "12px"
  lg: "16px"
  xl: "24px"
  full: "9999px"
spacing:
  container: "1200px"
  arena-container: "1240px"
  xs: "4px"
  sm: "8px"
  md: "16px"
  lg: "24px"
  xl: "32px"
  2xl: "48px"
  3xl: "64px"
components:
  button-primary:
    backgroundColor: "{colors.primary}"
    textColor: "#FFFFFF"
    rounded: "{rounded.md}"
    padding: "14px 24px"
  button-secondary:
    backgroundColor: "{colors.accent}"
    textColor: "{colors.navy}"
    rounded: "{rounded.md}"
    padding: "14px 24px"
  button-arena:
    backgroundColor: "{colors.accent}"
    textColor: "{colors.arena-bg}"
    rounded: "{rounded.xs}"
    padding: "15px 20px"
  card:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.ink}"
    rounded: "{rounded.lg}"
    padding: "24px"
  card-dark:
    backgroundColor: "{colors.arena-bg}"
    textColor: "#FFFFFF"
    borderColor: "{colors.arena-line}"
    rounded: "{rounded.md}"
    padding: "24px"
---

# Design System — Maratona de Programação Turing 2026

## 1. Overview & Linguagem Visual
O sistema de design da Maratona de Programação Turing expressa energia acadêmica, excelência algorítmica e a esportividade eletrizante das competições no formato ICPC. A identidade visual opera em uma cadência rítmica de duas atmosferas complementares:

1. **A Dark Arena (Arena Noturna e Painéis de Precisão):** Inspirada em terminais de programação competitiva, telemetria de submissão e arenas esportivas. Utilizada no cabeçalho sticky, no Hero com palco 3D, no simulador de código interativo microHelium, no esquema de rede de campi, no fechamento e no cabeçalho de rankings.
2. **A Superfície Editorial Clara (Institucional & Regulamento):** Foco em clareza extrema para leitura de regras, níveis, cotas de patrocínio, premiação, cronograma e tabelas de classificação de equipes.
3. **Mascote Oficial PATO:** Elemento central de afeto e esportividade, acompanhado de anéis orbitais, balões de pontuação e gatilhos de comemoração festiva.

---

## 2. Paleta de Cores e Tokens

### 2.1 Cores Institucionais e Marca
- **Azul Primário (`#079BD0`)**: Assinatura visual oficial de marca, botões primários, links ativos e detalhes estruturais.
- **Azul Primário Hover (`#067FAE`)**: Estado de interação para ações primárias azuis.
- **Amarelo Destaque (`#FFD900`)**: Ações secundárias, acentos de destaque, pódios de 1º lugar e balão aceito (AC). Sempre combinado com tipografia escura (`#091B4A` ou `#07152A`) garantindo contraste superior a 7:1 (WCAG AAA).
- **Amarelo Hover (`#FFE747` / `#E6C400`)**: Estado de foco/interação para botões amarelos.
- **Azul Marinho Nobre (`#091B4A`)**: Cabeçalhos e títulos editoriais, cartões Diamante e base dos pódios.
- **Azul Marinho Profundo (`#050E28`)**: Subníveis de sombra e divisórias institucionais.

### 2.2 Tema "Dark Arena" e Instrumentação
- **Fundo da Arena (`--arena-bg: #07152A`)**: Fundo escuro imersivo que remete a terminais de desenvolvimento e noites de prova.
- **Linha de Grade da Arena (`--arena-line: rgba(255, 255, 255, 0.09)`)**: Linhas finas de retículo, divisores técnicos e contornos discretos.
- **Texto Muted da Arena (`--arena-muted: #9BACC1`)**: Texto secundário, legendas de telemetria e metadados.
- **Ciano da Arena (`--arena-cyan: #5CDBFF`)**: Indicadores de pulso elétrico, labels de coordenadas, badges ativos e status dots.

### 2.3 Superfícies e Textos Claros
- **Fundo Branco (`#FFFFFF`)**: Base de leitura de seções editoriais.
- **Superfície Suave (`--color-surface: #F2F7FA` e `#F2F6F9`)**: Fundo de cards, detalhes de cronograma e alternância de seções.
- **Borda Neutra (`--color-border-turing: #D8E0E8`)**: Delimitação sutil de cartões e divisores horizontais.
- **Tinta Carvão (`--color-ink: #201C19`)**: Texto corrido de alta legibilidade, evitando o cansaço do preto puro.
- **Texto Secundário (`--color-text-muted: #475569`)**: Subtítulos, instruções e descrições auxiliares.

### 2.4 Status de Julgamento & Código (microHelium)
- **Veredito Aceito (AC):** `#79E2B5` (texto), `#143C36` (fundo do veredito), `#169377` (marcador).
- **Veredito Errado / Falha (WA/TLE):** `#FFA1A1` / `#ED6B62` (texto e ponto), `#44272C` (fundo do veredito).
- **Sintaxe & Código:** Roxo `#C5A1FA` (palavras-chave), Azul Claro `#7CD7FC` (tipos), Comentário `#617D96`, Valor Inicial `#FFC276`.

---

## 3. Tipografia

O projeto adota uma hierarquia tipográfica precisa de 4 famílias:

| Família | Pesos Utilizados | Função Principal |
| :--- | :--- | :--- |
| **`Montserrat`** | 800, 900 | Manchetes display de grande escala dos campi na página de Rankings (`.rk-title`). |
| **`Space Grotesk`** | 500, 600, 700 | Títulos de seções (H1–H4), nomes de equipes no pódio e marcas numéricas. |
| **`Inter`** | 400, 500, 600, 700 | Texto corrido, interface geral, parágrafos, botões e listas de membros. |
| **`JetBrains Mono`** | 400, 500, 600, 700 | Timers, telemetria, código-fonte, coordenadas, labels de capítulo e numerais. |

### Escala e Comportamento
- **Display Campus Headline:** `clamp(3.25rem, 13vw, 9.5rem)`, line-height `0.88`, tracking `-0.04em`, uppercase.
- **Hero H1:** `clamp(2.5rem, 6.6vw, 5.5rem)`, line-height `0.99`, tracking `-0.065em`.
- **H2 Editorial:** `clamp(1.75rem, 3.5vw, 2.5rem)`, line-height `1.15`, tracking `-0.045em`.
- **H3:** `clamp(1.25rem, 2vw, 1.5rem)`, line-height `1.3`, tracking `-0.015em`.
- **Body:** `1rem` (16px), line-height `1.65`.
- **Labels Técnicos & Kicker:** `8px` a `10px`, `JetBrains Mono`, tracking `0.1em`, uppercase.

---

## 4. Grid, Layout e Espaçamentos

- **Container Padrão:** `max-width: 1200px` com `px-4 sm:px-6 lg:px-8`.
- **Arena Shell:** `max-width: 1240px` com `padding: 0 40px` (adaptável para 22px em mobile).
- **Barra de Leitura:** Indicador de progresso no topo da viewport (`.reading-progress`), altura 3px, em `#FFD900`.
- **Alternância de Seções:**
  - Seções escuras com grade matemática sutil (`56px x 56px` com linhas em `#ffffff04`).
  - Seções claras pontuadas por labels de capítulo (`.chapter-label`) e divisores precisos.
- **Breakpoints Responsivos Estratégicos:**
  - `1100px`: Compactação de gaps no Hero e simulação.
  - `960px`: Transição de grids de 2 colunas para 1 coluna e ajuste das órbitas do mascote.
  - `699px` (Mobile): Alinhamento vertical completo, tipografia fluida e reajuste de tabelas.
  - `359px`: Telas ultracompactas com redução de densidade e fontes adaptadas.

---

## 5. Componentes do Sistema

### 5.1 Header Sticky & Barra de Anúncios
- Barra superior fixa (`#site-header`) em fundo translúcido escuro (`#07152AF5` com `backdrop-blur-md`).
- Links de navegação com underline dinâmico amarelo (`#FFD900`) e transição em hover.
- Botão de ação rápida para patrocinadores com ícone Handshake.

### 5.2 Arena Hero & Palco do Mascote (PATO)
- **Canvas 3D Particle Field:** Renderização no arquivo `arena-field.ts` com gradiente ciano e máscara radial.
- **Sistema de Órbitas:**
  - Órbita externa rotativa com traço tracejado (`.orbit--outer`) e satélite luminoso.
  - Órbita intermediária inclinada em perspectiva 3D (`.orbit--equator`).
  - Órbita interna com halo luminoso suave (`.orbit--inner`).
- **Badges Orbitais Flutuantes:** Balões interativos (`.orbit-badge--a`, `--b`, `--c`) com notas técnicas e animação flutuante.
- **Botão Mascote:** Mascote PATO responsivo ao mouse com suporte a clique que dispara confetes festivos comemorativos.
- **Contador Regressivo da Prova:** Painel tabular mono com dias, horas, minutos e segundos.

### 5.3 Simulador Interativo de Submissão (microHelium Experience)
- Janela simulada de editor de código com abas de problema (Problema F — "O Enigma dos Patos").
- Painel de testes unitários com status em tempo real (Pass / Fail).
- Alternador de estágios (1: Erro de Lógica, 2: Ajuste de Inicialização, 3: Veredito Aceito).
- Balão amarelo comemorativo tridimensional (`.accepted-balloon`) que flutua ao atingir AC.
- Suporte a scroll pinado em desktops com `prefers-reduced-motion: no-preference`.

### 5.4 Faixa Contínua de Linguagens (`.language-ribbon`)
- Marquee horizontal infinito contendo as linguagens suportadas pela competição: **C, C++, Java, Python e JavaScript**.

### 5.5 Esquema da Rede dos Campi (`.campus-network`)
- Mapa esquemático conectando os 8 campi do IFMG (Bambuí, Betim, Ibirité, Ipatinga, Itabirito, Ouro Preto, Ponte Nova e a sede Ouro Branco).
- Trajetos animados em CSS com pulsos ciano fluindo em direção à sede anfitriã.

### 5.6 Cards Editoriais & Abas
- **Cards de Níveis:** Abas acessíveis (`[role="tablist"]`) com painéis estilizados com borda acentuada à esquerda em azul primário.
- **Pódio Gráfico de Premiação:** Pódio vetorial com alturas proporcionais para 2º, 1º e 3º lugares com coroa de louros.
- **Cronograma Sequencial:** Grade de 4 etapas com conectores circulares de status (concluído em verde, ativo em azul).
- **Cards de Cotas de Patrocínio:** Cartões modulares (Bronze, Prata, Ouro e Diamante), onde o card Diamante assume acabamento nobre em fundo Navy escuro com selo geométrico em rotação.
- **Accordion de FAQ:** Elementos nativos `<details>` / `<summary>` estilizados com chevron animado sem scripts externos.

### 5.7 Módulo de Rankings & Classificação (`/rankings/`)
- **Manchete do Campus:** Título em `Montserrat 900` com carimbo angular de byline (`.rk-byline`) e indicador do docente representante local.
- **Filtros e Busca:** Abas de navegação rápida por campus (`.rk-nav`) e campo de pesquisa instantânea (`.rk-search`).
- **Pódio Leaderboard (2º - 1º - 3º):**
  - Coluna do 1º colocado elevada, em Navy nobre com troféu dourado e tipografia branca.
  - 2º e 3º colocados em superfícies azuis suaves proporcionais.
  - Em telas móveis, reorganiza-se em lista vertical com cantos arredondados contínuos.
- **Linhas de Classificação (`.rk-row`):** Numerais com efeito vazado/outline (`-webkit-text-stroke: 1.5px var(--color-navy)`) e destaque para os classificados.
- **Grade de Fatos Curiosos e Telemetria (`.rk-facts`):** Cartões de dados de alto impacto visual com números monumentais para curiosidades da prova local.

---

## 6. Acessibilidade e Inclusão

- **Contraste de Cor (WCAG 2.2 AA / AAA):** Todos os textos em fundos claros e escuros obedecem à taxa de contraste mínima de 4.5:1 (e 7:1 para botões e pódios principais).
- **Foco Visível:** Todos os elementos focáveis possuem anel de foco visível (`outline: 2px solid #079BD0` com offset de `3px`).
- **Respeito a `prefers-reduced-motion`:** Quando o usuário solicita redução de movimento:
  - Animações contínuas de órbitas, pulso de rede e marquee são desativadas ou pausadas.
  - A rolagem suave volta ao modo instantâneo.
  - As histórias pinadas tornam-se blocos estáticos roláveis normais.
- **Equidade e Reconhecimento:** Destaque visual consistente para a categoria especial "Melhor Equipe Feminina".

---

## 7. Diretrizes de Uso (Do's and Don'ts)

### Do's
- Mantenha a cadência intencional entre o espetáculo da Dark Arena e a clareza da Superfície Editorial.
- Use sempre o mascote PATO em suas proporções oficiais, permitindo apenas rotações sutis de animação.
- Use `Montserrat` exclusivamente em pesos 800 e 900 para títulos display de grande escala na página de rankings.
- Use `JetBrains Mono` sempre que exibir tempos, pontuações, coordenadas ou código.
- Garanta que textos sobre o fundo amarelo `#FFD900` utilizem obrigatoriamente cor escura (`#091B4A` ou `#07152A`).

### Don'ts
- Não utilize degradês arco-íris ou efeitos coloridos fora da paleta aprovada.
- Não utilize emojis genéricos no lugar de ícones SVG limpos (Lucide Icons).
- Não adicione sombras duras ou pesadas com estilo neo-brutalista descontextualizado.
- Não altere as cores oficiais aprovadas das cotas e vereditos.
