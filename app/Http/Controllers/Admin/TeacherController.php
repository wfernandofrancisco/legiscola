<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\Services\TeacherServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Escola\StoreTeacherRequest;
use App\Http\Requests\Escola\UpdateTeacherRequest;
use App\Models\Teacher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class TeacherController extends Controller
{
    public function __construct(private TeacherServiceInterface $service) {}

    public function index(Request $request): View
    {
        $teachers = $this->service->paginateFiltered(15, $request->string('search')->toString());
        $breadcrumbs = [
            ['label' => 'Painel', 'href' => route('admin.dashboard')],
            ['label' => 'Professores'],
        ];
        return view('admin.teachers.index', compact('teachers', 'breadcrumbs'));
    }

    public function create(): View
    {
        $breadcrumbs = [
            ['label' => 'Painel', 'href' => route('admin.dashboard')],
            ['label' => 'Professores', 'href' => route('admin.professores.index')],
            ['label' => 'Novo professor'],
        ];
        return view('admin.teachers.create', compact('breadcrumbs'));
    }

    public function store(StoreTeacherRequest $request): RedirectResponse
    {
        $data = $request->validated();
        unset($data['photo']);
        $data['phone'] = preg_replace('/\D/', '', (string) ($data['celular'] ?? '')) ?: null;
        unset($data['celular']);

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('teachers', 'public');
        }

        $this->service->create($data);
        return redirect()->route('admin.professores.index')->with('success', 'Professor cadastrado com sucesso.');
    }

    public function edit(Teacher $professore): View
    {
        $teacher = $professore;
        $breadcrumbs = [
            ['label' => 'Painel', 'href' => route('admin.dashboard')],
            ['label' => 'Professores', 'href' => route('admin.professores.index')],
            ['label' => 'Editar professor'],
        ];
        return view('admin.teachers.edit', compact('teacher', 'breadcrumbs'));
    }

    public function update(UpdateTeacherRequest $request, Teacher $professore): RedirectResponse
    {
        $data = $request->validated();
        unset($data['photo']);
        $data['phone'] = preg_replace('/\D/', '', (string) ($data['celular'] ?? '')) ?: null;
        unset($data['celular']);

        if ($request->hasFile('photo')) {
            if ($professore->photo_path) {
                Storage::disk('public')->delete($professore->photo_path);
            }
            $data['photo_path'] = $request->file('photo')->store('teachers', 'public');
        }

        $this->service->update($professore, $data);
        return redirect()->route('admin.professores.index')->with('success', 'Professor atualizado com sucesso.');
    }

    public function destroy(Teacher $professore): RedirectResponse
    {
        $this->service->delete($professore);
        return redirect()->route('admin.professores.index')->with('success', 'Professor removido com sucesso.');
    }
}
