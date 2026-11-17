# Auto-hospedar fontes Turing e configurar tsconfig

**Status:** done

## What to do

Instalar os pacotes `@fontsource/montserrat`, `@fontsource/space-grotesk`, `@fontsource/inter` e `@fontsource/jetbrains-mono` no `package.json` para que o Vite empacote todas as fontes localmente em conformidade com `font-src 'self'` e `COEP: require-corp` de `SecurityHeaders.php`. Criar também o arquivo `tsconfig.json` na raiz do projeto com `moduleResolution: "bundler"` e alias `@/* -> resources/js/*` para dar suporte limpo a `arena-field.ts` e aos SFCs `<script setup lang="ts">`.

## Original line

> - Auto-hospedar as fontes Montserrat (800, 900), Space Grotesk (500, 600, 700), Inter (400-700) e JetBrains Mono (400-700) e configurar tsconfig.json para suporte a TypeScript e CSP/COEP

## Summary

Pacotes `@fontsource/montserrat`, `@fontsource/space-grotesk`, `@fontsource/inter` e `@fontsource/jetbrains-mono` instalados em `package.json` (`devDependencies`) e `tsconfig.json` criado na raiz com resolução `bundler` e alias `@/* -> ./resources/js/*`.
