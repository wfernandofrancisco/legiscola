<?php

namespace App\Services;

use App\Contracts\Repositories\CourseClassRepositoryInterface;
use App\Contracts\Repositories\EnrollmentRepositoryInterface;
use App\Contracts\Repositories\EventRepositoryInterface;
use App\Contracts\Services\EnrollmentServiceInterface;
use App\Models\CourseClassSchedule;
use App\Models\Enrollment;
use App\Models\EventEnrollment;
use App\Support\TenantContext;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class EnrollmentService implements EnrollmentServiceInterface
{
    public function __construct(
        private EnrollmentRepositoryInterface $repository,
        private CourseClassRepositoryInterface $courseClassRepository,
        private EventRepositoryInterface $eventRepository
    ) {}

    public function matricularEmTurma(int $studentId, int $courseClassId): \App\Models\Enrollment
    {
        $this->validateScheduleConflict($studentId, $courseClassId);

        $courseClass = $this->courseClassRepository->findById($courseClassId);
        if (! $courseClass) {
            throw ValidationException::withMessages(['course_class_id' => 'Turma não encontrada.']);
        }

        $agora = CarbonImmutable::now();

        if (
            ! $courseClass->enrollment_start
            || ! $courseClass->enrollment_end
            || $agora->lt($courseClass->enrollment_start)
            || $agora->gt($courseClass->enrollment_end)
        ) {
            throw ValidationException::withMessages([
                'course_class_id' => 'Período de inscrição encerrado ou ainda não iniciado para esta turma.',
            ]);
        }

        if ($courseClass->status !== 'inscricao') {
            throw ValidationException::withMessages([
                'course_class_id' => 'Esta turma não está com inscrições abertas.',
            ]);
        }

        if ($this->repository->findByStudentAndCourseClass($studentId, $courseClassId)) {
            throw ValidationException::withMessages([
                'student_id' => 'Aluno já possui matrícula nesta turma.',
            ]);
        }

        $ocupadas = $this->repository->countByCourseClass($courseClassId);
        if ($courseClass->max_seats !== null && $ocupadas >= $courseClass->max_seats) {
            throw ValidationException::withMessages([
                'course_class_id' => 'Não há vagas disponíveis para esta turma.',
            ]);
        }

        return $this->repository->create([
            'tenant_id' => TenantContext::getTenantId(),
            'student_id' => $studentId,
            'class_id' => null,
            'course_class_id' => $courseClassId,
            'status' => 'inscrito',
        ]);
    }

    public function matricularEmTurmaAdmin(int $studentId, int $courseClassId, string $status = 'inscrito', ?string $observations = null): Enrollment
    {
        $this->validateScheduleConflict($studentId, $courseClassId);

        $courseClass = $this->courseClassRepository->findById($courseClassId);
        if (! $courseClass) {
            throw ValidationException::withMessages(['course_class_id' => 'Turma não encontrada.']);
        }

        if ($this->repository->findByStudentAndCourseClass($studentId, $courseClassId)) {
            throw ValidationException::withMessages(['student_id' => 'Aluno já possui matrícula nesta turma.']);
        }

        $ocupadas = $this->repository->countByCourseClass($courseClassId);
        if ($courseClass->max_seats !== null && $ocupadas >= $courseClass->max_seats) {
            throw ValidationException::withMessages(['course_class_id' => 'Não há vagas disponíveis para esta turma.']);
        }

        return $this->repository->create([
            'tenant_id' => TenantContext::getTenantId(),
            'student_id' => $studentId,
            'class_id' => null,
            'course_class_id' => $courseClassId,
            'status' => $status,
            'observations' => $observations,
        ]);
    }

    public function inscreverEmEvento(int $studentId, int $eventId): void
    {
        $event = $this->eventRepository->findById($eventId);
        if (! $event) {
            throw ValidationException::withMessages(['event_id' => 'Evento não encontrado.']);
        }

        if (! $event->isOnlineRegistrationOpen()) {
            throw ValidationException::withMessages([
                'event_id' => 'Inscrição online não está disponível para este evento (período ou configuração).',
            ]);
        }

        if (EventEnrollment::query()
            ->where('event_id', $eventId)
            ->where('student_id', $studentId)
            ->exists()) {
            throw ValidationException::withMessages(['event_id' => 'Você já está inscrito neste evento.']);
        }

        if (! $event->hasVacancyForEnrollment()) {
            throw ValidationException::withMessages(['event_id' => 'Evento sem vagas disponíveis.']);
        }

        EventEnrollment::query()->create([
            'tenant_id' => TenantContext::getTenantId(),
            'event_id' => $eventId,
            'student_id' => $studentId,
            'presente' => false,
        ]);
    }

    public function paginateByCourseClass(int $courseClassId, int $perPage = 15, ?string $search = null, ?string $status = null): LengthAwarePaginator
    {
        return $this->repository->paginateByCourseClass($courseClassId, $perPage, $search, $status);
    }

    public function statusSummary(int $courseClassId): array
    {
        return [
            'total' => $this->repository->countByCourseClass($courseClassId),
            'inscrito' => $this->repository->countByStatus($courseClassId, 'inscrito'),
            'cursando' => $this->repository->countByStatus($courseClassId, 'cursando'),
            'desistido' => $this->repository->countByStatus($courseClassId, 'desistido'),
            'concluido' => $this->repository->countByStatus($courseClassId, 'concluido'),
            'baixa_presenca' => $this->repository->countByStatus($courseClassId, 'baixa_presenca'),
        ];
    }

    public function updateStatus(Enrollment $enrollment, string $status, ?string $observations = null): bool
    {
        $allowed = ['inscrito', 'cursando', 'desistido', 'concluido', 'baixa_presenca'];
        if (! in_array($status, $allowed, true)) {
            throw ValidationException::withMessages([
                'status' => 'Status de matrícula inválido.',
            ]);
        }

        return $this->repository->updateEnrollment($enrollment, [
            'status' => $status,
            'observations' => $observations,
        ]);
    }

    private function validateScheduleConflict(int $studentId, int $courseClassId): void
    {
        $targetClass = $this->courseClassRepository->findById($courseClassId);
        if (! $targetClass || $targetClass->tipo_turma !== 'presencial') {
            return;
        }

        $targetSchedules = CourseClassSchedule::query()
            ->where('course_class_id', $courseClassId)
            ->get(['weekday', 'start_time', 'end_time']);

        if ($targetSchedules->isEmpty()) {
            return;
        }

        $existing = Enrollment::query()
            ->with('courseClass.schedules')
            ->where('student_id', $studentId)
            ->where('course_class_id', '!=', $courseClassId)
            ->whereIn('status', ['inscrito', 'cursando'])
            ->get()
            ->filter(fn (Enrollment $e) => $e->courseClass && $e->courseClass->status !== 'cancelado' && $e->courseClass->tipo_turma === 'presencial');

        foreach ($existing as $enrollment) {
            foreach ($enrollment->courseClass->schedules as $other) {
                foreach ($targetSchedules as $target) {
                    if ((int) $target->weekday !== (int) $other->weekday) {
                        continue;
                    }

                    $startA = strtotime((string) $target->start_time);
                    $endA = strtotime((string) $target->end_time);
                    $startB = strtotime((string) $other->start_time);
                    $endB = strtotime((string) $other->end_time);

                    if ($startA < $endB && $endA > $startB) {
                        throw ValidationException::withMessages([
                            'student_id' => 'Conflito de horário: aluno já matriculado em outra turma presencial neste dia/horário.',
                        ]);
                    }
                }
            }
        }
    }
}
