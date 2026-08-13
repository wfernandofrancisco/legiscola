<?php

namespace App\Support;

use App\Models\CourseClass;
use App\Support\CourseClassAttendance;
use App\Models\QuizAttempt;
use App\Models\Student;

final class AlunoProgress
{
    /**
     * Percentual de quizzes ativos da turma em que o aluno obteve aprovação (0–100).
     */
    public static function quizCompletionPercent(Student $student, CourseClass $courseClass): ?int
    {
        $quizIds = $courseClass->linkedQuizzes()
            ->wherePivot('is_active', true)
            ->pluck('quizzes.id');

        if ($quizIds->isEmpty()) {
            $quizIds = $courseClass->quizzes()
                ->where('is_active', true)
                ->pluck('id');
        }

        $total = $quizIds->count();
        if ($total === 0) {
            return null;
        }

        $passed = QuizAttempt::query()
            ->where('student_id', $student->id)
            ->where('course_class_id', $courseClass->id)
            ->whereIn('quiz_id', $quizIds)
            ->where('passed', true)
            ->pluck('quiz_id')
            ->unique()
            ->count();

        return (int) round(min(100, ($passed / $total) * 100));
    }

    /**
     * Presença: aulas da turma com presença confirmada / total de aulas cadastradas.
     */
    public static function attendanceSheetPercent(Student $student, CourseClass $courseClass): ?int
    {
        return CourseClassAttendance::studentPercent($student, $courseClass);
    }
}
