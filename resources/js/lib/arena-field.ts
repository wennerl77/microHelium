export interface ArenaParticle {
    x: number;
    y: number;
    z: number;
    vx: number;
    vy: number;
    vz: number;
    radius: number;
    alpha: number;
}

export interface ArenaFieldController {
    destroy: () => void;
    isReducedMotion: () => boolean;
    getParticleCount: () => number;
}

const DEFAULT_PARTICLE_COUNT = 54;
const FOCAL_LENGTH = 260;

/**
 * Cria partículas distribuídas no espaço 3D para o palco da Dark Arena.
 */
export function createArenaParticles(count = DEFAULT_PARTICLE_COUNT): ArenaParticle[] {
    const particles: ArenaParticle[] = [];
    for (let i = 0; i < count; i += 1) {
        const angle = (i * 2.399963) % (Math.PI * 2);
        const spread = 0.25 + ((i % 9) / 9) * 0.75;
        particles.push({
            x: Math.cos(angle) * 180 * spread,
            y: Math.sin(angle) * 140 * spread,
            z: ((i * 37) % 240) - 60,
            vx: Math.cos(angle * 1.7) * 0.14,
            vy: Math.sin(angle * 1.3) * 0.14,
            vz: 0.45 + (i % 5) * 0.1,
            radius: 1.2 + (i % 3) * 0.65,
            alpha: 0.35 + (i % 4) * 0.15,
        });
    }
    return particles;
}

/**
 * Projeta uma partícula 3D no plano 2D do canvas.
 */
export function projectArenaParticle(
    particle: ArenaParticle,
    width: number,
    height: number,
): { sx: number; sy: number; size: number; alpha: number } {
    const depth = Math.max(40, FOCAL_LENGTH + particle.z);
    const scale = FOCAL_LENGTH / depth;
    return {
        sx: width * 0.5 + particle.x * scale,
        sy: height * 0.5 + particle.y * scale,
        size: Math.max(0.8, particle.radius * scale),
        alpha: Math.min(0.95, particle.alpha * scale),
    };
}

/**
 * Inicializa o campo de partículas 3D ciano da Dark Arena em um elemento canvas.
 */
export function mountArenaField(canvas: HTMLCanvasElement): ArenaFieldController {
    const ctx = canvas.getContext('2d');
    const motionQuery = typeof window !== 'undefined' && typeof window.matchMedia === 'function'
        ? window.matchMedia('(prefers-reduced-motion: reduce)')
        : null;

    const particles = createArenaParticles();
    let rafId = 0;
    let destroyed = false;
    let width = 320;
    let height = 240;

    const resize = (): void => {
        const rect = canvas.getBoundingClientRect();
        width = Math.max(160, Math.round(rect.width || canvas.clientWidth || 320));
        height = Math.max(160, Math.round(rect.height || canvas.clientHeight || 240));
        const dpr = typeof window !== 'undefined' ? Math.min(window.devicePixelRatio || 1, 2) : 1;
        canvas.width = Math.round(width * dpr);
        canvas.height = Math.round(height * dpr);
        if (ctx) {
            ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
        }
    };

    const drawFrame = (animate: boolean): void => {
        if (!ctx || destroyed) return;
        ctx.clearRect(0, 0, width, height);

        const gradient = ctx.createRadialGradient(
            width * 0.5,
            height * 0.5,
            8,
            width * 0.5,
            height * 0.5,
            Math.max(width, height) * 0.55,
        );
        gradient.addColorStop(0, 'rgba(92, 219, 255, 0.16)');
        gradient.addColorStop(0.55, 'rgba(7, 155, 208, 0.06)');
        gradient.addColorStop(1, 'rgba(7, 21, 42, 0)');
        ctx.fillStyle = gradient;
        ctx.fillRect(0, 0, width, height);

        for (const p of particles) {
            if (animate) {
                p.x += p.vx;
                p.y += p.vy;
                p.z -= p.vz;
                if (p.z < -140) {
                    p.z = 180;
                }
            }
            const projected = projectArenaParticle(p, width, height);
            ctx.beginPath();
            ctx.arc(projected.sx, projected.sy, projected.size, 0, Math.PI * 2);
            ctx.fillStyle = `rgba(92, 219, 255, ${projected.alpha.toFixed(3)})`;
            ctx.fill();
        }
    };

    const tick = (): void => {
        if (destroyed || motionQuery?.matches) {
            drawFrame(false);
            return;
        }
        drawFrame(true);
        rafId = window.requestAnimationFrame(tick);
    };

    resize();
    if (motionQuery?.matches) {
        drawFrame(false);
    } else if (typeof window !== 'undefined' && typeof window.requestAnimationFrame === 'function') {
        rafId = window.requestAnimationFrame(tick);
    } else {
        drawFrame(false);
    }

    const observer = typeof ResizeObserver !== 'undefined'
        ? new ResizeObserver(() => {
            resize();
            if (motionQuery?.matches) drawFrame(false);
        })
        : null;
    observer?.observe(canvas);

    const onMotionChange = (): void => {
        if (motionQuery?.matches) {
            if (rafId) window.cancelAnimationFrame(rafId);
            rafId = 0;
            drawFrame(false);
        } else if (!destroyed && !rafId) {
            rafId = window.requestAnimationFrame(tick);
        }
    };
    motionQuery?.addEventListener?.('change', onMotionChange);

    return {
        destroy: (): void => {
            destroyed = true;
            if (rafId && typeof window !== 'undefined') {
                window.cancelAnimationFrame(rafId);
            }
            observer?.disconnect();
            motionQuery?.removeEventListener?.('change', onMotionChange);
        },
        isReducedMotion: (): boolean => Boolean(motionQuery?.matches),
        getParticleCount: (): number => particles.length,
    };
}
