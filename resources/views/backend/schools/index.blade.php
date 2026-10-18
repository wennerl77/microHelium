@extends('layouts.app')

@section('title', 'Escolas e Participantes')

@section('description', 'Cadastre as escolas, seus times e os alunos participantes de cada nível (N1, N2 ou N3).')

@section('content')
<div class="space-y-6">
    @if($errors->any())
        <div class="p-4 rounded-lg bg-destructive-soft text-destructive text-sm font-medium">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="bg-card rounded-lg border border-border shadow-sm p-4 flex flex-wrap items-center justify-between gap-4">
        <form method="POST" action="{{ route('backend.schools.store') }}" class="flex flex-wrap items-end gap-2 w-full md:w-auto">
            @csrf
            <label class="text-sm">Escola / Campus
                <input name="name" required placeholder="Ex: IFMG - Campus Ouro Branco" class="block px-3 py-2 bg-background border border-border rounded-lg text-sm">
            </label>
            <label class="text-sm">Cidade
                <input name="city" placeholder="Ex: Ouro Branco" class="block px-3 py-2 bg-background border border-border rounded-lg text-sm">
            </label>
            <button class="button-primary" type="submit">Adicionar escola</button>
        </form>

        <div class="text-xs text-muted-foreground">
            Dica: Para sincronizar os dados do CSV de inscrições, execute <code class="bg-muted px-1.5 py-0.5 rounded font-mono">php artisan turing:import-registrations</code>
        </div>
    </div>

    @foreach($schools as $school)
    <div class="bg-card rounded-lg border border-border shadow-sm p-6 space-y-4">
        <div class="flex items-center justify-between gap-4 flex-wrap border-b border-border pb-3">
            <div>
                <h2 class="text-lg font-semibold text-foreground flex items-center gap-2">
                    {{ $school->name }}
                    @if($school->city)
                        <span class="text-xs px-2 py-0.5 font-normal rounded-full bg-muted text-muted-foreground">{{ $school->city }}</span>
                    @endif
                </h2>
                <p class="text-xs text-muted-foreground mt-0.5">
                    {{ $school->teams->count() }} equipe(s) cadastrada(s)
                    (N1: {{ $school->teams->where('level', 1)->count() }} | N2: {{ $school->teams->where('level', 2)->count() }} | N3: {{ $school->teams->where('level', 3)->count() }})
                </p>
            </div>
            <form method="POST" action="{{ route('backend.schools.destroy', $school) }}" data-confirm="Remover a escola {{ $school->name }} e todos os seus times?">
                @csrf @method('DELETE')
                <button class="text-xs text-destructive hover:underline p-1" type="submit">Excluir escola</button>
            </form>
        </div>

        <div class="space-y-4">
            @forelse($school->teams as $team)
            <div class="rounded-lg border border-border p-4 bg-background/50 space-y-3">
                <div class="flex items-center justify-between gap-2 flex-wrap">
                    <form method="POST" action="{{ route('backend.schools.teams.update', $team) }}" class="flex items-center gap-2 flex-wrap">
                        @csrf @method('PUT')
                        <input name="name" value="{{ $team->name }}" required class="px-2.5 py-1 text-sm font-medium bg-background border border-border rounded">
                        <select name="level" class="px-2 py-1 text-xs font-semibold bg-background border border-border rounded">
                            @foreach([1, 2, 3] as $lvl)
                                <option value="{{ $lvl }}" @selected($team->level === $lvl)>N{{ $lvl }}</option>
                            @endforeach
                        </select>
                        <button class="button-secondary text-xs px-2 py-1 min-h-[30px]" type="submit">Salvar time</button>
                    </form>

                    <form method="POST" action="{{ route('backend.schools.teams.destroy', $team) }}" data-confirm="Remover time {{ $team->name }}?">
                        @csrf @method('DELETE')
                        <button class="text-xs text-muted-foreground hover:text-destructive p-1" type="submit" title="Excluir time">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </form>
                </div>

                {{-- Integrantes do Time --}}
                <div class="pl-2 border-l-2 border-primary/20 space-y-2">
                    <div class="text-xs font-semibold text-muted-foreground uppercase tracking-wide">
                        Participantes ({{ $team->members->count() }})
                    </div>

                    @if($team->members->isNotEmpty())
                    <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach($team->members as $member)
                        <div class="bg-card rounded border border-border/80 p-2.5 text-xs flex justify-between items-start gap-2">
                            <div class="space-y-0.5 overflow-hidden">
                                <p class="font-medium text-foreground truncate" title="{{ $member->name }}">{{ $member->name }}</p>
                                @if($member->academic_registry)
                                    <p class="text-muted-foreground">RA: <span class="font-mono text-foreground">{{ $member->academic_registry }}</span></p>
                                @endif
                                @if($member->education_level)
                                    <p class="text-muted-foreground truncate" title="{{ $member->education_level }}">{{ $member->education_level }}
                                        @if($member->college_period) ({{ $member->college_period }}) @endif
                                    </p>
                                @endif
                                @if($member->email)
                                    <p class="text-muted-foreground truncate" title="{{ $member->email }}">{{ $member->email }}</p>
                                @endif
                            </div>
                            <form method="POST" action="{{ route('backend.schools.members.destroy', $member) }}" data-confirm="Remover {{ $member->name }}?">
                                @csrf @method('DELETE')
                                <button class="text-muted-foreground/60 hover:text-destructive p-0.5" type="submit" title="Remover aluno">
                                    &times;
                                </button>
                            </form>
                        </div>
                        @endforeach
                    </div>
                    @else
                        <p class="text-xs text-muted-foreground italic">Nenhum aluno vinculado a esta equipe.</p>
                    @endif

                    {{-- Form para adicionar aluno --}}
                    <details class="text-xs pt-1">
                        <summary class="cursor-pointer text-primary hover:underline font-medium">+ Adicionar aluno ao time</summary>
                        <form method="POST" action="{{ route('backend.schools.members.store', $team) }}" class="mt-2 p-2.5 bg-card rounded border border-border flex flex-wrap gap-2 items-end">
                            @csrf
                            <label class="block text-xs">Nome
                                <input name="name" required class="block px-2 py-1 text-xs bg-background border border-border rounded mt-0.5">
                            </label>
                            <label class="block text-xs">RA
                                <input name="academic_registry" class="block px-2 py-1 text-xs bg-background border border-border rounded mt-0.5">
                            </label>
                            <label class="block text-xs">Escolaridade
                                <input name="education_level" placeholder="Ex: 1º ano" class="block px-2 py-1 text-xs bg-background border border-border rounded mt-0.5">
                            </label>
                            <label class="block text-xs">E-mail
                                <input name="email" type="email" class="block px-2 py-1 text-xs bg-background border border-border rounded mt-0.5">
                            </label>
                            <label class="block text-xs">Celular
                                <input name="phone" class="block px-2 py-1 text-xs bg-background border border-border rounded mt-0.5">
                            </label>
                            <button class="button-secondary text-xs px-2 py-1 min-h-[28px]" type="submit">Salvar aluno</button>
                        </form>
                    </details>
                </div>
            </div>
            @empty
                <p class="text-sm text-muted-foreground py-2">Nenhum time cadastrado nesta escola ainda.</p>
            @endforelse
        </div>

        <form method="POST" action="{{ route('backend.schools.teams.store', $school) }}" class="pt-3 border-t border-border flex items-end gap-2 flex-wrap">
            @csrf
            <label class="text-sm">Novo time
                <input name="name" required placeholder="Nome do time" class="block px-3 py-1.5 text-sm bg-background border border-border rounded-lg mt-0.5">
            </label>
            <label class="text-sm">Nível
                <select name="level" class="block px-3 py-1.5 text-sm bg-background border border-border rounded-lg mt-0.5">
                    @foreach([1, 2, 3] as $lvl)<option value="{{ $lvl }}">N{{ $lvl }}</option>@endforeach
                </select>
            </label>
            <button class="button-primary text-xs px-3 py-2 min-h-[34px]" type="submit">Adicionar time</button>
        </form>
    </div>
    @endforeach
</div>
@endsection
