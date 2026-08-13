<?php

namespace App\Jobs;

use App\Contracts\TransactionalSmsSenderInterface;
use App\Mail\CourseClassAnnouncementMail;
use App\Models\CourseClassAnnouncement;
use App\Models\CourseClassAnnouncementDelivery;
use App\Models\Enrollment;
use App\Models\Student;
use App\Support\TenantContext;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class ProcessCourseClassAnnouncementJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;

    public int $tries = 3;

    public function __construct(
        public int $courseClassAnnouncementId,
    ) {}

    public function handle(TransactionalSmsSenderInterface $sms): void
    {
        $announcement = CourseClassAnnouncement::query()
            ->with(['courseClass.course'])
            ->find($this->courseClassAnnouncementId);

        if ($announcement === null) {
            return;
        }

        TenantContext::set($announcement->tenant_id);

        try {
            $channels = array_values(array_unique(array_filter($announcement->channels ?? [])));

            $enrollments = Enrollment::query()
                ->where('course_class_id', $announcement->course_class_id)
                ->where('status', '!=', 'desistido')
                ->with(['student.user'])
                ->get();

            $delayUs = max(0, (int) config('course_class_announcements.email_delay_microseconds', 200_000));
            $prefixMax = (int) config('course_class_announcements.sms_prefix_max_chars', 36);
            $smsBodyMax = (int) config('course_class_announcements.sms_body_max_chars', 280);

            $turmaName = $announcement->courseClass?->name ?? 'Turma';
            $smsPrefix = mb_substr('[Legiscola - '.$turmaName.']', 0, $prefixMax);

            foreach ($enrollments as $enrollment) {
                $student = $enrollment->student;
                if (! $student instanceof Student) {
                    continue;
                }

                $displayName = (string) ($student->user?->name ?? 'Aluno(a)');
                $email = self::resolveStudentEmail($student);
                $phoneDigits = self::resolveStudentPhoneDigits($student);

                if (in_array('email', $channels, true)) {
                    $this->dispatchEmailChannel(
                        $announcement,
                        $enrollment,
                        $student,
                        $displayName,
                        $email,
                        $delayUs
                    );
                }

                if (in_array('sms', $channels, true)) {
                    $this->dispatchSmsChannel(
                        $announcement,
                        $enrollment,
                        $student,
                        $phoneDigits,
                        $sms,
                        $smsPrefix,
                        $smsBodyMax,
                        $prefixMax
                    );
                }
            }

            $announcement->update(['processed_at' => now()]);
        } finally {
            TenantContext::clear();
        }
    }

    private function dispatchEmailChannel(
        CourseClassAnnouncement $announcement,
        Enrollment $enrollment,
        Student $student,
        string $displayName,
        string $email,
        int $delayUs
    ): void {
        $exists = CourseClassAnnouncementDelivery::query()
            ->where('course_class_announcement_id', $announcement->id)
            ->where('enrollment_id', $enrollment->id)
            ->where('channel', 'email')
            ->exists();

        if ($exists) {
            return;
        }

        if ($email === '') {
            CourseClassAnnouncementDelivery::query()->create([
                'tenant_id' => $announcement->tenant_id,
                'course_class_announcement_id' => $announcement->id,
                'enrollment_id' => $enrollment->id,
                'student_id' => $student->id,
                'channel' => 'email',
                'destination' => null,
                'status' => 'skipped',
                'error_message' => 'Sem e-mail no cadastro do aluno ou do usuário.',
            ]);

            return;
        }

        try {
            Mail::to($email)->queue(new CourseClassAnnouncementMail(
                announcement: $announcement,
                recipientName: $displayName,
            ));

            CourseClassAnnouncementDelivery::query()->create([
                'tenant_id' => $announcement->tenant_id,
                'course_class_announcement_id' => $announcement->id,
                'enrollment_id' => $enrollment->id,
                'student_id' => $student->id,
                'channel' => 'email',
                'destination' => $email,
                'status' => 'queued',
                'error_message' => null,
            ]);
        } catch (Throwable $e) {
            Log::error('Falha ao enfileirar aviso da turma (e-mail)', [
                'announcement_id' => $announcement->id,
                'student_id' => $student->id,
                'exception' => $e->getMessage(),
            ]);

            CourseClassAnnouncementDelivery::query()->create([
                'tenant_id' => $announcement->tenant_id,
                'course_class_announcement_id' => $announcement->id,
                'enrollment_id' => $enrollment->id,
                'student_id' => $student->id,
                'channel' => 'email',
                'destination' => $email,
                'status' => 'failed',
                'error_message' => mb_substr($e->getMessage(), 0, 500),
            ]);
        }

        if ($delayUs > 0) {
            usleep($delayUs);
        }
    }

    private function dispatchSmsChannel(
        CourseClassAnnouncement $announcement,
        Enrollment $enrollment,
        Student $student,
        string $phoneDigits,
        TransactionalSmsSenderInterface $sms,
        string $smsPrefix,
        int $smsBodyMax,
        int $prefixMax
    ): void {
        $exists = CourseClassAnnouncementDelivery::query()
            ->where('course_class_announcement_id', $announcement->id)
            ->where('enrollment_id', $enrollment->id)
            ->where('channel', 'sms')
            ->exists();

        if ($exists) {
            return;
        }

        if (strlen($phoneDigits) < 10) {
            CourseClassAnnouncementDelivery::query()->create([
                'tenant_id' => $announcement->tenant_id,
                'course_class_announcement_id' => $announcement->id,
                'enrollment_id' => $enrollment->id,
                'student_id' => $student->id,
                'channel' => 'sms',
                'destination' => null,
                'status' => 'skipped',
                'error_message' => 'Celular/telefone ausente ou com menos de 10 dígitos.',
            ]);

            return;
        }

        $body = trim((string) $announcement->body);
        $smsText = mb_substr($smsPrefix.' '.$body, 0, $prefixMax + 1 + $smsBodyMax);
        $maskedDestination = strlen($phoneDigits) > 4
            ? str_repeat('*', strlen($phoneDigits) - 4).substr($phoneDigits, -4)
            : str_repeat('*', strlen($phoneDigits));

        try {
            $sms->send($phoneDigits, $smsText);

            CourseClassAnnouncementDelivery::query()->create([
                'tenant_id' => $announcement->tenant_id,
                'course_class_announcement_id' => $announcement->id,
                'enrollment_id' => $enrollment->id,
                'student_id' => $student->id,
                'channel' => 'sms',
                'destination' => $maskedDestination,
                'status' => 'sent',
                'error_message' => null,
            ]);
        } catch (Throwable $e) {
            CourseClassAnnouncementDelivery::query()->create([
                'tenant_id' => $announcement->tenant_id,
                'course_class_announcement_id' => $announcement->id,
                'enrollment_id' => $enrollment->id,
                'student_id' => $student->id,
                'channel' => 'sms',
                'destination' => $maskedDestination,
                'status' => 'failed',
                'error_message' => mb_substr($e->getMessage(), 0, 500),
            ]);
        }
    }

    private static function resolveStudentEmail(Student $student): string
    {
        $direct = trim((string) ($student->email ?? ''));
        if ($direct !== '') {
            return $direct;
        }

        return trim((string) ($student->user?->email ?? ''));
    }

    private static function resolveStudentPhoneDigits(Student $student): string
    {
        $raw = (string) ($student->celular ?? '');
        if ($raw === '') {
            $raw = (string) ($student->telefone ?? '');
        }

        return preg_replace('/\D/', '', $raw) ?? '';
    }
}
