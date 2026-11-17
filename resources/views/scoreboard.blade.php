@extends('layouts.app')

@section('title', __('Placar'))

@section('description', __('Acompanhe a classificação e os problemas resolvidos pelos times.'))

@section('content')
<div class="space-y-6">
    @php $podiumEntries = collect($entries)->take(3)->values(); @endphp
    @if($podiumEntries->count() >= 3)
    <section class="surface overflow-hidden" aria-label="{{ __('Pódio da Competição') }}">
        <div class="border-b border-border px-6 py-4 flex items-center justify-between gap-4">
            <div>
                <span class="chapter-label">{{ __('CLASSIFICAÇÃO PRINCIPAL') }}</span>
                <h2 class="text-lg font-semibold mt-1">{{ __('Pódio da Competição') }}</h2>
            </div>
        </div>
        <div class="turing-podium">
            @foreach($podiumEntries as $podiumIndex => $podiumEntry)
                @php
                    $place = $podiumIndex + 1;
                    $teamLabel = $podiumEntry['user']->fullname ?? $podiumEntry['user']->username ?? __('Usuario #:id', ['id' => $podiumEntry['user_id']]);
                @endphp
                <article class="turing-podium-slot turing-podium-{{ $place }}">
                    <div class="flex items-center justify-between gap-2">
                        <span class="inline-flex items-center gap-1.5 font-mono text-xs font-bold uppercase tracking-wider {{ $place === 1 ? 'text-accent' : 'text-primary' }}">
                            @if($place === 1)
                            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/><path d="M4 22h16"/><path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"/><path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"/><path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"/></svg>
                            @endif
                            {{ $podiumEntry['rank'] }}<span aria-hidden="true">º</span>
                        </span>
                        <span class="font-mono text-xs {{ $place === 1 ? 'text-brand-muted' : 'text-muted-foreground' }}">{{ $podiumEntry['problems_solved'] }} {{ __('Resolvidos') }}</span>
                    </div>
                    <div>
                        <h3 class="text-base font-bold leading-snug">{{ $teamLabel }}</h3>
                        <p class="font-mono text-xs mt-1 {{ $place === 1 ? 'text-brand-muted' : 'text-muted-foreground' }}">{{ $podiumEntry['total_time'] }} <span translate="no">min</span></p>
                    </div>
                </article>
            @endforeach
        </div>
    </section>
    @endif

    <!-- Main Scoreboard -->
    <div class="rounded-xl border border-border bg-card overflow-hidden">
        <div class="border-b border-border px-6 py-4">
            <h2 class="text-lg font-semibold flex items-center gap-2">
                <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                {{ __('Placar da Competição') }}
            </h2>
            <p class="text-sm text-muted-foreground">{{ __('Classificação dos times participantes. Recarregue para consultar novos resultados.') }}</p>
        </div>
        {{-- Issue #211: até aqui o congelamento não escondia nada, e esta
             página nem chegava a perguntar. Agora que esconde, ela precisa
             dizer que esconde -- um placar congelado sem aviso é um placar
             errado. --}}
        @if($frozen ?? false)
        <div class="border-b border-warning/40 bg-warning-soft px-6 py-3">
            <p class="text-sm font-medium text-warning">{{ __('Placar congelado') }}</p>
            <p class="text-sm text-muted-foreground">{!! __('As submissões feitas a partir do congelamento aparecem como pendentes (<span class="font-mono">?</span>) e não alteram a classificação. A competição continua.') !!}</p>
        </div>
        @endif
        @include('partials.table-filter', ['tableId' => 'scoreboard-table', 'searchLabel' => __('times'), 'difficulty' => false])
        <div class="overflow-x-auto">
            <table id="scoreboard-table" class="w-full">
                <thead>
                    <tr class="border-b border-border bg-muted/50">
                        <th class="w-16 px-4 py-3 text-center text-xs font-medium text-muted-foreground uppercase tracking-wider">#</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">{{ __('Time') }}</th>
                        <th class="w-24 px-4 py-3 text-center text-xs font-medium text-muted-foreground uppercase tracking-wider">{{ __('Resolvidos') }}</th>
                        <th class="w-28 px-4 py-3 text-center text-xs font-medium text-muted-foreground uppercase tracking-wider">{{ __('Penalidade') }}</th>
                        @foreach ($problems as $problem)
                        <th class="w-14 px-2 py-3 text-center text-xs font-medium text-muted-foreground uppercase tracking-wider" title="{{ $problem->name }}">{{ $problem->short_name }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse ($entries as $index => $entry)
                    <tr class="rk-row hover:bg-muted/50 transition-colors {{ $index < 3 ? 'bg-success-soft' : '' }}">
                        <td class="px-4 py-3 text-center">
                            @if($index < 3)
                                <span class="rank-number">{{ $entry['rank'] }}</span>
                            @else
                                <span class="rk-rank-outline" aria-label="{{ $entry['rank'] }}">{{ $entry['rank'] }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="font-semibold">{{ $entry['user']->fullname ?? $entry['user']->username ?? __('Usuario #:id', ['id' => $entry['user_id']]) }}</div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center rounded-md bg-primary-soft px-2.5 py-1 text-sm font-semibold text-primary ring-1 ring-inset ring-primary/20">
                                {{ $entry['problems_solved'] }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center text-sm text-muted-foreground">
                            {{ $entry['total_time'] }} min
                        </td>
                        @foreach ($problems as $problem)
                            @php $ps = $entry['problems']->get($problem->id); @endphp
                            <td class="px-2 py-3 text-center">
                                @if($ps && $ps['is_solved'])
                                    <span class="inline-flex flex-col items-center justify-center w-10 h-9 rounded bg-success-soft text-xs font-medium text-success ring-1 ring-inset ring-success/20" title="{{ __(':problem: Aceito na tentativa :attempt', ['problem' => $problem->name, 'attempt' => $ps['attempts']]) }}">
                                        +{{ $ps['attempts'] > 1 ? $ps['attempts'] - 1 : '' }}
                                    </span>
                                {{-- Issue #211: sem este estado, uma célula
                                     congelada com quatro submissões dentro
                                     caía no "Não tentado" lá embaixo, que é
                                     falso e é justamente a informação que a
                                     ICPC mantém visível durante o
                                     congelamento. --}}
                                @elseif($ps && ($ps['pending'] ?? 0) > 0)
                                    <span class="inline-flex items-center justify-center w-10 h-9 rounded bg-warning-soft text-xs font-medium text-warning ring-1 ring-inset ring-warning/20" title="{{ __(':problem: :count submissão(ões), resultado não divulgado durante o congelamento', ['problem' => $problem->name, 'count' => $ps['attempts'] + $ps['pending']]) }}">
                                        ?{{ $ps['attempts'] + $ps['pending'] }}
                                    </span>
                                @elseif($ps && $ps['attempts'] > 0)
                                    <span class="inline-flex items-center justify-center w-10 h-9 rounded bg-destructive-soft text-xs font-medium text-destructive ring-1 ring-inset ring-destructive/20" title="{{ __(':problem: :count tentativa(s) incorreta(s)', ['problem' => $problem->name, 'count' => $ps['attempts']]) }}">
                                        -{{ $ps['attempts'] }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center justify-center w-10 h-9 rounded bg-muted text-xs font-medium text-muted-foreground ring-1 ring-inset ring-border" title="{{ __(':problem: Não tentado', ['problem' => $problem->name]) }}">
                                        &middot;
                                    </span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ 4 + count($problems) }}" class="px-4 py-12 text-center">
                            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-muted-foreground/50 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <p class="text-muted-foreground">{{ __('Nenhum time participando ainda') }}</p>
                        </td>
                    </tr>
                    @endforelse
                <tr id="scoreboard-table-empty" hidden><td colspan="{{ 4 + count($problems) }}" class="text-center text-muted-foreground">{{ __('Nenhum resultado para estes filtros. Tente outro termo ou limpe a busca.') }}</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Legend & Timer -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 rounded-xl border border-border bg-card overflow-hidden">
            <div class="border-b border-border px-6 py-4">
                <h2 class="text-lg font-semibold flex items-center gap-2">
                    <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ __('Legenda') }}
                </h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-3">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded bg-success-soft text-xs font-medium text-success ring-1 ring-inset ring-success/20">+</span>
                            <span class="text-sm text-muted-foreground">{{ __('Problema aceito') }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded bg-destructive-soft text-xs font-medium text-destructive ring-1 ring-inset ring-destructive/20">−1</span>
                            <span class="text-sm text-muted-foreground">{{ __('Tentativas incorretas') }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded bg-muted text-xs font-medium text-muted-foreground ring-1 ring-inset ring-border">·</span>
                            <span class="text-sm text-muted-foreground">{{ __('Não tentado') }}</span>
                        </div>
                    </div>
                    <div class="space-y-2 text-sm">
                        <p><strong class="text-foreground">{{ __('Resolvidos:') }}</strong> <span class="text-muted-foreground">{{ __('Número de problemas aceitos') }}</span></p>
                        <p><strong class="text-foreground">{{ __('Penalidade:') }}</strong> <span class="text-muted-foreground">{{ __('Tempo total + penalidades por erros') }}</span></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-border bg-card overflow-hidden">
            <div class="border-b border-border px-6 py-4">
                <h2 class="text-lg font-semibold flex items-center gap-2">
                    <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ __('Tempo') }}
                </h2>
            </div>
            <div class="p-6 text-center">
                <div class="text-4xl font-mono font-bold text-foreground mb-2" id="scoreboard-timer">
                    <contest-timer></contest-timer>
                </div>
                <p class="text-sm text-muted-foreground">{{ __('Tempo restante') }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
