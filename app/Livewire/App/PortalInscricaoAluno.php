<?php

namespace App\Livewire\App;

use App\Contracts\Repositories\CourseClassRepositoryInterface;
use App\Contracts\Repositories\EventRepositoryInterface;
use App\Contracts\Services\EnrollmentServiceInterface;
use App\Models\EventEnrollment;
use App\Models\Enrollment;
use App\Models\Student;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class PortalInscricaoAluno extends Component
{
    public ?int $studentId = null;

    public function mount(): void
    {
        $this->studentId = Student::query()
            ->where('user_id', auth()->id())
            ->value('id');
    }

    public function inscreverEmTurma(int $courseClassId, EnrollmentServiceInterface $service): void
    {
        if (! $this->studentId) {
            session()->flash('error', 'Perfil de aluno não encontrado.');
            return;
        }

        $service->matricularEmTurma($this->studentId, $courseClassId);
        session()->flash('success', 'Matrícula registrada com sucesso.');
    }

    public function inscreverEmEvento(int $eventId, EnrollmentServiceInterface $service): void
    {
        if (! $this->studentId) {
            session()->flash('error', 'Perfil de aluno não encontrado.');
            return;
        }

        try {
            $service->inscreverEmEvento($this->studentId, $eventId);
        } catch (ValidationException $e) {
            session()->flash('error', collect($e->errors())->flatten()->first());

            return;
        }

        session()->flash('success', 'Inscrição em evento registrada com sucesso.');
    }

    public function render()
    {
        $courseClasses = app(CourseClassRepositoryInterface::class)->listOpenForEnrollment();
        $events = app(EventRepositoryInterface::class)->listOpenForEnrollment();

        $classEnrollments = $this->studentId
            ? Enrollment::query()->where('student_id', $this->studentId)->pluck('course_class_id')->all()
            : [];

        $eventEnrollments = $this->studentId
            ? EventEnrollment::query()->where('student_id', $this->studentId)->pluck('event_id')->all()
            : [];

        return view('livewire.app.portal-inscricao-aluno', compact('courseClasses', 'events', 'classEnrollments', 'eventEnrollments'));
    }
}
