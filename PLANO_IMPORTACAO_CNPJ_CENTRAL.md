# Plano de Implementacao - Importacao CNPJ no Modulo Central

## Objetivo

Migrar o processo legado (C# offline) para o Laravel, executando no servidor, com:

- Download direto da base publica da Receita por competencia (`YYYY-MM`)
- Processamento em background (fila/jobs), sem travar o sistema
- Filtro por municipios dos tenants (codigo IBGE)
- Persistencia apenas dos dados relevantes para seus clientes
- Retomada segura apos falha de conexao, queda de worker ou erro no processamento

Fontes oficiais:

- [Repositorio CNPJ SERPRO+](https://arquivos.receitafederal.gov.br/index.php/s/YggdBLfdninEJX9)
- [Exemplo de pasta mensal](https://arquivos.receitafederal.gov.br/index.php/s/YggdBLfdninEJX9?dir=/2023-05)

---

## Visao geral da estrategia

1. O usuario informa no painel a competencia (ex.: `2023-05`).
2. O sistema cria uma execucao de importacao (`import_runs`) com status `queued`.
3. Jobs em fila:
   - Montam as URLs dos ZIPs da competencia
   - Baixam para storage local
   - Importam para tabelas de staging
   - Filtram por municipios IBGE dos tenants
   - Fazem `upsert` nas tabelas finais
4. O painel mostra progresso, logs e erros.
5. Se cair conexao/processo, a execucao fica registrada e pode ser retomada.

Modo operacional definido para esta etapa:

- importacao manual de arquivos (sem download automatico)
- os arquivos descompactados devem ser colocados em `storage/app/private/imports/manual/{competencia}`
- o comando processa diretamente os arquivos dessa pasta

---

## Fase 1 - Fundacao de dados

### 1.1 Criar codigo IBGE no tenant

Adicionar coluna em `tenants`:

- `codigo_ibge_municipio` (string/char com 7 digitos)

Objetivo: cada tenant tem uma cidade principal e a importacao filtra por esse codigo.

### 1.2 Criar tabelas de controle de execucao

Tabela `import_runs`:

- `id`
- `competencia` (`YYYY-MM`)
- `status` (`queued`, `running`, `failed`, `finished`, `cancelled`)
- `started_at`, `finished_at`
- `requested_by` (user_id)
- `error_message` (nullable)
- `meta` (json: contadores, arquivos, etc.)

Tabela `import_run_steps`:

- `id`
- `import_run_id`
- `step` (`download_estabelecimentos`, `import_estabelecimentos`, etc.)
- `status`
- `processed_count`
- `error_message`
- `started_at`, `finished_at`

---

## Fase 2 - Staging (importacao bruta)

Criar tabelas de staging (prefixo `stg_`):

- `stg_estabelecimentos`
- `stg_empresas`
- `stg_simples`

Campos devem refletir layout Receita (somente os usados no sistema + chaves).

Regras:

- Sempre gravar `import_run_id` para rastreabilidade.
- Indices em campos de join/filtro (`cnpj_basico`, `municipio`, etc.).
- Evitar `truncate` cego em producao.

---

## Layout oficial confirmado (guia Receita)

### Tabela final unica: `empresas`

Decisao confirmada: teremos uma tabela final unica chamada `empresas`, consolidando:

- dados de `Estabelecimentos` (base principal do CNPJ completo)
- dados de `Empresas` (dados cadastrais da PJ por `cnpj_basico`)
- dados de `Simples` (opcao simples/MEI por `cnpj_basico`)

Chave recomendada da tabela final:

- `cnpj_basico` (8)
- `cnpj_ordem` (4)
- `cnpj_dv` (2)
- `cnpj` (14, derivado da concatenacao)
- unique index em `cnpj`

Campos principais a consolidar na tabela `empresas`:

- **Do arquivo EMPRESAS**
  - `razao_social`
  - `natureza_juridica`
  - `qualificacao_responsavel`
  - `capital_social`
  - `porte_empresa`
  - `ente_federativo_responsavel`
- **Do arquivo ESTABELECIMENTOS**
  - `identificador_matriz_filial`
  - `nome_fantasia`
  - `situacao_cadastral`
  - `data_situacao_cadastral`
  - `motivo_situacao_cadastral`
  - `nome_cidade_exterior`
  - `pais`
  - `data_inicio_atividade`
  - `cnae_fiscal_principal`
  - `cnae_fiscal_secundaria`
  - `tipo_logradouro`
  - `logradouro`
  - `numero`
  - `complemento`
  - `bairro`
  - `cep`
  - `uf`
  - `municipio_codigo_ibge`
  - `ddd1`, `telefone1`, `ddd2`, `telefone2`, `ddd_fax`, `fax`
  - `email`
  - `situacao_especial`
  - `data_situacao_especial`
- **Do arquivo SIMPLES**
  - `opcao_simples`
  - `data_opcao_simples`
  - `data_exclusao_simples`
  - `opcao_mei`
  - `data_opcao_mei`
  - `data_exclusao_mei`
- **Campos internos do sistema (mapa/geolocalizacao)**
  - `latitude` (nullable)
  - `longitude` (nullable)
  - `geocoding_status` (pending/success/failed)
  - `geocoded_at` (nullable)
  - `geocoding_source` (nullable: provider usado)

Observacoes de layout importantes:

- Separador oficial dos arquivos: `;`
- `cnae_fiscal_secundaria` vem com multiplas ocorrencias separadas por `,`
- filtro de negocio por cidade deve usar `municipio` (codigo IBGE)

Fora de escopo nesta etapa:

- arquivo de `Socios` (nao sera importado nesta versao inicial)

---

## Fase 3 - Servicos de download

### 3.1 Padrao de URL por competencia

Base:

`https://arquivos.receitafederal.gov.br/dados/cnpj/dados_abertos_cnpj/{competencia}/`

Arquivos principais:

- `Estabelecimentos0.zip` ... `Estabelecimentos9.zip` (ou quantidade disponivel no mes)
- `Empresas0.zip` ... `Empresas9.zip`
- `Simples.zip`
- `Cnaes.zip`
- `Naturezas.zip`

### 3.2 Comportamento do downloader

- Baixar para `storage/app/imports/{competencia}/{run_id}/`
- Validar tamanho do arquivo e hash/consistencia minima
- Retry com backoff (3 tentativas)
- Gravar log por arquivo em `import_run_steps`

---

## Fase 4 - Pipeline de processamento

Ordem recomendada:

1. Importar `Estabelecimentos*` para `stg_estabelecimentos`
2. Filtrar por municipios IBGE ativos dos tenants
3. Gerar conjunto de `cnpj_basico` elegiveis
4. Importar `Empresas*` filtrando por `cnpj_basico` elegivel
5. Importar `Simples.zip` filtrando por `cnpj_basico` elegivel
6. Consolidar nas tabelas finais com `upsert`

Observacao:

- O download pode ser nacional, mas o armazenamento final deve ficar focado no universo dos tenants.

---

## Fase 5 - Filtragem multi-tenant

### Regra de negocio principal

- Um CNPJ entra na base final se o municipio IBGE do estabelecimento for igual ao `codigo_ibge_municipio` de algum tenant ativo.

### Estrategia recomendada de persistencia

- Base final unica (nao duplicar linhas por tenant)
- Criar relacionamento de visibilidade por tenant quando necessario (ex.: tabela de vinculo)
- Consultas no sistema usam tenant context + relacao de visibilidade

---

## Fase 6 - UI no modulo central

Criar tela `Central > Importacao CNPJ` com:

- Campo `competencia` (`YYYY-MM`)
- Botao `Iniciar importacao`
- Lista de execucoes recentes
- Barra de progresso por etapa
- Logs e mensagem de erro amigavel
- Acao `Retomar` quando status `failed`
- Acao `Cancelar` quando status `running`

---

## Fase 7 - Resiliencia (queda de conexao)

Importante: a queda da sua internet/browser nao deve parar job de fila se worker estiver no servidor.

### Regras para retomada segura

1. Toda etapa atualiza `import_run_steps`.
2. Jobs sao idempotentes (podem rodar novamente sem duplicar dados).
3. Em falha, status fica `failed` com erro salvo.
4. Botao `Retomar` dispara apenas etapas pendentes/falhadas.
5. Se arquivo ja existe no storage, nao baixar de novo (cache por run/competencia).

### Checklist de retomada rapida

Quando a conexao cair:

1. Abrir sistema e ir na tela de importacao.
2. Ver ultimo `import_run` com status.
3. Se `running`: aguardar ou verificar worker.
4. Se `failed`: clicar `Retomar`.
5. Validar contadores finais e logs.

---

## Fase 8 - Infra de execucao

### Ambiente

- Queue driver de producao (Redis recomendado)
- Worker gerenciado por Supervisor/PM2/systemd
- Timeout alto para jobs pesados
- Monitoramento de falhas (logs + notificacao)

### Comandos base (exemplo)

```bash
php artisan queue:work --queue=imports --tries=3 --timeout=1200
```

Opcional:

- Separar fila `imports` das filas comuns do sistema
- Rodar importacao fora de horario comercial

---

## Fase 9 - Validacoes e testes

### Testes tecnicos

- Teste de parse de arquivos Receita
- Teste de filtro por IBGE dos tenants ativos
- Teste de idempotencia (rodar duas vezes sem duplicar)
- Teste de retomada apos falha simulada
- Teste de performance com amostra grande

### Validacao funcional

- Conferir se tenants veem apenas empresas dos municipios configurados
- Conferir contagem esperada por competencia
- Conferir logs de inicio/fim por etapa

---

## Fase 10 - Normalizacao de endereco (pos-importacao)

Requisito registrado:

- apos a importacao ficar estavel, executar processo de padronizacao de endereco
- objetivo: resolver variacoes de escrita em `bairro/logradouro` (ex.: "JD ELOISA" x "JARDIM ELOISA")

Estrategia recomendada:

1. Criar script/job de normalizacao separado da importacao principal.
2. Usar `cep` + `numero` para tentar enriquecer/padronizar endereco.
3. Priorizar fontes de consulta com cache local para reduzir custo e limite de requisicoes.
4. Gravar campos normalizados separados dos campos brutos importados:
   - `logradouro_normalizado`
   - `bairro_normalizado`
   - `cidade_normalizada`
5. Nunca sobrescrever o valor bruto original sem trilha de auditoria.

Nota:

- esse passo deve rodar em lote, por fila, com retry e rate limit.

---

## Fase 11 - Geolocalizacao para mapa (latitude/longitude)

Requisito registrado:

- apos importacao e normalizacao de endereco, enriquecer `empresas` com `latitude` e `longitude` para uso no mapa.

Observacao importante:

- os arquivos padrao da Receita normalmente nao trazem latitude/longitude prontas para os estabelecimentos.
- portanto, a geolocalizacao sera processo interno do sistema.

Estrategia recomendada:

1. Criar job em lote para geocodificar registros sem coordenadas.
2. Priorizar endereco normalizado (`cep`, `logradouro`, `numero`, `municipio`, `uf`).
3. Aplicar cache para evitar geocodificacao repetida do mesmo endereco.
4. Definir `rate limit` e retries para nao bloquear o provider.
5. Salvar `geocoding_status` por registro para retomada segura.

Fallback:

- se endereco incompleto, geocodificar pelo `cep`/`municipio` como aproximacao e marcar baixa precisao.

---

## Entregas em ordem (para fazer junto comigo)

1. Migrations (`codigo_ibge_municipio` em `tenants`, `import_runs`, `import_run_steps`, staging, `empresas`)
2. Models e relacoes
3. Service de download por competencia
4. Service de parse/import por tipo de arquivo
5. Jobs e orchestrator da pipeline
6. Tela no modulo central (listar execucoes, iniciar, retomar, cancelar)
7. Ajustes de autorizacao/permissoes
8. Testes e validacao final

---

## Log de progresso (preencher durante implementacao)

Use este bloco para nao perder contexto entre sessoes:

```txt
Data:
Responsavel:
Etapa atual:
Ultimo commit:
Status da fila:
Pendencias:
Proximo passo:
```

---

## Decisoes importantes (congelar antes de codar)

- [x] Base final unica chamada `empresas`
- [ ] Banco de staging no mesmo schema ou schema separado
- [ ] Manter copia local dos ZIPs por quantos dias
- [ ] Limite de reprocessamento por competencia
- [ ] Permissao para quem pode iniciar importacao

---

## Observacao final

Este plano foi feito para implementar tudo no Laravel e no servidor, sem depender do fluxo offline atual em C# no seu PC.
