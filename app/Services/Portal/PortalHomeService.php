<?php

namespace App\Services\Portal;

use App\Contracts\Repositories\Portal\PortalCatalogRepositoryInterface;
use App\Contracts\Services\Portal\PortalHomeServiceInterface;
use Illuminate\Support\Str;

class PortalHomeService implements PortalHomeServiceInterface
{
    public function __construct(
        private PortalCatalogRepositoryInterface $catalog,
    ) {}

    public function homePayload(): array
    {
        $sobre = $this->catalog->firstSobreEscola();

        return [
            'noticias' => $this->catalog->latestPublishedNews(6),
            'eventos' => $this->catalog->upcomingEvents(6),
            'turmasInscricao' => $this->catalog->homeEnrollmentTurmas(4),
            'turmasAndamento' => $this->catalog->homeEmAndamentoTurmas(4),
            'sobreEscola' => $sobre,
            'sobreSnippet' => $sobre ? Str::limit(strip_tags((string) $sobre->institucional), 280) : null,
            'professoresDestaque' => $this->catalog->featuredTeachers(12),
            'stats' => $this->catalog->portalStats(),
        ];
    }
}
