<?php

namespace App\Http\Requests\Escola\Concerns;

trait CourseClassFormMessages
{
    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'course_id' => 'curso',
            'course_search' => 'curso',
            'name' => 'nome da turma',
            'tipo_turma' => 'tipo da turma',
            'max_seats' => 'vagas',
            'enrollment_start' => 'início das inscrições',
            'enrollment_end' => 'fim das inscrições',
            'certificado_disponivel_ate' => 'data limite do certificado',
            'satisfaction_survey_id' => 'pesquisa de satisfação',
            'status' => 'status',
            'teacher_ids' => 'professores',
            'teacher_ids.*' => 'professor',
            'schedules' => 'horários da turma',
            'schedules.*.weekday' => 'dia da semana',
            'schedules.*.start_time' => 'horário de início',
            'schedules.*.end_time' => 'horário de término',
            'grade' => 'grade das aulas',
            'grade.*.date' => 'data da aula',
            'grade.*.start_time' => 'início da aula',
            'grade.*.end_time' => 'término da aula',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'course_id.required' => 'Selecione o curso desta turma.',
            'course_id.exists' => 'O curso selecionado não foi encontrado.',
            'name.required' => 'Informe o nome da turma.',
            'tipo_turma.required' => 'Escolha se a turma é presencial ou online.',
            'tipo_turma.in' => 'O tipo da turma precisa ser presencial ou online.',
            'max_seats.required' => 'Informe a quantidade de vagas.',
            'max_seats.min' => 'A turma precisa ter pelo menos 1 vaga.',
            'enrollment_start.required' => 'Informe quando as inscrições começam.',
            'enrollment_end.required' => 'Informe quando as inscrições terminam.',
            'enrollment_end.after_or_equal' => 'O fim das inscrições precisa ser no mesmo dia ou depois do início.',
            'status.required' => 'Escolha o status da turma.',
            'schedules.required_if' => 'Turma presencial precisa de pelo menos um horário semanal (dia, início e fim).',
            'schedules.min' => 'Informe pelo menos um horário para a turma presencial.',
            'schedules.*.weekday.required_with' => 'Em cada horário, escolha o dia da semana.',
            'schedules.*.weekday.integer' => 'Escolha um dia da semana válido.',
            'schedules.*.weekday.between' => 'Escolha um dia da semana válido.',
            'schedules.*.start_time.required_with' => 'Em cada horário, informe o horário de início.',
            'schedules.*.end_time.required_with' => 'Em cada horário, informe o horário de término.',
            'schedules.*.start_time.date_format' => 'O horário de início precisa estar no formato HH:MM.',
            'schedules.*.end_time.date_format' => 'O horário de término precisa estar no formato HH:MM.',
            'grade.*.date.date' => 'Informe uma data válida para a aula.',
            'grade.*.start_time.date_format' => 'O início da aula precisa estar no formato HH:MM.',
            'grade.*.end_time.date_format' => 'O término da aula precisa estar no formato HH:MM.',
        ];
    }

    /**
     * Turma online não usa o horário semanal. O select de dia vinha como segunda-feira
     * mesmo vazio, e a validação pedia início e término que o usuário não estava preenchendo.
     */
    protected function dropWeeklySchedulesWhenOnline(): void
    {
        if ($this->input('tipo_turma') === 'online') {
            $input = $this->all();
            unset($input['schedules']);
            $this->replace($input);

            return;
        }

        $this->merge([
            'schedules' => $this->normalizedSchedules(),
        ]);
    }

    protected function normalizeGradeInput(): void
    {
        if (! $this->exists('grade')) {
            return;
        }

        $grade = collect($this->input('grade', []))
            ->map(function ($row) {
                if (! is_array($row)) {
                    return $row;
                }

                $row['date'] = filled($row['date'] ?? null) ? $row['date'] : null;
                $row['start_time'] = $this->normalizeClock($row['start_time'] ?? null);
                $row['end_time'] = $this->normalizeClock($row['end_time'] ?? null);
                $row['is_online'] = $this->gradeRowIsOnline($row) ? 1 : 0;

                return $row;
            })
            ->all();

        $this->merge(['grade' => $grade]);
    }

    /**
     * @param  array<string, mixed>  $row
     */
    protected function gradeRowIsOnline(array $row): bool
    {
        return in_array($row['is_online'] ?? null, [true, 1, '1'], true);
    }

    /**
     * @return list<array{weekday: mixed, start_time: ?string, end_time: ?string}>
     */
    protected function normalizedSchedules(): array
    {
        return collect($this->input('schedules', []))
            ->filter(fn ($row) => is_array($row))
            ->map(function (array $row): array {
                $weekday = $row['weekday'] ?? null;

                return [
                    'weekday' => ($weekday === '' || $weekday === null) ? null : $weekday,
                    'start_time' => $this->normalizeClock($row['start_time'] ?? null),
                    'end_time' => $this->normalizeClock($row['end_time'] ?? null),
                ];
            })
            ->values()
            ->all();
    }

    protected function normalizeClock(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);
        if ($value === '') {
            return null;
        }

        if (preg_match('/^(\d{2}:\d{2})(?::\d{2})?$/', $value, $matches)) {
            return $matches[1];
        }

        return $value;
    }
}
