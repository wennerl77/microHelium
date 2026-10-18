<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\ExamTemplate;
use App\Models\ProblemBank;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Templates N1/N2/N3 sao fixos (seed): aqui so se visualiza e edita. Nao ha
 * create nem destroy de proposito. Editar nao altera provas ja agendadas,
 * porque o agendamento copia as questoes para o contest.
 */
class ExamTemplateController extends Controller
{
    public function index()
    {
        $templates = ExamTemplate::with('problems')->orderBy('level')->get();

        return view('backend.exams.templates', compact('templates'));
    }

    public function edit(ExamTemplate $template)
    {
        $template->load('problems');
        $bank = ProblemBank::where('is_active', true)->orderBy('code')->get(['id', 'code', 'name']);

        return view('backend.exams.template-edit', compact('template', 'bank'));
    }

    public function update(Request $request, ExamTemplate $template): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:200',
            'duration' => 'required|integer|min:1',
            'penalty' => 'required|integer|min:0',
            'freeze_minutes' => 'required|integer|min:0',
            'problems' => 'required|array|min:1|max:26',
            'problems.*' => ['integer', 'distinct', Rule::exists('problem_bank', 'id')->where('is_active', true)],
        ]);

        DB::transaction(function () use ($template, $data) {
            $template->update(collect($data)->except('problems')->all());
            $template->problems()->detach();
            foreach (array_values($data['problems']) as $i => $id) {
                $template->problems()->attach($id, ['position' => $i + 1]);
            }
        });

        return redirect()->route('backend.exam-templates')->with('success', "Template {$template->code} atualizado. Provas já agendadas não foram alteradas.");
    }
}
