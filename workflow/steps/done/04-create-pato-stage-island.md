# Criar componente PatoStage.vue e registrar ilha

**Status:** done

## What to do

Criar `resources/js/components/PatoStage.vue` sem props obrigatórias, integrando `arena-field.ts`, as três órbitas (`.orbit--outer`, `.orbit--equator`, `.orbit--inner`), badges flutuantes (`.orbit-badge--a`, `--b`, `--c`) com textos traduzidos via `t()`, e o botão acessível do mascote PATO com parallax sutil e confetes comemorativos ao clicar ou pressionar Enter/Space (respeitando `prefers-reduced-motion`). Registrar `'pato-stage': PatoStage` em `resources/js/app.js` e atualizar `tests/Unit/FrontendIslandsTest.php`.

## Original line

> - Criar o componente Vue PatoStage.vue com órbitas 3D (.orbit--outer, .orbit--equator, .orbit--inner), badges flutuantes, parallax/confete ao clique e registrá-lo em resources/js/app.js

## Summary

Criado `resources/js/components/PatoStage.vue` com `arena-field.ts`, órbitas `.orbit--outer`/`--equator`/`--inner`, badges flutuantes e botão acessível do PATO com parallax e confete (com respeito a `prefers-reduced-motion`). Ilha `pato-stage` registrada em `resources/js/app.js` e incluída em `tests/Unit/FrontendIslandsTest.php`.
