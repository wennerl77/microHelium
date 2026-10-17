import './vue-loader.js';
import test, { afterEach } from 'node:test';
import assert from 'node:assert/strict';
import { JSDOM } from 'jsdom';

const dom = new JSDOM('<!doctype html><html><head><meta name="csrf-token" content="test-csrf"></head><body><main id="app"></main></body></html>', { url: 'https://microhelium.test/backend/polygon' });
for (const key of ['window', 'document', 'location', 'history', 'Element', 'HTMLElement', 'Document', 'SVGElement', 'Node', 'Event', 'MutationObserver']) {
    globalThis[key] = dom.window[key];
}
globalThis.requestAnimationFrame = dom.window.requestAnimationFrame?.bind(dom.window) || (fn => setTimeout(fn, 16));
globalThis.cancelAnimationFrame = dom.window.cancelAnimationFrame?.bind(dom.window) || (id => clearTimeout(id));

const { createApp, nextTick } = await import('vue');
const PolygonDashboard = (await import('../../resources/js/features/PolygonDashboard.vue')).default;
const tick = async () => { await new Promise(resolve => setTimeout(resolve, 15)); await nextTick(); };

const originalFetch = globalThis.fetch;
let app;
afterEach(() => {
    app?.unmount();
    app = null;
    globalThis.fetch = originalFetch;
    document.body.innerHTML = '<main id="app"></main>';
});

test('PolygonDashboard: renderiza cabeçalho, KPIs e busca exercícios via GET /api/backend/polygon', async () => {
    let capturedUrl = null;
    let capturedHeaders = null;

    globalThis.fetch = async (url, options = {}) => {
        capturedUrl = url;
        capturedHeaders = options.headers || {};
        return {
            ok: true,
            status: 200,
            headers: { get: h => h.toLowerCase() === 'content-type' ? 'application/json' : null },
            json: async () => ({
                data: [
                    { id: 1, name: 'Soma Simples', short_name: 'A', basename: 'soma-simples', time_limit: 1, memory_limit: 256, status: 'draft', contest: null },
                    { id: 2, name: 'Grafo Conexo', short_name: 'B', basename: 'grafo-conexo', time_limit: 2, memory_limit: 512, status: 'calibrated', contest: { id: 5, name: 'Fase 1' } },
                ],
                counts: { total: 2, draft: 1, generated: 0, calibrated: 1, published: 0 },
                contests: [{ id: 5, name: 'Fase 1' }],
            }),
        };
    };

    app = createApp(PolygonDashboard);
    app.mount('#app');
    await tick();
    await tick();

    assert.match(capturedUrl, /\/api\/backend\/polygon/);
    assert.equal(capturedHeaders['X-CSRF-TOKEN'], 'test-csrf');

    const text = document.body.textContent;
    assert.match(text, /Polygon — Seletor de Exercícios/);
    assert.match(text, /Rascunhos no Polygon/);
    assert.match(text, /Exercícios Já Criados/);
    assert.match(text, /Soma Simples/);
    assert.match(text, /Grafo Conexo/);
    assert.match(text, /Fase 1/);
    assert.match(text, /Banco Geral/);

    // Verifica div superior de rascunhos e inferior de publicados
    assert.ok(document.querySelector('#section-drafts'), 'Div superior de rascunhos presente');
    assert.ok(document.querySelector('#section-published'), 'Div inferior de publicados presente');

    // Verifica grid de cards
    assert.ok(document.querySelector('#drafts-cards'), 'Grid de cards de rascunhos presente');
    const cards = document.querySelectorAll('.exercise-card');
    assert.equal(cards.length, 2, 'Dois cards de rascunho renderizados');

    // Verifica botão de novo exercício
    const newBtn = document.querySelector('#btn-open-new-modal');
    assert.ok(newBtn, 'Botão "+ Novo Exercício" presente');
});

test('PolygonDashboard: abre modal de criação, preenche formulário e submete via POST /api/backend/polygon', async () => {
    let postCalled = false;
    let postBody = null;

    globalThis.fetch = async (url, options = {}) => {
        const method = options.method || 'GET';
        if (method === 'GET') {
            return {
                ok: true,
                status: 200,
                headers: { get: () => 'application/json' },
                json: async () => ({ data: [], counts: { total: 0, draft: 0, generated: 0, calibrated: 0, published: 0 }, contests: [] }),
            };
        }
        if (method === 'POST') {
            postCalled = true;
            postBody = JSON.parse(options.body);
            return {
                ok: true,
                status: 201,
                headers: { get: () => 'application/json' },
                json: async () => ({
                    data: { id: 42, name: postBody.name, short_name: postBody.short_name, basename: postBody.basename },
                }),
            };
        }
        return { ok: false, status: 404, headers: { get: () => null }, json: async () => ({}) };
    };

    app = createApp(PolygonDashboard);
    app.mount('#app');
    await tick();

    // Abre o modal
    const openModalBtn = document.querySelector('#btn-open-new-modal');
    openModalBtn.dispatchEvent(new Event('click', { bubbles: true }));
    await tick();

    assert.ok(document.querySelector('#modal-title'), 'Modal aberto');

    // Preenche campos
    const nameInput = document.querySelector('#create-name');
    nameInput.value = 'Mochila Booleana';
    nameInput.dispatchEvent(new Event('input', { bubbles: true }));

    const shortNameInput = document.querySelector('#create-short-name');
    shortNameInput.value = 'C';
    shortNameInput.dispatchEvent(new Event('input', { bubbles: true }));

    const basenameInput = document.querySelector('#create-basename');
    basenameInput.value = 'mochila-booleana';
    basenameInput.dispatchEvent(new Event('input', { bubbles: true }));
    await tick();

    // Submete form
    const form = document.querySelector('form');
    form.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
    await tick();
    await tick();
    await tick();

    assert.ok(postCalled, 'POST /api/backend/polygon foi chamado');
    assert.equal(postBody.name, 'Mochila Booleana');
    assert.equal(postBody.short_name, 'C');
    assert.equal(postBody.basename, 'mochila-booleana');
});

test('PolygonDashboard: exclusão de exercício abre diálogo e executa DELETE /api/backend/polygon/{id}', async () => {
    let deleteCalled = false;
    let deletedId = null;

    globalThis.fetch = async (url, options = {}) => {
        const method = options.method || 'GET';
        if (method === 'GET') {
            return {
                ok: true,
                status: 200,
                headers: { get: () => 'application/json' },
                json: async () => ({
                    data: [
                        { id: 77, name: 'Problema Descartável', short_name: 'D', basename: 'descartavel', status: 'draft' },
                    ],
                    counts: { total: 1, draft: 1, generated: 0, calibrated: 0, published: 0 },
                    contests: [],
                }),
            };
        }
        if (method === 'DELETE') {
            deleteCalled = true;
            const match = url.match(/\/api\/backend\/polygon\/(\d+)/);
            if (match) deletedId = match[1];
            return { ok: true, status: 204, headers: { get: () => null } };
        }
        return { ok: false, status: 404, headers: { get: () => null }, json: async () => ({}) };
    };

    app = createApp(PolygonDashboard);
    app.mount('#app');
    await tick();
    await tick();

    // Clica no botão de excluir (lixeira)
    const deleteBtn = document.querySelector('button[title="Excluir rascunho"]');
    assert.ok(deleteBtn, 'Botão de excluir encontrado');
    deleteBtn.dispatchEvent(new Event('click', { bubbles: true }));
    await tick();

    assert.match(document.body.textContent, /Excluir Exercício\?/);
    assert.match(document.body.textContent, /Problema Descartável/);

    // Confirma exclusão
    const confirmBtn = document.querySelector('.button-danger');
    assert.ok(confirmBtn, 'Botão de confirmar exclusão encontrado');
    confirmBtn.dispatchEvent(new Event('click', { bubbles: true }));
    await tick();
    await tick();

    assert.ok(deleteCalled, 'DELETE chamado');
    assert.equal(deletedId, '77');
});
