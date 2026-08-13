<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\Services\EventCrudServiceInterface;
use App\Enums\CertificateTipoEmissao;
use App\Http\Controllers\Controller;
use App\Http\Requests\Escola\StoreEventRequest;
use App\Http\Requests\Escola\UpdateEventRequest;
use App\Models\Certificate;
use App\Models\CertificateTemplate;
use App\Models\Event;
use App\Models\EventEnrollment;
use App\Models\TenantAdminSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class EventController extends Controller
{
    public function __construct(private EventCrudServiceInterface $service) {}

    public function index(Request $request): View
    {
        $events = $this->service->paginateFiltered(15, $request->string('search')->toString());
        $breadcrumbs = [
            ['label' => 'Painel', 'href' => route('admin.dashboard')],
            ['label' => 'Eventos'],
        ];

        return view('admin.events.index', compact('events', 'breadcrumbs'));
    }

    public function create(): View
    {
        $breadcrumbs = [
            ['label' => 'Painel', 'href' => route('admin.dashboard')],
            ['label' => 'Eventos', 'href' => route('admin.eventos.index')],
            ['label' => 'Novo evento'],
        ];

        return view('admin.events.create', compact('breadcrumbs'));
    }

    public function store(StoreEventRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('events/photos', 'public');
        }

        $this->service->create($data);

        return redirect()->route('admin.eventos.index')->with('success', 'Evento cadastrado com sucesso.');
    }

    public function edit(Event $evento): View
    {
        $event = $evento->load(['enrollments' => fn ($q) => $q->latest('id'), 'enrollments.student.user']);

        $studentIds = $event->enrollments->pluck('student_id')->filter()->values();
        $latestCertificateHashByStudent = [];
        if ($studentIds->isNotEmpty()) {
            $latestCertificateHashByStudent = Certificate::query()
                ->whereIn('student_id', $studentIds)
                ->where(function ($q) use ($event): void {
                    $q->where('event_id', $event->id)
                        ->orWhere('snapshot->event_id', $event->id);
                })
                ->whereNotIn('status', ['revoked', 'revogado', 'inativo', 'cancelado'])
                ->whereNotNull('validation_hash')
                ->orderByDesc('issued_at')
                ->orderByDesc('id')
                ->get(['student_id', 'validation_hash'])
                ->unique('student_id')
                ->mapWithKeys(fn (Certificate $c): array => [(int) $c->student_id => $c->validation_hash])
                ->all();
        }

        $activeEventCertificateTemplate = CertificateTemplate::latestActiveForEmission(CertificateTipoEmissao::Evento);

        $breadcrumbs = [
            ['label' => 'Painel', 'href' => route('admin.dashboard')],
            ['label' => 'Eventos', 'href' => route('admin.eventos.index')],
            ['label' => 'Editar evento'],
        ];

        return view('admin.events.edit', compact(
            'event',
            'breadcrumbs',
            'latestCertificateHashByStudent',
            'activeEventCertificateTemplate',
        ));
    }

    public function updateEnrollmentPresente(Request $request, Event $evento, EventEnrollment $event_enrollment): RedirectResponse
    {
        abort_unless((int) $event_enrollment->event_id === (int) $evento->id, 404);

        $request->validate([
            'presente' => ['required', 'boolean'],
        ]);

        $event_enrollment->update([
            'presente' => $request->boolean('presente'),
        ]);

        return back()->with('success', 'Presença atualizada.');
    }

    public function markAllEnrollmentsPresente(Event $evento): RedirectResponse
    {
        $evento->enrollments()->update(['presente' => true]);

        return back()->with('success', 'Todos os inscritos foram marcados como presentes.');
    }

    public function printEventTriagemPdf(Event $evento)
    {
        $event = $evento->load(['enrollments.student.user']);

        $enrollments = $event->enrollments
            ->sortBy(fn ($row) => mb_strtolower((string) ($row->student?->user?->name ?? $row->student?->email ?? '')))
            ->values();

        $total = $enrollments->count();
        $presentes = $enrollments->where('presente', true)->count();
        $ausentes = $total - $presentes;

        $tenant = auth()->user()->tenant()->first();
        $settings = TenantAdminSetting::query()->where('tenant_id', auth()->user()->tenant_id)->first();

        $logoPath = null;
        if (! empty($settings?->logo_prefeitura_path)) {
            $candidate = storage_path('app/public/'.$settings->logo_prefeitura_path);
            if (is_file($candidate)) {
                $logoPath = $candidate;
            }
        }

        $pdf = Pdf::loadView('admin.events.event-triagem-pdf', [
            'event' => $event,
            'enrollments' => $enrollments,
            'total' => $total,
            'presentes' => $presentes,
            'ausentes' => $ausentes,
            'tenant' => $tenant,
            'logoPath' => $logoPath,
            'printedBy' => auth()->user()->name,
            'printedAt' => now(),
        ])->setPaper('a4', 'portrait');

        $filename = 'evento-triagem-'.str($event->title)->slug().'.pdf';

        return $pdf->stream($filename);
    }

    public function update(UpdateEventRequest $request, Event $evento): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            if ($evento->photo_path) {
                Storage::disk('public')->delete($evento->photo_path);
            }
            $data['photo_path'] = $request->file('photo')->store('events/photos', 'public');
        }

        $this->service->update($evento, $data);

        return redirect()->route('admin.eventos.index')->with('success', 'Evento atualizado com sucesso.');
    }

    public function destroy(Event $evento): RedirectResponse
    {
        if ($evento->photo_path) {
            Storage::disk('public')->delete($evento->photo_path);
        }
        $this->service->delete($evento);

        return redirect()->route('admin.eventos.index')->with('success', 'Evento removido com sucesso.');
    }

    public function printEnrollmentsPdf(Event $evento)
    {
        $event = $evento->load(['enrollments.student.user']);

        $enrollments = $event->enrollments
            ->sortBy(fn ($row) => mb_strtolower((string) ($row->student?->user?->name ?? $row->student?->email ?? '')))
            ->values();

        $tenant = auth()->user()->tenant()->first();
        $settings = TenantAdminSetting::query()->where('tenant_id', auth()->user()->tenant_id)->first();

        $logoPath = null;
        if (! empty($settings?->logo_prefeitura_path)) {
            $candidate = storage_path('app/public/'.$settings->logo_prefeitura_path);
            if (is_file($candidate)) {
                $logoPath = $candidate;
            }
        }

        $pdf = Pdf::loadView('admin.events.event-enrollments-pdf', [
            'event' => $event,
            'enrollments' => $enrollments,
            'tenant' => $tenant,
            'logoPath' => $logoPath,
            'printedBy' => auth()->user()->name,
            'printedAt' => now(),
        ])->setPaper('a4', 'portrait');

        $filename = 'evento-inscritos-'.str($event->title)->slug().'.pdf';

        return $pdf->stream($filename);
    }
}
