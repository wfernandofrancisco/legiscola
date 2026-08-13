<?php

namespace App\Livewire\Admin;

use App\Contracts\Services\ExamBuilderServiceInterface;
use App\Models\ExamTemplate;
use App\Models\Question;
use App\Models\Turma;
use Livewire\Component;
use Livewire\WithFileUploads;

class ConstrutorProvas extends Component
{
    use WithFileUploads;

    public ?int $classId = null;
    public string $title = '';
    public string $instructions = '';

    /** @var array<int> */
    public array $questionIds = [];

    /** @var array<int, \Livewire\Features\SupportFileUploads\TemporaryUploadedFile> */
    public array $attachments = [];

    public ?int $templateId = null;

    public function salvar(ExamBuilderServiceInterface $service): void
    {
        $this->validate([
            'classId' => ['required', 'integer', 'exists:classes,id'],
            'title' => ['required', 'string', 'max:255'],
            'instructions' => ['nullable', 'string'],
            'questionIds' => ['array'],
            'questionIds.*' => ['integer', 'exists:questions,id'],
            'attachments.*' => ['file', 'max:10240'],
        ]);

        $template = $service->criarTemplate([
            'class_id' => $this->classId,
            'title' => $this->title,
            'instructions' => $this->instructions,
        ]);

        $service->anexarQuestoes($template, $this->questionIds);
        $service->uploadAnexos($template, $this->attachments);

        $this->templateId = $template->id;
        $this->dispatch('toast', type: 'success', message: 'Prova montada com sucesso.');
    }

    public function render()
    {
        return view('livewire.admin.construtor-provas', [
            'turmas' => Turma::query()->with('course')->orderByDesc('id')->get(),
            'questions' => Question::query()->with('subject')->latest()->get(),
            'template' => $this->templateId ? ExamTemplate::query()->find($this->templateId) : null,
        ]);
    }
}
