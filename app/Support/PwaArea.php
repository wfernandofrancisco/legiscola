<?php

namespace App\Support;

/**
 * Áreas do Legiscola que podem ser instaladas como PWA.
 */
final class PwaArea
{
    public const ALUNO = 'aluno';

    public const DIRETOR = 'diretor';

    public const PROFESSOR = 'professor';

    public const PORTAL = 'portal';

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return [self::ALUNO, self::DIRETOR, self::PROFESSOR, self::PORTAL];
    }

    public static function normalize(?string $area): string
    {
        $area = strtolower(trim((string) $area));

        return in_array($area, self::all(), true) ? $area : self::ALUNO;
    }

    /**
     * @return array{
     *     start_url: string,
     *     name_suffix: string,
     *     short_name: string,
     *     description: string,
     *     theme_color: string,
     *     background_color: string,
     *     install_title: string,
     *     install_hint: string,
     *     install_hint_ios: string
     * }
     */
    public static function config(string $area): array
    {
        return match (self::normalize($area)) {
            self::DIRETOR => [
                'start_url' => '/diretor',
                'name_suffix' => 'Direção regional',
                'short_name' => 'Diretor',
                'description' => 'Catálogo, licenças, agenda e financeiro da direção regional.',
                'theme_color' => '#312e81',
                'background_color' => '#0f172a',
                'install_title' => 'Instale a direção regional',
                'install_hint' => 'Abra o painel do diretor pelo ícone na tela inicial.',
                'install_hint_ios' => 'No Safari, toque em Compartilhar e depois em “Adicionar à Tela de Início”.',
            ],
            self::PROFESSOR => [
                'start_url' => '/docente',
                'name_suffix' => 'Área do professor',
                'short_name' => 'Professor',
                'description' => 'Turmas, aulas, chamadas e quizzes no celular.',
                'theme_color' => '#1e3a8a',
                'background_color' => '#0f172a',
                'install_title' => 'Instale a área do professor',
                'install_hint' => 'Acesse turmas e chamadas pelo ícone na tela inicial.',
                'install_hint_ios' => 'No Safari, toque em Compartilhar e depois em “Adicionar à Tela de Início”.',
            ],
            self::PORTAL => [
                'start_url' => '/',
                'name_suffix' => 'Portal',
                'short_name' => 'Portal',
                'description' => 'Portal da Escola Legislativa no celular.',
                'theme_color' => '#0f172a',
                'background_color' => '#0f172a',
                'install_title' => 'Leve a Escola Legislativa no celular',
                'install_hint' => 'Instale o app para abrir turmas, eventos e notícias pela tela inicial.',
                'install_hint_ios' => 'No iPhone: toque em Compartilhar e depois em “Adicionar à Tela de Início”.',
            ],
            default => [
                'start_url' => '/aluno',
                'name_suffix' => 'Área do aluno',
                'short_name' => 'Aluno',
                'description' => 'Cursos, aulas, eventos e certificados no celular.',
                'theme_color' => '#0f172a',
                'background_color' => '#0f172a',
                'install_title' => 'Instale a área do aluno',
                'install_hint' => 'Acesse aulas e eventos pelo ícone na tela inicial, como um aplicativo.',
                'install_hint_ios' => 'No Safari, toque em Compartilhar e depois em “Adicionar à Tela de Início”.',
            ],
        };
    }
}
