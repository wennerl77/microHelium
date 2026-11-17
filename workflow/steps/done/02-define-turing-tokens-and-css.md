# Definir tokens globais e Dark Arena em app.css

**Status:** done

## What to do

Importar os pesos definidos no `DESIGN.md` das 4 fontes em `resources/css/app.css` e atualizar `@theme` e `.dark` com a paleta oficial da Maratona Turing 2026 (Superfície Editorial clara e Dark Arena no `.dark`), calibrando os tokens semânticos de texto/botão/input para passar integralmente nos 68 pares de contraste de `tests/Frontend/design-tokens.test.js`. Adicionar também as escalas fluidas, raios, breakpoints (`1100px`, `960px`, `699px`, `359px`), grade `56px x 56px` (`.arena-grid`), `.reading-progress`, `.chapter-label`, `:focus-visible` e regras completas de `prefers-reduced-motion`.

## Original line

> - Definir os tokens globais de cores, Dark Arena (.dark), tipografia, raios, grade 56x56, .reading-progress, .chapter-label, foco e reduced-motion de DESIGN.md em resources/css/app.css e atualizar design-tokens.test.js

## Summary

Importados os pesos oficiais de `@fontsource/*` e definidos todos os tokens Turing 2026 (`@theme`, `.dark` Dark Arena, escalas fluidas, grade `56px x 56px`, `.reading-progress`, `.chapter-label`, `.turing-podium`, `.pato-stage`, `:focus-visible` e `prefers-reduced-motion`) em `resources/css/app.css`. `node --test tests/Frontend/design-tokens.test.js` passou 4/4.
