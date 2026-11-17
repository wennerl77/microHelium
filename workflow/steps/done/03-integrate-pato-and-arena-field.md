# Integrar mascote PATO e implementar arena-field.ts

**Status:** done

## What to do

Copiar o SVG oficial do mascote PATO anexado para `public/img/pato.svg`, preservando exatamente suas proporções `173x173` originais sem scripts ou atributos inseguros. Implementar `resources/js/lib/arena-field.ts` com um renderizador `<canvas>` de campo de partículas 3D em ciano (`#5CDBFF`), `ResizeObserver`, limpeza adequada de `requestAnimationFrame` e desativação automática quando `prefers-reduced-motion: reduce` estiver ativo.

## Original line

> - Integrar o SVG oficial do mascote PATO em public/img/pato.svg e implementar resources/js/lib/arena-field.ts com o campo de partículas 3D em canvas e respeito a prefers-reduced-motion

## Summary

Mascote oficial do PATO copiado para `public/img/pato.svg` (`173x173`) e módulo `resources/js/lib/arena-field.ts` criado com partículas 3D ciano, projeção em perspectiva, `ResizeObserver` e pausa automática em `prefers-reduced-motion: reduce`.
