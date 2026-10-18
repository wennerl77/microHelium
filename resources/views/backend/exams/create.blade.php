@extends('layouts.app')

@section('title', 'Agendar prova')

@section('description', 'Escolha o template, o horário de início e as escolas. Entram os times de cada escola que tenham o nível do template.')

@section('content')
<div class="space-y-6">
    <form id="templateSelectForm" method="GET" action="{{ route('backend.exams.create') }}" class="bg-card rounded-lg border border-border shadow-sm p-4 flex flex-wrap items-end gap-3">
        <label class="text-sm font-medium flex-1 min-w-[280px]">
            Template de prova
            <select id="templateSelect" name="template_id" class="mt-1 block w-full px-3 py-2 bg-background border border-border rounded-lg text-foreground text-sm">
                <option value="">Selecione um template…</option>
                @foreach($templates as $t)
                    <option value="{{ $t->id }}" @selected($template?->id === $t->id)>{{ $t->code }} — {{ $t->name }} ({{ $t->duration }} min)</option>
                @endforeach
            </select>
        </label>
        <button type="submit" class="button-primary">Continuar</button>
    </form>

    @if($template)
    <div class="bg-card rounded-lg border border-border shadow-sm p-6 space-y-6">
        <div class="p-4 rounded-lg bg-muted/60 border border-border/60 flex items-center justify-between gap-4 flex-wrap">
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold uppercase tracking-wider px-2 py-0.5 rounded bg-primary text-primary-foreground">{{ $template->code }}</span>
                    <h3 class="text-lg font-bold text-foreground">{{ $template->name }}</h3>
                </div>
                <p class="text-xs text-muted-foreground mt-1">
                    Duração: <strong>{{ $template->duration }} min</strong> · 
                    Penalidade: <strong>{{ $template->penalty }} min</strong> · 
                    Congela <strong>{{ $template->freeze_minutes }} min</strong> antes do fim · 
                    Questões: <strong>{{ $template->problems->count() }}</strong>
                </p>
            </div>
            <a href="{{ route('backend.exams.create') }}" class="button-secondary text-xs">Trocar template</a>
        </div>

        <form method="POST" action="{{ route('backend.exams.store') }}" class="space-y-5">
            @csrf
            <input type="hidden" name="template_id" value="{{ $template->id }}">

            @if($errors->any())
                <div class="p-3 rounded bg-destructive-soft text-destructive text-sm font-medium">
                    {{ $errors->first() }}
                </div>
            @endif

            <div>
                <label class="block text-sm font-medium text-foreground mb-1">Data e Horário de Início</label>
                <div class="flex flex-wrap sm:flex-nowrap gap-3 max-w-md">
                    <div class="w-full sm:w-1/2">
                        <label for="start_date" class="block text-xs text-muted-foreground mb-1">Data de início</label>
                        <input type="date" id="start_date" name="start_date" value="{{ old('start_date', now()->format('Y-m-d')) }}" required class="block w-full px-3 py-2 bg-background border border-border rounded-lg text-foreground text-sm">
                    </div>
                    <div class="w-full sm:w-1/2">
                        <label for="start_time" class="block text-xs text-muted-foreground mb-1">Horário de início</label>
                        <input type="time" id="start_time" name="start_time" value="{{ old('start_time') }}" required class="block w-full px-3 py-2 bg-background border border-border rounded-lg text-foreground text-sm">
                    </div>
                </div>
                <p class="text-xs text-muted-foreground mt-1.5">
                    Horário do servidor: <strong>{{ now()->format('H:i') }}</strong> ({{ config('app.timezone') }}). Os computadores das bancadas sincronizam com o início definido aqui.
                </p>
            </div>

            <fieldset class="space-y-3">
                <div class="flex items-center justify-between gap-2 flex-wrap">
                    <legend class="text-sm font-medium text-foreground">Escolas participantes</legend>
                    @if($schools->isNotEmpty())
                    <div class="flex items-center gap-3 text-xs">
                        <button type="button" id="selectAllSchools" class="text-primary hover:underline">Selecionar todas</button>
                        <span class="text-muted-foreground">·</span>
                        <button type="button" id="deselectAllSchools" class="text-muted-foreground hover:underline">Desmarcar todas</button>
                    </div>
                    @endif
                </div>

                <div class="grid gap-2 sm:grid-cols-2">
                    @forelse($schools as $school)
                    @php $teamCount = $preview[$school->id] ?? 0; @endphp
                    <label class="flex items-start gap-3 p-3 rounded-lg border border-border bg-background/50 hover:bg-muted/40 transition-colors cursor-pointer {{ $teamCount === 0 ? 'opacity-60' : '' }}">
                        <input type="checkbox" name="school_ids[]" value="{{ $school->id }}" class="school-checkbox mt-0.5 rounded border-border" @checked(in_array($school->id, old('school_ids', [])))>
                        <div class="text-sm">
                            <span class="font-medium text-foreground">{{ $school->name }}</span>
                            @if($school->city)
                                <span class="text-xs text-muted-foreground">({{ $school->city }})</span>
                            @endif
                            <div class="text-xs text-muted-foreground mt-0.5">
                                {{ $teamCount }} time(s) elegíveis {{ $template->code }}
                            </div>
                        </div>
                    </label>
                    @empty
                    <div class="col-span-full py-4 text-center text-sm text-muted-foreground">
                        Nenhuma escola cadastrada. <a class="text-primary hover:underline font-medium" href="{{ route('backend.schools') }}">Cadastrar escolas</a>
                    </div>
                    @endforelse
                </div>
            </fieldset>

            <div class="pt-2 flex items-center gap-3">
                <button class="button-primary" type="submit">Agendar Prova</button>
                <a href="{{ route('backend.exams') }}" class="button-secondary">Cancelar</a>
            </div>
        </form>
    </div>
    @endif
</div>

<script @cspNonce>
document.addEventListener('DOMContentLoaded', function () {
    const select = document.getElementById('templateSelect');
    const form = document.getElementById('templateSelectForm');
    if (select && form) {
        select.addEventListener('change', function () {
            if (select.value) {
                form.submit();
            }
        });
    }

    const selectAllBtn = document.getElementById('selectAllSchools');
    const deselectAllBtn = document.getElementById('deselectAllSchools');
    const checkboxes = document.querySelectorAll('.school-checkbox');

    if (selectAllBtn) {
        selectAllBtn.addEventListener('click', function () {
            checkboxes.forEach(function (cb) { cb.checked = true; });
        });
    }
    if (deselectAllBtn) {
        deselectAllBtn.addEventListener('click', function () {
            checkboxes.forEach(function (cb) { cb.checked = false; });
        });
    }
});
</script>
@endsection
