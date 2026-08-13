<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamTemplate;
use Illuminate\View\View;

class ProvaController extends Controller
{
    public function imprimir(ExamTemplate $prova): View
    {
        $prova->load(['turma.course', 'questions.subject', 'attachments']);
        return view('admin.provas.imprimir', compact('prova'));
    }
}
