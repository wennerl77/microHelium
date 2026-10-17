@extends('layouts.app')

@section('title', __('Polygon - Autoria de Problemas'))

@section('description', __('Ambiente de autoria determinística de testes em C++ (testlib.h), geração via scripts e calibração de limites.'))

@section('content')
<div data-feature-page="polygon-workspace" data-exercise-id="{{ $exerciseId ?? '' }}">
    <div class="surface p-8 text-center text-muted-foreground" role="status">
        <p class="animate-pulse">{{ __('Carregando Polygon Workspace...') }}</p>
    </div>
</div>
<noscript>
    <div class="surface feature-empty">
        <h2>{{ __('Ative o JavaScript para usar o Polygon') }}</h2>
        <p>{{ __('O ambiente de autoria e calibração de problemas requer JavaScript para execução interativa das ferramentas e validações.') }}</p>
        <a href="/backend/exercises" class="button-secondary">{{ __('Voltar aos Problemas') }}</a>
    </div>
</noscript>
@endsection
