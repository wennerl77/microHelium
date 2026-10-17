<template>
    <div class="polygon-dashboard feature-stack space-y-6">
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

        <!-- Top Header & Primary Action -->
        <header class="surface p-6 rounded-2xl border flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-primary/10 text-primary font-bold text-lg">
                    ⬡
                </span>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-foreground">
                        Polygon — Seletor de Exercícios
                    </h1>
                    <p class="text-sm text-muted-foreground mt-0.5">
                        Gerencie rascunhos em desenvolvimento ou consulte problemas já publicados.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="/backend/exercises" class="button-secondary text-sm">
                    ← Gerenciar Problemas
                </a>
                <button type="button"
                        id="btn-open-new-modal"
                        @click="openCreateModal"
                        class="button-primary flex items-center gap-2">
                    <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>+ Novo Exercício</span>
                </button>
            </div>
        </header>

        <!-- Barra de Busca Geral (Opcional) -->
        <div v-if="drafts.length > 3 || published.length > 3" class="surface p-3.5 rounded-xl border flex items-center gap-3">
            <span class="pl-2 text-muted-foreground">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </span>
            <input type="text"
                   id="filter-search"
                   v-model="searchQuery"
                   @input="debounceSearch"
                   placeholder="Buscar por nome, sigla ou slug..."
                   class="w-full text-sm bg-transparent text-foreground placeholder:text-muted-foreground focus:outline-none" />
            <button v-if="searchQuery"
                    type="button"
                    @click="searchQuery = ''; fetchExercises()"
                    class="text-xs text-muted-foreground hover:text-foreground px-2">
                Limpar
            </button>
        </div>

        <!-- ======================================================== -->
        <!-- PARTE DE CIMA: RASCUNHOS EM ANDAMENTO                    -->
        <!-- ======================================================== -->
        <div id="section-drafts" class="surface p-6 rounded-2xl border space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-border gap-3">
                <div class="flex items-center gap-3">
                    <span class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400 font-bold flex items-center justify-center text-sm">
                        ✏️
                    </span>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-lg font-bold text-foreground">Rascunhos no Polygon</h2>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                                {{ drafts.length }} {{ drafts.length === 1 ? 'em andamento' : 'em andamento' }}
                            </span>
                        </div>
                        <p class="text-xs text-muted-foreground mt-0.5">
                            Exercícios em desenvolvimento que ainda não foram finalizados ou publicados.
                        </p>
                    </div>
                </div>

                <button type="button"
                        @click="openCreateModal"
                        class="button-primary text-xs py-2 px-3.5 inline-flex items-center gap-1.5 self-start sm:self-auto">
                    <span>+ Novo Exercício</span>
                </button>
            </div>

            <!-- Loading State -->
            <div v-if="loading" class="p-8 text-center text-muted-foreground">
                <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-primary mb-2"></div>
                <p class="text-xs">Carregando rascunhos...</p>
            </div>

            <!-- Empty State Rascunhos -->
            <div v-else-if="drafts.length === 0" class="p-8 text-center border border-dashed rounded-xl space-y-3 bg-muted/10">
                <div class="w-12 h-12 rounded-xl bg-muted/40 flex items-center justify-center mx-auto text-muted-foreground text-xl">
                    📝
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-foreground">Nenhum rascunho em andamento</h3>
                    <p class="text-xs text-muted-foreground mt-0.5 max-w-sm mx-auto">
                        Inicie um novo exercício com testes determinísticos em C++ (testlib.h) e calibração de limites.
                    </p>
                </div>
                <button type="button"
                        @click="openCreateModal"
                        class="button-primary text-xs inline-flex items-center gap-1.5 mt-1">
                    <span>+ Iniciar Novo Exercício</span>
                </button>
            </div>

            <!-- Grid de Cards de Rascunhos -->
            <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4" id="drafts-cards">
                <div v-for="item in drafts"
                     :key="item.id"
                     @click="goToExercise(item.id)"
                     role="button"
                     tabindex="0"
                     @keydown.enter="goToExercise(item.id)"
                     class="exercise-card surface p-5 rounded-xl border border-border hover:border-primary/50 hover:shadow-md transition-all duration-200 cursor-pointer flex flex-col justify-between group relative"
                     :data-exercise-id="item.id">
                    
                    <!-- Topo do Card: Balão/Sigla + Status Pill + Botão Excluir -->
                    <div class="flex items-start justify-between gap-2 mb-3">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg font-bold text-xs shadow-xs text-white shrink-0 ring-1 ring-black/10"
                                  :style="{ backgroundColor: item.color_hex || '#3B82F6' }"
                                  :title="`Balão: ${item.color_name || 'Azul'}`">
                                {{ item.short_name || '?' }}
                            </span>
                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-2xs font-semibold"
                                  :class="{
                                      'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300': item.status === 'draft',
                                      'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300': item.status === 'generated',
                                      'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300': item.status === 'calibrated',
                                  }">
                                <span class="w-1.5 h-1.5 rounded-full"
                                      :class="{
                                          'bg-amber-500': item.status === 'draft',
                                          'bg-blue-500': item.status === 'generated',
                                          'bg-purple-500': item.status === 'calibrated',
                                      }"></span>
                                {{ statusLabel(item.status) }}
                            </span>
                        </div>

                        <!-- Botão Excluir Discreto (não dispara navegação do card) -->
                        <button type="button"
                                @click.stop.prevent="confirmDelete(item)"
                                class="text-muted-foreground hover:text-destructive p-1 rounded-md transition-colors opacity-70 group-hover:opacity-100"
                                title="Excluir rascunho"
                                aria-label="Excluir rascunho">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>

                    <!-- Miolo do Card: Nome e Slug -->
                    <div class="space-y-1 mb-4 flex-1">
                        <h3 class="font-bold text-foreground text-sm group-hover:text-primary transition-colors line-clamp-2">
                            <a :href="`/backend/polygon/${item.id}`" @click.stop class="hover:underline">
                                {{ item.name }}
                            </a>
                        </h3>
                        <p class="text-xs text-muted-foreground font-mono truncate" :title="item.basename">
                            {{ item.basename }}
                        </p>
                    </div>

                    <!-- Rodapé do Card: Chips de Metadados e Ação -->
                    <div class="pt-3 border-t border-border/60 space-y-2.5">
                        <div class="flex flex-wrap items-center gap-1.5 text-xs">
                            <span v-if="item.contest"
                                  class="inline-flex items-center gap-1 text-2xs text-primary bg-primary/10 px-2 py-0.5 rounded-md font-medium truncate max-w-[150px]"
                                  :title="item.contest.name">
                                🏆 {{ item.contest.name }}
                            </span>
                            <span v-else
                                  class="inline-flex items-center gap-1 text-2xs text-muted-foreground bg-muted px-2 py-0.5 rounded-md font-medium">
                                🏛️ Banco Geral
                            </span>
                            <span class="inline-flex items-center gap-1 text-2xs text-muted-foreground bg-muted/60 px-2 py-0.5 rounded-md font-mono">
                                ⏱️ {{ item.time_limit }}s · 💾 {{ item.memory_limit }}MB
                            </span>
                        </div>

                        <div class="flex items-center justify-between text-xs text-primary font-medium pt-1">
                            <span>Continuar Editando</span>
                            <span class="transform group-hover:translate-x-1 transition-transform">→</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- PARTE DE BAIXO (DIV SEPARADA): EXERCÍCIOS JÁ CRIADOS     -->
        <!-- ======================================================== -->
        <div id="section-published" class="surface p-6 rounded-2xl border space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-border gap-3">
                <div class="flex items-center gap-3">
                    <span class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-bold flex items-center justify-center text-sm">
                        ✓
                    </span>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-lg font-bold text-foreground">Exercícios Já Criados</h2>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                                {{ published.length }} {{ published.length === 1 ? 'publicado' : 'publicados' }}
                            </span>
                        </div>
                        <p class="text-xs text-muted-foreground mt-0.5">
                            Problemas que concluíram o ciclo de autoria e calibração e foram integrados ao sistema.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Loading State -->
            <div v-if="loading" class="p-8 text-center text-muted-foreground">
                <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-primary mb-2"></div>
                <p class="text-xs">Carregando exercícios criados...</p>
            </div>

            <!-- Empty State Publicados -->
            <div v-else-if="published.length === 0" class="p-8 text-center border border-dashed rounded-xl space-y-2 bg-muted/10">
                <div class="w-12 h-12 rounded-xl bg-muted/40 flex items-center justify-center mx-auto text-muted-foreground text-xl">
                    📦
                </div>
                <h3 class="text-sm font-semibold text-foreground">Nenhum exercício publicado ainda</h3>
                <p class="text-xs text-muted-foreground max-w-sm mx-auto">
                    Quando você finalizar um rascunho na Etapa 5 (Finalização & Publicação), o problema publicado aparecerá aqui.
                </p>
            </div>

            <!-- Grid de Cards de Exercícios Criados/Publicados -->
            <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4" id="published-cards">
                <div v-for="item in published"
                     :key="item.id"
                     @click="goToExercise(item.id)"
                     role="button"
                     tabindex="0"
                     @keydown.enter="goToExercise(item.id)"
                     class="published-card surface p-5 rounded-xl border border-border hover:border-emerald-500/50 hover:shadow-md transition-all duration-200 cursor-pointer flex flex-col justify-between group"
                     :data-exercise-id="item.id">
                    
                    <!-- Topo do Card: Balão/Sigla + Status Publicado -->
                    <div class="flex items-start justify-between gap-2 mb-3">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg font-bold text-xs shadow-xs text-white shrink-0 ring-1 ring-black/10"
                              :style="{ backgroundColor: item.color_hex || '#10B981' }"
                              :title="`Balão: ${item.color_name || 'Verde'}`">
                            {{ item.short_name || '?' }}
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-2xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            Publicado
                        </span>
                    </div>

                    <!-- Miolo do Card: Nome e Slug/Código -->
                    <div class="space-y-1 mb-4 flex-1">
                        <h3 class="font-bold text-foreground text-sm group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors line-clamp-2">
                            <a :href="`/backend/polygon/${item.id}`" @click.stop class="hover:underline">
                                {{ item.name }}
                            </a>
                        </h3>
                        <p class="text-xs text-muted-foreground font-mono truncate" :title="item.published_bank_code || item.basename">
                            {{ item.published_bank_code || item.basename }}
                        </p>
                    </div>

                    <!-- Rodapé do Card: Chips de Metadados e Ação -->
                    <div class="pt-3 border-t border-border/60 space-y-2.5">
                        <div class="flex flex-wrap items-center gap-1.5 text-xs">
                            <span v-if="item.contest"
                                  class="inline-flex items-center gap-1 text-2xs text-emerald-800 dark:text-emerald-300 bg-emerald-100 dark:bg-emerald-900/40 px-2 py-0.5 rounded-md font-medium truncate max-w-[150px]"
                                  :title="item.contest.name">
                                🏆 {{ item.contest.name }}
                            </span>
                            <span v-else
                                  class="inline-flex items-center gap-1 text-2xs text-blue-800 dark:text-blue-300 bg-blue-100 dark:bg-blue-900/40 px-2 py-0.5 rounded-md font-medium">
                                🏛️ Banco Geral ({{ item.published_bank_code || item.basename }})
                            </span>
                            <span class="inline-flex items-center gap-1 text-2xs text-muted-foreground bg-muted/60 px-2 py-0.5 rounded-md font-mono">
                                ⏱️ {{ item.time_limit }}s · 💾 {{ item.memory_limit }}MB
                            </span>
                        </div>

                        <div class="flex items-center justify-between text-xs text-emerald-600 dark:text-emerald-400 font-medium pt-1">
                            <span>Abrir Workspace</span>
                            <span class="transform group-hover:translate-x-1 transition-transform">→</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal: Novo Exercício -->
        <div v-if="createModal.show"
             class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-xs flex items-center justify-center p-4"
             role="dialog"
             aria-modal="true"
             aria-labelledby="modal-title"
             @keydown.esc="closeCreateModal">
            <div class="surface max-w-lg w-full rounded-2xl p-6 shadow-2xl border space-y-5 animate-in fade-in zoom-in-95 duration-150">
                <div class="flex items-center justify-between pb-3 border-b border-border">
                    <div class="flex items-center gap-2.5">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-primary/10 text-primary font-bold text-sm">
                            +
                        </span>
                        <div>
                            <h2 id="modal-title" class="text-lg font-bold text-foreground">Novo Exercício no Polygon</h2>
                            <p class="text-xs text-muted-foreground">Configure os atributos iniciais para abrir o estúdio de autoria.</p>
                        </div>
                    </div>
                    <button type="button"
                            @click="closeCreateModal"
                            class="text-muted-foreground hover:text-foreground p-1 rounded-md text-lg"
                            aria-label="Fechar modal">✕</button>
                </div>

                <form @submit.prevent="submitCreateExercise" class="space-y-4">
                    <!-- Nome do Exercício -->
                    <div>
                        <label for="create-name" class="block text-xs font-semibold text-foreground mb-1">
                            Nome do Exercício <span class="text-destructive">*</span>
                        </label>
                        <input type="text"
                               id="create-name"
                               ref="createNameInput"
                               v-model="createForm.name"
                               @input="onNameChange"
                               placeholder="Ex: Resto da Divisão, Soma Máxima..."
                               required
                               class="w-full px-3 py-2 text-sm border rounded-lg bg-background text-foreground border-input focus:ring-2 focus:ring-ring focus:outline-none" />
                    </div>

                    <!-- Sigla e Slug -->
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label for="create-short-name" class="block text-xs font-semibold text-foreground mb-1">
                                Sigla / Letra <span class="text-destructive">*</span>
                            </label>
                            <input type="text"
                                   id="create-short-name"
                                   v-model="createForm.short_name"
                                   placeholder="A"
                                   maxlength="10"
                                   required
                                   class="w-full px-3 py-2 text-sm uppercase text-center font-bold border rounded-lg bg-background text-foreground border-input focus:ring-2 focus:ring-ring focus:outline-none" />
                        </div>
                        <div class="col-span-2">
                            <label for="create-basename" class="block text-xs font-semibold text-foreground mb-1">
                                Identificador em Disco (Slug) <span class="text-destructive">*</span>
                            </label>
                            <input type="text"
                                   id="create-basename"
                                   v-model="createForm.basename"
                                   placeholder="resto-divisao"
                                   required
                                   class="w-full px-3 py-2 text-sm font-mono border rounded-lg bg-background text-foreground border-input focus:ring-2 focus:ring-ring focus:outline-none" />
                        </div>
                    </div>

                    <!-- Destino (Contest ou Banco Geral) -->
                    <div>
                        <label for="create-contest" class="block text-xs font-semibold text-foreground mb-1">
                            Destino do Problema
                        </label>
                        <select id="create-contest"
                                v-model="createForm.contest_id"
                                class="w-full px-3 py-2 text-sm border rounded-lg bg-background text-foreground border-input focus:ring-2 focus:ring-ring focus:outline-none">
                            <option value="">(Nenhuma - Salvar apenas no Banco de Problemas Geral)</option>
                            <option v-for="c in contests" :key="c.id" :value="String(c.id)">
                                🏆 {{ c.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Limites Base -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="create-time-limit" class="block text-xs font-semibold text-foreground mb-1">
                                Tempo Base (segundos)
                            </label>
                            <input type="number"
                                   id="create-time-limit"
                                   v-model.number="createForm.time_limit"
                                   min="1"
                                   max="60"
                                   class="w-full px-3 py-2 text-sm border rounded-lg bg-background text-foreground border-input focus:ring-2 focus:ring-ring focus:outline-none" />
                        </div>
                        <div>
                            <label for="create-memory-limit" class="block text-xs font-semibold text-foreground mb-1">
                                Memória Base (MB)
                            </label>
                            <input type="number"
                                   id="create-memory-limit"
                                   v-model.number="createForm.memory_limit"
                                   min="16"
                                   max="2048"
                                   class="w-full px-3 py-2 text-sm border rounded-lg bg-background text-foreground border-input focus:ring-2 focus:ring-ring focus:outline-none" />
                        </div>
                    </div>

                    <!-- Cor do Balão -->
                    <div>
                        <label class="block text-xs font-semibold text-foreground mb-1">
                            Cor do Balão (Maratona)
                        </label>
                        <div class="flex items-center gap-3">
                            <input type="color"
                                   id="create-color-hex"
                                   v-model="createForm.color_hex"
                                   class="w-10 h-10 p-0.5 rounded-lg border border-input cursor-pointer bg-background" />
                            <input type="text"
                                   id="create-color-name"
                                   v-model="createForm.color_name"
                                   placeholder="Ex: Azul, Vermelho..."
                                   class="flex-1 px-3 py-2 text-sm border rounded-lg bg-background text-foreground border-input focus:ring-2 focus:ring-ring focus:outline-none" />
                        </div>
                    </div>

                    <div class="pt-3 border-t border-border flex items-center justify-end gap-3">
                        <button type="button"
                                @click="closeCreateModal"
                                class="button-secondary text-sm">
                            Cancelar
                        </button>
                        <button type="submit"
                                id="btn-submit-new-exercise"
                                @click="submitCreateExercise"
                                :disabled="createModal.submitting"
                                class="button-primary text-sm flex items-center gap-2">
                            <svg v-if="createModal.submitting" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>{{ createModal.submitting ? 'Criando...' : 'Criar e Abrir Workspace' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Confirmação de Exclusão -->
        <div v-if="deleteModal.show"
             class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4"
             role="alertdialog"
             aria-modal="true"
             aria-labelledby="delete-title">
            <div class="surface max-w-md w-full rounded-2xl p-6 shadow-2xl border space-y-4">
                <div class="flex items-center gap-3 text-destructive">
                    <span class="w-10 h-10 rounded-full bg-destructive/10 flex items-center justify-center text-lg font-bold">
                        ⚠️
                    </span>
                    <div>
                        <h3 id="delete-title" class="font-bold text-foreground">Excluir Exercício?</h3>
                        <p class="text-xs text-muted-foreground">Esta ação não pode ser desfeita.</p>
                    </div>
                </div>
                <p class="text-sm text-foreground">
                    Tem certeza de que deseja excluir permanentemente o rascunho de <strong class="text-foreground">"{{ deleteModal.exercise?.name }}"</strong> e todos os seus arquivos temporários?
                </p>
                <div class="flex items-center justify-end gap-3 pt-3 border-t border-border">
                    <button type="button"
                            @click="deleteModal.show = false"
                            class="button-secondary text-sm">
                        Cancelar
                    </button>
                    <button type="button"
                            @click="executeDelete"
                            :disabled="deleteModal.deleting"
                            class="button-danger text-sm flex items-center gap-2">
                        <span>{{ deleteModal.deleting ? 'Excluindo...' : 'Sim, Excluir' }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, nextTick } from 'vue';

// Template base C++ com testlib.h
const DEFAULT_GENERATOR_CODE = `#include "testlib.h"
#include <iostream>

using namespace std;

int main(int argc, char* argv[]) {
    registerGen(argc, argv, 1);
    
    // Parâmetros via linha de comando
    int n = opt<int>(1, 10);
    int maxVal = opt<int>(2, 100);

    cout << n << endl;
    for (int i = 0; i < n; i++) {
        if (i > 0) cout << " ";
        cout << rnd.next(1, maxVal);
    }
    cout << endl;

    return 0;
}
`;

// Estado
const loading = ref(false);
const rawExercises = ref([]);
const contests = ref([]);

const searchQuery = ref('');
let searchTimeout = null;

// Rascunhos (status !== 'published')
const drafts = computed(() => {
    let list = rawExercises.value.filter(e => e.status !== 'published');
    if (searchQuery.value.trim()) {
        const q = searchQuery.value.toLowerCase().trim();
        list = list.filter(e =>
            (e.name || '').toLowerCase().includes(q) ||
            (e.short_name || '').toLowerCase().includes(q) ||
            (e.basename || '').toLowerCase().includes(q)
        );
    }
    return list;
});

// Publicados (status === 'published')
const published = computed(() => {
    let list = rawExercises.value.filter(e => e.status === 'published');
    if (searchQuery.value.trim()) {
        const q = searchQuery.value.toLowerCase().trim();
        list = list.filter(e =>
            (e.name || '').toLowerCase().includes(q) ||
            (e.short_name || '').toLowerCase().includes(q) ||
            (e.basename || '').toLowerCase().includes(q)
        );
    }
    return list;
});

// Modal Novo Exercício
const createNameInput = ref(null);
const createModal = reactive({
    show: false,
    submitting: false,
});
const createForm = reactive({
    name: '',
    short_name: 'A',
    basename: '',
    contest_id: '',
    time_limit: 1,
    memory_limit: 256,
    color_name: 'Azul',
    color_hex: '#3B82F6',
});

// Modal Exclusão
const deleteModal = reactive({
    show: false,
    deleting: false,
    exercise: null,
});

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

// Utilitário de requisição com CSRF
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

    if (response.status === 204) {
        return null;
    }

    const data = await response.json();
    if (!response.ok) {
        const error = new Error(data.message || `Erro HTTP ${response.status}`);
        error.data = data;
        error.status = response.status;
        throw error;
    }

    return data;
}

// Formatação de status de rascunho
function statusLabel(status) {
    switch (status) {
        case 'draft': return 'Rascunho';
        case 'generated': return 'Testes OK';
        case 'calibrated': return 'Calibrado';
        case 'published': return 'Publicado';
        default: return status || 'Rascunho';
    }
}

// Navegação direta ao clicar no Card
function goToExercise(id) {
    if (!id) return;
    window.location.href = `/backend/polygon/${id}`;
}

// Auto slugify
function slugify(text) {
    return text
        .toString()
        .toLowerCase()
        .trim()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/\s+/g, '-')
        .replace(/[^\w\-]+/g, '')
        .replace(/\-\-+/g, '-')
        .substring(0, 50);
}

function onNameChange() {
    if (!createForm.basename || createForm.basename === slugify(createForm.name.slice(0, -1))) {
        createForm.basename = slugify(createForm.name);
    }
}

function debounceSearch() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        // busca local via computed drafts e published
    }, 200);
}

// Carregar lista de exercícios
async function fetchExercises() {
    loading.value = true;
    try {
        const res = await apiRequest('/api/backend/polygon');
        rawExercises.value = res.exercises || res.data || [];
        if (Array.isArray(res.contests) && res.contests.length > 0) {
            contests.value = res.contests;
        }
    } catch (err) {
        showToast('Não foi possível carregar os exercícios: ' + err.message, 'error');
    } finally {
        loading.value = false;
    }
}

// Modal de Criação
function openCreateModal() {
    createForm.name = '';
    createForm.short_name = 'A';
    createForm.basename = '';
    createForm.contest_id = '';
    createForm.time_limit = 1;
    createForm.memory_limit = 256;
    createForm.color_name = 'Azul';
    createForm.color_hex = '#3B82F6';
    createModal.show = true;
    nextTick(() => {
        createNameInput.value?.focus();
    });
}

function closeCreateModal() {
    createModal.show = false;
}

async function submitCreateExercise() {
    if (!createForm.name || !createForm.short_name || !createForm.basename) {
        showToast('Preencha os campos obrigatórios.', 'error');
        return;
    }

    createModal.submitting = true;
    try {
        const payload = {
            name: createForm.name.trim(),
            short_name: createForm.short_name.trim().toUpperCase(),
            basename: createForm.basename.trim().toLowerCase(),
            contest_id: createForm.contest_id ? Number(createForm.contest_id) : null,
            time_limit: Number(createForm.time_limit) || 1,
            memory_limit: Number(createForm.memory_limit) || 256,
            output_limit: 1024,
            color_name: createForm.color_name || 'Azul',
            color_hex: createForm.color_hex || '#3B82F6',
            description: `# ${createForm.name}\n\nDescreva o problema aqui.`,
            generator_source: DEFAULT_GENERATOR_CODE,
            generator_script: 'gen 5 10 > $\ngen 10 50 > $\n',
            manual_tests: [],
            solution_accept: '',
        };

        const res = await apiRequest('/api/backend/polygon', {
            method: 'POST',
            body: JSON.stringify(payload),
        });

        const createdId = res.data?.id || res.id || res.exercise?.id;
        showToast('Exercício criado com sucesso! Abrindo workspace...', 'success');
        closeCreateModal();

        if (createdId) {
            window.location.href = `/backend/polygon/${createdId}`;
        } else {
            await fetchExercises();
        }
    } catch (err) {
        showToast('Erro ao criar exercício: ' + (err.data?.message || err.message), 'error');
    } finally {
        createModal.submitting = false;
    }
}

// Exclusão
function confirmDelete(exercise) {
    deleteModal.exercise = exercise;
    deleteModal.show = true;
}

async function executeDelete() {
    if (!deleteModal.exercise) return;
    deleteModal.deleting = true;
    try {
        await apiRequest(`/api/backend/polygon/${deleteModal.exercise.id}`, {
            method: 'DELETE',
        });
        showToast(`Exercício "${deleteModal.exercise.name}" excluído.`);
        deleteModal.show = false;
        await fetchExercises();
    } catch (err) {
        showToast('Erro ao excluir exercício: ' + err.message, 'error');
    } finally {
        deleteModal.deleting = false;
    }
}

onMounted(() => {
    fetchExercises();
});
</script>
