@extends('layouts.app')

@section('title', 'Editar template ' . $template->code)

@section('description', 'As alterações valem apenas para provas agendadas depois.')

@section('content')
@php $selected = old('problems', $template->problems->pluck('id')->all()); @endphp
<form method="POST" action="{{ route('backend.exam-templates.update', $template) }}" class="bg-card rounded-lg border border-border shadow-sm p-6 space-y-4">
    @csrf
    @method('PUT')
    @if($errors->any())
        <div class="text-sm text-destructive">{{ $errors->first() }}</div>
    @endif
    <label class="block text-sm">Nome
        <input name="name" value="{{ old('name', $template->name) }}" required class="w-full px-3 py-2 bg-background border border-border rounded-lg">
    </label>
    <div class="grid gap-4 sm:grid-cols-3">
        <label class="block text-sm">Duração (min)
            <input type="number" min="1" name="duration" value="{{ old('duration', $template->duration) }}" required class="w-full px-3 py-2 bg-background border border-border rounded-lg">
        </label>
        <label class="block text-sm">Penalidade (min)
            <input type="number" min="0" name="penalty" value="{{ old('penalty', $template->penalty) }}" required class="w-full px-3 py-2 bg-background border border-border rounded-lg">
        </label>
        <label class="block text-sm">Congelar (min antes do fim)
            <input type="number" min="0" name="freeze_minutes" value="{{ old('freeze_minutes', $template->freeze_minutes) }}" required class="w-full px-3 py-2 bg-background border border-border rounded-lg">
        </label>
    </div>
    <div>
        <p class="text-sm font-medium mb-2">Questões (a ordem define as letras A, B, C…)</p>
        <div class="space-y-2">
            @for($i = 0; $i < max(count($selected), 12); $i++)
            <select name="problems[]" class="w-full px-3 py-2 bg-background border border-border rounded-lg text-sm">
                <option value="">—</option>
                @foreach($bank as $b)
                    <option value="{{ $b->id }}" @selected(($selected[$i] ?? null) == $b->id)>{{ $b->code }} — {{ $b->name }}</option>
                @endforeach
            </select>
            @endfor
        </div>
        <p class="text-xs text-muted-foreground mt-1">Linhas em branco são ignoradas.</p>
    </div>
    <div class="flex gap-2">
        <button class="button-primary" type="submit">Salvar</button>
        <a class="button-secondary" href="{{ route('backend.exam-templates') }}">Cancelar</a>
    </div>
</form>
@endsection
