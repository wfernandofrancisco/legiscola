<?php

namespace App\Services;

use App\Enums\CatalogItemTipo;
use App\Exceptions\LicenseNotAvailableException;
use App\Models\CatalogLesson;
use App\Models\CatalogLicense;
use App\Models\ClassLesson;
use App\Models\Course;
use App\Models\CourseClass;
use App\Models\Event;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

/**
 * Traz um item licenciado do catálogo para dentro da câmara.
 *
 * Modelo híbrido: a estrutura (curso, turma, aulas) é criada no tenant e passa a ser dele —
 * datas, presença, matrículas e certificados são locais. O vídeo e o material continuam
 * apontando para o catálogo pelo catalog_lesson_id, sem cópia.
 */
class LicenseActivationService
{
    private const HORA_INICIO_PADRAO = '19:00';

    private const HORA_FIM_PADRAO = '21:00';

    private const INTERVALO_PADRAO_DIAS = 7;

    /**
     * Cria o curso da câmara para esta licença. Idempotente: chamar de novo devolve o mesmo curso.
     */
    public function activate(CatalogLicense $license, int $tenantId, ?int $adminUserId = null): Course
    {
        $this->assertUsable($license, $tenantId);

        $existente = $this->courseFor($license, $tenantId);

        if ($existente) {
            return $existente;
        }

        $item = $license->catalogItem;

        return Course::create([
            'tenant_id' => $tenantId,
            'name' => $item->titulo,
            'description' => $item->descricao ?: $item->resumo,
            'workload_hours' => $item->workload_hours ?: 0,
            'status' => 'ativo',
            'admin_user_id' => $adminUserId,
            'catalog_item_id' => $item->id,
            'catalog_license_id' => $license->id,
        ]);
    }

    /**
     * Abre uma turma da licença e materializa as aulas do catálogo já com datas.
     */
    public function openTurma(CatalogLicense $license, int $tenantId, array $data, ?int $adminUserId = null): CourseClass
    {
        $this->assertUsable($license, $tenantId);
        $this->assertEhCurso($license);
        $this->assertDentroDoLimite($license);

        return DB::transaction(function () use ($license, $tenantId, $data, $adminUserId): CourseClass {
            $course = $this->activate($license, $tenantId, $adminUserId);
            $primeiraAula = CarbonImmutable::parse($data['data_inicio']);

            $turma = CourseClass::create([
                'tenant_id' => $tenantId,
                'course_id' => $course->id,
                'name' => $data['name'],
                'tipo_turma' => $data['tipo_turma'] ?? 'online',
                // A tabela não aceita nulo aqui; 0 é o valor que o resto do sistema lê como "sem limite".
                'max_seats' => $data['max_seats'] ?? 0,
                // Sem janela informada, as inscrições ficam abertas de hoje até a véspera da 1ª aula.
                'enrollment_start' => $data['enrollment_start'] ?? now(),
                'enrollment_end' => $data['enrollment_end'] ?? $primeiraAula->endOfDay(),
                'status' => $data['status'] ?? 'inscricao',
            ]);

            $this->materializeLessons($license, $turma, $tenantId, $data);

            return $turma;
        });
    }

    /**
     * Agenda uma edição da palestra licenciada como evento da câmara.
     *
     * Diferente do curso, aqui não há aulas a materializar: o item vira um evento único, com
     * data, local e inscrições próprios da câmara.
     */
    public function openEvento(CatalogLicense $license, int $tenantId, array $data, ?int $adminUserId = null): Event
    {
        $this->assertUsable($license, $tenantId);
        $this->assertEhPalestra($license);
        $this->assertDentroDoLimiteDeEventos($license);

        $item = $license->catalogItem;

        return Event::create([
            'tenant_id' => $tenantId,
            'catalog_item_id' => $item->id,
            'catalog_license_id' => $license->id,
            'title' => $data['title'] ?? $item->titulo,
            'description' => $data['description'] ?? ($item->descricao ?: $item->resumo),
            // Preferência: o que a câmara informou; senão a data combinada na licença.
            'date_time' => $data['date_time'] ?? $license->palestra_em,
            'max_seats' => $data['max_seats'] ?? $license->max_inscritos,
            'allow_online_registration' => (bool) ($data['allow_online_registration'] ?? true),
            'com_certificado' => (bool) ($data['com_certificado'] ?? false),
            'registration_starts_at' => $data['registration_starts_at'] ?? null,
            'registration_ends_at' => $data['registration_ends_at'] ?? null,
            'palestrante_nome' => $data['palestrante_nome'] ?? $license->professor_nome,
            'zipcode' => $data['zipcode'] ?? null,
            'address' => $data['address'] ?? null,
            'number' => $data['number'] ?? null,
            'complement' => $data['complement'] ?? null,
            'district' => $data['district'] ?? null,
            'city' => $data['city'] ?? null,
            'state' => $data['state'] ?? null,
        ]);
    }

    /**
     * Uma aula da turma para cada aula do catálogo, espaçadas pelo intervalo informado.
     */
    private function materializeLessons(CatalogLicense $license, CourseClass $turma, int $tenantId, array $data): void
    {
        $inicio = CarbonImmutable::parse($data['data_inicio']);
        $intervalo = max(1, (int) ($data['intervalo_dias'] ?? self::INTERVALO_PADRAO_DIAS));
        $horaInicio = $data['hora_inicio'] ?? self::HORA_INICIO_PADRAO;
        $horaFim = $data['hora_fim'] ?? self::HORA_FIM_PADRAO;

        $license->catalogItem->lessons->values()->each(
            function (CatalogLesson $aula, int $indice) use ($turma, $tenantId, $inicio, $intervalo, $horaInicio, $horaFim): void {
                ClassLesson::create([
                    'tenant_id' => $tenantId,
                    'course_class_id' => $turma->id,
                    'catalog_lesson_id' => $aula->id,
                    'title' => $aula->titulo,
                    'date' => $inicio->addDays($indice * $intervalo)->toDateString(),
                    'start_time' => $horaInicio,
                    'end_time' => $horaFim,
                    // Aula de catálogo nasce como online: o conteúdo é o vídeo hospedado no item.
                    'is_online' => true,
                ]);
            }
        );
    }

    private function courseFor(CatalogLicense $license, int $tenantId): ?Course
    {
        return Course::query()
            ->where('tenant_id', $tenantId)
            ->where('catalog_license_id', $license->id)
            ->first();
    }

    private function assertUsable(CatalogLicense $license, int $tenantId): void
    {
        if ((int) $license->tenant_id !== $tenantId) {
            throw LicenseNotAvailableException::foraDoTenant();
        }

        if ($license->isExpired()) {
            throw LicenseNotAvailableException::prazoVencido($license->exibir_ate->format('d/m/Y'));
        }

        if (! $license->status->isUsable()) {
            throw LicenseNotAvailableException::naoUtilizavel();
        }
    }

    private function assertDentroDoLimite(CatalogLicense $license): void
    {
        $restantes = $license->turmasRestantes();

        if ($restantes !== null && $restantes <= 0) {
            throw LicenseNotAvailableException::limiteDeTurmas((int) $license->max_turmas);
        }
    }

    private function assertDentroDoLimiteDeEventos(CatalogLicense $license): void
    {
        $restantes = $license->eventosRestantes();

        if ($restantes !== null && $restantes <= 0) {
            throw LicenseNotAvailableException::limiteDeEdicoes((int) $license->max_turmas);
        }
    }

    private function assertEhCurso(CatalogLicense $license): void
    {
        if ($license->catalogItem->tipo !== CatalogItemTipo::Curso) {
            throw LicenseNotAvailableException::tipoIncompativel('curso');
        }
    }

    private function assertEhPalestra(CatalogLicense $license): void
    {
        if ($license->catalogItem->tipo !== CatalogItemTipo::Palestra) {
            throw LicenseNotAvailableException::tipoIncompativel('palestra');
        }
    }
}
