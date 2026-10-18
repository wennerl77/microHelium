<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Contest;
use App\Models\ExamTemplate;
use App\Models\School;
use App\Services\ExamScheduler;
use Illuminate\Http\Request;

class ExamScheduleController extends Controller
{
    public function index()
    {
        $contests = Contest::with('examTemplate')
            ->whereNotNull('exam_template_id')
            ->withCount('users')
            ->orderByDesc('start_time')
            ->get();

        return view('backend.exams.index', compact('contests'));
    }

    public function create(Request $request, ExamScheduler $scheduler)
    {
        $templates = ExamTemplate::with('problems')->orderBy('level')->get();
        $schools = School::whereNull('archived_at')->with('teams')->orderBy('name')->get();

        // Preview: how many teams each school would put in the chosen exam.
        $preview = [];
        $template = $templates->firstWhere('id', (int) $request->query('template_id'));
        if ($template) {
            foreach ($schools as $school) {
                $preview[$school->id] = $scheduler->eligibleTeams($template, [$school->id])->count();
            }
        }

        return view('backend.exams.create', compact('templates', 'schools', 'preview', 'template'));
    }

    public function store(Request $request, ExamScheduler $scheduler)
    {
        $startDate = $request->input('start_date');
        $startTime = $request->input('start_time');

        if ($request->has('start_date')) {
            $combinedStart = trim((string) $startDate) . ' ' . trim((string) $startTime);
        } else {
            $combinedStart = (string) $startTime;
        }

        $validator = \Illuminate\Support\Facades\Validator::make(
            array_merge($request->all(), ['combined_start' => $combinedStart]),
            [
                'template_id' => 'required|exists:exam_templates,id',
                'start_date' => $request->has('start_date') ? 'required|date_format:Y-m-d' : 'nullable',
                'start_time' => 'required',
                'combined_start' => 'required|date|after:now',
                'school_ids' => 'required|array|min:1',
                'school_ids.*' => 'integer|exists:schools,id',
            ],
            [
                'template_id.required' => 'O template de prova é obrigatório.',
                'template_id.exists' => 'O template selecionado não existe.',
                'start_date.required' => 'A data de início é obrigatória.',
                'start_date.date_format' => 'A data de início deve estar no formato válido (AAAA-MM-DD).',
                'start_time.required' => 'O horário de início é obrigatório.',
                'combined_start.required' => 'A data e o horário de início são obrigatórios.',
                'combined_start.date' => 'A data e horário informados são inválidos.',
                'combined_start.after' => 'O horário de início deve ser posterior ao horário atual do servidor (' . now()->format('H:i') . ').',
                'school_ids.required' => 'Selecione ao menos uma escola participante.',
                'school_ids.min' => 'Selecione ao menos uma escola participante.',
            ]
        );

        if ($validator->fails()) {
            return back()->withInput()->withErrors($validator);
        }

        $data = $validator->validated();

        try {
            $result = $scheduler->schedule(
                ExamTemplate::findOrFail($data['template_id']),
                \Illuminate\Support\Carbon::parse($combinedStart),
                $data['school_ids'],
                $request->user()->user_id,
            );
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['school_ids' => $e->getMessage()]);
        }

        // Activation links are shown once: only their hash is stored.
        return view('backend.exams.scheduled', $result);
    }
}
