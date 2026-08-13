<?php

namespace App\Http\Controllers\Professor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Escola\StoreCourseClassAnnouncementRequest;
use App\Jobs\ProcessCourseClassAnnouncementJob;
use App\Models\CourseClass;
use App\Models\CourseClassAnnouncement;
use Illuminate\Http\RedirectResponse;

class CourseClassAnnouncementController extends Controller
{
    public function store(StoreCourseClassAnnouncementRequest $request, CourseClass $turma): RedirectResponse
    {
        $this->authorize('interactAsAssignedProfessor', $turma);

        $data = $request->validated();
        $channels = array_values(array_unique($data['channels']));

        $announcement = CourseClassAnnouncement::query()->create([
            'tenant_id' => $turma->tenant_id,
            'course_class_id' => $turma->id,
            'reference_date' => $data['reference_date'] ?? null,
            'subject' => isset($data['subject']) ? trim((string) $data['subject']) : null,
            'body' => trim((string) $data['body']),
            'channels' => $channels,
            'consent_acknowledged' => true,
            'created_by' => auth()->id(),
        ]);

        ProcessCourseClassAnnouncementJob::dispatch($announcement->id)->afterCommit();

        return back()->with(
            'success',
            'Aviso registrado. E-mails foram enfileirados (fila + MAIL_* do .env).'
        );
    }
}
