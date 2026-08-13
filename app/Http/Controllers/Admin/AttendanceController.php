<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\Services\AttendanceServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Escola\StoreAttendanceRequest;
use App\Http\Requests\Escola\UpdateAttendanceRequest;
use App\Models\Attendance;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function __construct(private AttendanceServiceInterface $service) {}

    public function index(): View
    {
        return view('app.professor.attendances.index', ['attendances' => $this->service->paginate()]);
    }

    public function store(StoreAttendanceRequest $request): RedirectResponse
    {
        $this->authorize('create', Attendance::class);
        $this->service->create($request->validated());
        return back()->with('success', 'Presença lançada com sucesso.');
    }

    public function update(UpdateAttendanceRequest $request, Attendance $attendance): RedirectResponse
    {
        $this->authorize('update', $attendance);
        $this->service->update($attendance, $request->validated());
        return back()->with('success', 'Presença atualizada com sucesso.');
    }
}
