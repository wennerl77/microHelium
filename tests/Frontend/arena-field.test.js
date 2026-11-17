import { test } from 'node:test';
import assert from 'node:assert/strict';
import { createArenaParticles, mountArenaField, projectArenaParticle } from '../../resources/js/lib/arena-field.ts';

test('createArenaParticles gera 54 partículas 3D válidas por padrão', () => {
    const particles = createArenaParticles();
    assert.equal(particles.length, 54);
    for (const p of particles) {
        assert.ok(Number.isFinite(p.x) && Number.isFinite(p.y) && Number.isFinite(p.z));
        assert.ok(p.radius > 0 && p.alpha > 0);
    }
});

test('projectArenaParticle projeta coordenadas dentro das dimensões positivas do canvas', () => {
    const projected = projectArenaParticle(
        { x: 0, y: 0, z: 0, vx: 0, vy: 0, vz: 1, radius: 2, alpha: 0.5 },
        400,
        300,
    );
    assert.equal(projected.sx, 200);
    assert.equal(projected.sy, 150);
    assert.ok(projected.size >= 0.8);
    assert.ok(projected.alpha > 0 && projected.alpha <= 0.95);
});

test('mountArenaField respeita prefers-reduced-motion e limpa recursos no destroy', () => {
    const prevWindow = globalThis.window;
    let rafCalled = false;
    globalThis.window = {
        devicePixelRatio: 1,
        matchMedia: (query) => ({
            matches: query === '(prefers-reduced-motion: reduce)',
            addEventListener() {},
            removeEventListener() {},
        }),
        requestAnimationFrame: () => {
            rafCalled = true;
            return 1;
        },
        cancelAnimationFrame() {},
    };

    try {
        const fakeCanvas = {
            width: 0,
            height: 0,
            clientWidth: 320,
            clientHeight: 240,
            getBoundingClientRect: () => ({ width: 320, height: 240 }),
            getContext: () => ({
                setTransform() {},
                clearRect() {},
                createRadialGradient: () => ({ addColorStop() {} }),
                fillRect() {},
                beginPath() {},
                arc() {},
                fill() {},
                fillStyle: '',
            }),
        };

        const controller = mountArenaField(fakeCanvas);
        assert.equal(controller.isReducedMotion(), true);
        assert.equal(controller.getParticleCount(), 54);
        assert.equal(rafCalled, false);
        controller.destroy();
    } finally {
        globalThis.window = prevWindow;
    }
});
