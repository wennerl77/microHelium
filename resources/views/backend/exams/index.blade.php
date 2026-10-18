@extends('layouts.app')

@section('title', 'Provas agendadas')

@section('description', 'Provas criadas a partir dos templates N1/N2/N3.')

@section('content')
<div class="bg-card rounded-lg border border-border shadow-sm p-6 space-y-4">
    <div class="flex items-center justify-between gap-4">
        <h2 class="text-xl font-semibold text-foreground">Provas</h2>
        <a href="{{ route('backend.exams.create') }}" class="button-primary">Agendar prova</a>
    </div>
    <table class="w-full text-sm">
        <thead><tr class="text-left text-muted-foreground"><th class="py-2">Prova</th><th>Template</th><th>Início</th><th>Duração</th><th>Contas</th></tr></thead>
        <tbody class="divide-y divide-border">
            @forelse($contests as $c)
            <tr>
                <td class="py-2 font-medium">{{ $c->name }}</td>
                <td>{{ $c->examTemplate?->code ?? '—' }}</td>
                <td>{{ $c->start_time?->format('d/m/Y H:i') }}</td>
                <td>{{ $c->duration }} min</td>
                <td>{{ $c->users_count }}</td>
            </tr>
            @empty
            <tr><td colspan="5" class="py-6 text-center text-muted-foreground">Nenhuma prova agendada.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
