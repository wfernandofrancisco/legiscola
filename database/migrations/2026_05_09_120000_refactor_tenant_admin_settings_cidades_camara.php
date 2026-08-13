<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('tenant_admin_settings', 'whatsapp_secretaria_industria_comercio')
            && ! Schema::hasColumn('tenant_admin_settings', 'whatsapp')) {
            Schema::table('tenant_admin_settings', function (Blueprint $table) {
                $table->renameColumn('whatsapp_secretaria_industria_comercio', 'whatsapp');
            });
        }

        if (Schema::hasColumn('tenant_admin_settings', 'nome_secretaria')
            && ! Schema::hasColumn('tenant_admin_settings', 'nome_camara')) {
            Schema::table('tenant_admin_settings', function (Blueprint $table) {
                $table->renameColumn('nome_secretaria', 'nome_camara');
            });
        }

        Schema::table('tenant_admin_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('tenant_admin_settings', 'email')) {
                $table->string('email', 255)->nullable();
            }
            if (! Schema::hasColumn('tenant_admin_settings', 'cep')) {
                $table->string('cep', 12)->nullable();
            }
            if (! Schema::hasColumn('tenant_admin_settings', 'logradouro')) {
                $table->string('logradouro', 255)->nullable();
            }
            if (! Schema::hasColumn('tenant_admin_settings', 'numero')) {
                $table->string('numero', 30)->nullable();
            }
            if (! Schema::hasColumn('tenant_admin_settings', 'bairro')) {
                $table->string('bairro', 120)->nullable();
            }
            if (! Schema::hasColumn('tenant_admin_settings', 'uf')) {
                $table->string('uf', 2)->nullable();
            }
            if (! Schema::hasColumn('tenant_admin_settings', 'horario_funcionamento')) {
                $table->string('horario_funcionamento', 500)->nullable();
            }
            if (! Schema::hasColumn('tenant_admin_settings', 'instagram')) {
                $table->string('instagram', 255)->nullable();
            }
            if (! Schema::hasColumn('tenant_admin_settings', 'x')) {
                $table->string('x', 255)->nullable();
            }
            if (! Schema::hasColumn('tenant_admin_settings', 'facebook')) {
                $table->string('facebook', 255)->nullable();
            }
            if (! Schema::hasColumn('tenant_admin_settings', 'telefone')) {
                $table->string('telefone', 30)->nullable();
            }
        });

        $drop = array_values(array_filter([
            Schema::hasColumn('tenant_admin_settings', 'aprovar_alteracoes_empresas') ? 'aprovar_alteracoes_empresas' : null,
            Schema::hasColumn('tenant_admin_settings', 'permitir_atualizacao_empresa') ? 'permitir_atualizacao_empresa' : null,
            Schema::hasColumn('tenant_admin_settings', 'logo_secretaria_path') ? 'logo_secretaria_path' : null,
        ]));

        if ($drop !== []) {
            Schema::table('tenant_admin_settings', function (Blueprint $table) use ($drop) {
                $table->dropColumn($drop);
            });
        }
    }

    public function down(): void
    {
        $newCols = ['email', 'cep', 'logradouro', 'numero', 'bairro', 'uf', 'horario_funcionamento', 'instagram', 'x', 'facebook', 'telefone'];
        $toDrop = array_values(array_filter($newCols, fn (string $c): bool => Schema::hasColumn('tenant_admin_settings', $c)));
        if ($toDrop !== []) {
            Schema::table('tenant_admin_settings', function (Blueprint $table) use ($toDrop) {
                $table->dropColumn($toDrop);
            });
        }

        if (Schema::hasColumn('tenant_admin_settings', 'nome_camara')
            && ! Schema::hasColumn('tenant_admin_settings', 'nome_secretaria')) {
            Schema::table('tenant_admin_settings', function (Blueprint $table) {
                $table->renameColumn('nome_camara', 'nome_secretaria');
            });
        }

        if (Schema::hasColumn('tenant_admin_settings', 'whatsapp')
            && ! Schema::hasColumn('tenant_admin_settings', 'whatsapp_secretaria_industria_comercio')) {
            Schema::table('tenant_admin_settings', function (Blueprint $table) {
                $table->renameColumn('whatsapp', 'whatsapp_secretaria_industria_comercio');
            });
        }

        Schema::table('tenant_admin_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('tenant_admin_settings', 'aprovar_alteracoes_empresas')) {
                $table->boolean('aprovar_alteracoes_empresas')->default(false);
            }
            if (! Schema::hasColumn('tenant_admin_settings', 'permitir_atualizacao_empresa')) {
                $table->boolean('permitir_atualizacao_empresa')->default(false);
            }
            if (! Schema::hasColumn('tenant_admin_settings', 'logo_secretaria_path')) {
                $table->string('logo_secretaria_path', 500)->nullable();
            }
        });
    }
};
