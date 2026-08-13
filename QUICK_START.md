# 🚀 Quick Start - Como Testar Agora

## 1️⃣ **Parar o Servidor Anterior**
```bash
# Ctrl+C no terminal onde Laravel estava rodando
```

## 2️⃣ **Limpar Cache**
```bash
php artisan optimize:clear
php artisan config:cache
```

## 3️⃣ **Rodar Migrações (se necessário)**
```bash
php artisan migrate:fresh --seed
```

## 4️⃣ **Iniciar Laravel**
```bash
php artisan serve
```

## 5️⃣ **Acessar a Página Inicial**

### Opção A: Localhost
```
http://127.0.0.1:8000
```

### Opção B: Subdomínios (Laragon)

#### 5a. Editar Laragon hosts
```
# Menu Laragon → Tools → Host Manager
Adicionar:
127.0.0.1  desenvolvecity.local
127.0.0.1  cliente1.desenvolvecity.local
127.0.0.1  cliente2.desenvolvecity.local
```

#### 5b. Editar Virtual Host (Laragon)
```
# Menu Laragon → Apache → httpd.conf (ou sites-available/desenvolvecity.conf)

Trocar de:
<VirtualHost *:80>
    ServerName desenvolvimento-city.test
    ...
</VirtualHost>

Para:
<VirtualHost *:80>
    ServerName desenvolvecity.local
    ServerAlias *.desenvolvecity.local
    DocumentRoot "C:\laragon\www\desenvolve-city\public"
    <Directory "C:\laragon\www\desenvolve-city\public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Salvar e Restart Apache

#### 5c. Agora acessar
```
http://desenvolvecity.local/              → Página pública
http://desenvolvecity.local/login/central → Login central
http://cliente1.desenvolvecity.local/      → Painel cliente 1
http://cliente2.desenvolvecity.local/      → Painel cliente 2
```

---

## 6️⃣ **Fazer Login**

### Central (Super Admin)
```
URL: http://127.0.0.1:8000/login/central
Email: admin@desenvolve.city
Senha: Admin@123
→ Redireciona para /central/dashboard
```

### Cliente (Precisa Criar Primeiro)
```bash
# Terminal
php artisan tinker

# Tinker commands:
> $tenant = \App\Models\Tenant::create(['name' => 'ACME Inc', 'slug' => 'acme'])
> $user = \App\Models\User::create([
    'name' => 'Admin ACME',
    'email' => 'admin@acme.com',
    'password' => bcrypt('Senha123'),
    'tenant_id' => $tenant->id,
    'user_type' => 'funcionário'
  ])
> $user->assignRole('tenant-admin')
> exit
```

Depois:
```
URL: http://127.0.0.1:8000/login
Email: admin@acme.com
Senha: Senha123
→ Redireciona para /admin/dashboard
```

---

## 7️⃣ **O que Você Deve Ver**

### Página Pública (/)
```
✅ Logo "🚀 DesenvolveCity"
✅ Botão "Acessar Painel" → /login
✅ Botão "Admin" → /login/central
✅ Seção "Funcionalidades" com 6 features
✅ Seção "Como Funciona Para Clientes" com 3 steps
✅ Footer com links
```

### Login Central (/login/central)
```
✅ Tema escuro (slate-900)
✅ Alerta "Apenas proprietário"
✅ Form: Email + Senha
✅ Link para "login de clientes"
```

### Login Tenant (/login)
```
✅ Tema vivo (emerald/blue)
✅ Mostra tipos de usuário
✅ Form: Email + Senha
✅ Link para "Painel Central"
```

### Dashboard Central (/central/dashboard)
```
✅ Só super-admin pode acessar
✅ Se não autenticado → /login/central
✅ Se tenant tenta → Erro 403 "Acesso negado"
```

### Dashboard Admin (/admin/dashboard)
```
✅ Só users com tenant_id podem acessar
✅ Se super-admin tenta → Redireciona para central
✅ Se não autenticado → /login
✅ Isolado por TenantScope
```

---

## ⚠️ **Se Algo Der Erro**

### Erro: "Route [tenant.login] not defined"
```bash
# Verificar rotas:
php artisan route:list | grep login

# Deve mostra:
GET  /login                          → TenantAuthController@showLoginForm
GET  /login/central                  → CentralAuthController@showLoginForm
POST /login                          → TenantAuthController@login
POST /login/central                  → CentralAuthController@login
```

### Erro: "Class not found" ou "Middleware not registered"
```bash
composer dump-autoload
php artisan optimize:clear
php artisan config:cache
```

### Erro: 404 em /central/dashboard
```bash
# Verificar middleware:
php artisan route:list | grep central

# Deve estar com middlewares: auth,verified,central-access
# Se faltar, revisar routes/modules/central.php
```

### Página em branco ou erro 500
```bash
# Ver logs:
tail -f storage/logs/laravel.log

# Ou:
php artisan tail
```

---

## 📱 **Testar API (Opcional)**

### Login API
```bash
curl -X POST http://127.0.0.1:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@desenvolve.city",
    "password": "Admin@123"
  }'

# Response:
{
  "token": "123|abcdefg...",
  "user": {...}
}
```

### Usar Token
```bash
curl http://127.0.0.1:8000/api/v1/me \
  -H "Authorization: Bearer 123|abcdefg..."
```

---

## ✅ **Checklist de Testes**

- [ ] Página pública (/) carrega sem erros
- [ ] Login Central (/login/central) funciona
- [ ] Login Tenant (/login) funciona
- [ ] Super Admin pode acessar /central/dashboard
- [ ] Tenant User pode acessar /admin/dashboard
- [ ] Super Admin NÃO pode acessar /admin/dashboard
- [ ] Tenant User NÃO pode acessar /login/central
- [ ] Routes estão corretas (route:list)
- [ ] Middlewares funcionam
- [ ] Activity logs registram logins

---

## 🎯 **Próximos Passos**

1. ✅ Testar logins separados
2. ✅ Testar acesso a dashboards
3. ✅ Testar isolamento de dados (TenantScope)
4. ⏳ Criar views dos dashboards
5. ⏳ Implementar CRUD de roles/permissions nas views
6. ⏳ Configurar subdomínios em produção

---

**Status:** Pronto para testar!  
**Tempo estimado:** 5 minutos para todo setup local
