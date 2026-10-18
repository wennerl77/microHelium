@extends('layouts.app')

@section('title', 'Templates de prova')

@section('description', 'Modelos fixos das provas N1, N2 e N3. Editar um template vale apenas para provas agendadas depois.')

@section('content')
<div class="space-y-6">
    @foreach($templates as $template)
    <div class="bg-card rounded-lg border border-border shadow-sm p-6">
        <div class="flex items-center justify-between gap-4 flex-wrap">
            <div>
                <h2 class="text-xl font-semibold text-foreground">{{ $template->code }} — {{ $template->name }}</h2>
                <p class="text-sm text-muted-foreground">{{ $template->duration }} min · penalidade {{ $template->penalty }} min · congela {{ $template->freeze_minutes }} min antes do fim</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('backend.exams.create', ['template_id' => $template->id]) }}" class="button-primary text-sm">Agendar Prova</a>
                <a href="{{ route('backend.exam-templates.edit', $template) }}" class="button-secondary text-sm">Editar</a>
            </div>
        </div>
        <ol class="mt-4 grid gap-1 sm:grid-cols-2 text-sm">
            @foreach($template->problems as $problem)
            <li class="text-foreground"><span class="font-mono text-muted-foreground">{{ $problem->pivot->position }}.</span> {{ $problem->code }} — {{ $problem->name }}</li>
            @endforeach
        </ol>
    </div>
    @endforeach
</div>
@endsection
