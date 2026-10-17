<template>
    <div class="polygon-workspace feature-stack space-y-6">
        <!-- Toast Notification -->
        <transition enter-active-class="transform ease-out duration-300 transition"
                    enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
                    enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
                    leave-active-class="transition ease-in duration-200"
                    leave-from-class="opacity-100"
                    leave-to-class="opacity-0">
            <div v-if="toast.show"
                 class="fixed top-5 right-5 z-50 flex items-center gap-3 px-4 py-3 rounded-xl shadow-lg border text-sm font-medium"
                 :class="{
                     'bg-success text-success-foreground border-success/30': toast.type === 'success',
                     'bg-destructive text-destructive-foreground border-destructive/30': toast.type === 'error',
                     'bg-primary text-primary-foreground border-primary/30': toast.type === 'info',
                 }"
                 role="status"
                 aria-live="polite">
                <span>{{ toast.message }}</span>
                <button type="button" @click="toast.show = false" class="ml-2 opacity-75 hover:opacity-100 font-bold" aria-label="Fechar notificação">✕</button>
            </div>
        </transition>

        <!-- Top Breadcrumb & Exercise Header -->
        <header class="surface p-5 rounded-2xl border flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <a href="/backend/polygon"
                   class="inline-flex items-center justify-center px-3 py-1.5 rounded-xl border hover:bg-muted text-muted-foreground hover:text-foreground text-xs font-medium transition-all gap-1.5"
                   title="Voltar ao Painel do Polygon">
                    <span>←</span>
                    <span class="hidden sm:inline">Seletor</span>
                </a>

                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center justify-center w-9 h-9 rounded-xl font-bold text-sm shadow-xs text-white"
                          :style="{ backgroundColor: exercise.color_hex || '#3B82F6' }"
                          :title="`Balão: ${exercise.color_name || 'Azul'}`">
                        {{ exercise.short_name || '?' }}
                    </span>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-xl font-bold tracking-tight text-foreground">
                                {{ exercise.name || (loadingExercise ? 'Carregando Exercício...' : 'Sem Nome') }}
                            </h1>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold"
                                  :class="{
                                      'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300': exercise.status === 'draft',
                                      'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300': exercise.status === 'generated',
                                      'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300': exercise.status === 'calibrated',
                                      'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300': exercise.status === 'published',
                                  }">
                                {{ statusLabel(exercise.status) }}
                            </span>
                        </div>
                        <p class="text-xs text-muted-foreground font-mono mt-0.5">
                            {{ exercise.basename || 'sem-slug' }} · {{ exercise.time_limit }}s / {{ exercise.memory_limit }}MB ·
                            <span v-if="exercise.contest_id" class="text-primary font-sans font-medium">Contest #{{ exercise.contest_id }}</span>
                            <span v-else class="font-sans">Banco Geral</span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Global Action Buttons -->
            <div class="flex items-center gap-2.5">
                <button type="button"
                        id="btn-save-draft"
                        @click="saveDraft(false)"
                        :disabled="savingDraft"
                        class="button-secondary text-sm flex items-center gap-2">
                    <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                    </svg>
                    <span>{{ savingDraft ? 'Salvando...' : 'Salvar Rascunho' }}</span>
                </button>

                <button type="button"
                        @click="activeTab = 'publish'"
                        class="button-primary text-sm flex items-center gap-2">
                    <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Checklist & Publicação</span>
                </button>
            </div>
        </header>

        <!-- Pipeline Stepper (5 Etapas) -->
        <nav aria-label="Etapas de Autoria do Problema" class="surface rounded-xl p-1.5 border">
            <ol class="grid grid-cols-2 md:grid-cols-5 gap-1.5 text-xs">
                <li v-for="(tab, index) in tabs" :key="tab.id">
                    <button type="button"
                            @click="activeTab = tab.id"
                            :class="[
                                'w-full py-2.5 px-3 rounded-lg font-semibold flex items-center gap-2.5 transition-all text-left',
                                activeTab === tab.id
                                    ? 'bg-primary text-primary-foreground shadow-xs'
                                    : 'hover:bg-muted text-muted-foreground hover:text-foreground'
                            ]">
                        <!-- Step Status Badge -->
                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-full text-xs font-bold"
                              :class="[
                                  activeTab === tab.id
                                      ? 'bg-primary-foreground/20 text-primary-foreground'
                                      : isTabCompleted(tab.id)
                                          ? 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400'
                                          : 'bg-muted text-muted-foreground'
                              ]">
                            <span v-if="isTabCompleted(tab.id)">✓</span>
                            <span v-else>{{ index + 1 }}</span>
                        </span>
                        <span class="truncate">{{ tab.name }}</span>
                    </button>
                </li>
            </ol>
        </nav>

        <!-- ========================================== -->
        <!-- ABA 1: Atributos & Enunciado               -->
        <!-- ========================================== -->
        <section v-show="activeTab === 'attributes'" class="space-y-6" aria-labelledby="tab-attributes-title">
            <!-- Form Grid: Dados Básicos -->
            <div class="surface p-6 rounded-2xl border space-y-5">
                <div class="flex items-center justify-between pb-3 border-b border-border">
                    <h2 id="tab-attributes-title" class="text-base font-bold text-foreground flex items-center gap-2">
                        <span>Configurações Básicas</span>
                        <span class="text-xs font-normal text-muted-foreground" title="Defina o nome, sigla e limites estruturais do problema.">(?)</span>
                    </h2>
                    <span class="text-xs text-muted-foreground">* Campos obrigatórios</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Nome -->
                    <div>
                        <label for="exercise-name" class="block text-xs font-semibold text-foreground mb-1">
                            Nome do Problema <span class="text-destructive">*</span>
                        </label>
                        <input type="text"
                               id="exercise-name"
                               v-model="exercise.name"
                               placeholder="Ex: Resto da Divisão"
                               required
                               class="w-full px-3 py-2 text-sm border rounded-lg bg-background text-foreground border-input focus:ring-2 focus:ring-ring focus:outline-none" />
                    </div>

                    <!-- Sigla / Letra -->
                    <div>
                        <label for="exercise-short-name" class="block text-xs font-semibold text-foreground mb-1">
                            Sigla / Letra <span class="text-destructive">*</span>
                        </label>
                        <input type="text"
                               id="exercise-short-name"
                               v-model="exercise.short_name"
                               placeholder="A"
                               maxlength="10"
                               required
                               class="w-full px-3 py-2 text-sm uppercase text-center font-bold border rounded-lg bg-background text-foreground border-input focus:ring-2 focus:ring-ring focus:outline-none" />
                    </div>

                    <!-- Slug / Basename -->
                    <div>
                        <label for="exercise-basename" class="block text-xs font-semibold text-foreground mb-1">
                            Identificador em Disco (Slug) <span class="text-destructive">*</span>
                        </label>
                        <input type="text"
                               id="exercise-basename"
                               v-model="exercise.basename"
                               placeholder="resto-divisao"
                               required
                               class="w-full px-3 py-2 text-sm font-mono border rounded-lg bg-background text-foreground border-input focus:ring-2 focus:ring-ring focus:outline-none" />
                    </div>
                </div>

                <!-- Limites e Destino -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 pt-2">
                    <div>
                        <label for="exercise-time-limit" class="block text-xs font-semibold text-foreground mb-1">
                            Tempo Base (C/C++)
                        </label>
                        <div class="relative">
                            <input type="number"
                                   id="exercise-time-limit"
                                   v-model.number="exercise.time_limit"
                                   min="1"
                                   max="60"
                                   class="w-full px-3 py-2 text-sm border rounded-lg bg-background text-foreground border-input focus:ring-2 focus:ring-ring focus:outline-none pr-12" />
                            <span class="absolute inset-y-0 right-3 flex items-center text-xs text-muted-foreground pointer-events-none">seg</span>
                        </div>
                    </div>

                    <div>
                        <label for="exercise-memory-limit" class="block text-xs font-semibold text-foreground mb-1">
                            Memória Base
                        </label>
                        <div class="relative">
                            <input type="number"
                                   id="exercise-memory-limit"
                                   v-model.number="exercise.memory_limit"
                                   min="16"
                                   max="2048"
                                   class="w-full px-3 py-2 text-sm border rounded-lg bg-background text-foreground border-input focus:ring-2 focus:ring-ring focus:outline-none pr-12" />
                            <span class="absolute inset-y-0 right-3 flex items-center text-xs text-muted-foreground pointer-events-none">MB</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-foreground mb-1">
                            Balão da Maratona
                        </label>
                        <div class="flex items-center gap-2">
                            <input type="color"
                                   v-model="exercise.color_hex"
                                   class="w-9 h-9 p-0.5 rounded-lg border border-input cursor-pointer bg-background" />
                            <input type="text"
                                   v-model="exercise.color_name"
                                   placeholder="Cor (ex: Azul)"
                                   class="flex-1 px-3 py-2 text-sm border rounded-lg bg-background text-foreground border-input focus:ring-2 focus:ring-ring focus:outline-none" />
                        </div>
                    </div>

                    <div>
                        <label for="exercise-contest-id" class="block text-xs font-semibold text-foreground mb-1">
                            Destino de Publicação
                        </label>
                        <select id="exercise-contest-id"
                                v-model="exercise.contest_id"
                                class="w-full px-3 py-2 text-sm border rounded-lg bg-background text-foreground border-input focus:ring-2 focus:ring-ring focus:outline-none">
                            <option value="">(Nenhuma - Banco Geral)</option>
                            <option v-for="c in availableContests" :key="c.id" :value="String(c.id)">
                                🏆 {{ c.name }}
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Markdown: Enunciado, Entrada e Saída -->
            <div class="surface p-6 rounded-2xl border space-y-4">
                <div class="flex items-center justify-between pb-2 border-b border-border">
                    <h2 class="text-base font-bold text-foreground">Enunciado & Seções do Problema</h2>
                    <div class="inline-flex rounded-lg border p-1 bg-muted/40 text-xs">
                        <button type="button"
                                @click="previewMode = 'split'"
                                :class="previewMode === 'split' ? 'bg-background shadow-xs text-foreground font-semibold' : 'text-muted-foreground'"
                                class="px-2.5 py-1 rounded-md transition-all">Lado a Lado</button>
                        <button type="button"
                                @click="previewMode = 'editor'"
                                :class="previewMode === 'editor' ? 'bg-background shadow-xs text-foreground font-semibold' : 'text-muted-foreground'"
                                class="px-2.5 py-1 rounded-md transition-all">Editor</button>
                        <button type="button"
                                @click="previewMode = 'preview'"
                                :class="previewMode === 'preview' ? 'bg-background shadow-xs text-foreground font-semibold' : 'text-muted-foreground'"
                                class="px-2.5 py-1 rounded-md transition-all">Preview</button>
                    </div>
                </div>

                <div class="grid gap-4" :class="previewMode === 'split' ? 'grid-cols-1 lg:grid-cols-2' : 'grid-cols-1'">
                    <!-- Editor Form -->
                    <div v-show="previewMode !== 'preview'" class="space-y-4">
                        <div>
                            <label for="markdown-desc" class="block text-xs font-semibold text-foreground mb-1">
                                Descrição Geral (Suporta Markdown & Fórmulas $N$)
                            </label>
                            <textarea id="markdown-desc"
                                      v-model="exercise.description"
                                      rows="8"
                                      class="w-full px-3 py-2 text-sm font-mono border rounded-lg bg-background text-foreground border-input focus:ring-2 focus:ring-ring focus:outline-none leading-relaxed"
                                      placeholder="# Título do Problema&#10;&#10;Descreva aqui a história ou contexto do problema..."></textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label for="input-desc" class="block text-xs font-semibold text-foreground mb-1">Formato de Entrada</label>
                                <textarea id="input-desc"
                                          v-model="exercise.input_description"
                                          rows="4"
                                          class="w-full px-3 py-2 text-sm border rounded-lg bg-background text-foreground border-input focus:ring-2 focus:ring-ring focus:outline-none"
                                          placeholder="A primeira linha contém um inteiro T..."></textarea>
                            </div>
                            <div>
                                <label for="output-desc" class="block text-xs font-semibold text-foreground mb-1">Formato de Saída</label>
                                <textarea id="output-desc"
                                          v-model="exercise.output_description"
                                          rows="4"
                                          class="w-full px-3 py-2 text-sm border rounded-lg bg-background text-foreground border-input focus:ring-2 focus:ring-ring focus:outline-none"
                                          placeholder="Para cada caso de teste, imprima a resposta..."></textarea>
                            </div>
                        </div>

                        <div>
                            <label for="notes-desc" class="block text-xs font-semibold text-foreground mb-1">Notas e Explicações dos Exemplos</label>
                            <textarea id="notes-desc"
                                      v-model="exercise.notes"
                                      rows="3"
                                      class="w-full px-3 py-2 text-sm border rounded-lg bg-background text-foreground border-input focus:ring-2 focus:ring-ring focus:outline-none"
                                      placeholder="No primeiro exemplo, a resposta é 42 porque..."></textarea>
                        </div>
                    </div>

                    <!-- Live Preview Panel -->
                    <div v-show="previewMode !== 'editor'" class="surface p-5 rounded-xl border space-y-4">
                        <span class="text-xs font-bold text-muted-foreground uppercase tracking-wider block">Visualização do Enunciado</span>
                        <div class="prose dark:prose-invert max-w-none text-sm space-y-3">
                            <div v-if="exercise.description" class="whitespace-pre-wrap">{{ exercise.description }}</div>
                            <p v-else class="text-muted-foreground italic text-xs">Nenhuma descrição informada ainda.</p>

                            <div v-if="exercise.input_description" class="pt-2 border-t border-border">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Entrada</h4>
                                <p class="text-foreground whitespace-pre-wrap text-xs">{{ exercise.input_description }}</p>
                            </div>

                            <div v-if="exercise.output_description" class="pt-2 border-t border-border">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Saída</h4>
                                <p class="text-foreground whitespace-pre-wrap text-xs">{{ exercise.output_description }}</p>
                            </div>

                            <div v-if="exercise.notes" class="pt-2 border-t border-border">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Notas</h4>
                                <p class="text-muted-foreground whitespace-pre-wrap text-xs">{{ exercise.notes }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pt-3 border-t border-border flex justify-end">
                    <button type="button" @click="activeTab = 'generator'" class="button-primary text-xs flex items-center gap-1.5">
                        <span>Avançar para Gerador C++</span>
                        <span>→</span>
                    </button>
                </div>
            </div>
        </section>

        <!-- ========================================== -->
        <!-- ABA 2: Gerador C++ (gen.cpp)               -->
        <!-- ========================================== -->
        <section v-show="activeTab === 'generator'" class="space-y-6" aria-labelledby="tab-generator-title">
            <div class="surface p-6 rounded-2xl border space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-border gap-2">
                    <div>
                        <h2 id="tab-generator-title" class="text-base font-bold text-foreground flex items-center gap-2">
                            <span>Gerador de Casos de Teste (gen.cpp)</span>
                            <span class="text-xs font-normal text-muted-foreground" title="Código C++ utilizando testlib.h para geração determinística via sementes.">(?)</span>
                        </h2>
                        <p class="text-xs text-muted-foreground mt-0.5">
                            Compila com flags <code class="font-mono text-primary">-O2 -std=c++20 -static -I resources/judge-runtime/include</code>.
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button"
                                id="btn-open-generator-wizard"
                                @click="showGeneratorBuilder = true"
                                class="button-secondary text-xs"
                                title="Abrir assistente visual do gerador">
                            Assistente Visual
                        </button>
                        <button type="button"
                                @click="restoreDefaultGenerator"
                                class="button-secondary text-xs"
                                title="Restaurar template padrão com testlib.h">
                            Restaurar Template
                        </button>
                        <button type="button"
                                id="btn-compile-generator"
                                @click="runCompileGenerator"
                                :disabled="compilingGenerator"
                                class="button-primary text-xs flex items-center gap-2">
                            <svg v-if="compilingGenerator" class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>{{ compilingGenerator ? 'Compilando...' : 'Compilar Gerador' }}</span>
                        </button>
                    </div>
                </div>

                <!-- Code Editor Textarea -->
                <div>
                    <label for="generator-source-code" class="sr-only">Código Fonte C++ do Gerador</label>
                    <textarea id="generator-source-code"
                              v-model="exercise.generator_source"
                              rows="18"
                              class="w-full px-3 py-2 text-xs font-mono border rounded-lg bg-background text-foreground border-input focus:ring-2 focus:ring-ring focus:outline-none leading-relaxed"
                              placeholder="#include &quot;testlib.h&quot;..."></textarea>
                </div>

                <!-- Collapsible Compilation Terminal Drawer -->
                <div class="rounded-xl border overflow-hidden">
                    <div class="bg-muted/50 px-4 py-2 flex items-center justify-between cursor-pointer select-none"
                         @click="showTerminalDrawer = !showTerminalDrawer">
                        <div class="flex items-center gap-2 text-xs">
                            <span class="font-bold text-foreground">Console de Compilação Sandbox</span>
                            <span v-if="generatorCompiled && generatorCompileSuccess"
                                  class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded-md">
                                ● Compilado com Sucesso
                            </span>
                            <span v-else-if="generatorLogs && !generatorCompileSuccess"
                                  class="inline-flex items-center gap-1 text-[11px] font-semibold text-destructive bg-destructive/10 px-2 py-0.5 rounded-md">
                                ● Erro de Compilação
                            </span>
                            <span v-else class="text-[11px] text-muted-foreground">
                                ● Aguardando compilação
                            </span>
                        </div>
                        <span class="text-xs text-muted-foreground hover:text-foreground font-semibold">
                            {{ showTerminalDrawer ? 'Recolher ▲' : 'Expandir ▼' }}
                        </span>
                    </div>

                    <div v-show="showTerminalDrawer" class="bg-card p-4 border-t border-border">
                        <pre class="font-mono text-xs text-foreground whitespace-pre-wrap overflow-x-auto max-h-48 leading-relaxed">{{ generatorLogs || 'Nenhum log de compilação disponível. Clique em "Compilar Gerador" para testar o código no sandbox.' }}</pre>
                    </div>
                </div>

                <div class="pt-2 flex justify-between items-center">
                    <button type="button" @click="activeTab = 'attributes'" class="button-secondary text-xs">
                        ← Voltar para Atributos
                    </button>
                    <button type="button" @click="activeTab = 'tests'" class="button-primary text-xs flex items-center gap-1.5">
                        <span>Avançar para Casos de Teste</span>
                        <span>→</span>
                    </button>
                </div>
            </div>
        </section>

        <!-- ========================================== -->
        <!-- ABA 3: Geração de Testes                   -->
        <!-- ========================================== -->
        <section v-show="activeTab === 'tests'" class="space-y-6" aria-labelledby="tab-tests-title">
            <div class="surface p-6 rounded-2xl border space-y-5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-border gap-2">
                    <div>
                        <h2 id="tab-tests-title" class="text-base font-bold text-foreground flex items-center gap-2">
                            <span>Gerador de Casos de Teste & Script</span>
                            <span class="text-xs font-normal text-muted-foreground" title="Defina scripts de geração e casos manuais de exemplo.">(?)</span>
                        </h2>
                        <p class="text-xs text-muted-foreground mt-0.5">
                            Formato de cada linha do script: <code class="font-mono text-primary">gen &lt;args&gt; > $</code>. Cada linha gera um arquivo <code class="font-mono text-primary">.in</code> determinístico.
                        </p>
                    </div>

                    <button type="button"
                            id="btn-generate-tests"
                            @click="runGenerateTests"
                            :disabled="generatingTests"
                            class="button-primary text-xs flex items-center gap-2">
                        <svg v-if="generatingTests" class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>{{ generatingTests ? 'Gerando Casos...' : 'Executar Geração de Testes' }}</span>
                    </button>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <!-- Script Editor -->
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label for="generator-script" class="block text-xs font-semibold text-foreground">
                                Script de Testes (1 comando por linha)
                            </label>
                            <button type="button"
                                    id="btn-add-batch-tests"
                                    @click="appendBatchTests"
                                    class="button-secondary text-[11px] py-0.5 px-2"
                                    title="Adicionar lote de 10 testes aleatórios com sementes">
                                + 10 Testes
                            </button>
                        </div>
                        <textarea id="generator-script"
                                  v-model="exercise.generator_script"
                                  rows="8"
                                  class="w-full px-3 py-2 text-xs font-mono border rounded-lg bg-background text-foreground border-input focus:ring-2 focus:ring-ring focus:outline-none leading-relaxed"
                                  placeholder="gen 1 > $&#10;gen 2 > $&#10;gen 3 > $"></textarea>
                    </div>

                    <!-- Manual Tests Editor -->
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-foreground">Casos de Teste Manuais (Amostras)</span>
                            <button type="button"
                                    @click="addManualTest"
                                    class="button-secondary text-[11px] py-1 px-2.5">
                                + Adicionar Amostra
                            </button>
                        </div>

                        <div v-if="exercise.manual_tests && exercise.manual_tests.length > 0" class="space-y-2 max-h-56 overflow-y-auto pr-1">
                            <div v-for="(test, idx) in exercise.manual_tests" :key="idx" class="surface p-3 rounded-xl border text-xs space-y-2">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                            <input type="checkbox" v-model="test.is_sample" class="rounded border-input text-primary" />
                                            <span class="font-semibold text-foreground">Exemplo no Enunciado (is_sample)</span>
                                        </label>
                                        <span v-if="!test.input || !test.input.trim()" class="text-[10px] text-amber-500 font-medium">
                                            (Entrada vazia será ignorada)
                                        </span>
                                    </div>
                                    <button type="button" @click="removeManualTest(idx)" class="text-destructive hover:underline text-[11px]">
                                        Remover
                                    </button>
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <textarea v-model="test.input" rows="2" placeholder="Entrada..." class="w-full px-2 py-1 text-xs font-mono border rounded bg-background text-foreground"></textarea>
                                    <textarea v-model="test.output" rows="2" placeholder="Saída..." class="w-full px-2 py-1 text-xs font-mono border rounded bg-background text-foreground"></textarea>
                                </div>
                            </div>
                        </div>
                        <div v-else class="p-6 text-center border rounded-xl text-xs text-muted-foreground">
                            Nenhum teste manual cadastrado. Amostras fornecem os exemplos da prova.
                        </div>
                    </div>
                </div>

                <!-- Generated Tests Table -->
                <div class="pt-4 border-t border-border space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-bold text-foreground">Casos de Teste Gerados no Sandbox</span>
                            <span class="text-xs text-muted-foreground font-mono">({{ generatedTests.length }} casos)</span>
                        </div>
                    </div>

                    <div v-if="generatedTests.length === 0" class="p-8 text-center border rounded-xl text-xs text-muted-foreground">
                        Nenhum caso de teste gerado ainda. Compile o gerador e clique em "Executar Geração de Testes".
                    </div>

                    <div v-else class="overflow-x-auto border rounded-xl">
                        <table class="w-full text-left text-xs" id="test-cases-table">
                            <thead class="bg-muted/40 text-muted-foreground uppercase tracking-wider border-b border-border">
                                <tr>
                                    <th scope="col" class="py-2.5 px-3 w-16">#</th>
                                    <th scope="col" class="py-2.5 px-3">Origem</th>
                                    <th scope="col" class="py-2.5 px-3 w-28">Tipo</th>
                                    <th scope="col" class="py-2.5 px-3 w-28">Tamanho</th>
                                    <th scope="col" class="py-2.5 px-3 w-24 text-right">Ação</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                <tr v-for="t in generatedTests" :key="t.number" class="hover:bg-muted/20">
                                    <td class="py-2.5 px-3 font-mono font-bold">{{ t.number }}</td>
                                    <td class="py-2.5 px-3 text-muted-foreground font-mono">{{ t.source || t.origin || 'script' }}</td>
                                    <td class="py-2.5 px-3">
                                        <span v-if="t.is_sample" class="bg-primary/10 text-primary px-2 py-0.5 rounded text-[11px] font-semibold">
                                            Amostra
                                        </span>
                                        <span v-else class="text-muted-foreground text-[11px]">Secreto</span>
                                    </td>
                                    <td class="py-2.5 px-3 font-mono text-muted-foreground">
                                        {{ t.input_bytes || t.size_bytes || 0 }} B
                                    </td>
                                    <td class="py-2.5 px-3 text-right">
                                        <button type="button"
                                                @click="openInspectModal(t)"
                                                class="button-secondary text-[11px] py-1 px-2.5">
                                            Inspecionar
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="pt-3 border-t border-border flex justify-between items-center">
                    <button type="button" @click="activeTab = 'generator'" class="button-secondary text-xs">
                        ← Voltar para Gerador
                    </button>
                    <button type="button" @click="activeTab = 'solutions'" class="button-primary text-xs flex items-center gap-1.5">
                        <span>Avançar para Soluções & Calibração</span>
                        <span>→</span>
                    </button>
                </div>
            </div>
        </section>

        <!-- ========================================== -->
        <!-- ABA 4: Soluções & Calibração               -->
        <!-- ========================================== -->
        <section v-show="activeTab === 'solutions'" class="space-y-6" aria-labelledby="tab-solutions-title">
            <div class="surface p-6 rounded-2xl border space-y-5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-border gap-2">
                    <div>
                        <h2 id="tab-solutions-title" class="text-base font-bold text-foreground flex items-center gap-2">
                            <span>Soluções Modelo & Calibração de Limites</span>
                            <span class="text-xs font-normal text-muted-foreground" title="A solução ACCEPT oficial gera os arquivos .out. Soluções TLE e WRONG garantem rigor no julgamento.">(?)</span>
                        </h2>
                        <p class="text-xs text-muted-foreground mt-0.5">
                            Executa a solução <code class="font-mono text-emerald-600 font-bold">ACCEPT</code> contra os casos gerados para produzir as saídas oficiais e calibrar tempo/memória.
                        </p>
                    </div>

                    <button type="button"
                            id="btn-run-calibration"
                            @click="runCalibration"
                            :disabled="calibrating"
                            class="button-primary text-xs flex items-center gap-2">
                        <svg v-if="calibrating" class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>{{ calibrating ? 'Calibrando no Sandbox...' : 'Calibrar Soluções' }}</span>
                    </button>
                </div>

                <!-- Solution Tabs: ACCEPT, TLE, WRONG -->
                <div class="space-y-3">
                    <div class="flex items-center gap-2 border-b border-border pb-2">
                        <button type="button"
                                @click="activeSolutionTab = 'accept'"
                                :class="activeSolutionTab === 'accept' ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-bold border-b-2 border-emerald-500' : 'text-muted-foreground'"
                                class="px-3 py-1.5 text-xs rounded-t-lg transition-all flex items-center gap-1.5">
                            <span>●</span>
                            <span>ACCEPT (Modelo Oficial) *</span>
                        </button>
                        <button type="button"
                                @click="activeSolutionTab = 'tle'"
                                :class="activeSolutionTab === 'tle' ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400 font-bold border-b-2 border-amber-500' : 'text-muted-foreground'"
                                class="px-3 py-1.5 text-xs rounded-t-lg transition-all flex items-center gap-1.5">
                            <span>○</span>
                            <span>TLE (Opcional - Deve estourar tempo)</span>
                        </button>
                        <button type="button"
                                @click="activeSolutionTab = 'wrong'"
                                :class="activeSolutionTab === 'wrong' ? 'bg-destructive/10 text-destructive font-bold border-b-2 border-destructive' : 'text-muted-foreground'"
                                class="px-3 py-1.5 text-xs rounded-t-lg transition-all flex items-center gap-1.5">
                            <span>○</span>
                            <span>WRONG (Opcional - Deve falhar)</span>
                        </button>
                    </div>

                    <!-- Code Textareas -->
                    <div v-show="activeSolutionTab === 'accept'">
                        <label for="solution-accept" class="sr-only">Código Fonte C++ ACCEPT</label>
                        <textarea id="solution-accept"
                                  v-model="exercise.solution_accept"
                                  rows="14"
                                  class="w-full px-3 py-2 text-xs font-mono border rounded-lg bg-background text-foreground border-input focus:ring-2 focus:ring-ring focus:outline-none leading-relaxed"
                                  placeholder="#include <iostream>&#10;&#10;using namespace std;&#10;&#10;int main() { ... }"></textarea>
                    </div>

                    <div v-show="activeSolutionTab === 'tle'">
                        <label for="solution-tle" class="sr-only">Código Fonte C++ TLE</label>
                        <textarea id="solution-tle"
                                  v-model="exercise.solution_tle"
                                  rows="14"
                                  class="w-full px-3 py-2 text-xs font-mono border rounded-lg bg-background text-foreground border-input focus:ring-2 focus:ring-ring focus:outline-none leading-relaxed"
                                  placeholder="// Código propositalmente lento para validar limite de tempo..."></textarea>
                    </div>

                    <div v-show="activeSolutionTab === 'wrong'">
                        <label for="solution-wrong" class="sr-only">Código Fonte C++ WRONG</label>
                        <textarea id="solution-wrong"
                                  v-model="exercise.solution_wrong"
                                  rows="14"
                                  class="w-full px-3 py-2 text-xs font-mono border rounded-lg bg-background text-foreground border-input focus:ring-2 focus:ring-ring focus:outline-none leading-relaxed"
                                  placeholder="// Código com falha de lógica para validar casos de borda..."></textarea>
                    </div>
                </div>

                <!-- Calibration Summary & Results -->
                <div v-if="calibrated && calibrationResults.summary" class="pt-4 border-t border-border space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-bold text-foreground">Resultados da Calibração Sandbox</span>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                            ✓ ACCEPT 100% OK
                        </span>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-xs">
                        <div class="surface p-3 rounded-xl border">
                            <span class="text-muted-foreground block text-[11px]">Tempo Máx (ACCEPT)</span>
                            <span class="text-base font-bold font-mono text-foreground mt-0.5 block">
                                {{ calibrationResults.summary.max_time_seconds ?? 0 }}s
                            </span>
                        </div>
                        <div class="surface p-3 rounded-xl border">
                            <span class="text-muted-foreground block text-[11px]">Pico de Memória</span>
                            <span class="text-base font-bold font-mono text-foreground mt-0.5 block">
                                {{ calibrationResults.summary.max_memory_mb ?? 0 }} MB
                            </span>
                        </div>
                        <div class="surface p-3 rounded-xl border">
                            <span class="text-muted-foreground block text-[11px]">Calibração TLE</span>
                            <span class="text-sm font-semibold mt-0.5 block" :class="calibrationResults.summary.tle_calibrated ? 'text-emerald-600' : 'text-muted-foreground'">
                                {{ calibrationResults.summary.tle_calibrated ? '✓ TLE Detectado' : 'Não Informado' }}
                            </span>
                        </div>
                        <div class="surface p-3 rounded-xl border">
                            <span class="text-muted-foreground block text-[11px]">Calibração WRONG</span>
                            <span class="text-sm font-semibold mt-0.5 block" :class="calibrationResults.summary.wrong_calibrated ? 'text-emerald-600' : 'text-muted-foreground'">
                                {{ calibrationResults.summary.wrong_calibrated ? '✓ WA Detectado' : 'Não Informado' }}
                            </span>
                        </div>
                    </div>

                    <!-- Warnings if any -->
                    <div v-if="calibrationResults.summary.warnings && calibrationResults.summary.warnings.length > 0" class="space-y-2">
                        <div v-for="(warn, i) in calibrationResults.summary.warnings" :key="i" class="p-3 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-700 dark:text-amber-300 text-xs flex items-center gap-2">
                            <span>⚠️</span>
                            <span>{{ warn }}</span>
                        </div>
                    </div>

                    <!-- Seção: Pares de Entrada (.in) e Saída Oficial (.out) -->
                    <div v-if="calibrationResults.cases && calibrationResults.cases.length > 0" class="space-y-3 pt-2">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-bold text-foreground flex items-center gap-2">
                                    <span>Pares de Entrada & Saída Oficial (.in / .out)</span>
                                    <span class="text-xs font-normal text-muted-foreground font-mono">({{ calibrationResults.cases.length }} casos calibrados)</span>
                                </h3>
                                <p class="text-xs text-muted-foreground mt-0.5">
                                    Saídas oficiais (.out) geradas pela execução da solução modelo <code class="font-mono text-emerald-600 font-bold">ACCEPT</code> no sandbox.
                                </p>
                            </div>
                        </div>

                        <!-- Lista de Cards dos Pares -->
                        <div class="space-y-3">
                            <div v-for="c in calibrationResults.cases" :key="c.number" class="surface p-4 rounded-xl border space-y-3">
                                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-border pb-2.5">
                                    <div class="flex items-center gap-2.5">
                                        <span class="font-mono font-bold text-sm text-foreground">Caso #{{ c.number }}</span>
                                        <span v-if="c.is_sample" class="bg-primary/10 text-primary px-2 py-0.5 rounded text-[11px] font-semibold">
                                            Amostra
                                        </span>
                                        <span v-else class="text-muted-foreground text-[11px] bg-muted px-2 py-0.5 rounded">
                                            Secreto
                                        </span>
                                        <span v-if="c.accept" class="inline-flex items-center gap-1 text-[11px] font-semibold px-2 py-0.5 rounded"
                                              :class="c.accept.verdict === 'AC' ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : 'bg-destructive/10 text-destructive'">
                                            ● {{ c.accept.verdict }} ({{ c.accept.time }}s, {{ c.accept.memory }} MB)
                                        </span>
                                    </div>
                                    <div class="text-xs text-muted-foreground font-mono flex items-center gap-3">
                                        <span>Entrada: {{ c.input_bytes || c.input?.length || 0 }} B</span>
                                        <span>Saída: {{ c.output_bytes || c.output?.length || 0 }} B</span>
                                    </div>
                                </div>

                                <!-- Grid de Entrada e Saída (Pares) -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs">
                                    <!-- Entrada (.in) -->
                                    <div class="space-y-1.5">
                                        <div class="flex items-center justify-between">
                                            <span class="font-semibold text-muted-foreground font-mono text-[11px]">Entrada (.in)</span>
                                            <button type="button"
                                                    @click="copyToClipboard(c.input, 'in_' + c.number)"
                                                    class="text-primary hover:underline text-[11px] flex items-center gap-1">
                                                {{ copiedKey === 'in_' + c.number ? '✓ Copiado!' : 'Copiar' }}
                                            </button>
                                        </div>
                                        <pre class="surface p-3 rounded-lg border font-mono max-h-48 overflow-y-auto whitespace-pre-wrap select-all leading-relaxed text-xs">{{ c.input || '(Vazio)' }}</pre>
                                    </div>

                                    <!-- Saída Oficial (.out) -->
                                    <div class="space-y-1.5">
                                        <div class="flex items-center justify-between">
                                            <span class="font-semibold text-emerald-600 dark:text-emerald-400 font-mono text-[11px] flex items-center gap-1">
                                                <span>Saída Oficial (.out)</span>
                                                <span class="bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 text-[10px] px-1 rounded font-normal">ACCEPT</span>
                                            </span>
                                            <button type="button"
                                                    @click="copyToClipboard(c.output, 'out_' + c.number)"
                                                    class="text-primary hover:underline text-[11px] flex items-center gap-1">
                                                {{ copiedKey === 'out_' + c.number ? '✓ Copiado!' : 'Copiar' }}
                                            </button>
                                        </div>
                                        <pre class="bg-emerald-500/5 dark:bg-emerald-950/20 p-3 rounded-lg border border-emerald-500/20 text-emerald-900 dark:text-emerald-200 font-mono max-h-48 overflow-y-auto whitespace-pre-wrap select-all leading-relaxed text-xs">{{ c.output || '(Sem saída gerada)' }}</pre>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Accordion: Limites Derivados por Linguagem -->
                    <div v-if="calibrationResults.language_limits && calibrationResults.language_limits.length > 0"
                         class="rounded-xl border overflow-hidden">
                        <div class="bg-muted/40 px-4 py-2.5 flex items-center justify-between cursor-pointer select-none text-xs"
                             @click="showLanguageLimits = !showLanguageLimits">
                            <span class="font-bold text-foreground">
                                Limites Derivados por Linguagem ({{ calibrationResults.language_limits.length }} linguagens)
                            </span>
                            <span class="text-muted-foreground hover:text-foreground font-semibold">
                                {{ showLanguageLimits ? 'Recolher ▲' : 'Expandir ▼' }}
                            </span>
                        </div>

                        <div v-show="showLanguageLimits" class="p-3 border-t border-border overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead class="text-muted-foreground border-b border-border">
                                    <tr>
                                        <th class="py-2 px-3">Linguagem</th>
                                        <th class="py-2 px-3">Extensão</th>
                                        <th class="py-2 px-3">Tempo Máx</th>
                                        <th class="py-2 px-3">Memória Máx</th>
                                        <th class="py-2 px-3">Regra de Derivação</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-border">
                                    <tr v-for="l in calibrationResults.language_limits" :key="l.extension">
                                        <td class="py-2 px-3 font-semibold text-foreground">{{ l.name || l.language }}</td>
                                        <td class="py-2 px-3 font-mono text-muted-foreground">.{{ l.extension }}</td>
                                        <td class="py-2 px-3 font-mono">{{ l.time_limit }}s</td>
                                        <td class="py-2 px-3 font-mono">{{ l.memory_limit }}MB</td>
                                        <td class="py-2 px-3 text-muted-foreground">{{ l.source }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="pt-3 border-t border-border flex justify-between items-center">
                    <button type="button" @click="activeTab = 'tests'" class="button-secondary text-xs">
                        ← Voltar para Casos de Teste
                    </button>
                    <button type="button" @click="activeTab = 'publish'" class="button-primary text-xs flex items-center gap-1.5">
                        <span>Avançar para Publicação</span>
                        <span>→</span>
                    </button>
                </div>
            </div>
        </section>

        <!-- ========================================== -->
        <!-- ABA 5: Finalização & Publicação            -->
        <!-- ========================================== -->
        <section v-show="activeTab === 'publish'" class="space-y-6" aria-labelledby="tab-publish-title">
            <div class="surface p-6 rounded-2xl border space-y-6">
                <div class="pb-3 border-b border-border">
                    <h2 id="tab-publish-title" class="text-base font-bold text-foreground">
                        Checklist de Prontidão & Publicação Oficial
                    </h2>
                    <p class="text-xs text-muted-foreground mt-0.5">
                        Todos os critérios de qualidade abaixo devem ser atendidos antes da liberação para competição ou banco geral.
                    </p>
                </div>

                <!-- Checklist Cards -->
                <div class="space-y-2.5">
                    <!-- 1. Metadados -->
                    <div class="p-3.5 rounded-xl border flex items-center justify-between text-xs"
                         :class="readiness.metadata.ok ? 'bg-emerald-500/5 border-emerald-500/20' : 'bg-muted/30'">
                        <div class="flex items-center gap-3">
                            <span class="w-6 h-6 rounded-full flex items-center justify-center font-bold text-xs"
                                  :class="readiness.metadata.ok ? 'bg-emerald-500 text-white' : 'bg-muted text-muted-foreground'">
                                {{ readiness.metadata.ok ? '✓' : '1' }}
                            </span>
                            <div>
                                <span class="font-semibold text-foreground block">Metadados & Enunciado</span>
                                <span class="text-muted-foreground">{{ readiness.metadata.msg }}</span>
                            </div>
                        </div>
                        <button type="button" @click="activeTab = 'attributes'" class="text-primary hover:underline text-xs">
                            Editar →
                        </button>
                    </div>

                    <!-- 2. Gerador -->
                    <div class="p-3.5 rounded-xl border flex items-center justify-between text-xs"
                         :class="readiness.generator.ok ? 'bg-emerald-500/5 border-emerald-500/20' : 'bg-muted/30'">
                        <div class="flex items-center gap-3">
                            <span class="w-6 h-6 rounded-full flex items-center justify-center font-bold text-xs"
                                  :class="readiness.generator.ok ? 'bg-emerald-500 text-white' : 'bg-muted text-muted-foreground'">
                                {{ readiness.generator.ok ? '✓' : '2' }}
                            </span>
                            <div>
                                <span class="font-semibold text-foreground block">Gerador C++ (gen.cpp)</span>
                                <span class="text-muted-foreground">{{ readiness.generator.msg }}</span>
                            </div>
                        </div>
                        <button type="button" @click="activeTab = 'generator'" class="text-primary hover:underline text-xs">
                            Compilar →
                        </button>
                    </div>

                    <!-- 3. Testes -->
                    <div class="p-3.5 rounded-xl border flex items-center justify-between text-xs"
                         :class="readiness.tests.ok ? 'bg-emerald-500/5 border-emerald-500/20' : 'bg-muted/30'">
                        <div class="flex items-center gap-3">
                            <span class="w-6 h-6 rounded-full flex items-center justify-center font-bold text-xs"
                                  :class="readiness.tests.ok ? 'bg-emerald-500 text-white' : 'bg-muted text-muted-foreground'">
                                {{ readiness.tests.ok ? '✓' : '3' }}
                            </span>
                            <div>
                                <span class="font-semibold text-foreground block">Casos de Teste Gerados</span>
                                <span class="text-muted-foreground">{{ readiness.tests.msg }}</span>
                            </div>
                        </div>
                        <button type="button" @click="activeTab = 'tests'" class="text-primary hover:underline text-xs">
                            Gerar →
                        </button>
                    </div>

                    <!-- 4. ACCEPT -->
                    <div class="p-3.5 rounded-xl border flex items-center justify-between text-xs"
                         :class="readiness.accept.ok ? 'bg-emerald-500/5 border-emerald-500/20' : 'bg-muted/30'">
                        <div class="flex items-center gap-3">
                            <span class="w-6 h-6 rounded-full flex items-center justify-center font-bold text-xs"
                                  :class="readiness.accept.ok ? 'bg-emerald-500 text-white' : 'bg-muted text-muted-foreground'">
                                {{ readiness.accept.ok ? '✓' : '4' }}
                            </span>
                            <div>
                                <span class="font-semibold text-foreground block">Solução Modelo (ACCEPT)</span>
                                <span class="text-muted-foreground">{{ readiness.accept.msg }}</span>
                            </div>
                        </div>
                        <button type="button" @click="activeTab = 'solutions'" class="text-primary hover:underline text-xs">
                            Calibrar →
                        </button>
                    </div>

                    <!-- 5. Calibração -->
                    <div class="p-3.5 rounded-xl border flex items-center justify-between text-xs"
                         :class="readiness.calibrated.ok ? 'bg-emerald-500/5 border-emerald-500/20' : 'bg-muted/30'">
                        <div class="flex items-center gap-3">
                            <span class="w-6 h-6 rounded-full flex items-center justify-center font-bold text-xs"
                                  :class="readiness.calibrated.ok ? 'bg-emerald-500 text-white' : 'bg-muted text-muted-foreground'">
                                {{ readiness.calibrated.ok ? '✓' : '5' }}
                            </span>
                            <div>
                                <span class="font-semibold text-foreground block">Status de Calibração</span>
                                <span class="text-muted-foreground">{{ readiness.calibrated.msg }}</span>
                            </div>
                        </div>
                        <button type="button" @click="activeTab = 'solutions'" class="text-primary hover:underline text-xs">
                            Revisar →
                        </button>
                    </div>
                </div>

                <!-- Publish Action Box -->
                <div class="surface p-5 rounded-xl border flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <span class="text-xs font-semibold text-muted-foreground uppercase tracking-wider block">Destino Final</span>
                        <div class="text-sm font-bold text-foreground mt-0.5">
                            <span v-if="exercise.contest_id">🏆 Competição ID #{{ exercise.contest_id }}</span>
                            <span v-else>🏛️ Banco Geral de Problemas</span>
                        </div>
                        <p class="text-xs text-muted-foreground mt-0.5">
                            Ao publicar, o pacote completo de testes e limites derivados serão integrados atomicamente.
                        </p>
                    </div>

                    <button type="button"
                            id="btn-publish-exercise"
                            @click="runPublishExercise"
                            :disabled="!readiness.allReady || publishing"
                            class="button-primary text-sm flex items-center gap-2"
                            :class="{ 'opacity-50 cursor-not-allowed': !readiness.allReady }">
                        <svg v-if="publishing" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>{{ publishing ? 'Publicando...' : 'Finalizar e Publicar' }}</span>
                    </button>
                </div>
            </div>
        </section>

        <!-- Modal de Inspeção de Teste -->
        <div v-if="showInspectModal"
             class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4"
             role="dialog"
             aria-modal="true"
             @keydown.esc="closeInspectModal">
            <div class="surface max-w-xl w-full rounded-2xl p-6 shadow-2xl border space-y-4">
                <div class="flex items-center justify-between pb-2 border-b border-border">
                    <h3 class="font-bold text-foreground">Caso de Teste #{{ inspectingTest?.number }}</h3>
                    <button type="button" @click="closeInspectModal" class="text-muted-foreground hover:text-foreground text-lg">✕</button>
                </div>
                <div class="space-y-3 text-xs">
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="font-semibold text-muted-foreground">Entrada (.in):</span>
                            <button type="button"
                                    @click="copyToClipboard(inspectingTest?.input || inspectingTest?.preview, 'modal_in')"
                                    class="text-primary hover:underline text-[11px] flex items-center gap-1">
                                {{ copiedKey === 'modal_in' ? '✓ Copiado!' : 'Copiar' }}
                            </button>
                        </div>
                        <pre class="surface p-3 rounded-lg border font-mono max-h-40 overflow-y-auto whitespace-pre-wrap select-all">{{ inspectingTest?.input || inspectingTest?.preview || '(Vazio)' }}</pre>
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="font-semibold text-muted-foreground flex items-center gap-1.5">
                                <span>Saída Oficial (.out):</span>
                                <span v-if="inspectingTest?.output" class="bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 text-[10px] px-1 rounded font-normal">ACCEPT</span>
                            </span>
                            <button v-if="inspectingTest?.output"
                                    type="button"
                                    @click="copyToClipboard(inspectingTest?.output, 'modal_out')"
                                    class="text-primary hover:underline text-[11px] flex items-center gap-1">
                                {{ copiedKey === 'modal_out' ? '✓ Copiado!' : 'Copiar' }}
                            </button>
                        </div>
                        <pre class="surface p-3 rounded-lg border font-mono max-h-40 overflow-y-auto whitespace-pre-wrap select-all"
                             :class="{ 'bg-emerald-500/5 dark:bg-emerald-950/20 border-emerald-500/30 text-emerald-900 dark:text-emerald-200': inspectingTest?.output }">{{ inspectingTest?.output || '(Gerado após calibração ACCEPT)' }}</pre>
                    </div>
                </div>
                <div class="flex justify-end pt-2">
                    <button type="button" @click="closeInspectModal" class="button-secondary text-xs">Fechar</button>
                </div>
            </div>
        </div>

        <!-- Assistente do Gerador -->
        <PolygonGeneratorBuilder v-if="showGeneratorBuilder"
                                 @close="showGeneratorBuilder = false"
                                 @apply="handleApplyGeneratedCode" />
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import PolygonGeneratorBuilder from '../components/PolygonGeneratorBuilder.vue';

const props = defineProps({
    exerciseId: {
        type: [String, Number],
        default: null,
    },
});

// Templates C++ oficiais
const DEFAULT_GENERATOR = `#include "testlib.h"
#include <iostream>

using namespace std;

int main(int argc, char* argv[]) {
    registerGen(argc, argv, 1);
    
    int n = opt<int>("n", 10);
    int maxVal = opt<int>("maxVal", 100);

    cout << n << endl;
    for (int i = 0; i < n; i++) {
        if (i > 0) cout << " ";
        cout << rnd.next(1, maxVal);
    }
    cout << endl;

    return 0;
}
`;

const DEFAULT_ACCEPT = `#include <iostream>

using namespace std;

int main() {
    ios_base::sync_with_stdio(false);
    cin.tie(NULL);

    long long n, m;
    if (cin >> n >> m) {
        if (n % 2 == 0) {
            cout << "PAR\\n";
        } else {
            cout << "IMPAR\\n";
        }
        cout << (n % m) << "\\n";
    }

    return 0;
}
`;

// Estado limpo do Exercício
const exercise = reactive({
    id: props.exerciseId || null,
    contest_id: '',
    name: '',
    short_name: 'A',
    basename: '',
    time_limit: 1,
    memory_limit: 256,
    output_limit: 1024,
    color_name: 'Azul',
    color_hex: '#3B82F6',
    description: '',
    input_description: '',
    output_description: '',
    notes: '',
    generator_source: DEFAULT_GENERATOR,
    generator_script: 'gen 5 10 > $\ngen 10 50 > $\n',
    manual_tests: [],
    solution_accept: DEFAULT_ACCEPT,
    solution_tle: '',
    solution_wrong: '',
    additional_solutions: [],
    status: 'draft',
});

// Abas de navegação
const activeTab = ref('attributes');
const tabs = [
    { id: 'attributes', name: 'Atributos & Enunciado' },
    { id: 'generator', name: 'Gerador (gen.cpp)' },
    { id: 'tests', name: 'Geração de Testes' },
    { id: 'solutions', name: 'Soluções & Calibração' },
    { id: 'publish', name: 'Finalização & Publicação' },
];

const previewMode = ref('split');
const activeSolutionTab = ref('accept');
const showTerminalDrawer = ref(false);
const showLanguageLimits = ref(false);
const availableContests = ref([]);

// Estados de Operação
const loadingExercise = ref(false);
const savingDraft = ref(false);
const compilingGenerator = ref(false);
const generatorCompiled = ref(false);
const generatorCompileSuccess = ref(false);
const generatorLogs = ref('');

const generatingTests = ref(false);
const generatedTests = ref([]);

const inspectingTest = ref(null);
const showInspectModal = ref(false);
const showGeneratorBuilder = ref(false);
const copiedKey = ref(null);

const calibrating = ref(false);
const calibrated = ref(false);
const calibrationResults = reactive({
    status: 'draft',
    summary: null,
    cases: [],
    language_limits: [],
});

const publishing = ref(false);

// Toast
const toast = reactive({
    show: false,
    message: '',
    type: 'success',
});

function showToast(message, type = 'success') {
    toast.message = message;
    toast.type = type;
    toast.show = true;
    setTimeout(() => {
        toast.show = false;
    }, 4000);
}

function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

async function apiRequest(url, options = {}) {
    const defaultHeaders = {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': getCsrfToken(),
    };

    const response = await fetch(url, {
        ...options,
        headers: {
            ...defaultHeaders,
            ...options.headers,
        },
    });

    if (response.status === 204) return null;

    const data = await response.json();
    if (!response.ok) {
        const error = new Error(data.message || `Erro HTTP ${response.status}`);
        error.data = data;
        error.status = response.status;
        throw error;
    }

    return data;
}

function statusLabel(status) {
    switch (status) {
        case 'draft': return 'Rascunho';
        case 'generated': return 'Testes Gerados';
        case 'calibrated': return 'Calibrado';
        case 'published': return 'Publicado';
        default: return status || 'Rascunho';
    }
}

// Checklist de Prontidão Reativo
const readiness = computed(() => {
    const metaOk = Boolean(exercise.name?.trim() && exercise.short_name?.trim() && exercise.basename?.trim());
    const genOk = Boolean(generatorCompiled.value && generatorCompileSuccess.value);
    const testsOk = Boolean(generatedTests.value.length > 0);
    const acceptOk = Boolean(calibrated.value && calibrationResults.summary?.accept_passed);
    const calibOk = Boolean(exercise.status === 'calibrated' || calibrated.value);

    return {
        metadata: { ok: metaOk, msg: metaOk ? 'Nome, sigla e slug configurados' : 'Preencha os campos obrigatórios' },
        generator: { ok: genOk, msg: genOk ? 'Binário gen compilado no sandbox' : 'Compile o gerador gen.cpp' },
        tests: { ok: testsOk, msg: testsOk ? `${generatedTests.value.length} casos gerados` : 'Gere ao menos 1 caso de teste' },
        accept: { ok: acceptOk, msg: acceptOk ? 'ACCEPT passou em 100% dos testes' : 'Execute a calibração com ACCEPT' },
        calibrated: { ok: calibOk, msg: calibOk ? 'Parâmetros de tempo e memória revisados' : 'Calibre os limites' },
        allReady: metaOk && genOk && testsOk && acceptOk && calibOk,
    };
});

function isTabCompleted(tabId) {
    if (tabId === 'attributes') return readiness.value.metadata.ok;
    if (tabId === 'generator') return readiness.value.generator.ok;
    if (tabId === 'tests') return readiness.value.tests.ok;
    if (tabId === 'solutions') return readiness.value.accept.ok;
    if (tabId === 'publish') return readiness.value.allReady;
    return false;
}

// 1. Carregar Exercício Existente
async function loadExercise(id) {
    if (!id) return;
    loadingExercise.value = true;
    try {
        const res = await apiRequest(`/api/backend/polygon/${id}`);
        const data = res.data || res.exercise || res;

        exercise.id = data.id;
        exercise.name = data.name || '';
        exercise.short_name = data.short_name || 'A';
        exercise.basename = data.basename || '';
        exercise.contest_id = data.contest_id ? String(data.contest_id) : '';
        exercise.time_limit = Number(data.time_limit) || 1;
        exercise.memory_limit = Number(data.memory_limit) || 256;
        exercise.output_limit = Number(data.output_limit) || 1024;
        exercise.color_name = data.color_name || 'Azul';
        exercise.color_hex = data.color_hex || '#3B82F6';
        exercise.description = data.description || '';
        exercise.input_description = data.input_description || '';
        exercise.output_description = data.output_description || '';
        exercise.notes = data.notes || '';
        exercise.generator_source = data.generator_source || DEFAULT_GENERATOR;
        exercise.generator_script = data.generator_script || 'gen 5 10 > $\ngen 10 50 > $\n';
        exercise.manual_tests = Array.isArray(data.manual_tests) ? data.manual_tests : [];
        exercise.solution_accept = data.solution_accept || DEFAULT_ACCEPT;
        exercise.solution_tle = data.solution_tle || '';
        exercise.solution_wrong = data.solution_wrong || '';
        exercise.additional_solutions = Array.isArray(data.additional_solutions) ? data.additional_solutions : [];
        exercise.status = data.status || 'draft';

        if (Array.isArray(data.tests) && data.tests.length > 0) {
            generatedTests.value = data.tests;
            generatorCompiled.value = true;
            generatorCompileSuccess.value = true;
        }

        if (data.calibration_results) {
            Object.assign(calibrationResults, data.calibration_results);
            if (calibrationResults.summary?.accept_passed) {
                calibrated.value = true;
            }
            if (Array.isArray(calibrationResults.cases) && generatedTests.value.length > 0) {
                const casesMap = new Map(calibrationResults.cases.map(c => [c.number, c]));
                generatedTests.value = generatedTests.value.map(t => {
                    const match = casesMap.get(t.number);
                    if (match) {
                        return { ...t, output: match.output || t.output, input: match.input || t.input };
                    }
                    return t;
                });
            }
        }

        if (Array.isArray(res.contests)) {
            availableContests.value = res.contests;
        }
    } catch (err) {
        showToast('Não foi possível carregar o exercício: ' + err.message, 'error');
    } finally {
        loadingExercise.value = false;
    }
}

// 2. Salvar Rascunho
async function saveDraft(silent = false) {
    if (!exercise.id) return;
    savingDraft.value = true;
    try {
        const payload = {
            name: exercise.name,
            short_name: exercise.short_name,
            basename: exercise.basename,
            contest_id: exercise.contest_id ? Number(exercise.contest_id) : null,
            time_limit: Number(exercise.time_limit) || 1,
            memory_limit: Number(exercise.memory_limit) || 256,
            output_limit: Number(exercise.output_limit) || 1024,
            description: exercise.description || '',
            input_description: exercise.input_description || '',
            output_description: exercise.output_description || '',
            notes: exercise.notes || '',
            color_name: exercise.color_name || '',
            color_hex: exercise.color_hex || '',
            generator_source: exercise.generator_source || '',
            generator_script: exercise.generator_script || '',
            manual_tests: exercise.manual_tests || [],
            solution_accept: exercise.solution_accept || '',
            solution_tle: exercise.solution_tle || '',
            solution_wrong: exercise.solution_wrong || '',
            additional_solutions: exercise.additional_solutions || [],
        };

        const res = await apiRequest(`/api/backend/polygon/${exercise.id}`, {
            method: 'PUT',
            body: JSON.stringify(payload),
        });

        if (res.data?.status) {
            exercise.status = res.data.status;
        }

        if (!silent) {
            showToast('Rascunho salvo com sucesso!');
        }
    } catch (err) {
        showToast('Erro ao salvar rascunho: ' + err.message, 'error');
        throw err;
    } finally {
        savingDraft.value = false;
    }
}

// 3. Compilar Gerador
async function runCompileGenerator() {
    if (compilingGenerator.value || !exercise.id) return;
    compilingGenerator.value = true;
    showTerminalDrawer.value = true;
    generatorLogs.value = '[Iniciando compilação no Sandbox]\ng++ -O2 -std=c++20 -static -I resources/judge-runtime/include gen.cpp -o gen ...\n';

    try {
        await saveDraft(true);
        const res = await apiRequest(`/api/backend/polygon/${exercise.id}/compile-generator`, { method: 'POST' });
        generatorCompiled.value = Boolean(res.success);
        generatorCompileSuccess.value = Boolean(res.success);
        generatorLogs.value = res.output || (res.success ? 'Compilação concluída com sucesso.' : 'Falha na compilação.');

        if (res.success) {
            showToast('Gerador C++ compilado com sucesso!');
        } else {
            showToast('Falha na compilação do gerador. Verifique o console.', 'error');
        }
    } catch (err) {
        generatorCompiled.value = false;
        generatorCompileSuccess.value = false;
        generatorLogs.value = `[Erro de Execução]: ${err.message}\n${err.data?.output || ''}`;
        showToast('Erro ao compilar: ' + err.message, 'error');
    } finally {
        compilingGenerator.value = false;
    }
}

function restoreDefaultGenerator() {
    exercise.generator_source = DEFAULT_GENERATOR;
    showToast('Template testlib.h padrão restaurado.');
}

// 4. Geração de Testes
async function runGenerateTests() {
    if (generatingTests.value || !exercise.id) return;
    generatingTests.value = true;
    try {
        if (Array.isArray(exercise.manual_tests)) {
            exercise.manual_tests = exercise.manual_tests.filter(t => t.input && t.input.trim() !== '');
        }
        await saveDraft(true);
        const res = await apiRequest(`/api/backend/polygon/${exercise.id}/generate-tests`, { method: 'POST' });
        if (res.success) {
            if (Array.isArray(res.cases)) {
                generatedTests.value = res.cases;
            }
            generatorCompiled.value = true;
            generatorCompileSuccess.value = true;
            exercise.status = 'generated';
            showToast(`${res.total_tests || generatedTests.value.length} casos de teste gerados com sucesso!`);
        } else {
            showToast(res.error || 'Falha ao gerar casos de teste.', 'error');
        }
    } catch (err) {
        showToast('Erro na geração de testes: ' + (err.data?.error || err.message), 'error');
    } finally {
        generatingTests.value = false;
    }
}

function addManualTest() {
    if (!Array.isArray(exercise.manual_tests)) exercise.manual_tests = [];
    exercise.manual_tests.push({
        id: Date.now(),
        is_sample: true,
        input: '',
        output: '',
    });
}

function removeManualTest(index) {
    exercise.manual_tests.splice(index, 1);
}

function appendBatchTests() {
    let currentScript = exercise.generator_script || '';
    const lines = currentScript.split('\n');
    let maxSeed = 0;
    lines.forEach(l => {
        const m = l.match(/\bgen\s+(?:-\w+=\S+\s+)*(\d+)\s*>/);
        if (m) {
            maxSeed = Math.max(maxSeed, parseInt(m[1], 10));
        }
    });

    let addition = currentScript && !currentScript.endsWith('\n') ? '\n' : '';
    for (let i = 1; i <= 10; i++) {
        addition += `gen ${maxSeed + i} > $\n`;
    }
    exercise.generator_script = (currentScript + addition).trimStart();
    showToast('10 testes adicionados ao script.');
}

function openInspectModal(test) {
    inspectingTest.value = test;
    showInspectModal.value = true;
}

function closeInspectModal() {
    showInspectModal.value = false;
    inspectingTest.value = null;
}

function handleApplyGeneratedCode({ code, suggestedScript }) {
    if (code) {
        exercise.generator_source = code;
    }
    if (suggestedScript && (!exercise.generator_script || exercise.generator_script === 'gen 5 10 > $\ngen 10 50 > $\n')) {
        exercise.generator_script = suggestedScript;
    }
    generatorCompiled.value = false;
    showGeneratorBuilder.value = false;
    showToast('Código do gerador aplicado no editor.');
}

function copyToClipboard(text, key) {
    if (!text) return;
    if (navigator?.clipboard?.writeText) {
        navigator.clipboard.writeText(text).then(() => {
            copiedKey.value = key;
            setTimeout(() => {
                if (copiedKey.value === key) copiedKey.value = null;
            }, 2000);
        }).catch(() => {
            fallbackCopy(text, key);
        });
    } else {
        fallbackCopy(text, key);
    }
}

function fallbackCopy(text, key) {
    try {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        copiedKey.value = key;
        setTimeout(() => {
            if (copiedKey.value === key) copiedKey.value = null;
        }, 2000);
    } catch (_) {}
}

// 5. Calibração
async function runCalibration() {
    if (calibrating.value || !exercise.id) return;
    if (generatedTests.value.length === 0) {
        showToast('Gere os casos de teste antes de executar a calibração.', 'error');
        activeTab.value = 'tests';
        return;
    }
    if (!exercise.solution_accept?.trim()) {
        showToast('A solução modelo ACCEPT é obrigatória para calibração.', 'error');
        activeSolutionTab.value = 'accept';
        return;
    }

    calibrating.value = true;
    try {
        await saveDraft(true);
        const res = await apiRequest(`/api/backend/polygon/${exercise.id}/calibrate`, { method: 'POST' });
        const data = res.data || res;

        if (data.status === 'calibrated' || data.summary?.accept_passed) {
            calibrated.value = true;
            exercise.status = 'calibrated';
            Object.assign(calibrationResults, data);

            // Sincronizar outputs com generatedTests para refletir no modal e listagem de testes
            if (Array.isArray(data.cases) && generatedTests.value.length > 0) {
                const casesMap = new Map(data.cases.map(c => [c.number, c]));
                generatedTests.value = generatedTests.value.map(t => {
                    const match = casesMap.get(t.number);
                    if (match) {
                        return { ...t, output: match.output || t.output, input: match.input || t.input };
                    }
                    return t;
                });
            }

            showToast('Calibração concluída com sucesso!');
        } else {
            const errorMsg = data.message || data.error || 'ACCEPT falhou na calibração. Verifique os erros.';
            showToast(errorMsg, 'error');
        }
    } catch (err) {
        showToast('Erro ao calibrar soluções: ' + (err.data?.message || err.message), 'error');
    } finally {
        calibrating.value = false;
    }
}

// 6. Publicação
async function runPublishExercise() {
    if (publishing.value || !exercise.id || !readiness.value.allReady) return;
    publishing.value = true;
    try {
        await saveDraft(true);
        const res = await apiRequest(`/api/backend/polygon/${exercise.id}/publish`, { method: 'POST' });
        exercise.status = 'published';
        showToast('Problema publicado com sucesso!', 'success');

        setTimeout(() => {
            if (res.target === 'contest' && res.problem_id) {
                window.location.href = `/backend/exercises`;
            } else {
                window.location.href = `/backend/polygon`;
            }
        }, 1200);
    } catch (err) {
        showToast('Erro ao publicar exercício: ' + err.message, 'error');
    } finally {
        publishing.value = false;
    }
}

onMounted(async () => {
    // Redireciona para o seletor se nenhum ID foi fornecido
    if (!props.exerciseId) {
        window.location.href = '/backend/polygon';
        return;
    }

    // Busca competições ativas
    try {
        const res = await apiRequest('/api/backend/polygon');
        if (Array.isArray(res.contests)) {
            availableContests.value = res.contests;
        }
    } catch (_) {}

    // Carrega exercício
    await loadExercise(props.exerciseId);
});
</script>
