<?php

namespace App\Mail;

use App\Models\CourseClassAnnouncement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CourseClassAnnouncementMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public CourseClassAnnouncement $announcement,
        public string $recipientName,
    ) {}

    public function envelope(): Envelope
    {
        $this->announcement->loadMissing('courseClass');

        $subject = trim((string) ($this->announcement->subject ?? ''));
        if ($subject === '') {
            $subject = 'Aviso — '.($this->announcement->courseClass?->name ?? 'Turma');
        }

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        $this->announcement->loadMissing(['courseClass.course']);

        return new Content(
            view: 'emails.course-class-announcement',
            with: [
                'announcement' => $this->announcement,
                'recipientName' => $this->recipientName,
                'turma' => $this->announcement->courseClass,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
