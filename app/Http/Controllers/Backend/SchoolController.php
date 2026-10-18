<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\SchoolTeam;
use App\Models\SchoolTeamMember;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SchoolController extends Controller
{
    public function index()
    {
        $schools = School::with(['teams.members'])->orderBy('name')->get();

        return view('backend.schools.index', compact('schools'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:200|unique:schools,name',
            'city' => 'nullable|string|max:120',
        ]);
        School::create($data);

        return back()->with('success', 'Escola cadastrada.');
    }

    public function update(Request $request, School $school)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:200', Rule::unique('schools', 'name')->ignore($school->id)],
            'city' => 'nullable|string|max:120',
        ]);
        $school->update($data);

        return back()->with('success', 'Escola atualizada.');
    }

    public function destroy(School $school)
    {
        if ($school->teams()->whereHas('participations')->exists()) {
            return back()->with('error', 'A escola tem times com provas realizadas e não pode ser removida.');
        }
        $school->delete();

        return back()->with('success', 'Escola removida.');
    }

    public function storeTeam(Request $request, School $school)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:200', Rule::unique('school_teams', 'name')->where('school_id', $school->id)],
            'level' => 'required|integer|between:1,3',
        ]);
        $school->teams()->create($data);

        return back()->with('success', 'Time cadastrado.');
    }

    public function updateTeam(Request $request, SchoolTeam $team)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:200', Rule::unique('school_teams', 'name')->where('school_id', $team->school_id)->ignore($team->id)],
            'level' => 'required|integer|between:1,3',
        ]);
        $team->update($data);

        return back()->with('success', 'Time atualizado.');
    }

    public function destroyTeam(SchoolTeam $team)
    {
        if ($team->participations()->exists()) {
            return back()->with('error', 'O time já participou de uma prova e não pode ser removido.');
        }
        $team->delete();

        return back()->with('success', 'Time removido.');
    }

    public function storeMember(Request $request, SchoolTeam $team)
    {
        $data = $request->validate([
            'name' => 'required|string|max:200',
            'academic_registry' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:200',
            'phone' => 'nullable|string|max:50',
            'education_level' => 'nullable|string|max:100',
            'college_period' => 'nullable|string|max:50',
        ]);

        $team->members()->create($data);

        return back()->with('success', 'Participante adicionado.');
    }

    public function destroyMember(SchoolTeamMember $member)
    {
        $member->delete();

        return back()->with('success', 'Participante removido.');
    }
}
