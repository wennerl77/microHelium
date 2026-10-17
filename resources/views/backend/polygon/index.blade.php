@extends('layouts.app')

@section('title', __('Polygon - Painel de Autoria'))

@section('description', __('Painel central de gestão e criação de problemas determinísticos para maratonas e banco geral de questões.'))

@section('content')
<div data-feature-page="polygon-dashboard">
    <div class="surface p-8 text-center text-muted-foreground" role="status">
        <p class="animate-pulse">{{ __('Carregando Painel do Polygon...') }}</p>
    </div>
</div>
<noscript>
    <div class="surface feature-empty">
        <h2>{{ __('Ative o JavaScript para usar o Polygon') }}</h2>
        <p>{{ __('O painel de gerenciamento e criação de problemas requer JavaScript para navegação e operações em tempo real.') }}</p>
        <a href="/backend/exercises" class="button-secondary">{{ __('Voltar aos Problemas') }}</a>
    </div>
</noscript>
@endsection
