# Sincronizar i18n, testes unitários e validar gates

**Status:** done

## What to do

Adicionar `PatoStage.vue` à lista de `InterfaceStringsTest.php` e sincronizar todas as novas chaves `pt_BR -> es` em `resources/lang/es.json` e `resources/lang/frontend/es.json` com paridade 1:1. Criar testes unitários em `tests/Frontend/arena-field.test.js` e `tests/Frontend/pato-stage.test.js`, executar `npm run build`, `npm run test:frontend`, PHPUnit (`--no-coverage --exclude-testsuite E2E`), `composer analyse`, Pint nos arquivos PHP novos e Playwright/Axe.

## Original line

> - Atualizar traduções i18n (es.json e frontend/es.json), escrever testes unitários para os novos componentes/arena-field.ts e validar build, test:frontend, PHPUnit, PHPStan, Pint e Playwright/Axe

## Summary

Traduzidas as novas chaves de `PatoStage.vue`, `partials/brand.blade.php` e `scoreboard.blade.php` em `resources/lang/es.json` e `resources/lang/frontend/es.json`, adicionados `tests/Frontend/arena-field.test.js` e `tests/Frontend/pato-stage.test.js`, e validados `npm run build`, `npm run test:frontend` (87/87), PHPUnit de frontend/CSP/rotas (91/91), Pint (`passed`) e Playwright (`8/8` em `console.spec.js` e Axe-core `/login` e `/` em modo claro e Dark Arena).
