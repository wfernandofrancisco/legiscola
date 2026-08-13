# 📐 Padrão Repository + Service + Interface

## ✅ O que foi refatorado

**Antes:** Controllers chamavam diretamente os Models
```
Controller → Model (queries diretas) ❌
```

**Agora:** Controllers usam Services que usam Repositories
```
Controller → Service (lógica negócio) → Repository (dados) → Model ✅
```

---

## 📁 Estrutura de Pastas

```
app/
├── Contracts/
│   ├── Repositories/
│   │   ├── BaseRepositoryInterface.php
│   │   ├── RoleRepositoryInterface.php      ← Define contrato
│   │   └── PermissionRepositoryInterface.php
│   └── Services/
│       ├── RoleServiceInterface.php          ← Define contrato
│       └── PermissionServiceInterface.php
│
├── Repositories/
│   ├── BaseRepository.php                   ← Métodos comuns
│   ├── RoleRepository.php                   ← Implementação
│   └── PermissionRepository.php
│
├── Services/
│   ├── RoleService.php                      ← Implementação
│   └── PermissionService.php
│
└── Http/Controllers/Central/
    ├── RoleController.php                   ← Usa Service
    └── PermissionController.php
```

---

## 🔄 Fluxo Completo

### Exemplo: Criar um novo Role

#### 1️⃣ **Controller** (apenas orquestra)
```php
// RoleController.php
public function store(Request $request): RedirectResponse
{
    $request->validate([...]);  // ← Validação
    
    // Chama service
    $role = $this->roleService->createRole($request->validated());
    
    return redirect()->route(...)->with('success', ...);
}
```
**Responsabilidade:** Request + Response, validação básica

---

#### 2️⃣ **Service** (lógica de negócio)
```php
// RoleService.php
public function createRole(array $data): Role
{
    // Criar via repository
    $role = $this->roleRepository->create([
        'name'       => $data['name'],
        'guard_name' => 'web',
    ]);

    // Vincular permissions
    if (isset($data['permissions'])) {
        $this->roleRepository->syncPermissions($role, $data['permissions']);
    }

    // Log de atividade
    activity('central')
        ->causedBy(auth()->user())
        ->performedOn($role)
        ->log('Novo role criado');

    return $role;
}
```
**Responsabilidade:** Regras de negócio, validação de lógica, activity logs

---

#### 3️⃣ **Repository** (acesso a dados)
```php
// RoleRepository.php
public function create(array $data): Role
{
    return $this->model->create($data);
}

public function syncPermissions(Role $role, array $permissionIds): void
{
    $role->syncPermissions($permissionIds);
}
```
**Responsabilidade:** Queries, acesso ao banco de dados

---

#### 4️⃣ **Model** (representação)
```php
// Role model (no Spatie Permission)
class Role extends Model {
    // Relacionamentos
    public function permissions() {...}
}
```
**Responsabilidade:** Estrutura, relacionamentos, atributos

---

## 🎯 Benefícios

| Benefício | O que muda | Por quê |
|-----------|-----------|--------|
| **Testabilidade** | Você pode mockar o Repository no teste | Service não conhece o banco de dados |
| **Reutilização** | Mesmo Service em Console/API/Web | Lógica centralizada |
| **Manutenção** | Mudança no banco = só edita Repository | Controller não precisa saber |
| **Single Responsibility** | Cada classe tem 1 responsabilidade | Fácil de entender e manutenr |
| **Proteção de dados** | Repository valida acesso | Queries complexas em 1 lugar |

---

## 🧩 Como Usar

### ✨ Injetar a Interface no Controller

```php
class RoleController extends Controller
{
    // Tipado com interface (dependência)
    public function __construct(private RoleServiceInterface $roleService)
    {
    }

    public function index(Request $request): View
    {
        // Chama método do service
        $roles = $this->roleService->paginate(15, $request->search);
        
        return view('central.roles.index', compact('roles'));
    }
}
```

### ✨ Usar em outro lugar (Console, API, etc)

```php
// Mesmo service pode ser usado em Console
class ImportRolesCommand extends Command
{
    public function __construct(private RoleServiceInterface $roleService)
    {
    }

    public function handle()
    {
        // Mesma lógica de negócio
        $role = $this->roleService->createRole([
            'name' => 'Editor',
            'permissions' => [1, 2, 3],
        ]);
    }
}
```

---

## 📊 Distribuição de Responsabilidades

```
┌─────────────────────────────────────┐
│        CONTROLLER                    │
├─────────────────────────────────────┤
│ ✓ Request/Response                  │
│ ✓ Validação básica                  │
│ ✓ Autenticação/Autorização          │
│ ✗ Queries SQL                       │
│ ✗ Lógica complexa                   │
└─────────────────────────────────────┘
             ↓
┌─────────────────────────────────────┐
│        SERVICE                       │
├─────────────────────────────────────┤
│ ✓ Regras de negócio                 │
│ ✓ Activity logs                     │
│ ✓ Validações complexas              │
│ ✓ Orquestração de operações         │
│ ✗ Queries SQL                       │
└─────────────────────────────────────┘
             ↓
┌─────────────────────────────────────┐
│      REPOSITORY                      │
├─────────────────────────────────────┤
│ ✓ Queries (SELECT, INSERT, etc)     │
│ ✓ Acesso ao banco de dados          │
│ ✓ Relacionamentos                   │
│ ✗ Lógica de negócio                 │
└─────────────────────────────────────┘
             ↓
┌─────────────────────────────────────┐
│        MODEL                         │
├─────────────────────────────────────┤
│ ✓ Estrutura e atributos             │
│ ✓ Casts e mutators                  │
│ ✓ Relacionamentos (definição)       │
│ ✗ Queries                           │
└─────────────────────────────────────┘
```

---

## 🔍 Exemplo Prático: Deletar Role

### Controller (limpo!)
```php
public function destroy($roleId): RedirectResponse
{
    try {
        $role = $this->roleService->getRole($roleId);
        $this->roleService->deleteRole($roleId);

        return redirect()
            ->route('central.roles.index')
            ->with('success', "Role \"{$role->name}\" deletado com sucesso.");
    } catch (\Exception $e) {
        return back()->with('error', $e->getMessage());
    }
}
```

### Service (lógica)
```php
public function deleteRole(int $id): bool
{
    $role = $this->roleRepository->findOrFail($id);

    // Validação de negócio!
    if ($this->roleRepository->isSystemRole($role)) {
        throw new \Exception('Roles do sistema não podem ser deletados.');
    }

    // Log antes de deletar
    activity('central')
        ->causedBy(auth()->user())
        ->log("Role \"{$role->name}\" deletado");

    return $this->roleRepository->delete($id);
}
```

### Repository (dados)
```php
public function delete(int $id): bool
{
    return $this->findOrFail($id)->delete();
}
```

---

## 🛡️ Proteções

### System Roles (hardcoded no Repository)
```php
// RoleRepository.php
public function getSystemRoles(): array
{
    return ['super-admin', 'tenant-admin', 'tenant-manager', 'tenant-user'];
}

public function isSystemRole(Role $role): bool
{
    return in_array($role->name, $this->getSystemRoles());
}
```

### System Permissions (lista no Repository)
```php
// PermissionRepository.php
public function getSystemPermissions(): array
{
    return [
        'view-users', 'create-users', 'edit-users', 'delete-users',
        'view-companies', 'create-companies', 'edit-companies', 'delete-companies',
        // ... 10 mais
    ];
}
```

---

## 🔗 Registro no Service Provider

```php
// app/Providers/AppServiceProvider.php

public function register(): void
{
    // Interface → Implementação
    $this->app->bind(RoleRepositoryInterface::class, RoleRepository::class);
    $this->app->bind(PermissionRepositoryInterface::class, PermissionRepository::class);
    
    $this->app->bind(RoleServiceInterface::class, RoleService::class);
    $this->app->bind(PermissionServiceInterface::class, PermissionService::class);
}
```

**Por quê?** Quando o Controller pede `RoleServiceInterface`, Laravel sabe injetar `RoleService`

---

## 📝 Interfaces

### RoleRepositoryInterface.php
- `paginateWithPermissions()`
- `findWithPermissions()`
- `findByName()`
- `getSystemRoles()`
- `isSystemRole()`
- `syncPermissions()`

### RoleServiceInterface.php
- `paginate()`
- `getRole()`
- `createRole()`
- `updateRole()`
- `deleteRole()`
- `syncPermissions()`

### PermissionRepositoryInterface.php
- `paginateWithSearch()`
- `findByName()`
- `getSystemPermissions()`
- `isSystemPermission()`

### PermissionServiceInterface.php
- `paginate()`
- `getPermission()`
- `createPermission()`
- `updatePermission()`
- `deletePermission()`

---

## 🧪 Teste com Repository

```php
// tests/Feature/RoleTest.php

class RoleTest extends TestCase
{
    public function test_can_create_role_with_permissions()
    {
        // Mock do repository
        $mockRepository = Mockery::mock(RoleRepositoryInterface::class);
        $mockRepository->shouldReceive('create')->andReturn(new Role(['name' => 'Editor']));

        // Usar service com mock
        $service = new RoleService($mockRepository);
        $role = $service->createRole(['name' => 'Editor']);

        $this->assertEquals('Editor', $role->name);
    }
}
```

---

## 🚀 Próximo Passo

Agora você pode aplicar esse padrão em **todos os Controllers**:

- ✅ Central/RoleController
- ✅ Central/PermissionController
- ⏳ Central/CompanyController (já usa CompanyService!)
- ⏳ Central/UserController
- ⏳ Admin/RoleController (mesmo código, TenantScope automático)
- ⏳ Admin/CompanyController (TenantScope automático)
- ⏳ Api/V1/* (compartilha mesmos Services!)

---

## 📚 Resumo

| Camada | Classe | Responsabilidade |
|--------|--------|-----------------|
| **Interface** | `RoleServiceInterface` | Define o "contrato" |
| **Service** | `RoleService` | Lógica de negócio, activity log |
| **Interface** | `RoleRepositoryInterface` | Define queries esperadas |
| **Repository** | `RoleRepository` | Executa queries, acesso a dados |
| **Model** | `Role` (Spatie) | Estrutura e relacionamentos |
| **Controller** | `RoleController` | Request, response, validação |

**Fluxo:** Controller → Service (lógica) → Repository (dados) → Model

---

**Status:** ✅ RoleController e PermissionController refatorados  
**Próximo:** Criar views Blade para o CRUD
