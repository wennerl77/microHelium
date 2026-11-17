<!doctype html>
<html lang="{{ \App\Support\InterfaceLocale::htmlLang() }}" class="h-full">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('img/favicon.ico') }}">
    <title>@yield('title', __('Entrar')) · MicroHelium</title>
    @include('partials.theme-init')
    @include('partials.i18n-catalog')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<div class="reading-progress" aria-hidden="true"></div>
<a href="#main-content" class="skip-link">{{ __('Pular para o conteúdo') }}</a>
<div id="app" class="auth-shell">
    <aside class="auth-story">
        @include('partials.brand')
        <div class="auth-story-copy">
            <div class="flex items-center gap-4 mb-4">
                <img src="/img/pato.svg" alt="" aria-hidden="true" width="64" height="64" class="shrink-0" />
                <span class="eyebrow">{{ __('IDEIAS. CÓDIGO. CONQUISTAS.') }}</span>
            </div>
            <h2>{!! __('Seu próximo<br>desafio começa<br><em>aqui.</em>') !!}</h2>
            <p>{{ __('Um espaço para resolver problemas, evoluir com seu time e transformar conhecimento em soluções.') }}</p>
            <div class="code-window" aria-hidden="true">
                <span class="code-window-label" translate="no">solution.cpp</span>
                <code>
                    <span class="syn-cm">// {{ __('uma ideia de cada vez') }}</span><br>
                    <span class="syn-kw" translate="no">while</span> <span translate="no">(</span><span class="syn-ty" translate="no">bool</span> <span translate="no">challenge =</span> <span class="syn-val" translate="no">true</span><span translate="no">) {</span><br>
                    <span translate="no">&nbsp;&nbsp;learn();</span><br>
                    <span translate="no">&nbsp;&nbsp;build();</span><br>
                    <span translate="no">&nbsp;&nbsp;try_again();</span><br>
                    <span translate="no">}</span>
                </code>
                <span class="code-result">✓ {{ __('Pronto para o próximo desafio') }}</span>
            </div>
        </div>
        <p class="text-sm">MicroHelium · {{ __('Competições de programação') }}</p>
    </aside>
    <main id="main-content" tabindex="-1" class="auth-main">
        <div class="auth-topbar"><div class="flex items-center gap-2 ml-auto">@include('partials.locale-switcher')<theme-toggle></theme-toggle></div></div>
        <div class="auth-form">
            <p class="eyebrow mb-3">{{ __('BEM-VINDO À ARENA') }}</p>
            <h1>@yield('auth-title', 'MicroHelium')</h1>
            <p class="text-muted-foreground mt-3 mb-8">@yield('auth-subtitle', __('Sistema de Maratonas de Programação'))</p>
            @foreach(['error' => 'destructive', 'success' => 'success', 'status' => 'success'] as $key => $tone)
                @if(session($key))<div role="status" class="mb-4 rounded-lg border p-4 text-sm">{{ session($key) }}</div>@endif
            @endforeach
            @include('partials.error-summary')
            @yield('content')
            @hasSection('footer')<div class="auth-footer">@yield('footer')</div>@endif
        </div>
        <p class="auth-copyright">&copy; {{ date('Y') }} MicroHelium</p>
        {{-- AGPL-3.0 secao 13: quem ve o login ja esta usando o sistema pela rede. --}}
        <p class="auth-copyright">{{ __('Software livre sob') }} <a href="{{ config('app.source_url') }}/blob/master/LICENSE">AGPL-3.0-or-later</a> — <a href="{{ config('app.source_url') }}">{{ __('código-fonte desta instalação') }}</a></p>
    </main>
</div>
@yield('scripts')
</body></html>
