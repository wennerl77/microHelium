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
const PolygonWorkspace = (await import('../../resources/js/features/PolygonWorkspace.vue')).default;
const tick = async () => { await new Promise(resolve => setTimeout(resolve, 15)); await nextTick(); };

const originalFetch = globalThis.fetch;
let app;
afterEach(() => {
    app?.unmount();
    app = null;
    globalThis.fetch = originalFetch;
    document.body.innerHTML = '<main id="app"></main>';
});

test('PolygonWorkspace: renderiza breadcrumb, stepper das 5 etapas e campos de metadados', async () => {
    globalThis.fetch = async (url) => {
        if (url.includes('/api/backend/polygon/10')) {
            return {
                ok: true,
                status: 200,
                headers: { get: () => 'application/json' },
                json: async () => ({
                    id: 10,
                    name: 'Resto da Divisão',
                    short_name: 'A',
                    basename: 'resto-divisao',
                    time_limit: 1,
                    memory_limit: 256,
                    status: 'draft',
                }),
            };
        }
        return { ok: true, status: 200, headers: { get: () => 'application/json' }, json: async () => ({ contests: [] }) };
    };

    app = createApp(PolygonWorkspace, { exerciseId: '10' });
    app.mount('#app');
    await tick();
    await tick();

    const text = document.body.textContent;
    assert.match(text, /Atributos & Enunciado/i);
    assert.match(text, /Gerador \(gen\.cpp\)/i);
    assert.match(text, /Geração de Testes/i);
    assert.match(text, /Soluções & Calibração/i);
    assert.match(text, /Finalização & Publicação/i);

    // Link de voltar ao painel
    const backLink = document.querySelector('a[title="Voltar ao Painel do Polygon"]');
    assert.ok(backLink, 'Link para voltar ao painel presente');
    assert.equal(backLink.getAttribute('href'), '/backend/polygon');

    // Campos de metadados preenchidos via API
    const nameInput = document.querySelector('#exercise-name');
    assert.ok(nameInput, 'Campo de nome presente');
    assert.equal(nameInput.value, 'Resto da Divisão');

    const shortNameInput = document.querySelector('#exercise-short-name');
    assert.equal(shortNameInput.value, 'A');

    const basenameInput = document.querySelector('#exercise-basename');
    assert.equal(basenameInput.value, 'resto-divisao');
});

test('PolygonWorkspace: navegação entre as 5 etapas preserva estado reativo', async () => {
    globalThis.fetch = async () => ({
        ok: true,
        status: 200,
        headers: { get: () => 'application/json' },
        json: async () => ({ id: 10, name: 'Original', short_name: 'A', basename: 'original', status: 'draft' }),
    });

    app = createApp(PolygonWorkspace, { exerciseId: '10' });
    app.mount('#app');
    await tick();
    await tick();

    // Modifica nome na Etapa 1
    const nameInput = document.querySelector('#exercise-name');
    nameInput.value = 'Problema Atualizado';
    nameInput.dispatchEvent(new Event('input', { bubbles: true }));
    await tick();

    // Clica na Etapa 2 (Gerador)
    const stepButtons = Array.from(document.querySelectorAll('nav[aria-label="Etapas de Autoria do Problema"] button'));
    const generatorStep = stepButtons.find(b => b.textContent.includes('Gerador'));
    assert.ok(generatorStep, 'Botão da etapa de gerador encontrado');
    generatorStep.dispatchEvent(new Event('click', { bubbles: true }));
    await tick();

    assert.match(document.querySelector('#generator-source-code').value, /testlib\.h/i);
    assert.match(document.body.textContent, /Console de Compilação Sandbox/i);

    // Volta para a Etapa 1 e verifica persistência
    const attrStep = stepButtons.find(b => b.textContent.includes('Atributos'));
    attrStep.dispatchEvent(new Event('click', { bubbles: true }));
    await tick();

    assert.equal(document.querySelector('#exercise-name').value, 'Problema Atualizado');
});

test('PolygonWorkspace: salvar rascunho envia PUT /api/backend/polygon/{id}', async () => {
    let putCalled = false;
    let putPayload = null;

    globalThis.fetch = async (url, options = {}) => {
        const method = options.method || 'GET';
        if (url.includes('/api/backend/polygon/10') && method === 'GET') {
            return {
                ok: true,
                status: 200,
                headers: { get: () => 'application/json' },
                json: async () => ({ id: 10, name: 'Exercício 10', short_name: 'A', basename: 'ex-10', status: 'draft' }),
            };
        }
        if (url.includes('/api/backend/polygon/10') && method === 'PUT') {
            putCalled = true;
            putPayload = JSON.parse(options.body);
            return {
                ok: true,
                status: 200,
                headers: { get: () => 'application/json' },
                json: async () => ({ data: { id: 10, status: 'draft' } }),
            };
        }
        return { ok: false, status: 404, headers: { get: () => null }, json: async () => ({}) };
    };

    app = createApp(PolygonWorkspace, { exerciseId: '10' });
    app.mount('#app');
    await tick();
    await tick();

    const saveBtn = document.querySelector('#btn-save-draft');
    assert.ok(saveBtn, 'Botão Salvar Rascunho presente');
    saveBtn.dispatchEvent(new Event('click', { bubbles: true }));
    await tick();
    await tick();

    assert.ok(putCalled, 'PUT foi chamado');
    assert.equal(putPayload.name, 'Exercício 10');
    assert.match(document.body.textContent, /Rascunho salvo com sucesso!/);
});

test('PolygonWorkspace: Etapa 2 compila gerador C++ com POST /compile-generator', async () => {
    let compileCalled = false;

    globalThis.fetch = async (url, options = {}) => {
        const method = options.method || 'GET';
        if (url.includes('/api/backend/polygon/10') && method === 'GET') {
            return {
                ok: true,
                status: 200,
                headers: { get: () => 'application/json' },
                json: async () => ({ id: 10, name: 'Exercício 10', short_name: 'A', basename: 'ex-10', status: 'draft' }),
            };
        }
        if (url.includes('/api/backend/polygon/10') && method === 'PUT') {
            return { ok: true, status: 200, headers: { get: () => 'application/json' }, json: async () => ({ data: {} }) };
        }
        if (url.includes('/compile-generator') && method === 'POST') {
            compileCalled = true;
            return {
                ok: true,
                status: 200,
                headers: { get: () => 'application/json' },
                json: async () => ({
                    success: true,
                    output: 'Compilação sandbox bem-sucedida com g++ -O2 -std=c++20.',
                }),
            };
        }
        return { ok: false, status: 404, headers: { get: () => null }, json: async () => ({}) };
    };

    app = createApp(PolygonWorkspace, { exerciseId: '10' });
    app.mount('#app');
    await tick();
    await tick();

    // Navega para Etapa 2
    const stepButtons = Array.from(document.querySelectorAll('nav[aria-label="Etapas de Autoria do Problema"] button'));
    const generatorStep = stepButtons.find(b => b.textContent.includes('Gerador'));
    generatorStep.dispatchEvent(new Event('click', { bubbles: true }));
    await tick();

    const compileBtn = document.querySelector('#btn-compile-generator');
    assert.ok(compileBtn, 'Botão Compilar Gerador presente');
    compileBtn.dispatchEvent(new Event('click', { bubbles: true }));
    await tick();
    await tick();

    assert.ok(compileCalled, 'POST compile-generator foi chamado');
    assert.match(document.body.textContent, /Gerador C\+\+ compilado com sucesso!/);
    assert.match(document.body.textContent, /Compilado com Sucesso/);
});

test('PolygonWorkspace: Etapa 3 gera casos de teste via POST /generate-tests e abre modal de inspeção', async () => {
    let generateCalled = false;

    globalThis.fetch = async (url, options = {}) => {
        const method = options.method || 'GET';
        if (url.includes('/api/backend/polygon/10') && method === 'GET') {
            return {
                ok: true,
                status: 200,
                headers: { get: () => 'application/json' },
                json: async () => ({ id: 10, name: 'Exercício 10', short_name: 'A', basename: 'ex-10', status: 'draft' }),
            };
        }
        if (url.includes('/api/backend/polygon/10') && method === 'PUT') {
            return { ok: true, status: 200, headers: { get: () => 'application/json' }, json: async () => ({ data: {} }) };
        }
        if (url.includes('/generate-tests') && method === 'POST') {
            generateCalled = true;
            return {
                ok: true,
                status: 200,
                headers: { get: () => 'application/json' },
                json: async () => ({
                    success: true,
                    total_tests: 2,
                    cases: [
                        { number: 1, origin: 'manual', is_sample: true, input_bytes: 4, input: '4 3\n', output: 'PAR\n1\n' },
                        { number: 2, origin: 'script', is_sample: false, input_bytes: 8, input: '10 5\n', output: 'PAR\n0\n' },
                    ],
                }),
            };
        }
        return { ok: false, status: 404, headers: { get: () => null }, json: async () => ({}) };
    };

    app = createApp(PolygonWorkspace, { exerciseId: '10' });
    app.mount('#app');
    await tick();
    await tick();

    // Navega para Etapa 3
    const stepButtons = Array.from(document.querySelectorAll('nav[aria-label="Etapas de Autoria do Problema"] button'));
    const testsStep = stepButtons.find(b => b.textContent.includes('Testes'));
    testsStep.dispatchEvent(new Event('click', { bubbles: true }));
    await tick();

    const genBtn = document.querySelector('#btn-generate-tests');
    assert.ok(genBtn, 'Botão Executar Geração de Testes presente');
    genBtn.dispatchEvent(new Event('click', { bubbles: true }));
    await tick();
    await tick();

    assert.ok(generateCalled, 'POST generate-tests chamado');
    assert.match(document.body.textContent, /2 casos de teste gerados com sucesso!/);
    await tick();
    await tick();

    // Inspeciona caso 1
    const inspectBtn = document.querySelector('#test-cases-table button');
    assert.ok(inspectBtn, 'Botão Inspecionar presente');
    inspectBtn.dispatchEvent(new Event('click', { bubbles: true }));
    await tick();

    assert.match(document.body.textContent, /Caso de Teste #1/);
});

test('PolygonWorkspace: Etapa 4 calibra soluções via POST /calibrate', async () => {
    let calibrateCalled = false;

    globalThis.fetch = async (url, options = {}) => {
        const method = options.method || 'GET';
        if (url.includes('/api/backend/polygon/10') && method === 'GET') {
            return {
                ok: true,
                status: 200,
                headers: { get: () => 'application/json' },
                json: async () => ({
                    id: 10,
                    name: 'Exercício 10',
                    short_name: 'A',
                    basename: 'ex-10',
                    status: 'generated',
                    solution_accept: '#include <iostream>\nint main(){return 0;}',
                    tests: [{ number: 1, origin: 'manual', is_sample: true, input_bytes: 4 }],
                }),
            };
        }
        if (url.includes('/api/backend/polygon/10') && method === 'PUT') {
            return { ok: true, status: 200, headers: { get: () => 'application/json' }, json: async () => ({ data: {} }) };
        }
        if (url.includes('/calibrate') && method === 'POST') {
            calibrateCalled = true;
            return {
                ok: true,
                status: 200,
                headers: { get: () => 'application/json' },
                json: async () => ({
                    status: 'calibrated',
                    summary: {
                        total_tests: 1,
                        accept_passed: true,
                        max_time_seconds: 0.02,
                        max_memory_mb: 12,
                        tle_calibrated: true,
                        wrong_calibrated: true,
                        warnings: [],
                    },
                    language_limits: [
                        { language: 'cpp', name: 'C++', extension: 'cpp', time_limit: 1, memory_limit: 256, source: 'base' },
                    ],
                }),
            };
        }
        return { ok: false, status: 404, headers: { get: () => null }, json: async () => ({}) };
    };

    app = createApp(PolygonWorkspace, { exerciseId: '10' });
    app.mount('#app');
    await tick();
    await tick();

    // Navega para Etapa 4
    const stepButtons = Array.from(document.querySelectorAll('nav[aria-label="Etapas de Autoria do Problema"] button'));
    const solStep = stepButtons.find(b => b.textContent.includes('Soluções'));
    solStep.dispatchEvent(new Event('click', { bubbles: true }));
    await tick();

    const calBtn = document.querySelector('#btn-run-calibration');
    assert.ok(calBtn, 'Botão Calibrar presente');
    calBtn.dispatchEvent(new Event('click', { bubbles: true }));
    await tick();
    await tick();

    assert.ok(calibrateCalled, 'POST calibrate chamado');
    assert.match(document.body.textContent, /Calibração concluída com sucesso!/);
});

test('PolygonWorkspace: Etapa 5 publica exercício via POST /publish', async () => {
    let publishCalled = false;

    globalThis.fetch = async (url, options = {}) => {
        const method = options.method || 'GET';
        if (url.includes('/api/backend/polygon/10') && method === 'GET') {
            return {
                ok: true,
                status: 200,
                headers: { get: () => 'application/json' },
                json: async () => ({
                    id: 10,
                    name: 'Exercício 10',
                    short_name: 'A',
                    basename: 'ex-10',
                    status: 'calibrated',
                    solution_accept: '#include <iostream>\nint main(){return 0;}',
                    tests: [{ number: 1, origin: 'manual', is_sample: true, input_bytes: 4 }],
                    calibration_results: {
                        summary: {
                            accept_passed: true,
                            max_time_seconds: 0.01,
                            max_memory_mb: 10,
                            tle_calibrated: true,
                            wrong_calibrated: true,
                        },
                    },
                }),
            };
        }
        if (url.includes('/api/backend/polygon/10') && method === 'PUT') {
            return { ok: true, status: 200, headers: { get: () => 'application/json' }, json: async () => ({ data: {} }) };
        }
        if (url.includes('/publish') && method === 'POST') {
            publishCalled = true;
            return {
                ok: true,
                status: 200,
                headers: { get: () => 'application/json' },
                json: async () => ({
                    success: true,
                    target: 'bank',
                    bank_code: 'BNK-EX-10',
                }),
            };
        }
        return { ok: false, status: 404, headers: { get: () => null }, json: async () => ({}) };
    };

    app = createApp(PolygonWorkspace, { exerciseId: '10' });
    app.mount('#app');
    await tick();
    await tick();

    // Navega para Etapa 5
    const stepButtons = Array.from(document.querySelectorAll('nav[aria-label="Etapas de Autoria do Problema"] button'));
    const pubStep = stepButtons.find(b => b.textContent.includes('Publicação'));
    pubStep.dispatchEvent(new Event('click', { bubbles: true }));
    await tick();

    const pubBtn = document.querySelector('#btn-publish-exercise');
    assert.ok(pubBtn, 'Botão Finalizar e Publicar presente');
    assert.equal(pubBtn.disabled, false, 'Botão habilitado quando pronto');

    pubBtn.dispatchEvent(new Event('click', { bubbles: true }));
    await tick();
    await tick();

    assert.ok(publishCalled, 'POST publish chamado');
    assert.match(document.body.textContent, /Problema publicado com sucesso!/);
});
