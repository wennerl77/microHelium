import './vue-loader.js';
import test, { afterEach } from 'node:test';
import assert from 'node:assert/strict';
import { JSDOM } from 'jsdom';

const dom = new JSDOM('<!doctype html><html><head><meta name="csrf-token" content="test-csrf"></head><body><main id="app"></main></body></html>', { url: 'https://microhelium.test/backend/polygon/10' });
for (const key of ['window', 'document', 'location', 'history', 'Element', 'HTMLElement', 'Document', 'SVGElement', 'Node', 'Event', 'MutationObserver']) {
    globalThis[key] = dom.window[key];
}
globalThis.requestAnimationFrame = dom.window.requestAnimationFrame?.bind(dom.window) || (fn => setTimeout(fn, 16));
globalThis.cancelAnimationFrame = dom.window.cancelAnimationFrame?.bind(dom.window) || (id => clearTimeout(id));

const { createApp, nextTick } = await import('vue');
const PolygonGeneratorBuilder = (await import('../../resources/js/components/PolygonGeneratorBuilder.vue')).default;
const PolygonWorkspace = (await import('../../resources/js/features/PolygonWorkspace.vue')).default;
const tick = async () => { await new Promise(resolve => setTimeout(resolve, 20)); await nextTick(); };

let app;
afterEach(() => {
    app?.unmount();
    app = null;
    document.body.innerHTML = '<main id="app"></main>';
});

test('PolygonGeneratorBuilder: renderiza variaveis padrao, matriz e preview', async () => {
    app = createApp(PolygonGeneratorBuilder);
    app.mount('#app');
    await tick();

    const text = document.body.textContent;
    assert.match(text, /Assistente de Gerador \(gen\.cpp\)/i);
    assert.match(text, /Variáveis Iniciais/i);
    assert.match(text, /Gerar Matriz \/ Vetor/i);

    // Amostra (.in) e Código (gen.cpp)
    assert.match(text, /Amostra \(\.in\)/i);
    assert.match(text, /Código \(gen\.cpp\)/i);

    // Inputs com variáveis iniciais N e M
    const inputs = Array.from(document.querySelectorAll('input[type="text"], input[type="number"]'));
    const nInput = inputs.find(i => i.value === 'N');
    const mInput = inputs.find(i => i.value === 'M');
    assert.ok(nInput, 'Input N presente');
    assert.ok(mInput, 'Input M presente');
});

test('PolygonGeneratorBuilder: adicionar variavel reflete no codigo C++', async () => {
    app = createApp(PolygonGeneratorBuilder);
    app.mount('#app');
    await tick();

    // Clica na aba Código (gen.cpp)
    const codeTabBtn = document.querySelector('#tab-btn-code');
    assert.ok(codeTabBtn, 'Botão da aba de código presente');
    codeTabBtn.dispatchEvent(new Event('click', { bubbles: true }));
    await tick();

    let preText = document.querySelector('#preview-code')?.textContent || '';
    assert.match(preText, /int n = opt<int>\(1/);
    assert.match(preText, /int m = opt<int>\(2/);

    // Adiciona variável
    const addBtn = document.querySelector('#btn-add-var');
    assert.ok(addBtn, 'Botão + Variável presente');
    addBtn.dispatchEvent(new Event('click', { bubbles: true }));
    await tick();

    preText = document.querySelector('#preview-code')?.textContent || '';
    assert.match(preText, /int k = opt<int>\(3/i, 'Nova variável K adicionada ao código C++');
});

test('PolygonGeneratorBuilder: alternar para regra por coluna gera logica j == 0 no C++', async () => {
    app = createApp(PolygonGeneratorBuilder);
    app.mount('#app');
    await tick();

    // Seleciona modo Por Coluna
    const colRadio = Array.from(document.querySelectorAll('input[type="radio"]')).find(r => r.value === 'columns');
    assert.ok(colRadio, 'Radio Por Coluna encontrado');
    colRadio.checked = true;
    colRadio.dispatchEvent(new Event('change', { bubbles: true }));
    await tick();

    // Alterna para visualização do código C++
    const codeTabBtn = document.querySelector('#tab-btn-code');
    codeTabBtn.dispatchEvent(new Event('click', { bubbles: true }));
    await tick();

    const code = document.querySelector('#preview-code')?.textContent || '';
    assert.match(code, /if\s*\(j == 0\)/, 'Contém condição if (j == 0) para coluna 0');
    assert.match(code, /rnd\.next\(0,\s*1\)/, 'Contém limites de coluna 0 configurados');
});

test('PolygonGeneratorBuilder: emitir apply com codigo e suggestedScript', async () => {
    let appliedData = null;
    app = createApp(PolygonGeneratorBuilder, {
        onApply: (payload) => {
            appliedData = payload;
        },
    });
    app.mount('#app');
    await tick();

    const applyBtn = document.querySelector('#btn-apply-generator');
    assert.ok(applyBtn, 'Botão de aplicar presente');
    applyBtn.dispatchEvent(new Event('click', { bubbles: true }));
    await tick();

    assert.ok(appliedData, 'Evento apply emitido');
    assert.match(appliedData.code, /registerGen\(argc, argv, 1\)/);
    assert.match(appliedData.suggestedScript, /gen\s+\d+\s+\d+\s*>\s*\$/);
});

test('PolygonWorkspace: abre assistente visual e aplica codigo gerado no editor gen.cpp', async () => {
    globalThis.fetch = async () => ({
        ok: true,
        status: 200,
        headers: { get: () => 'application/json' },
        json: async () => ({ id: 10, name: 'Problema Teste', status: 'draft' }),
    });

    app = createApp(PolygonWorkspace, { exerciseId: '10' });
    app.mount('#app');
    await tick();
    await tick();

    // Navega para aba Gerador
    const stepButtons = Array.from(document.querySelectorAll('nav[aria-label="Etapas de Autoria do Problema"] button'));
    const generatorStep = stepButtons.find(b => b.textContent.includes('Gerador'));
    generatorStep.dispatchEvent(new Event('click', { bubbles: true }));
    await tick();

    // Clica no botão Assistente Visual
    const openWizardBtn = document.querySelector('#btn-open-generator-wizard');
    assert.ok(openWizardBtn, 'Botão Assistente Visual presente na Aba 2');
    openWizardBtn.dispatchEvent(new Event('click', { bubbles: true }));
    await tick();

    // Verifica modal aberto
    const modalTitle = document.querySelector('#generator-builder-title');
    assert.ok(modalTitle, 'Modal do assistente aberto');

    // Clica em Aplicar no Editor
    const applyBtn = document.querySelector('#btn-apply-generator');
    assert.ok(applyBtn, 'Botão de aplicar no modal presente');
    applyBtn.dispatchEvent(new Event('click', { bubbles: true }));
    await tick();

    // Verifica se modal fechou
    assert.equal(document.querySelector('#generator-builder-title'), null, 'Modal fechado após aplicar');

    // Verifica textarea atualizado com novo código gerado
    const textarea = document.querySelector('#generator-source-code');
    assert.match(textarea.value, /registerGen\(argc, argv, 1\)/);
    assert.match(textarea.value, /int n = opt<int>\(1/);
});
