@extends('layouts.app')

@section('title', 'Prova Agendada — Credenciais de Acesso')

@section('content')
<div class="space-y-6">
    <div class="bg-card rounded-lg border border-border shadow-sm p-6 space-y-4">
        <div class="flex items-center justify-between gap-4 flex-wrap border-b border-border pb-4">
            <div>
                <span class="text-xs font-semibold px-2.5 py-0.5 rounded bg-primary/10 text-primary uppercase tracking-wide">Prova Agendada com Sucesso</span>
                <h2 class="text-2xl font-bold text-foreground mt-1">{{ $contest->name }}</h2>
                <p class="text-sm text-muted-foreground mt-0.5">
                    Template: <strong class="text-foreground">{{ $contest->examTemplate?->code ?? 'N/A' }}</strong> · 
                    Início: <strong class="text-foreground">{{ $contest->start_time?->format('d/m/Y H:i') }}</strong> · 
                    Duração: <strong class="text-foreground">{{ $contest->duration }} min</strong>
                </p>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                <button type="button" id="downloadCsvBtn" class="button-primary text-sm flex items-center gap-2">
                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Baixar Credenciais (.CSV)
                </button>
                <button type="button" id="copyAllBtn" class="button-secondary text-sm flex items-center gap-2">
                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                    </svg>
                    Copiar Todas
                </button>
                <a href="{{ route('backend.contest.operations', $contest) }}" class="button-secondary text-sm">
                    Operações da Prova
                </a>
                <a href="{{ route('backend.exams') }}" class="button-secondary text-sm">
                    Ver Provas
                </a>
            </div>
        </div>

        <div class="p-4 rounded-lg bg-warning-soft text-warning text-xs leading-relaxed flex items-start gap-2.5">
            <svg aria-hidden="true" class="h-5 w-5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <div>
                <strong>Atenção às Credenciais de Acesso:</strong> As senhas geradas são exibidas <strong>uma única vez</strong> nesta tela por motivos de segurança. Baixe o arquivo CSV ou copie os dados antes de sair desta página para distribuir às bancadas e aos coordenadores das escolas.
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left" id="credentialsTable">
                <thead class="bg-muted/50 text-xs uppercase text-muted-foreground">
                    <tr>
                        <th class="px-4 py-3">Escola / Campus</th>
                        <th class="px-4 py-3">Nome da Equipe</th>
                        <th class="px-4 py-3">Líder da Equipe</th>
                        <th class="px-4 py-3">Login de Acesso</th>
                        <th class="px-4 py-3">Senha de Acesso</th>
                        <th class="px-4 py-3">Demais Integrantes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @php $items = $credentials ?? $links ?? []; @endphp
                    @foreach($items as $c)
                    <tr class="hover:bg-muted/40 transition-colors">
                        <td class="px-4 py-3 text-muted-foreground">{{ $c['school'] }}</td>
                        <td class="px-4 py-3 font-semibold text-foreground">{{ $c['team'] }}</td>
                        <td class="px-4 py-3 text-foreground font-medium">{{ $c['leader'] ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="font-mono text-xs px-2 py-1 rounded bg-muted font-bold text-primary">{{ $c['username'] }}</span>
                        </td>
                        <td class="px-4 py-3">
                            @if(isset($c['password']))
                            <div class="inline-flex items-center gap-1.5 font-mono text-xs px-2 py-1 rounded bg-success-soft text-success font-bold">
                                <span>{{ $c['password'] }}</span>
                                <button type="button" data-copy-password="{{ $c['password'] }}" class="copy-password-btn hover:opacity-75 p-0.5" title="Copiar senha">
                                    <svg aria-hidden="true" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                </button>
                            </div>
                            @else
                                <span class="text-xs text-muted-foreground font-mono">{{ $c['path'] ?? '—' }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs text-muted-foreground max-w-xs truncate" title="{{ $c['members'] ?? '' }}">
                            {{ $c['members'] ?? '—' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<script @cspNonce>
document.addEventListener('DOMContentLoaded', function () {
    const credentialsData = @json($items);

    const downloadBtn = document.getElementById('downloadCsvBtn');
    if (downloadBtn) {
        downloadBtn.addEventListener('click', function () {
            if (!credentialsData || credentialsData.length === 0) return;

            let csvContent = "\uFEFF"; // BOM UTF-8 para Excel
            csvContent += "Escola;Nome da Equipe;Líder da Equipe;Login (Username);Senha;Integrantes\n";

            credentialsData.forEach(function(row) {
                const clean = (val) => '"' + (val || '').replace(/"/g, '""') + '"';
                csvContent += [
                    clean(row.school),
                    clean(row.team),
                    clean(row.leader),
                    clean(row.username),
                    clean(row.password),
                    clean(row.members)
                ].join(";") + "\n";
            });

            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement("a");
            const contestName = "{{ Str::slug($contest->name) }}";
            link.setAttribute("href", url);
            link.setAttribute("download", `credenciais_${contestName}.csv`);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });
    }

    const copyAllBtn = document.getElementById('copyAllBtn');
    if (copyAllBtn) {
        copyAllBtn.addEventListener('click', function () {
            if (!credentialsData || credentialsData.length === 0) return;

            let text = "Escola\tNome da Equipe\tLíder da Equipe\tLogin\tSenha\tIntegrantes\n";
            credentialsData.forEach(function(row) {
                text += `${row.school}\t${row.team}\t${row.leader || ''}\t${row.username}\t${row.password || ''}\t${row.members || ''}\n`;
            });

            navigator.clipboard.writeText(text).then(function() {
                const originalText = copyAllBtn.innerHTML;
                copyAllBtn.innerHTML = `<svg class="h-4 w-4 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg> Copiado!`;
                setTimeout(() => { copyAllBtn.innerHTML = originalText; }, 2500);
            });
        });
    }

    document.querySelectorAll('.copy-password-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const pass = btn.dataset.copyPassword;
            if (pass) {
                navigator.clipboard.writeText(pass);
            }
        });
    });
});
</script>
@endsection
