<template>
    <div class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-5"
         role="dialog"
         aria-modal="true"
         aria-labelledby="generator-builder-title"
         @keydown.esc="$emit('close')">
        <div class="surface max-w-5xl w-full max-h-[92vh] flex flex-col rounded-2xl border shadow-2xl overflow-hidden text-xs">
            <!-- Cabeçalho -->
            <div class="flex items-center justify-between px-5 py-3 border-b border-border bg-card">
                <h3 id="generator-builder-title" class="font-bold text-sm text-foreground">
                    Assistente de Gerador (gen.cpp)
                </h3>
                <button type="button"
                        id="btn-close-builder"
                        @click="$emit('close')"
                        class="text-muted-foreground hover:text-foreground text-base px-1 leading-none"
                        aria-label="Fechar">
                    ✕
                </button>
            </div>

            <!-- Corpo em 2 colunas -->
            <div class="grid grid-cols-1 lg:grid-cols-2 divide-y lg:divide-y-0 lg:divide-x divide-border flex-1 overflow-y-auto">
                <!-- Coluna 1: Controles -->
                <div class="p-5 space-y-5 overflow-y-auto">
                    <!-- 1. Variáveis Iniciais -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-foreground">Variáveis Iniciais</span>
                            <button type="button"
                                    id="btn-add-var"
                                    @click="addVariable"
                                    class="button-secondary text-[11px] py-0.5 px-2">
                                + Variável
                            </button>
                        </div>

                        <div class="space-y-1.5">
                            <div v-for="(v, idx) in config.variables" :key="v.id" class="flex items-center gap-2">
                                <input type="text"
                                       v-model="v.name"
                                       placeholder="Nome"
                                       class="w-16 px-2 py-1 font-mono border rounded bg-background text-foreground uppercase"
                                       maxlength="4" />
                                <span class="text-muted-foreground">de</span>
                                <input type="number"
                                       v-model.number="v.min"
                                       class="w-20 px-2 py-1 font-mono border rounded bg-background text-foreground" />
                                <span class="text-muted-foreground">a</span>
                                <input type="number"
                                       v-model.number="v.max"
                                       class="w-24 px-2 py-1 font-mono border rounded bg-background text-foreground" />
                                <button type="button"
                                        @click="removeVariable(idx)"
                                        :disabled="config.variables.length <= 1"
                                        class="text-destructive hover:opacity-80 p-1 disabled:opacity-30"
                                        title="Remover variável">
                                    ✕
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Matriz / Vetor -->
                    <div class="space-y-3 pt-3 border-t border-border">
                        <label class="flex items-center gap-2 cursor-pointer select-none font-bold text-foreground">
                            <input type="checkbox"
                                   id="chk-has-matrix"
                                   v-model="config.hasMatrix"
                                   class="rounded border-input text-primary" />
                            <span>Gerar Matriz / Vetor</span>
                        </label>

                        <div v-if="config.hasMatrix" class="space-y-3 pl-5 border-l-2 border-border">
                            <!-- Tipo -->
                            <div class="flex items-center gap-4">
                                <label class="flex items-center gap-1.5 cursor-pointer">
                                    <input type="radio" value="matrix" v-model="config.matrixType" name="matrixType" />
                                    <span>Matriz</span>
                                </label>
                                <label class="flex items-center gap-1.5 cursor-pointer">
                                    <input type="radio" value="vector" v-model="config.matrixType" name="matrixType" />
                                    <span>Vetor (1 × N)</span>
                                </label>
                            </div>

                            <!-- Dimensões da Matriz -->
                            <div v-if="config.matrixType === 'matrix'" class="space-y-2">
                                <div class="flex items-center gap-2">
                                    <span class="w-14 text-muted-foreground">Linhas:</span>
                                    <select v-model="config.rowsMode" class="px-2 py-1 border rounded bg-background text-foreground">
                                        <option value="var">Variável</option>
                                        <option value="fixed">Fixo</option>
                                    </select>
                                    <select v-if="config.rowsMode === 'var'" v-model="config.rowsVar" class="px-2 py-1 border rounded bg-background text-foreground font-mono">
                                        <option v-for="v in config.variables" :key="v.id" :value="v.name">{{ v.name }}</option>
                                    </select>
                                    <input v-else type="number" v-model.number="config.rowsFixed" min="1" class="w-20 px-2 py-1 border rounded bg-background text-foreground font-mono" />
                                </div>

                                <div class="flex items-center gap-2">
                                    <span class="w-14 text-muted-foreground">Colunas:</span>
                                    <select v-model="config.colsMode" class="px-2 py-1 border rounded bg-background text-foreground">
                                        <option value="var">Variável</option>
                                        <option value="fixed">Fixo</option>
                                    </select>
                                    <select v-if="config.colsMode === 'var'" v-model="config.colsVar" class="px-2 py-1 border rounded bg-background text-foreground font-mono">
                                        <option v-for="v in config.variables" :key="v.id" :value="v.name">{{ v.name }}</option>
                                    </select>
                                    <input v-else type="number" v-model.number="config.colsFixed" min="1" class="w-20 px-2 py-1 border rounded bg-background text-foreground font-mono" />
                                </div>
                            </div>

                            <!-- Dimensões do Vetor -->
                            <div v-else class="flex items-center gap-2">
                                <span class="w-14 text-muted-foreground">Tamanho:</span>
                                <select v-model="config.vectorLenMode" class="px-2 py-1 border rounded bg-background text-foreground">
                                    <option value="var">Variável</option>
                                    <option value="fixed">Fixo</option>
                                </select>
                                <select v-if="config.vectorLenMode === 'var'" v-model="config.vectorLenVar" class="px-2 py-1 border rounded bg-background text-foreground font-mono">
                                    <option v-for="v in config.variables" :key="v.id" :value="v.name">{{ v.name }}</option>
                                </select>
                                <input v-else type="number" v-model.number="config.vectorLenFixed" min="1" class="w-20 px-2 py-1 border rounded bg-background text-foreground font-mono" />
                            </div>

                            <!-- Regras de Elementos -->
                            <div class="space-y-2 pt-2 border-t border-border">
                                <span class="font-semibold text-foreground">Regra dos Elementos</span>
                                <div class="flex items-center gap-4">
                                    <label class="flex items-center gap-1.5 cursor-pointer">
                                        <input type="radio" value="uniform" v-model="config.elementMode" name="elementMode" />
                                        <span>Uniforme</span>
                                    </label>
                                    <label v-if="config.matrixType === 'matrix'" class="flex items-center gap-1.5 cursor-pointer">
                                        <input type="radio" value="columns" v-model="config.elementMode" name="elementMode" />
                                        <span>Por Coluna</span>
                                    </label>
                                </div>

                                <!-- Elementos Uniformes -->
                                <div v-if="config.elementMode === 'uniform' || config.matrixType === 'vector'" class="flex items-center gap-2">
                                    <span class="text-muted-foreground">Valores de</span>
                                    <input type="text"
                                           v-model="config.uniformMin"
                                           placeholder="1"
                                           class="w-16 px-2 py-1 font-mono border rounded bg-background text-foreground" />
                                    <span class="text-muted-foreground">a</span>
                                    <input type="text"
                                           v-model="config.uniformMax"
                                           placeholder="N"
                                           class="w-16 px-2 py-1 font-mono border rounded bg-background text-foreground" />
                                </div>

                                <!-- Elementos Por Coluna -->
                                <div v-else class="space-y-1.5">
                                    <div class="flex items-center gap-2">
                                        <span class="w-24 text-muted-foreground">Coluna 0:</span>
                                        <input type="text" v-model="config.col0Min" class="w-14 px-2 py-1 font-mono border rounded bg-background text-foreground" />
                                        <span class="text-muted-foreground">a</span>
                                        <input type="text" v-model="config.col0Max" class="w-14 px-2 py-1 font-mono border rounded bg-background text-foreground" />
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="w-24 text-muted-foreground">Demais colunas:</span>
                                        <input type="text" v-model="config.restColsMin" class="w-14 px-2 py-1 font-mono border rounded bg-background text-foreground" />
                                        <span class="text-muted-foreground">a</span>
                                        <input type="text" v-model="config.restColsMax" class="w-14 px-2 py-1 font-mono border rounded bg-background text-foreground" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Coluna 2: Pré-visualização -->
                <div class="p-5 flex flex-col space-y-3 bg-muted/20 overflow-y-auto">
                    <!-- Abas da Pré-visualização -->
                    <div class="flex items-center justify-between border-b border-border pb-2">
                        <div class="flex items-center gap-2">
                            <button type="button"
                                    id="tab-btn-sample"
                                    @click="activePreviewTab = 'sample'"
                                    :class="activePreviewTab === 'sample' ? 'font-bold text-primary border-b-2 border-primary' : 'text-muted-foreground hover:text-foreground'"
                                    class="pb-1 px-1">
                                Amostra (.in)
                            </button>
                            <button type="button"
                                    id="tab-btn-code"
                                    @click="activePreviewTab = 'code'"
                                    :class="activePreviewTab === 'code' ? 'font-bold text-primary border-b-2 border-primary' : 'text-muted-foreground hover:text-foreground'"
                                    class="pb-1 px-1">
                                Código (gen.cpp)
                            </button>
                        </div>

                        <button v-if="activePreviewTab === 'sample'"
                                type="button"
                                id="btn-regenerate-sample"
                                @click="refreshSample"
                                class="button-secondary text-[11px] py-0.5 px-2 flex items-center gap-1">
                            <span>🔄</span>
                            <span>Regerar</span>
                        </button>
                    </div>

                    <!-- Conteúdo da Amostra -->
                    <div v-show="activePreviewTab === 'sample'" class="flex-1 min-h-[220px]">
                        <pre id="preview-sample" class="w-full h-full p-3 font-mono text-xs border rounded-lg bg-background text-foreground overflow-auto max-h-[360px] whitespace-pre">{{ sampleOutput }}</pre>
                    </div>

                    <!-- Conteúdo do Código C++ -->
                    <div v-show="activePreviewTab === 'code'" class="flex-1 min-h-[220px]">
                        <pre id="preview-code" class="w-full h-full p-3 font-mono text-xs border rounded-lg bg-background text-foreground overflow-auto max-h-[360px] whitespace-pre">{{ generatedCode }}</pre>
                    </div>
                </div>
            </div>

            <!-- Rodapé -->
            <div class="flex items-center justify-between px-5 py-3 border-t border-border bg-card">
                <label class="flex items-center gap-2 cursor-pointer select-none text-muted-foreground">
                    <input type="checkbox" v-model="updateSuggestedScript" class="rounded border-input text-primary" />
                    <span>Atualizar script sugerido (gen > $)</span>
                </label>

                <div class="flex items-center gap-2">
                    <button type="button"
                            @click="$emit('close')"
                            class="button-secondary text-xs">
                        Fechar
                    </button>
                    <button type="button"
                            id="btn-apply-generator"
                            @click="applyCode"
                            class="button-primary text-xs">
                        Aplicar no Editor
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, computed } from 'vue';

const emit = defineEmits(['apply', 'close']);

const activePreviewTab = ref('sample');
const updateSuggestedScript = ref(true);
const sampleSeed = ref(0);

const config = reactive({
    variables: [
        { id: '1', name: 'N', min: 1, max: 10 },
        { id: '2', name: 'M', min: 1, max: 5 },
    ],
    hasMatrix: true,
    matrixType: 'matrix', // 'matrix' ou 'vector'
    rowsMode: 'var',
    rowsVar: 'N',
    rowsFixed: 5,
    colsMode: 'var',
    colsVar: 'M',
    colsFixed: 3,
    vectorLenMode: 'var',
    vectorLenVar: 'N',
    vectorLenFixed: 10,
    elementMode: 'uniform', // 'uniform' ou 'columns'
    uniformMin: '1',
    uniformMax: 'N',
    col0Min: '0',
    col0Max: '1',
    restColsMin: '1',
    restColsMax: 'M',
});

function addVariable() {
    const letters = ['N', 'M', 'K', 'Q', 'L', 'R', 'X', 'Y', 'Z'];
    const used = config.variables.map(v => v.name.toUpperCase());
    const nextLetter = letters.find(l => !used.includes(l)) || `V${config.variables.length + 1}`;
    config.variables.push({
        id: String(Date.now()),
        name: nextLetter,
        min: 1,
        max: 50,
    });
}

function removeVariable(idx) {
    if (config.variables.length <= 1) return;
    config.variables.splice(idx, 1);
    const validNames = config.variables.map(v => v.name);
    if (!validNames.includes(config.rowsVar) && validNames.length) config.rowsVar = validNames[0];
    if (!validNames.includes(config.colsVar) && validNames.length) config.colsVar = validNames[0];
    if (!validNames.includes(config.vectorLenVar) && validNames.length) config.vectorLenVar = validNames[0];
}

function refreshSample() {
    sampleSeed.value++;
}

function formatBoundForCpp(val) {
    if (val === undefined || val === null || String(val).trim() === '') return '1';
    const s = String(val).trim();
    const matchingVar = config.variables.find(v => v.name.toUpperCase() === s.toUpperCase());
    if (matchingVar) {
        return matchingVar.name.toLowerCase();
    }
    const num = Number(s);
    return isNaN(num) ? '1' : String(num);
}

const generatedCode = computed(() => {
    let code = `#include "testlib.h"\n#include <iostream>\n\nusing namespace std;\n\nint main(int argc, char* argv[]) {\n    registerGen(argc, argv, 1);\n\n`;

    const varIdentifiers = [];
    config.variables.forEach((v, idx) => {
        const idName = v.name.toLowerCase().trim() || `v${idx + 1}`;
        varIdentifiers.push(idName);
        const minVal = Math.min(Number(v.min) || 1, Number(v.max) || 1);
        const maxVal = Math.max(Number(v.min) || 1, Number(v.max) || 1);
        code += `    int ${idName} = opt<int>(${idx + 1}, rnd.next(${minVal}, ${maxVal}));\n`;
    });

    if (varIdentifiers.length > 0) {
        code += `\n    cout << ${varIdentifiers.join(' << " " << ')} << "\\n";\n`;
    }

    if (config.hasMatrix) {
        if (config.matrixType === 'vector') {
            const lenExpr = config.vectorLenMode === 'var' ? config.vectorLenVar.toLowerCase() : config.vectorLenFixed;
            const minExpr = formatBoundForCpp(config.uniformMin);
            const maxExpr = formatBoundForCpp(config.uniformMax);

            code += `\n    for (int i = 0; i < ${lenExpr}; i++) {\n`;
            code += `        if (i > 0) cout << " ";\n`;
            code += `        cout << rnd.next(${minExpr}, ${maxExpr});\n`;
            code += `    }\n    cout << "\\n";\n`;
        } else {
            const rowsExpr = config.rowsMode === 'var' ? config.rowsVar.toLowerCase() : config.rowsFixed;
            const colsExpr = config.colsMode === 'var' ? config.colsVar.toLowerCase() : config.colsFixed;

            code += `\n    for (int i = 0; i < ${rowsExpr}; i++) {\n`;
            code += `        for (int j = 0; j < ${colsExpr}; j++) {\n`;
            code += `            if (j > 0) cout << " ";\n`;

            if (config.elementMode === 'uniform') {
                const minExpr = formatBoundForCpp(config.uniformMin);
                const maxExpr = formatBoundForCpp(config.uniformMax);
                code += `            cout << rnd.next(${minExpr}, ${maxExpr});\n`;
            } else {
                const col0Min = formatBoundForCpp(config.col0Min);
                const col0Max = formatBoundForCpp(config.col0Max);
                const restMin = formatBoundForCpp(config.restColsMin);
                const restMax = formatBoundForCpp(config.restColsMax);

                code += `            if (j == 0) {\n`;
                code += `                cout << rnd.next(${col0Min}, ${col0Max});\n`;
                code += `            } else {\n`;
                code += `                cout << rnd.next(${restMin}, ${restMax});\n`;
                code += `            }\n`;
            }

            code += `        }\n        cout << "\\n";\n    }\n`;
        }
    }

    code += `\n    return 0;\n}\n`;
    return code;
});

const sampleOutput = computed(() => {
    // Reavalia a reatividade quando o seed muda
    void sampleSeed.value;

    const lines = [];
    const varMap = {};
    const firstLineVals = [];

    config.variables.forEach(v => {
        const vName = v.name.trim();
        const min = Math.min(Number(v.min) || 1, Number(v.max) || 1);
        const max = Math.max(Number(v.min) || 1, Number(v.max) || 1);
        const val = Math.floor(Math.random() * (max - min + 1)) + min;
        varMap[vName.toUpperCase()] = val;
        varMap[vName.toLowerCase()] = val;
        firstLineVals.push(val);
    });

    if (firstLineVals.length > 0) {
        lines.push(firstLineVals.join(' '));
    }

    function resolve(boundVal, fallback = 1) {
        if (!boundVal) return fallback;
        const upper = String(boundVal).trim().toUpperCase();
        if (varMap[upper] !== undefined) return varMap[upper];
        const num = Number(boundVal);
        return isNaN(num) ? fallback : num;
    }

    function rnd(minVal, maxVal) {
        const lo = Math.min(minVal, maxVal);
        const hi = Math.max(minVal, maxVal);
        return Math.floor(Math.random() * (hi - lo + 1)) + lo;
    }

    if (config.hasMatrix) {
        if (config.matrixType === 'vector') {
            const rawLen = config.vectorLenMode === 'var' ? resolve(config.vectorLenVar, 5) : Number(config.vectorLenFixed) || 5;
            const len = Math.min(Math.max(1, rawLen), 20);
            const row = [];
            for (let i = 0; i < len; i++) {
                const lo = resolve(config.uniformMin, 1);
                const hi = resolve(config.uniformMax, 10);
                row.push(rnd(lo, hi));
            }
            lines.push(row.join(' '));
            if (rawLen > 20) {
                lines.push(`... (+ ${rawLen - 20} elementos)`);
            }
        } else {
            const rawRows = config.rowsMode === 'var' ? resolve(config.rowsVar, 4) : Number(config.rowsFixed) || 4;
            const rawCols = config.colsMode === 'var' ? resolve(config.colsVar, 3) : Number(config.colsFixed) || 3;

            const rows = Math.min(Math.max(1, rawRows), 15);
            const cols = Math.min(Math.max(1, rawCols), 15);

            for (let i = 0; i < rows; i++) {
                const row = [];
                for (let j = 0; j < cols; j++) {
                    if (config.elementMode === 'uniform') {
                        const lo = resolve(config.uniformMin, 1);
                        const hi = resolve(config.uniformMax, 10);
                        row.push(rnd(lo, hi));
                    } else {
                        if (j === 0) {
                            const lo = resolve(config.col0Min, 0);
                            const hi = resolve(config.col0Max, 1);
                            row.push(rnd(lo, hi));
                        } else {
                            const lo = resolve(config.restColsMin, 1);
                            const hi = resolve(config.restColsMax, 10);
                            row.push(rnd(lo, hi));
                        }
                    }
                }
                lines.push(row.join(' '));
            }

            if (rawRows > 15) {
                lines.push(`... (+ ${rawRows - 15} linhas)`);
            }
        }
    }

    return lines.join('\n');
});

function applyCode() {
    let script = '';
    if (updateSuggestedScript.value) {
        const smallArgs = config.variables.map(v => Math.min(Number(v.min) || 1, Number(v.max) || 1)).join(' ');
        const maxArgs = config.variables.map(v => Math.max(Number(v.min) || 1, Number(v.max) || 1)).join(' ');
        const midArgs = config.variables.map(v => {
            const mi = Math.min(Number(v.min) || 1, Number(v.max) || 1);
            const ma = Math.max(Number(v.min) || 1, Number(v.max) || 1);
            return Math.floor((mi + ma) / 2);
        }).join(' ');

        script = `gen ${smallArgs} > $\ngen ${midArgs} > $\ngen ${maxArgs} > $\n`;
    }

    emit('apply', {
        code: generatedCode.value,
        suggestedScript: script,
    });
}
</script>
