# Project Definition

**Project:** MicroHelium
**Created:** 2026-11-15

---

## Overview

MicroHelium é uma plataforma de gerenciamento de competições de programação baseada em Laravel 13 e Vue 3, inspirada no BOCA Online Contest Administrator. O objetivo atual é evoluí-la para rodar uma maratona de programação completa com alta confiabilidade operacional, permitindo que alunos, staff e juízes foquem exclusivamente na prova.

## Problem

Em competições de programação, falhas de infraestrutura, instabilidades no julgamento ou falta de visibilidade operacional geram dúvida se um erro veio do código submetido ou do servidor/plataforma. Isso prejudica a experiência dos alunos e sobrecarrega juízes e staff durante a prova.

## Goals

- Garantir confiabilidade ponta a ponta no envio e julgamento de submissões, eliminando ambiguidades entre erro de código e erro de servidor.
- Suportar múltiplas competições acontecendo simultaneamente com isolamento adequado.
- Entregar observabilidade clara para o Admin acompanhar a saúde dos juízes, filas, erros e estado da competição em tempo real.
- Sustentar confortavelmente **50 equipes simultâneas** nesta primeira fase (com base preparada para escalar depois).

## Non-goals

- Modos CTF e Hackathon neste momento.
- Aplicativo mobile nativo.
- Fallback offline por sede (toda sede depende de conectividade estável com o servidor central).
- Suporte a linguagens fora do pacote definido (`Java`, `C`, `C++`, `JS`, `Python`).

## Users

- **Alunos / Equipes:** Precisam submeter soluções, acompanhar vereditos em tempo real, consultar o placar e enviar esclarecimentos sem se preocupar com instabilidades da plataforma.
- **Staff:** Precisa de fluxos claros de logística durante a prova (entrega de balões, impressões, gestão de salas/equipes).
- **Juízes e Admins:** Precisam de ferramentas confiáveis para julgar, responder esclarecimentos, gerenciar múltiplas competições e monitorar a saúde do sistema (observabilidade).

## Key features

- **Envio e Julgamento de Submissões:** Pipeline estável de submissão e AutoJudge nas linguagens `Java`, `C`, `C++`, `JS` e `Python`, com vereditos claros e detecção de falhas internas vs. erros de código.
- **Múltiplas Competições Simultâneas:** Gestão independente de provas, horários, freeze e equipes rodando em paralelo.
- **Placar (Scoreboard):** Atualização em tempo real no padrão ICPC com suporte a freeze e exportação de resultados.
- **Observabilidade do Admin:** Painéis e métricas de saúde dos judgehosts, filas de execução, erros e andamento das competições.

## Tech stack

| Layer | Choice | Notes |
|-------|--------|-------|
| Language | PHP 8.3+ / JavaScript (Node >= 22.15) | Backend PHP e frontend/testes JS |
| Framework | Laravel 13 + Vue 3 | Vite 8, Tailwind CSS v4, Radix Vue, Sanctum, Spatie Permission |
| Database | PostgreSQL | Banco principal em produção (SQLite/MySQL suportados para dev/testes) |
| Infrastructure | Docker / Docker Compose | Nginx, Supervisor, `Dockerfile.judge` para isolamento do juiz |

## Architecture

Monólito modular em **Laravel 13**, com regras de negócio organizadas por domínio em `app/Services/*` (ex.: `Polygon`, `Judgehost`, `Icpc`, `Clics`, `JudgingAlerts`, `Practice`, `TeamImport`), processamento assíncrono via filas (`app/Jobs`), e execução isolada de submissões em containers dedicados de Judgehost (`Dockerfile.judge`). O frontend é construído com **Vue 3**, Vite 8 e Tailwind CSS v4.

## Conventions

- **Idioma:** Interface do usuário (UI) e comentários de código em **português**.
- **Commits:** Conventional Commits (`feat:`, `fix:`, `docs:`, etc.).
- **Testes:** Todo novo componente deve ter **pelo menos um teste unitário**. Antes de abrir/enviar qualquer PR, é obrigatório rodar e passar na suíte de testes e análise estática (`PHPUnit`, `PHPStan/Larastan`, `Laravel Pint` e testes frontend).
- **Código:** Seguir os padrões PSR/Laravel do projeto e manter validações e políticas de acesso (`app/Policies`) consistentes.

## Risks and constraints

- **Escala inicial de 50 equipes simultâneas:** Picos de submissão (especialmente perto do freeze e do fim da prova) exigem fila e workers de Judgehost dimensionados e monitorados para evitar gargalos.
- **Dependência de rede em múltiplas sedes:** Como o sistema opera em instalação centralizada, cada sede precisa de link de internet estável e redundante.
- **Isolamento do Judgehost:** Linguagens permitidas restritas a `Java`, `C`, `C++`, `JS` e `Python` para manter previsibilidade de ambiente, tempo de execução e segurança no container de julgamento.
