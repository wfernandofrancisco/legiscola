# 🔐 Sistema de Autenticação Separada por Role

## ✅ O que foi criado

### 1️⃣ **Dois Logins Completamente Separados**

```
🔐 Login Central (Super Admin)
   └─ URL: /login/central
   └─ Usuário: admin@desenvolve.city
   └─ Senha: Admin@123
   └─ Redireciona para: /central/dashboard
   └─ View: resources/views/auth/central-login.blade.php

📊 Login Tenant (Clientes)
   └─ URL: /login
   └─ Usuários: admin, responsible, users
   └─ Redireciona para painel correto:
      ├─ tenant-admin → /admin/dashboard
      ├─ tenant-manager → /responsavel/dashboard
      └─ tenant-user → /app/dashboard
   └─ View: resources/views/auth/tenant-login.blade.php
```

---

## 🚀 Como Acessar

### Central (Super Admin)
```
1. Acesse: http://localhost:8000/login/central
2. Email: admin@desenvolve.city
3. Senha: Admin@123
4. Você é redirecionado para: /central/dashboard
```

### Tenant (Cliente - Admin)
```
1. Acesse: http://localhost:8000/login
2. Email: admin@seutenantaqui.com (exemplo)
3. Senha: Senha123
4. Você é redirecionado para: /admin/dashboard
```

---

## 🔒 Middlewares de Proteção

### `central-access`
```php
// app/Http/Middleware/EnsureCentralAccess.php

✓ Verifica se usuário está autenticado
✓ Verifica se tem role super-admin
✗ Nega acesso se não for super-admin
✗ Redireciona para /login/central se não autenticado
```

Aplicado em: `/central/*`

### `tenant-access`
```php
// app/Http/Middleware/EnsureTenantAccess.php

✓ Verifica se usuário está autenticado
✓ Verifica se tem tenant_id
✗ Nega acesso se for super-admin
✗ Nega acesso se não tiver tenant
✗ Redireciona para /login se não autenticado
```

Aplicado em: `/admin/*`, `/responsavel/*`, `/app/*`

---

## 📁 Controllers de Autenticação

### `CentralAuthController` (Super Admin)
```php
// app/Http/Controllers/Auth/CentralAuthController.php

✓ showLoginForm()   → GET /login/central
✓ login()           → POST /login/central
✓ logout()          → POST /logout/central

Validações:
- Email + Senha
- Verifica se é super-admin
- Log de atividade
```

### `TenantAuthController` (Clientes)
```php
// app/Http/Controllers/Auth/TenantAuthController.php

✓ showLoginForm()       → GET /login
✓ login()               → POST /login
✓ logout()              → POST /logout
✓ redirectToMissedDashboard() → Auto-redireciona pro painel

Validações:
- Email + Senha
- Verifica se NÃO é super-admin
- Verifica se tem tenant_id
- Redireciona para painel correto:
  ├─ tenant-admin    → /admin/dashboard
  ├─ tenant-manager  → /responsavel/dashboard
  └─ tenant-user     → /app/dashboard
```

---

## 📊 Fluxo de Login

### Super Admin (Central)

```
GET /login/central
    ↓
     showLoginForm() → central-login.blade.php
    ↓
POST /login/central (email + senha)
    ↓
Validar email + senha ✓
    ↓
Validar se super-admin? ✓
    ↓
Auth::login($user)
    ↓
activity('central')->log('Super Admin autenticado')
    ↓
redirect('/central/dashboard')
```

### Tenant (Cliente)

```
GET /login
    ↓
showLoginForm() → tenant-login.blade.php
    ↓
POST /login (email + senha)
    ↓
Validar email + senha ✓
    ↓
Validar se NÃO é super-admin? ✓
    ↓
Validar se tem tenant_id? ✓
    ↓
Auth::login($user)
    ↓
activity('admin')->log('Usuário autenticado')
    ↓
redirectToMissedDashboard($user)
    ├─ Se tenant-admin  → /admin/dashboard
    ├─ Se tenant-manager → /responsavel/dashboard
    └─ Se tenant-user    → /app/dashboard
```

---

## 🔑 Rotas Registradas

### routes/modules/auth.php

```php
// Central Login
GET    /login/central         → CentralAuthController@showLoginForm
POST   /login/central         → CentralAuthController@login
POST   /logout/central        → CentralAuthController@logout

// Tenant Login
GET    /login                 → TenantAuthController@showLoginForm
POST   /login                 → TenantAuthController@login
POST   /logout                → TenantAuthController@logout

// Redirect
GET    /auth                  → /login (Tenant)
```

---

## 🎯 Por Que Separar?

| Aspecto | Benefício |
|---------|-----------|
| **Segurança** | Ataque de força-bruta em 2 URLs diferentes = mais lento |
| **UX** | Super Admin vê interface diferente do cliente |
| **Branding** | Central com tema escuro, Tenant com tema corporativo |
| **Logs** | Easy to track super-admin logins vs client logins |
| **Permissões** | Middleware específico para cada painel |
| **Proteção** | Super-admin NÃO pode acessar `tenant-access` routes |

---

## 📋 Middleware por Rota

### /central/* (Super Admin Only)

Middlewares:
- `auth` - Verificar autenticação
- `verified` - Email deve ser verificado
- `central-access` - **Apenas super-admin** (novo!)

```php
Route::middleware(['auth', 'verified', 'central-access'])
    ->group(function () {
        // Super Admin vê TUDO
        // Sem TenantScope
    });
```

### /admin/* (Tenant Admin)

Middlewares:
- `auth` - Verificar autenticação
- `verified` - Email verificado
- `tenant-access` - **Auto-redireciona se super-admin** (novo!)
- `tenant` - Define TenantScope automaticamente
- `has-tenant` - Garante que tem tenant_id

```php
Route::middleware(['auth', 'verified', 'tenant-access', 'tenant', 'has-tenant'])
    ->group(function () {
        // Vê apenas dados do seu tenant
        // Com TenantScope ativo
    });
```

### /responsavel/* (Tenant Manager)

Middlewares:
- `auth`, `verified`, `tenant-access`, `tenant`, `has-tenant`
- `role:tenant-admin|tenant-manager` - Pode ser admin ou manager

```php
Route::middleware([
    'auth', 'verified', 'tenant-access', 'tenant', 'has-tenant',
    'role:tenant-admin|tenant-manager'
])
->group(function () { ... });
```

### /app/* (Tenant User)

Middlewares:
- `auth`, `verified`, `tenant-access`, `tenant`, `has-tenant`
- Sem role específico (qualquer um do tenant pode entrar)

---

## 🚫 Proteção contra Fraude

### Super Admin tentando entrar em /admin/*

```
1. Super Admin faz login normalmente em /login
2. Tenta acessar /admin/dashboard
3. Middleware tenant-access verifica:
   - É super-admin? SIM
   - NEGA ACESSO com mensagem:
     "Super Admin deve usar o painel Central."
   - Redireciona para /central/dashboard
```

### Tenant User tentando entrar em /central/*

```
1. User normal tenta acessar /login/central
2. Tenta fazer login com email dele
3. CentralAuthController verifica:
   - É super-admin? NÃO
   - NEGA ACESSO com mensagem:
     "Credenciais inválidas ou permissão insuficiente para Central."
   - Redireciona para /login/central (form novamente)
```

### Tenant Admin tentando entrar em /app/* (Restricted Area)

- Pode entrar em `/admin/*` (seu painel)
- Pode ver `/app/*` vazio (pode acessar se quiser)
- **Não pode ver dados de outro tenant** (TenantScope filtra)

---

## 🔄 Logout

### Central
```
POST /logout/central
    → activity('central')->log('Super Admin desconectado')
    → Auth::logout()
    → Redireciona para /login/central
```

### Tenant
```
POST /logout
    → activity('admin')->log('Usuário desconectado')
    → Auth::logout()
    → Redireciona para /login
```

---

## 📱 Views de Login

### central-login.blade.php
- Tema escuro (slate-900)
- Alerta "Apenas proprietário"
- Link para "login client"
- Branding: "🔐 DesenvolveCity"

### tenant-login.blade.php
- Tema vivo (emerald/blue)
- Mostra tipos de usuário (Admin, Responsible, User)
- Link para "Painel Central"
- Branding: "📊 DesenvolveCity"

---

## ⚙️ Configuração no Service Provider

```php
// app/Providers/AppServiceProvider.php

$middleware->alias([
    'central-access'  => \App\Http\Middleware\EnsureCentralAccess::class,
    'tenant-access'   => \App\Http\Middleware\EnsureTenantAccess::class,
]);
```

---

## 🧪 Testar Localmente

### 1. Login Central
```bash
# Terminal
php artisan serve

# Browser
http://localhost:8000/login/central

Email:    admin@desenvolve.city
Senha:    Admin@123

→ Deve ir para /central/dashboard
```

### 2. Login Tenant (Criar usuário primeiro)
```bash
# Terminal - Tinker
php artisan tinker

> $tenant = \App\Models\Tenant::first();
> $user = \App\Models\User::create([
    'name' => 'Admin Tenant',
    'email' => 'admin@tenant.com',
    'password' => bcrypt('Senha123'),
    'tenant_id' => $tenant->id,
    'user_type' => 'funcionário',
  ]);
> $user->assignRole('tenant-admin');

# Browser
http://localhost:8000/login

Email: admin@tenant.com
Senha: Senha123

→ Deve ir para /admin/dashboard
```

---

## 📝 Próximos Passos

1. ✅ Separar logins por role (FEITO!)
2. ⏳ Criar views do dashboard de cada painel
3. ⏳ Criar views de role/permission CRUD
4. ⏳ Adicionar "Esqueci minha senha" em ambos logins
5. ⏳ Adicionar 2FA (autenticação dois fatores)
6. ⏳ Rate limiting para evitar força-bruta

---

## 🔍 Monitoramento

### Verificar logins no Activity Log

```bash
php artisan tinker

# Logins da Central
> activity('central')->latest()->limit(5)->get();

# Logins de Tenants
> activity('admin')->latest()->limit(5)->get();

# Logins de um usuário específico
> activity()->whereCausedBy(\App\Models\User::find(1))->latest()->get();
```

---

**Status:** ✅ Sistema de autenticação separado implementado  
**Segurança:** 🔒 Todos os acessos protegidos  
**Próximo:** Criar dashboards e views de CRUD
