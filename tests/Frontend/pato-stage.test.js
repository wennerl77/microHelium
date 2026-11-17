import { test } from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';

const patoSfc = readFileSync(new URL('../../resources/js/components/PatoStage.vue', import.meta.url), 'utf8');

test('PatoStage.vue possui órbitas, badges, imagem oficial do PATO e suporte a prefers-reduced-motion', () => {
    assert.match(patoSfc, /orbit--outer/);
    assert.match(patoSfc, /orbit--equator/);
    assert.match(patoSfc, /orbit--inner/);
    assert.match(patoSfc, /orbit-badge--a/);
    assert.match(patoSfc, /orbit-badge--b/);
    assert.match(patoSfc, /orbit-badge--c/);
    assert.match(patoSfc, /\/img\/pato\.svg/);
    assert.match(patoSfc, /prefers-reduced-motion:\s*reduce/);
    assert.match(patoSfc, /mountArenaField/);
});
