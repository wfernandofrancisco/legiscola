# ✅ **RESUMO: CRUD de Roles, Permissions e Explicações**

## 🎯 **O que foi criado nesta sessão:**

### 1️⃣ **CRUD Completo de Roles** ✅
- ✅ `app/Http/Controllers/Central/RoleController.php`
- Features:
  - Listar todos os roles
  - Criar novo role
  - Editar role (com proteção de roles do sistema)
  - Deletar role (com proteção)
  - Sincronizar permissions do role
  - Activity log em cada ação

### 2️⃣ **CRUD Completo de Permissions** ✅
- ✅ `app/Http/Controllers/Central/PermissionController.php`
- Features:
  - Listar permissões
  - Criar permissão
  - Editar permissão (com proteção de permissions padrão)
  - Deletar permissão (com proteção)
  - Activity log em cada ação

### 3️⃣ **Rotas Adicionadas** ✅
- ✅ Atualizadas em `routes/modules/central.php`
```php
// Roles
Route::resource('roles', RoleController::class);
Route::post('roles/{role}/sync-permissions', [RoleController::class, 'syncPermissions']);

// Permissions
Route::resource('permissions', PermissionController::class);
```

---

## 📚 **Documentação Criada:**

### 📖 **ACTIVITY_LOG_GUIDE.md**
Explicação completa sobre `activity()`:
- O que é e como funciona
- Exemplos práticos (simples, com modelo, CRUD)
- Estrutura da tabela `activity_log`
- Consultas no Tinker
- Visua

lização no dashboard

**Exemplo do seu código:**
```php
activity('central')
    ->causedBy(auth()->user())
    ->log('Listagem de empresas (central) visualizada');
```

✅ Isso registra na tabela `activity_log`:
- **Quem fez:** Usuario autenticado (`causedBy`)
- **O que fez:** mensagem ("Listagem...")
- **Quando:** timestamp automático
- **Onde:** módulo 'central'

---

### 📖 **CONTROLLERS_STRUCTURE.md**
Explicação completa sobre as pastas de Controllers:

```
app/Http/Controllers/
├── Central/       ← VOCÊ (Super Admin) - Vê TUDO
├── Admin/         ← Admin do Tenant - Vê apenas seu tenant
├── Responsible/   ← Manager - Vê seu tenant (permissões restritas)
├── App/           ← User Normal - Vê seu tenant (leitura)
├── Api/           ← API REST para Mobile (não usar ainda)
├── Public/        ← Páginas públicas
└── Auth/          ← Autenticação
```

**Resposta: Por que a pasta App existe?**
```
❌ ERRADO: App/ = API para consumir localmente
✅ CORRETO: 
   - App/ = Painel WEB do usuário normal
   - Api/ = API para o Ionic consumir
```

---

## 🔐 **PROTEÇÕES IMPLEMENTADAS**

### 1. Roles do Sistema (Não podem ser editados/deletados)
```php
$systemRoles = ['super-admin', 'tenant-admin', 'tenant-manager', 'tenant-user'];

if (in_array($role->name, $systemRoles)) {
    return back()->with('error', 'Não é permitido editar roles do sistema.');
}
```

### 2. Permissions Padrão (Não podem ser alteradas)
```php
$systemPermissions = [
    'view-users', 'create-users', 'edit-users', 'delete-users',
    'view-companies', 'create-companies', 'edit-companies', 'delete-companies',
    // ... mais 10 permissions
];

if (in_array($permission->name, $systemPermissions)) {
    return back()->with('error', 'Não é permitido editar permissions do sistema.');
}
```

---

## 📝 **FLUXO DO CRUD DE ROLES**

### Criar Role:
```
1. GET /central/roles/create
   → RoleController@create
   → Carrega todas as permissions
   → Mostra formulário

2. POST /central/roles
   → RoleController@store
   → Valida (nome único, permissions array)
   → Cria role com Role::create()
   → Vincula permissions com $role->syncPermissions()
   → Registra log: 'Novo role criado'
   → Redireciona para show com sucesso
```

### Editar Role:
```
1. GET /central/roles/{role}/edit
   → RoleController@edit
   → Carrega role + permissions vinculadas
   → Mostra formulário com checkboxes marcadas

2. PUT /central/roles/{role}
   → RoleController@update
   → Valida (nome único, sem ser do sistema)
   → Atualiza nome
   → Ressincroni za permissions com syncPermissions()
   → Registra log: "Role \"{$role->name}\" atualizado"
   → Redireciona para show com sucesso
```

### Deletar Role:
```
1. DELETE /central/roles/{role}
   → RoleController@destroy
   → Verifica se é role do sistema (error se sim)
   → Deleta role: $role->delete()
   → Registra log: "Role deletado"
   → Redireciona para index com sucesso
```

### Sincronizar Permissions:
```
POST /central/roles/{role}/sync-permissions
→ RoleController@syncPermissions
→ Recebe array de permission IDs
→ Executa: $role->syncPermissions($ids)
→ Registra log: "Permissions sincronizadas"
→ Volta para página anterior
```

---

## 🔄 **COMO USAR (Exemplo na Blade)**

```blade
<!-- Criar novo role -->
<a href="{{ route('central.roles.create') }}" class="btn btn-primary">
    Novo Role
</a>

<!-- Listar roles com permissions -->
@foreach($roles as $role)
    <tr>
        <td>{{ $role->name }}</td>
        <td>{{ $role->permissions_count }} permissões</td>
        <td>
            <a href="{{ route('central.roles.edit', $role) }}">Editar</a>
            <form action="{{ route('central.roles.destroy', $role) }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit">Deletar</button>
            </form>
        </td>
    </tr>
@endforeach

<!-- Vincular permissions no formulário -->
<form action="{{ route('central.roles.update', $role) }}" method="POST">
    @csrf
    @method('PUT')
    
    <label>
        <input type="checkbox" name="permissions[]" value="1" 
            {{ in_array(1, $rolePermissions) ? 'checked' : '' }}>
        View Users
    </label>
</form>
```

---

## 📊 **O que Registra o Activity Log**

### Ação: Criar Role
```
activity_log entry:
{
  log_name: 'central',
  description: 'Novo role criado',
  subject_type: 'Spatie\Permission\Models\Role',
  subject_id: 5,
  causer_id: 1,
  event: 'created',
  created_at: '2026-04-09 10:30:00'
}
```

### Ação: Editar Role
```
activity_log entry:
{
  log_name: 'central',
  description: 'Role "Editor" atualizado',
  subject_type: 'Spatie\Permission\Models\Role',
  subject_id: 5,
  causer_id: 1,
  event: 'updated',
  created_at: '2026-04-09 10:35:00'
}
```

### Ação: Sincronizar Permissions
```
activity_log entry:
{
  log_name: 'central',
  description: 'Permissions do role "Editor" sincronizadas',
  subject_type: 'Spatie\Permission\Models\Role',
  subject_id: 5,
  causer_id: 1,
  event: 'updated',
  created_at: '2026-04-09 10:40:00'
}
```

---

## 🔍 **Consultar Logs Criados**

```php
php artisan tinker

# Ver últimas ações em 'central'
>>> activity('central')->latest()->limit(10)->get();

# Ver ações do usuário 1 (você)
>>> \DB::table('activity_log')->where('causer_id', 1)->latest()->get();

# Ver ações sobre Role com ID 5
>>> activity()
   ->where('subject_type', 'Spatie\Permission\Models\Role')
   ->where('subject_id', 5)
   ->get();

# Contar logs por módulo
>>> \DB::table('activity_log')->groupBy('log_name')->selectRaw('log_name, COUNT(*) as count')->get();
```

---

## ✅ **PRÓXIMOS PASSOS**

1. **Criar as Views:**
   - `resources/views/central/roles/index.blade.php`
   - `resources/views/central/roles/create.blade.php`
   - `resources/views/central/roles/edit.blade.php`
   - `resources/views/central/roles/show.blade.php`
   - Similar para permissions

2. **Testes Pest:**
   ```php
   tests/Feature/RoleTest.php
   tests/Feature/PermissionTest.php
   ```

3. **Menu de Navegação:**
   Adicionar links no menu principal:
   ```blade
   <a href="{{ route('central.roles.index') }}">Roles</a>
   <a href="{{ route('central.permissions.index') }}">Permissions</a>
   ```

---

## 📚 **Documentação Relacionada**

- [ACTIVITY_LOG_GUIDE.md](ACTIVITY_LOG_GUIDE.md) - Guia completo de activity log
- [CONTROLLERS_STRUCTURE.md](CONTROLLERS_STRUCTURE.md) - Estrutura de controllers explicada
- [SAAS_SETUP.md](SAAS_SETUP.md) - Setup geral do sistema

---

**Status:** ✅ Controllers prontos para usar
**Próximo:** Criar Views Blade + Testes
