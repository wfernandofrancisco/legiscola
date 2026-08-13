<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\Services\CourseClassServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Escola\CompleteCourseClassRequest;
use App\Models\CourseClass;
use Illuminate\Http\RedirectResponse;

class CourseClassController extends Controller
{
    public function __construct(private CourseClassServiceInterface $service) {}

    public function complete(CompleteCourseClassRequest $request, CourseClass $courseClass): RedirectResponse
    {
        $this->service->completeClass(
            $courseClass->id,
            (int) ($request->validated()['minimum_attendance'] ?? 75)
        );

        return back()->with('success', 'Turma finalizada com sucesso.');
    }
}
