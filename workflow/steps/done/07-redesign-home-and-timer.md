# Redesenhar home.blade.php e ContestTimer.vue

**Status:** done

## What to do

Atualizar `resources/views/home.blade.php` transformando `.hero-panel` no palco Dark Arena `#07152A` com `<pato-stage></pato-stage>`, tipografia `Space Grotesk` e CTAs em `#FFD900`/`#091B4A` e `#056E96`/`#FFFFFF`, mantendo todas as variáveis de `HomeController` e links exigidos por `HomeControllerTest` e `FrontendNavigationTest`. Atualizar `resources/js/components/ContestTimer.vue` com exibição tabular mono (`JetBrains Mono`) mantendo todos os computed properties testados em `tests/Frontend/contest-timer.test.js`.

## Original line

> - Redesenhar home.blade.php e ContestTimer.vue no tema Dark Arena + Superfície Editorial com o palco PatoStage, painel tabular mono e cartões de estatísticas e submissões

## Summary

Atualizado `resources/views/home.blade.php` com o palco Dark Arena `<pato-stage></pato-stage>` e CTAs Turing (`button-primary` e `button-arena`), além de `ContestTimer.vue` com tipografia `JetBrains Mono` tabular.
