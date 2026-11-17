<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { mountArenaField, type ArenaFieldController } from '../lib/arena-field';
import { t } from '../i18n.js';

interface ConfettiPiece {
    id: number;
    tx: string;
    ty: string;
    rot: string;
    color: string;
}

const CONFETTI_COLORS = ['#FFD900', '#5CDBFF', '#79E2B5', '#079BD0'];

const canvasRef = ref<HTMLCanvasElement | null>(null);
const patoOffset = ref({ x: 0, y: 0 });
const confetti = ref<ConfettiPiece[]>([]);
const celebratedCount = ref(0);

let fieldController: ArenaFieldController | null = null;
let confettiSeq = 0;
let confettiTimer: ReturnType<typeof setTimeout> | null = null;

function prefersReducedMotion(): boolean {
    return typeof window !== 'undefined'
        && typeof window.matchMedia === 'function'
        && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

function handlePointerMove(event: PointerEvent): void {
    if (prefersReducedMotion()) return;
    const target = event.currentTarget as HTMLElement | null;
    if (!target) return;
    const rect = target.getBoundingClientRect();
    if (!rect.width || !rect.height) return;
    const nx = (event.clientX - rect.left) / rect.width - 0.5;
    const ny = (event.clientY - rect.top) / rect.height - 0.5;
    patoOffset.value = {
        x: Math.round(nx * 14),
        y: Math.round(ny * 10),
    };
}

function resetPointer(): void {
    patoOffset.value = { x: 0, y: 0 };
}

function celebrate(): void {
    celebratedCount.value += 1;
    if (prefersReducedMotion()) return;

    const batch: ConfettiPiece[] = [];
    for (let i = 0; i < 16; i += 1) {
        const angle = (Math.PI * 2 * i) / 16;
        const distance = 58 + (i % 4) * 18;
        confettiSeq += 1;
        batch.push({
            id: confettiSeq,
            tx: `${Math.round(Math.cos(angle) * distance)}px`,
            ty: `${Math.round(Math.sin(angle) * distance - 18)}px`,
            rot: `${(i * 47) % 360}deg`,
            color: CONFETTI_COLORS[i % CONFETTI_COLORS.length],
        });
    }
    confetti.value = batch;
    if (confettiTimer) clearTimeout(confettiTimer);
    confettiTimer = setTimeout(() => {
        confetti.value = [];
    }, 980);
}

onMounted(() => {
    if (canvasRef.value) {
        fieldController = mountArenaField(canvasRef.value);
    }
});

onBeforeUnmount(() => {
    fieldController?.destroy();
    if (confettiTimer) clearTimeout(confettiTimer);
});
</script>

<template>
    <div
        class="pato-stage"
        @pointermove="handlePointerMove"
        @pointerleave="resetPointer"
    >
        <canvas ref="canvasRef" class="pato-stage-canvas" aria-hidden="true"></canvas>

        <div class="orbit orbit--outer" aria-hidden="true">
            <span class="orbit-satellite"></span>
        </div>
        <div class="orbit orbit--equator" aria-hidden="true"></div>
        <div class="orbit orbit--inner" aria-hidden="true"></div>

        <span class="orbit-badge orbit-badge--a" aria-hidden="true">AC · 100%</span>
        <span class="orbit-badge orbit-badge--b" aria-hidden="true">{{ t('TURING 2026') }}</span>
        <span class="orbit-badge orbit-badge--c" aria-hidden="true">{{ t('ICPC ARENA') }}</span>

        <button
            type="button"
            class="pato-mascot-btn"
            :aria-label="t('Mascote PATO da Maratona Turing — comemorar')"
            :style="{ transform: `translate3d(${patoOffset.x}px, ${patoOffset.y}px, 0)` }"
            @click="celebrate"
        >
            <img src="/img/pato.svg" :alt="t('Mascote oficial PATO')" width="124" height="124" />
        </button>

        <div class="confetti-layer" aria-hidden="true">
            <span
                v-for="piece in confetti"
                :key="piece.id"
                class="confetti-piece"
                :style="{
                    backgroundColor: piece.color,
                    '--tx': piece.tx,
                    '--ty': piece.ty,
                    '--rot': piece.rot,
                }"
            ></span>
        </div>

        <span class="sr-only" role="status" aria-live="polite">
            <template v-if="celebratedCount > 0">{{ t('PATO comemorou na arena!') }}</template>
        </span>
    </div>
</template>
