# 🌍 Arquitetura de Subdomínios + Multi-Tenant

## ✅ Como Funciona

### Estrutura de URLs

```
Seu Site Público (Marketing)
└─ http://desenvolve-city.com.br/
   └─ Página de marketing, funcionalidades, clientes
   └─ Botão Login → /login (para clientes)
   └─ Botão Admin → /login/central (para você)

Painel Central (Super Admin - Você)
└─ http://desenvolve-city.com.br/login/central
   └─ Login exclusivo para proprietário
   └─ Gerencia TODOS os clientes
   └─ Ver: /central/*

Painel de Cliente 1 (Subdomínio)
└─ http://cliente1.desenvolve-city.com.br/
   └─ Login genérico em /login (redireciona para subdomínio)
   └─ Isolado: Vê apenas dados do cliente1
   └─ Ver: /admin/*, /responsavel/*, /app/*

Painel de Cliente 2 (Subdomínio)
└─ http://cliente2.desenvolve-city.com.br/
   └─ Mesma estrutura, mas isolado do cliente1
   └─ Banco de dados único, mas com tenant_id filtrando

Painel de Cliente N (Subdomínio)
└─ http://clienteN.desenvolve-city.com.br/
   └─ Cada cliente tem seu próprio subdomínio
```

---

## 🔧 Configuração de Subdomínios

### 1️⃣ **DNS Records (Seu Registro de Domínio)**

Você precisa criar um **Wildcard DNS** para aceitar todos os subdomínios:

```dns
Type: A Record
Name: *.desenvolve-city.com.br
Value: 123.45.67.89  (seu IP do servidor)
```

Isso faz:
- `cliente1.desenvolve-city.com.br` → Seu servidor
- `cliente2.desenvolve-city.com.br` → Seu servidor
- `admin.desenvolve-city.com.br` → Seu servidor
- Qualquer subdomínio → Seu servidor

---

### 2️⃣ **Servidor Apache / Nginx**

#### Se está usando **Laragon** (local):

```bash
# Abrir: C:\laragon\etc\apache2\sites-available\desarrolle-city.conf

# Trocar por:
<VirtualHost *:80>
    ServerName desenvolve-city.test
    ServerAlias *.desenvolve-city.test
    DocumentRoot "C:\laragon\www\desenvolve-city\public"

    <Directory "C:\laragon\www\desenvolve-city\public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>

# Restart Apache:
# Menu Laragon → Apache → Restart
```

#### Se está usando **Nginx** (produção):

```nginx
server {
    listen 80;
    server_name ~^(?<subdomain>.+)\.desenvolve-city\.com\.br$ desenvolve-city.com.br;
    
    root /var/www/desenvolve-city/public;
    index index.php index.html;

    # Passar subdomain para Laravel
    location @rewrite {
        rewrite ^/(.*)$ /index.php?path=$1 last;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php-fpm.sock;
        fastcgi_param SUBDOMAIN $subdomain;
        # ... outras configs php-fpm
    }
}
```

---

### 3️⃣ **Laravel: Detectar Subdomínio (SetTenantContext Middleware)**

```php
// app/Http/Middleware/SetTenantContext.php

public function handle(Request $request, Closure $next)
{
    // Detectar subdomínio
    $host = $request->getHost();
    
    // Se localhost ou desenvolvecity.com, não tem tenant
    if ($host === 'localhost' || $host === 'desenvolvecity.com.br' || str_contains($host, '127.0.0.1')) {
        // Sem tenant (Central ou público)
        return $next($request);
    }

    // Extrair subdomínio
    $parts = explode('.', $host);
    if (count($parts) > 2) {
        $subdomain = $parts[0];
        
        // Buscar tenant pelo slug/subdomain
        $tenant = Tenant::where('slug', $subdomain)->first();
        
        if ($tenant) {
            // Registrar no contexto global
            TenantContext::setTenant($tenant);
        } else {
            // Subdomínio não existe
            abort(404, 'Tenant não encontrado');
        }
    }

    return $next($request);
}
```

---

### 4️⃣ **Localidades para Testes**

Editar seu arquivo `hosts`:

```
# Windows: C:\Windows\System32\drivers\etc\hosts
# Linux/Mac: /etc/hosts

127.0.0.1  localhost
127.0.0.1  desenvolvecity.local
127.0.0.1  cliente1.desenvolvecity.local
127.0.0.1  cliente2.desenvolvecity.local
127.0.0.1  cliente3.desenvolvecity.local
```

Depois acessar:
- http://desenvolvecity.local/ (público/central)
- http://cliente1.desenvolvecity.local/login (cliente 1)
- http://cliente2.desenvolvecity.local/login (cliente 2)

---

## 🗄️ Estrutura de Tenants

### Tabela `tenants`

```sql
id    | name           | slug       | subdomain      | status | created_at
------|----------------|------------|----------------|--------|----------------
1     | Acme Inc       | acme       | acme           | active | 2026-04-09
2     | TechCorp Ltd   | techcorp   | techcorp       | active | 2026-04-09
3     | StartUp XYZ    | xyz        | xyz            | active | 2026-04-09
```

### TenantContext (Contexto Global)

```php
// app/Support/TenantContext.php

class TenantContext
{
    protected static $tenant = null;

    public static function setTenant(Tenant $tenant): void
    {
        static::$tenant = $tenant;
    }

    public static function getTenant(): ?Tenant
    {
        return static::$tenant;
    }

    public static function getTenantId(): ?int
    {
        return static::$tenant?->id;
    }
}
```

---

## 🔒 Como TenantScope Funciona

### Model com TenantScope

```php
// app/Models/Company.php

class Company extends Model
{
    use TenantScoped; // ← Aplica scope automaticamente!

    protected static function booted()
    {
        static::addGlobalScope(new TenantScope);
    }
}
```

### TenantScope Automático

```php
// app/Scopes/TenantScope.php

class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        $tenantId = TenantContext::getTenantId();
        
        // Se tem tenant, filtra por tenant_id
        if ($tenantId) {
            $builder->where('tenant_id', $tenantId);
        }
    }
}
```

### Resultado

```php
// Quando cliente1 faz:
$companies = Company::all();

// Automáticamente é como:
$companies = Company::where('tenant_id', 1)->get();
// → Vê apenas as 3 empresas do tenant 1

// Quando cliente2 faz:
$companies = Company::all();

// Automáticamente é como:
$companies = Company::where('tenant_id', 2)->get();
// → Vê apenas as 5 empresas do tenant 2

// Central (super-admin) desativa scope:
Company::withoutGlobalScopes()->all();
// → Vê TODAS as empresas de TODOS os tenants
```

---

## 🚀 Fluxo Completo de Cliente

### 1. Cliente acessa http://cliente1.desenvolve-city.com.br/

```
REQUEST → SetTenantContext Middleware
    ↓
Extrai "cliente1" do host
    ↓
Busca Tenant::where('slug', 'cliente1')
    ↓
TenantContext::setTenant($tenant)  // id = 1
    ↓
OK → Middleware prossegue
```

### 2. Cliente clica em "Login"

```
GET http://cliente1.desenvolve-city.com.br/login
    ↓
TenantAuthController@showLoginForm
    ↓
Retorna view('auth.tenant-login')
    ↓
Cliente entra com email + senha
```

### 3. Cliente submete login

```
POST http://cliente1.desenvolve-city.com.br/login (email + senha)
    ↓
SetTenantContext Middleware (TenantContext::$tenant ainda = id 1)
    ↓
TenantAuthController@login
    ↓
Valida email + senha
    ↓
Auth::login($user)  // user.tenant_id = 1
    ↓
redirectToMissedDashboard($user)
    ↓
Se tenant-admin → Redireciona para /admin/dashboard
```

### 4. Cliente acessa /admin/dashboard

```
GET http://cliente1.desenvolve-city.com.br/admin/dashboard
    ↓
SetTenantContext Middleware
    ↓
Detecta cliente1 → TenantContext::setTenant(1)
    ↓
Middleware tenant-access
    ↓
Verifica se autenticado ✓
    ↓
Verifica se tem tenant_id ✓
    ↓
OK → Controller response
```

### 5. Controller acessa dados

```php
// AdminDashboardController
public function index()
{
    // TenantContext::$tenant = Tenant(id: 1)
    
    $companies = Company::all();
    // Com TenantScope automático:
    // SELECT * FROM companies 
    // WHERE tenant_id = 1  ← Automático!
    
    return view('admin.dashboard', compact('companies'));
}
```

---

## 📝 Criar um Novo Tenant

### Via Tinker

```bash
php artisan tinker

> $tenant = \App\Models\Tenant::create([
    'name' => 'Acme Industries',
    'slug' => 'acme',
    'status' => 'active'
]);

> $user = \App\Models\User::create([
    'name' => 'Admin Acme',
    'email' => 'admin@acme.com',
    'password' => bcrypt('Senha123'),
    'tenant_id' => $tenant->id,
    'user_type' => 'funcionário',
]);

> $user->assignRole('tenant-admin');
```

### Depois acessar

```
http://acme.desenvolvecity.local/login
Email: admin@acme.com
Senha: Senha123

→ Vai para /admin/dashboard (isolado)
→ Vê apenas dados de tenant_id = $tenant->id
```

---

## 🔐 Segurança de Isolamento

### ✅ Garantido por:

1. **TenantScope** - Todos os queries filtram por tenant_id
2. **Auth::user()->tenant_id** - Usuário só vê seu tenant
3. **Middleware tenant-access** - Bloqueia super-admin de entrar
4. **Middleware central-access** - Bloqueia tenant users de entrar na central

### ✅ Verificações

```php
// Tentar acessar dados de outro tenant
$company = Company::where('tenant_id', 999)->first();
// Retorna null (TenantScope filtrou)

// Tentar desabilitar scope
$company = Company::withoutGlobalScopes()
    ->where('tenant_id', 999)
    ->first();
// Precisa de verificação de admin antes!

// Auth verifica tenant
if (auth()->user()->tenant_id !== $company->tenant_id) {
    abort(403, 'Acesso negado');
}
```

---

## 🧪 Testar Localmente

### 1. Adicionar ao /etc/hosts

```
127.0.0.1  desenvolvecity.local
127.0.0.1  acme.desenvolvecity.local
127.0.0.1  techcorp.desenvolvecity.local
```

### 2. Laragon: Configurar Wildcard

```
Menu Laragon → Apache → Extra Apps → Virtual Hosts
Adicionar:
<VirtualHost *:80>
    ServerName *.desenvolvecity.local
    ServerAlias desenvolvecity.local
    DocumentRoot "C:\laragon\www\desenvolve-city\public"
    <Directory "C:\laragon\www\desenvolve-city\public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### 3. Criar Tenants

```bash
php artisan tinker

> \App\Models\Tenant::create(['name' => 'ACME', 'slug' => 'acme'])
> \App\Models\Tenant::create(['name' => 'TechCorp', 'slug' => 'techcorp'])

> $tenant = \App\Models\Tenant::where('slug', 'acme')->first()
> \App\Models\User::create([
    'name' => 'Admin ACME',
    'email' => 'admin@acme.local',
    'password' => bcrypt('Senha123'),
    'tenant_id' => $tenant->id,
    'user_type' => 'funcionário'
  ])->assignRole('tenant-admin')
```

### 4. Acessar

```
Browser 1:
http://desenvolvecity.local/  → Página pública
http://desenvolvecity.local/login/central → Login central

Browser 2:
http://acme.desenvolvecity.local/login → Login acme
Email: admin@acme.local
Senha: Senha123
→ /admin/dashboard

Browser 3:
http://techcorp.desenvolvecity.local/login → Login techcorp
Email: admin@techcorp.local
Senha: Senha123
→ /admin/dashboard
```

---

## 🌍 Deploy em Produção

### AWS Route53 / Digital Ocean DNS

```
Type: A Record
Name: *.desenvolvecity.com.br
Value: 123.45.67.89  (seu IP)
TTL: 300
```

### Certificado SSL Wildcard

```bash
# Let's Encrypt para *.desenvolvecity.com.br
sudo certbot certonly -d "*.desenvolvecity.com.br" -d desenvolvecity.com.br

# Nginx
ssl_certificate /etc/letsencrypt/live/desenvolvecity.com.br/fullchain.pem;
ssl_certificate_key /etc/letsencrypt/live/desenvolvecity.com.br/privkey.pem;
```

### Resultado

```
https://desenvolvecity.com.br/           → Site público
https://desenvolvecity.com.br/login/central  → Login central
https://cliente1.desenvolvecity.com.br/   → Painel cliente 1
https://cliente2.desenvolvecity.com.br/   → Painel cliente 2
https://clienteN.desenvolvecity.com.br/   → Painel cliente N
```

---

## 📊 Performance

### Vantagens Multi-Tenant:

- ✅ **1 aplicação** - Menos manutenção
- ✅ **1 banco de dados** - Backup único
- ✅ **Escalável** - Suporta 1000s de clientes
- ✅ **Isolado** - Cada cliente vê só seus dados

### Desvantagens:

- ⚠️ **Complexidade** - TenantScope em todos os models
- ⚠️ **Performance** - Queries precisam filtrar tenant_id
- ⚠️ **Segurança** - Risco de data leak se scope falhar

---

**Status:** ✅ Arquitetura pronta  
**Próximo:** Deploy em subdomínios reais
