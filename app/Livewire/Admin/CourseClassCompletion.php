<?php

namespace App\Livewire\Admin;

use App\Contracts\Services\CourseClassServiceInterface;
use Livewire\Component;

class CourseClassCompletion extends Component
{
    public int $courseClassId;

    public int $minimumAttendance = 75;

    public function complete(CourseClassServiceInterface $service): void
    {
        $service->completeClass($this->courseClassId, $this->minimumAttendance);
        session()->flash('success', 'Turma encerrada com sucesso.');
    }

    public function render()
    {
        return view('livewire.admin.course-class-completion');
    }
}
