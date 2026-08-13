<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('perfis_alunos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('codigo_matricula')->nullable();
            $table->string('nivel_escolaridade')->nullable();
            $table->date('data_nascimento')->nullable();
            $table->string('telefone_secundario')->nullable();
            $table->text('observacoes_acessibilidade')->nullable();
            $table->timestamp('termos_aceitos_em')->nullable();
            $table->string('status', 20)->default('ativo');
            $table->timestamps();

            $table->unique(['tenant_id', 'user_id']);
            $table->index(['tenant_id', 'status']);
        });

        Schema::create('perfis_professores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('nome_exibicao')->nullable();
            $table->text('bio')->nullable();
            $table->text('mini_curriculo')->nullable();
            $table->string('area_especialidade')->nullable();
            $table->string('nome_assinatura')->nullable();
            $table->string('url_lattes')->nullable();
            $table->string('status', 20)->default('ativo');
            $table->timestamps();

            $table->unique(['tenant_id', 'user_id']);
            $table->index(['tenant_id', 'status']);
        });

        Schema::create('cursos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('codigo');
            $table->string('titulo');
            $table->string('slug');
            $table->string('tipo', 20);
            $table->unsignedInteger('carga_horaria');
            $table->text('resumo')->nullable();
            $table->longText('descricao')->nullable();
            $table->longText('objetivos')->nullable();
            $table->longText('publico_alvo')->nullable();
            $table->string('status', 20)->default('rascunho');
            $table->unsignedTinyInteger('presenca_minima_percentual')->default(75);
            $table->decimal('nota_minima', 5, 2)->nullable();
            $table->boolean('permite_autoinscricao')->default(false);
            $table->boolean('exige_avaliacao')->default(false);
            $table->foreignId('criado_por_user_id')->constrained('users');
            $table->foreignId('atualizado_por_user_id')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'codigo']);
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'tipo']);
        });

        Schema::create('modulos_curso', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('curso_id')->constrained('cursos')->cascadeOnDelete();
            $table->string('titulo');
            $table->text('descricao')->nullable();
            $table->unsignedInteger('ordem');
            $table->unsignedInteger('carga_horaria')->nullable();
            $table->boolean('obrigatorio')->default(true);
            $table->timestamps();

            $table->index(['curso_id', 'ordem']);
        });

        Schema::create('aulas_modulo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('modulo_curso_id')->constrained('modulos_curso')->cascadeOnDelete();
            $table->string('titulo');
            $table->text('descricao')->nullable();
            $table->unsignedInteger('ordem');
            $table->unsignedInteger('minutos_previstos');
            $table->string('tipo_conteudo', 20)->default('aula');
            $table->boolean('exige_presenca')->default(true);
            $table->timestamps();

            $table->index(['modulo_curso_id', 'ordem']);
        });

        Schema::create('materiais_curso', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('curso_id')->nullable()->constrained('cursos')->nullOnDelete();
            $table->foreignId('modulo_curso_id')->nullable()->constrained('modulos_curso')->nullOnDelete();
            $table->foreignId('aula_modulo_id')->nullable()->constrained('aulas_modulo')->nullOnDelete();
            $table->string('titulo');
            $table->text('descricao')->nullable();
            $table->string('arquivo_caminho');
            $table->string('arquivo_nome');
            $table->string('mime_type', 120);
            $table->unsignedBigInteger('arquivo_tamanho');
            $table->string('visibilidade', 20)->default('alunos');
            $table->foreignId('enviado_por_user_id')->constrained('users');
            $table->timestamps();
        });

        Schema::create('modelos_certificado', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('nome');
            $table->string('tipo', 20)->default('sistema');
            $table->string('engine', 30)->default('html');
            $table->string('arquivo_caminho')->nullable();
            $table->longText('markup_html')->nullable();
            $table->json('config_json')->nullable();
            $table->boolean('padrao')->default(false);
            $table->boolean('ativo')->default(true);
            $table->unsignedInteger('versao')->default(1);
            $table->foreignId('criado_por_user_id')->constrained('users');
            $table->timestamps();

            $table->index(['tenant_id', 'ativo']);
        });

        Schema::create('turmas_curso', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('curso_id')->constrained('cursos')->cascadeOnDelete();
            $table->string('codigo');
            $table->string('nome');
            $table->string('status', 20)->default('rascunho');
            $table->string('modalidade', 20);
            $table->timestamp('inscricao_inicio_em')->nullable();
            $table->timestamp('inscricao_fim_em')->nullable();
            $table->timestamp('inicio_em');
            $table->timestamp('fim_em');
            $table->unsignedInteger('capacidade');
            $table->unsignedInteger('capacidade_espera')->nullable();
            $table->string('local_nome')->nullable();
            $table->string('local_endereco')->nullable();
            $table->string('sala')->nullable();
            $table->string('plataforma_reuniao')->nullable();
            $table->text('url_reuniao')->nullable();
            $table->foreignId('modelo_certificado_id')->nullable()->constrained('modelos_certificado')->nullOnDelete();
            $table->unsignedTinyInteger('presenca_minima_percentual')->nullable();
            $table->decimal('nota_minima', 5, 2)->nullable();
            $table->boolean('permite_inscricao_publica')->default(true);
            $table->foreignId('criado_por_user_id')->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'codigo']);
            $table->index(['tenant_id', 'curso_id', 'status']);
            $table->index(['tenant_id', 'inicio_em', 'fim_em']);
            $table->index(['tenant_id', 'inscricao_inicio_em', 'inscricao_fim_em'], 'turmas_curso_tenant_inscricao_idx');
        });

        Schema::create('instrutores_turma', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('turma_curso_id')->constrained('turmas_curso')->cascadeOnDelete();
            $table->foreignId('perfil_professor_id')->constrained('perfis_professores')->cascadeOnDelete();
            $table->string('papel', 20)->default('principal');
            $table->boolean('principal')->default(false);
            $table->timestamps();

            $table->unique(['turma_curso_id', 'perfil_professor_id']);
        });

        Schema::create('encontros_turma', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('turma_curso_id')->constrained('turmas_curso')->cascadeOnDelete();
            $table->foreignId('aula_modulo_id')->nullable()->constrained('aulas_modulo')->nullOnDelete();
            $table->foreignId('perfil_professor_id')->nullable()->constrained('perfis_professores')->nullOnDelete();
            $table->string('titulo');
            $table->text('descricao')->nullable();
            $table->date('data_encontro');
            $table->timestamp('inicio_em');
            $table->timestamp('fim_em');
            $table->unsignedInteger('carga_horaria_minutos');
            $table->string('local_nome')->nullable();
            $table->text('url_reuniao')->nullable();
            $table->boolean('exige_presenca')->default(true);
            $table->string('status', 20)->default('agendado');
            $table->foreignId('registrado_por_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['turma_curso_id', 'data_encontro']);
            $table->index(['turma_curso_id', 'inicio_em']);
            $table->index(['perfil_professor_id', 'data_encontro']);
        });

        Schema::create('matriculas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('turma_curso_id')->constrained('turmas_curso')->cascadeOnDelete();
            $table->foreignId('perfil_aluno_id')->constrained('perfis_alunos')->cascadeOnDelete();
            $table->string('numero_matricula');
            $table->string('status', 20)->default('pendente');
            $table->timestamp('matriculado_em');
            $table->timestamp('confirmado_em')->nullable();
            $table->timestamp('cancelado_em')->nullable();
            $table->timestamp('concluido_em')->nullable();
            $table->text('motivo_cancelamento')->nullable();
            $table->decimal('percentual_presenca_cache', 5, 2)->default(0);
            $table->decimal('percentual_conclusao_cache', 5, 2)->default(0);
            $table->decimal('nota_final', 5, 2)->nullable();
            $table->timestamp('certificado_emitido_em')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['turma_curso_id', 'perfil_aluno_id']);
            $table->unique(['tenant_id', 'numero_matricula']);
            $table->index(['tenant_id', 'status']);
            $table->index(['turma_curso_id', 'status']);
            $table->index(['perfil_aluno_id', 'status']);
        });

        Schema::create('historicos_status_matricula', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('matricula_id')->constrained('matriculas')->cascadeOnDelete();
            $table->string('status_anterior', 20)->nullable();
            $table->string('novo_status', 20);
            $table->text('motivo')->nullable();
            $table->foreignId('alterado_por_user_id')->constrained('users');
            $table->timestamps();
        });

        Schema::create('presencas_encontro', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('encontro_turma_id')->constrained('encontros_turma')->cascadeOnDelete();
            $table->foreignId('matricula_id')->constrained('matriculas')->cascadeOnDelete();
            $table->string('status', 20)->default('presente');
            $table->unsignedInteger('minutos_presentes')->nullable();
            $table->decimal('percentual_presenca', 5, 2)->nullable();
            $table->text('observacoes')->nullable();
            $table->foreignId('registrado_por_user_id')->constrained('users');
            $table->timestamp('registrado_em');
            $table->timestamps();

            $table->unique(['encontro_turma_id', 'matricula_id']);
            $table->index(['matricula_id', 'status']);
        });

        Schema::create('avaliacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('turma_curso_id')->constrained('turmas_curso')->cascadeOnDelete();
            $table->foreignId('modulo_curso_id')->nullable()->constrained('modulos_curso')->nullOnDelete();
            $table->string('titulo');
            $table->text('descricao')->nullable();
            $table->string('tipo', 20)->default('prova');
            $table->decimal('nota_maxima', 5, 2);
            $table->decimal('nota_minima', 5, 2)->nullable();
            $table->decimal('peso', 8, 2)->default(1);
            $table->boolean('obrigatoria')->default(true);
            $table->timestamp('disponivel_em')->nullable();
            $table->timestamp('entrega_ate_em')->nullable();
            $table->timestamps();

            $table->index(['turma_curso_id', 'tipo']);
        });

        Schema::create('resultados_avaliacao', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('avaliacao_id')->constrained('avaliacoes')->cascadeOnDelete();
            $table->foreignId('matricula_id')->constrained('matriculas')->cascadeOnDelete();
            $table->decimal('nota', 5, 2)->nullable();
            $table->string('status', 20)->default('pendente');
            $table->text('feedback')->nullable();
            $table->timestamp('enviado_em')->nullable();
            $table->timestamp('corrigido_em')->nullable();
            $table->foreignId('corrigido_por_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['avaliacao_id', 'matricula_id']);
        });

        Schema::create('feedbacks_aluno', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('turma_curso_id')->constrained('turmas_curso')->cascadeOnDelete();
            $table->foreignId('matricula_id')->constrained('matriculas')->cascadeOnDelete();
            $table->unsignedTinyInteger('nota')->nullable();
            $table->text('comentario')->nullable();
            $table->boolean('anonimo')->default(false);
            $table->string('status', 20)->default('aberto');
            $table->timestamps();
        });

        Schema::create('certificados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('matricula_id')->constrained('matriculas')->cascadeOnDelete();
            $table->foreignId('curso_id')->constrained('cursos');
            $table->foreignId('turma_curso_id')->constrained('turmas_curso');
            $table->foreignId('perfil_aluno_id')->constrained('perfis_alunos');
            $table->foreignId('modelo_certificado_id')->nullable()->constrained('modelos_certificado')->nullOnDelete();
            $table->string('numero_certificado')->unique();
            $table->string('codigo_validacao')->unique();
            $table->string('hash_qr_code')->nullable()->unique();
            $table->string('nome_aluno_snapshot');
            $table->string('nome_curso_snapshot');
            $table->unsignedInteger('carga_horaria_snapshot');
            $table->date('data_conclusao_snapshot');
            $table->timestamp('emitido_em');
            $table->string('pdf_caminho')->nullable();
            $table->json('payload_renderizacao')->nullable();
            $table->string('status', 20)->default('emitido');
            $table->timestamp('revogado_em')->nullable();
            $table->text('motivo_revogacao')->nullable();
            $table->timestamps();

            $table->unique('matricula_id');
            $table->index(['tenant_id', 'emitido_em']);
            $table->index(['perfil_aluno_id', 'emitido_em']);
        });

        Schema::create('logs_validacao_certificado', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->foreignId('certificado_id')->nullable()->constrained('certificados')->nullOnDelete();
            $table->string('codigo_pesquisado');
            $table->string('ip')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('validado_em');
            $table->boolean('sucesso')->default(false);
            $table->timestamps();

            $table->index(['codigo_pesquisado']);
            $table->index(['validado_em', 'sucesso']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logs_validacao_certificado');
        Schema::dropIfExists('certificados');
        Schema::dropIfExists('feedbacks_aluno');
        Schema::dropIfExists('resultados_avaliacao');
        Schema::dropIfExists('avaliacoes');
        Schema::dropIfExists('presencas_encontro');
        Schema::dropIfExists('historicos_status_matricula');
        Schema::dropIfExists('matriculas');
        Schema::dropIfExists('encontros_turma');
        Schema::dropIfExists('instrutores_turma');
        Schema::dropIfExists('turmas_curso');
        Schema::dropIfExists('modelos_certificado');
        Schema::dropIfExists('materiais_curso');
        Schema::dropIfExists('aulas_modulo');
        Schema::dropIfExists('modulos_curso');
        Schema::dropIfExists('cursos');
        Schema::dropIfExists('perfis_professores');
        Schema::dropIfExists('perfis_alunos');
    }
};
