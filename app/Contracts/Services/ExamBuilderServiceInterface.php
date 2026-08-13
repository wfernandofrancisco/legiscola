<?php

namespace App\Contracts\Services;

use App\Models\ExamTemplate;
use Illuminate\Http\UploadedFile;

interface ExamBuilderServiceInterface
{
    public function criarTemplate(array $data): ExamTemplate;

    public function anexarQuestoes(ExamTemplate $template, array $questionIds): void;

    /** @param array<int, UploadedFile> $arquivos */
    public function uploadAnexos(ExamTemplate $template, array $arquivos): void;
}
