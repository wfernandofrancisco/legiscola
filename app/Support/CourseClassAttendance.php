<?php

namespace App\Support;

use App\Models\Attendance;
use App\Models\CourseClass;
use App\Models\Student;
use Illuminate\Support\Collection;

final class CourseClassAttendance
{
    /**
     * Frequência do aluno: presenças em aulas da turma / total de aulas cadastradas (0–100).
     * Aulas sem registro contam como não presente no denominador.
     *
     * @return int|null null se a turma ainda não tem aulas cadastradas
     */
    public static function studentPercent(Student $student, CourseClass $courseClass): ?int
    {
        $lessonIds = $courseClass->lessons()->pluck('id');
        $total = $lessonIds->count();
        if ($total === 0) {
            return null;
        }

        $present = Attendance::query()
            ->where('student_id', $student->id)
            ->whereIn('class_lesson_id', $lessonIds)
            ->where('is_present', true)
            ->count();

        return (int) round(min(100, ($present / $total) * 100));
    }

    /**
     * @param  Collection<int>|iterable<int>  $studentIds
     * @return array{denominator: int, by_student_id: array<int, int|null>}
     */
    public static function percentagesByStudentIds(CourseClass $turma, Collection|iterable $studentIds): array
    {
        $studentIds = collect($studentIds)->map(fn ($id) => (int) $id)->unique()->values();

        if ($studentIds->isEmpty()) {
            return ['denominator' => 0, 'by_student_id' => []];
        }

        $lessonIds = $turma->lessons()->pluck('id');
        $lessonCount = $lessonIds->count();

        if ($lessonCount === 0) {
            $empty = [];
            foreach ($studentIds as $sid) {
                $empty[(int) $sid] = null;
            }

            return ['denominator' => 0, 'by_student_id' => $empty];
        }

        $presentCountByStudent = Attendance::query()
            ->selectRaw('student_id, COUNT(DISTINCT class_lesson_id) as present_lessons')
            ->whereIn('class_lesson_id', $lessonIds)
            ->where('is_present', true)
            ->whereIn('student_id', $studentIds)
            ->groupBy('student_id')
            ->pluck('present_lessons', 'student_id');

        $byStudent = [];
        foreach ($studentIds as $studentId) {
            $present = (int) ($presentCountByStudent[$studentId] ?? 0);
            $byStudent[(int) $studentId] = (int) round(min(100, ($present / $lessonCount) * 100));
        }

        return ['denominator' => $lessonCount, 'by_student_id' => $byStudent];
    }
}
