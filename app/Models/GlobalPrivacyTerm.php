<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Documento único de privacidade / segurança da informação, editado na Central e aplicado a todos os tenants (LGPD).
 *
 * @property int $id
 * @property string $title
 * @property string|null $body_html
 * @property int $version
 * @property \Illuminate\Support\Carbon|null $published_at
 */
class GlobalPrivacyTerm extends Model
{
    protected $fillable = [
        'title',
        'body_html',
        'version',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    /**
     * Registro editorial (uma linha na prática; criada sob demanda).
     */
    public static function document(): self
    {
        $row = static::query()->orderBy('id')->first();
        if ($row !== null) {
            return $row;
        }

        $defaultBody = '<p>Este conteúdo é um modelo inicial. O super administrador deve substituí-lo na Central (Termo LGPD global) e publicar uma versão oficial.</p>'
            .'<h2>1. Controlador e bases legais</h2>'
            .'<p>Tratamos dados pessoais em conformidade com a Lei nº 13.709/2018 (LGPD), respeitando os princípios da finalidade, adequação, necessidade, transparência, segurança e responsabilização.</p>'
            .'<h2>2. Quais dados coletamos</h2>'
            .'<p>Podemos tratar identificação (nome, e-mail, telefone, CPF quando aplicável), dados de perfil acadêmico, registros de acesso (IP, data/hora), conteúdos enviados em formulários e informações necessárias à emissão e validação de certificados.</p>'
            .'<h2>3. Finalidades</h2>'
            .'<p>Prestação da plataforma SaaS de escola legislativa (matrículas, cursos, comunicações, certificados), suporte, segurança, cumprimento de obrigações legais e melhoria do serviço, observando a base legal aplicável em cada caso.</p>'
            .'<h2>4. Compartilhamento</h2>'
            .'<p>Dados podem ser acessados por prestadores de serviço (hospedagem, e-mail, antifraude) contratados com cláusulas de confidencialidade e proteção, e quando exigido por autoridade competente.</p>'
            .'<h2>5. Direitos do titular</h2>'
            .'<p>Você pode solicitar confirmação de tratamento, acesso, correção, anonimização, portabilidade, eliminação de dados desnecessários, informação sobre compartilhamentos e revogação de consentimento, quando cabível, pelo canal de contato indicado no portal.</p>'
            .'<h2>6. Cookies</h2>'
            .'<p>Utilizamos cookies ou tecnologias similares estritamente necessários ao funcionamento (sessão, segurança) e, somente com o seu consentimento, cookies analíticos. Você pode gerir preferências pelo aviso de cookies no site.</p>'
            .'<h2>7. Retenção e segurança</h2>'
            .'<p>Mantemos dados pelo tempo necessário às finalidades e às obrigações legais, aplicando medidas técnicas e administrativas de segurança da informação.</p>'
            .'<h2>8. Encarregado (DPO)</h2>'
            .'<p>O contato do encarregado de dados, quando houver, será divulgado neste termo após definição institucional.</p>';

        return static::query()->create([
            'title' => 'Política de privacidade e segurança da informação',
            'body_html' => $defaultBody,
            'version' => 0,
            'published_at' => null,
        ]);
    }

    /**
     * Última versão publicada (obrigatoriedade de aceite quando existir).
     */
    public static function currentPublished(): ?self
    {
        return static::query()
            ->whereNotNull('published_at')
            ->where('version', '>', 0)
            ->orderByDesc('version')
            ->orderByDesc('id')
            ->first();
    }

    public function isPublished(): bool
    {
        return $this->published_at !== null && $this->version > 0;
    }
}
