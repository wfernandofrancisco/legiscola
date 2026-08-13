<?php

namespace App\Services;

use App\Contracts\Repositories\ExamTemplateRepositoryInterface;
use App\Contracts\Services\ExamBuilderServiceInterface;
use App\Models\ExamAttachment;
use App\Models\ExamTemplate;
use App\Support\TenantContext;
use Illuminate\Http\UploadedFile;

class ExamBuilderService implements ExamBuilderServiceInterface
{
    public function __construct(private ExamTemplateRepositoryInterface $repository) {}

    public function criarTemplate(array $data): ExamTemplate
    {
        $data['tenant_id'] = TenantContext::getTenantId();
        return $this->repository->create($data);
    }

    public function anexarQuestoes(ExamTemplate $template, array $questionIds): void
    {
        $payload = [];
        foreach (array_values($questionIds) as $index => $questionId) {
            $payload[(int) $questionId] = [
                'tenant_id' => TenantContext::getTenantId(),
                'position' => $index + 1,
            ];
        }

        $template->questions()->sync($payload);
    }

    public function uploadAnexos(ExamTemplate $template, array $arquivos): void
    {
        foreach ($arquivos as $arquivo) {
            $path = $arquivo->store('provas/anexos', 'public');
            ExamAttachment::query()->create([
                'tenant_id' => TenantContext::getTenantId(),
                'exam_template_id' => $template->id,
                'file_path' => $path,
            ]);
        }
    }
}
