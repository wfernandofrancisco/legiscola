<?php

namespace App\Http\Controllers\Aluno;

use App\Contracts\Services\StudentServiceInterface;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventEnrollment;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventoController extends Controller
{
    public function __construct(
        private StudentServiceInterface $studentService
    ) {}

    public function index(): View
    {
        $student = $this->requireStudent();

        $enrollments = EventEnrollment::query()
            ->where('student_id', $student->id)
            ->with('event')
            ->latest('id')
            ->get()
            ->filter(fn (EventEnrollment $e) => $e->event !== null)
            ->values();

        return view('aluno.eventos.index', compact('student', 'enrollments'));
    }

    public function show(Event $evento): View
    {
        $student = $this->requireStudent();
        $enrollment = $this->requireEnrollment($student, $evento);

        $windowOpen = $evento->isPresenceWindowOpen();
        $canCheckIn = $evento->isGeofenceCheckInEnabled() && $windowOpen && ! $enrollment->presente;

        return view('aluno.eventos.show', [
            'student' => $student,
            'event' => $evento,
            'enrollment' => $enrollment,
            'windowOpen' => $windowOpen,
            'canCheckIn' => $canCheckIn,
        ]);
    }

    public function storePresence(Request $request, Event $evento): RedirectResponse
    {
        $student = $this->requireStudent();
        $enrollment = $this->requireEnrollment($student, $evento);

        if ($enrollment->presente) {
            return back()->with('info', 'Sua presença neste evento já foi registrada.');
        }

        if (! $evento->isGeofenceCheckInEnabled()) {
            return back()->with('error', 'Este evento não está configurado para chamada por georreferência.');
        }

        if (! $evento->isPresenceWindowOpen()) {
            return back()->with('error', 'Fora do horário permitido para registrar presença.');
        }

        $data = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'accuracy' => ['nullable', 'numeric', 'min:0', 'max:5000'],
        ], [
            'latitude.required' => 'Não foi possível obter sua localização. Permita o acesso ao GPS e tente novamente.',
            'longitude.required' => 'Não foi possível obter sua localização. Permita o acesso ao GPS e tente novamente.',
        ]);

        $latitude = (float) $data['latitude'];
        $longitude = (float) $data['longitude'];
        $distance = $evento->distanceFromMeters($latitude, $longitude);

        if ($distance === null || $distance > (float) $evento->geofence_raio_metros) {
            $meters = $distance !== null ? (int) round($distance) : null;
            $message = $meters !== null
                ? "Você está a aproximadamente {$meters} m do local. Aproxime-se (raio de {$evento->geofence_raio_metros} m) e tente novamente."
                : 'Não foi possível validar sua localização em relação ao evento.';

            return back()->with('error', $message);
        }

        $enrollment->update([
            'presente' => true,
            'checkin_latitude' => $latitude,
            'checkin_longitude' => $longitude,
            'checkin_accuracy_metros' => isset($data['accuracy']) ? (int) round((float) $data['accuracy']) : null,
            'checkin_em' => now(),
        ]);

        return back()->with('success', 'Presença registrada com sucesso.');
    }

    private function requireEnrollment(Student $student, Event $evento): EventEnrollment
    {
        return EventEnrollment::query()
            ->where('event_id', $evento->id)
            ->where('student_id', $student->id)
            ->firstOrFail();
    }

    private function requireStudent(): Student
    {
        $student = $this->studentService->findByUserId((int) auth()->id());
        abort_unless($student instanceof Student, 404);

        return $student;
    }
}
