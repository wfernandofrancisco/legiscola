# 📊 **Spatie Activity Log - Guia Completo**

## O que é `activity()`?

É uma função do **Spatie Activity Log** que registra automaticamente tudo o que acontece no sistema (ações de usuários, criações, edições, deletions).

---

## 📝 **EXEMPLOS PRÁTICOS**

### ✅ Exemplo 1: Log Simples (Apenas Log)
```php
activity('central')
    ->causedBy(auth()->user())
    ->log('Listagem de empresas (central) visualizada');
```

**O que registra:**
```
Tabela: activity_log
┌──────┬──────────────────────────────────┬────────────┬────────────────┐
│ id   │ description                      │ subject_id │ causer_id      │
├──────┼──────────────────────────────────┼────────────┼────────────────┤
│ 1    │ Listagem de empresas visualizada │ NULL       │ 1 (admin user) │
└──────┴──────────────────────────────────┴────────────┴────────────────┘
```

---

### ✅ Exemplo 2: Log em um Modelo (performedOn)
```php
$company = Company::find(1);

activity('central')
    ->causedBy(auth()->user())
    ->performedOn($company)  // ← Registra qual objeto foi afetado
    ->log('Detalhes da empresa visualizados');
```

**O que registra:**
```
┌──────┬──────────────────┬──────────────┬────────────────┐
│ id   │ description      │ subject_id   │ subject_type   │
├──────┼──────────────────┼──────────────┼────────────────┤
│ 2    │ Detalhes da... │ 1 (company.id)│ App\Models\Company│
└──────┴──────────────────┴──────────────┴────────────────┘
```

---

### ✅ Exemplo 3: Log de CRUD Automático
```php
// Em RoleController@store
$role = Role::create(['name' => 'Editor']);

// O Activity Log DO MODEL registra automaticamente:
activity('central')
    ->causedBy(auth()->user())
    ->performedOn($role)
    ->log('Novo role criado');
```

---

## 🔍 **ESTRUTURA DA TABELA activity_log**

```sql
CREATE TABLE activity_log (
    id BIGINT PRIMARY KEY,
    log_name VARCHAR(255),           -- 'central', 'admin', 'app'
    description TEXT,                -- 'Novo role criado'
    subject_type VARCHAR(255),       -- 'App\Models\Role', 'App\Models\User'
    subject_id BIGINT,               -- ID do objeto (ex: role.id = 5)
    causer_type VARCHAR(255),        -- 'App\Models\User'
    causer_id BIGINT,                -- ID do usuário que fez a ação
    properties TEXT (JSON),          -- Dados antes/depois (diff)
    event VARCHAR(255),              -- 'created', 'updated', 'deleted'
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

---

## 🎯 **CASOS DE USO NO CÓDIGO**

### 1️⃣ Visualização de Página
```php
// Em CompanyController@index (linha 50 do seu código)
activity('central')
    ->causedBy(auth()->user())
    ->log('Listagem de empresas (central) visualizada');
```

### 2️⃣ Criação de Recurso
```php
// Em RoleController@store
$role = Role::create(['name' => 'Editor']);

activity('central')
    ->causedBy(auth()->user())
    ->performedOn($role)
    ->log('Novo role criado');
```

### 3️⃣ Atualização de Recurso
```php
// Em RoleController@update
$role->update(['name' => 'Super Editor']);

activity('central')
    ->causedBy(auth()->user())
    ->performedOn($role)
    ->log("Role \"{$role->name}\" atualizado");
```

### 4️⃣ Deletação de Recurso
```php
// Em RoleController@destroy
activity('central')
    ->causedBy(auth()->user())
    ->log("Role \"{$role->name}\" deletado");

$role->delete();
```

### 5️⃣ Ação Customizada
```php
// Em RoleController@syncPermissions
activity('central')
    ->causedBy(auth()->user())
    ->performedOn($role)
    ->log("Permissions do role \"{$role->name}\" sincronizadas");
```

---

## 📊 **CONSULTAR LOGS NO TINKER**

```php
php artisan tinker

# Listar todos os logs
>>> activity()->all();

# Logs apenas do 'central'
>>> activity('central')->all();

# Logs de um usuário específico
>>> user = User::find(1); user.activities();

# Logs de um modelo
>>> company = Company::find(1); company.activities();

# Últimos 10 logs
>>> activity()->latest()->limit(10)->get();

# Logs de um tipo (created, updated, deleted)
>>> activity()->where('event', 'created')->get();

# Logs dentro de um período
>>> activity()->whereBetween('created_at', ['2026-04-01', '2026-04-09'])->get();
```

---

## 🛠️ **IMPLEMENTAR LOGS AUTOMÁTICOS NO MODEL**

Você pode fazer logs automáticos no Model usando `getActivitylogOptions()`:

```php
// app/Models/Role.php
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Role extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name'])      // Apenas track mudanças no 'name'
            ->logOnlyDirty()         // Apenas quando houver mudança
            ->useLogName('central'); // Registra como 'central'
    }
}
```

Agora, quando você fizer:
```php
$role = Role::find(1);
$role->update(['name' => 'Novo Nome']);
```

Automaticamente registra na tabela activity_log com:
- **description**: "Role foi updated" (definido em getActivitylogOptions)
- **log_name**: "central"
- **subject_type**: "App\Models\Role"
- **subject_id**: 1
- **causer_id**: ID do usuário autenticado
- **properties**: `{"attributes": {"name": "Novo Nome"}, "old": {"name": "Nome Antigo"}}`

---

## 📈 **VISUALIZAR LOGS NO DASHBOARD**

```php
// Em DashboardController@index
$recentActivity = activity('central')
    ->latest()
    ->limit(20)
    ->get();

return view('central.dashboard', compact('recentActivity'));
```

```blade
<!-- View: resources/views/central/dashboard.blade.php -->
<div class="log-list">
    @foreach($recentActivity as $log)
        <div class="log-item">
            <strong>{{ $log->causer->name }}</strong>
            <p>{{ $log->description }}</p>
            <small>{{ $log->created_at->diffForHumans() }}</small>
        </div>
    @endforeach
</div>
```

---

## 🔐 **PROTEÇÃO: Logs Não Podem Ser Removidos**

Os logs são **imutáveis** (apenas leitura). Você não consegue editar/deletar logs:

```php
// ❌ NÃO FUNCIONA
activity()->first()->update(['description' => 'Modificado']);

// ✅ APENAS LEITURA
activity()->all();
activity()->where('causer_id', 1)->get();
```

Isso é bom para **auditoria e compliance** (Lei Lgpd, Sarbanes-Oxley, etc).

---

## 🎯 **RESUMO DAS FUNÇÕES**

| Função | O que faz |
|--------|-----------|
| `activity('central')` | Inicia um registro de log |
| `->causedBy(auth()->user())` | Marca quem fez a ação |
| `->performedOn($model)` | Registra qual objeto foi afetado |
| `->log('mensagem')` | Define a mensagem do log |
| `->latest()` | Ordena por mais recente |
| `->limit(10)` | Limita a 10 registros |
| `->where('event', 'created')` | Filtra por tipo de evento |

---

## 📚 **DOCUMENTAÇÃO OFICIAL**

https://spatie.be/docs/laravel-activitylog/v4/introduction

---

**Quer ver os logs criados?** Execute:
```bash
php artisan tinker
>>> DB::table('activity_log')->latest()->limit(5)->get();
```
