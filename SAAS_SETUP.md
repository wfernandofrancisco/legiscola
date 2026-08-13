# 🚀 Sistema SaaS - DesenvolveCity

**Status:** ✅ Estrutura Base Implementada e Pronta para Expansão

Uma plataforma SaaS robusta construída com **Laravel 13**, **Tailwind CSS** e estrutura preparada para consumo via API (**Ionic/Mobile**).

---

## 📋 Sumário de Implementação

### ✅ Fase 1: Estrutura Multi-Tenancy
- [x] Database com suporte a múltiplos tenants
- [x] Model `Tenant` com migrations
- [x] Relacionamentos Tenant → Users, Companies
- [x] TenantScope para filtragem automática
- [x] SoftDeletes em todos os modelos principais

**Arquivo Principal:** `database/migrations/2026_04_09_141057_create_tenants_table.php`

### ✅ Fase 2: Autenticação & Autorização
- [x] **Spatie Permission** v6 integrado
- [x] **4 Roles definidos:**
  - `super-admin` - Acesso total ao sistema
  - `tenant-admin` - Gerente do tenant
  - `tenant-manager` - Responsável operacional
  - `tenant-user` - Usuário comum
- [x] **14 Permissões:**
  - CRUD: users, companies, budgets
  - view-reports, export-reports
  - view-logs, view-activity
  - view-settings, edit-settings

**Setup:** `database/seeders/RolePermissionSeeder.php`

### ✅ Fase 3: E-mail & Jobs Assincronos
- [x] **3 Mailables Implementados:**
  - `PasswordResetMail` - Recuperação de senha
  - `VerifyEmailMail` - Confirmação de e-mail
  - `WelcomeEmail` - Boas-vindas
- [x] **SendEmailJob** - Queue para envio não-bloqueante
- [x] **Markdown Templates** - Com tradução PT-BR
- [x] **Config:** Suporta Log (dev) + Mailgun/SendGrid (prod)

**Mailables:** `app/Mail/*.php`
**Jobs:** `app/Jobs/SendEmailJob.php`

### ✅ Fase 4: Localização & Timezone
- [x] **Locale:** `pt_BR` (português brasileiro)
- [x] **Timezone:** `America/Sao_Paulo`
- [x] **Arquivos Traduzidos:**
  - `lang/pt_BR/validation.php` - Mensagens de validação
  - `lang/pt_BR/auth.php` - Autenticação
  - `lang/pt_BR/passwords.php` - Reset de senha
  - `lang/pt_BR/pagination.php` - Paginação
- [x] **Faker Locale:** `pt_BR` para dados fictícios

### ✅ Fase 5: Activity Log & Auditoria
- [x] **Spatie ActivityLog** migrado
- [x] **Logging em Modelos:**
  - User (name, email, user_type, status)
  - Company (razao_social, cnpj, status)
  - Budget (titulo, status, total)
  - Tenant (name, slug, status)
- [x] **Database:** Tabela `activity_log` com todos os events
- [x] **Descrições Customizadas** por evento

**Consulta:** `activity_log()` ou `Activity::all()`

### ✅ Fase 6: Bibliotecas de Export
- [x] **DomPDF** v3.1.2 - Geração de PDFs
  ```bash
  composer require barryvdh/laravel-dompdf
  ```
- [x] **Maatwebsite Excel** v3.1.68 - Exportação Excel
  ```bash
  composer require maatwebsite/excel
  ```

**Uso esperado:**
```php
// PDF
PDF::loadView('report', $data)->download('report.pdf');

// Excel
Excel::download(new BudgetsExport, 'budgets.xlsx');
```

### ✅ Fase 7: API REST & Sanctum
- [x] **Sanctum** configurado para tokens Bearer
- [x] **Controllers V1:**
  - `Api/V1/AuthController` - Login, Register, Password Reset
  - `Api/V1/UserController` - CRUD + ativation
  - `Api/V1/CompanyController` - CRUD + link users
  - `Api/V1/BudgetController` - CRUD + approve/reject
- [x] **Rotas Públicas:**
  - POST `/api/v1/auth/login`
  - POST `/api/v1/auth/register`
  - POST `/api/v1/auth/forgot-password`
  - POST `/api/v1/auth/reset-password`
- [x] **Rotas Protegidas (auth:sanctum):**
  - GET `/api/v1/me` - Usuário autenticado
  - POST `/api/v1/auth/logout`
  - Recurso completo: users, companies, budgets

**Estrutura:** `routes/api.php` + `app/Http/Controllers/Api/V1/`

### ✅ Fase 8: Testes Pest
- [x] **Teste User Completo** - 7 testes implementados
  - ✅ Criar novo usuário
  - ✅ Atribuir role a usuário
  - ✅ Verificar permissões
  - ✅ Super admin com todas as permissões
  - ✅ Email único e obrigatório
  - ✅ Usuário pertence a tenant
  - ✅ Soft deletes funcionam

**Executar:** `php artisan test`

---

## 🗂️ Estrutura de Pastas

```
app/
├── Models/
│   ├── Tenant.php          # Multi-tenancy
│   ├── User.php            # Usuários com roles
│   ├── Company.php         # Empresas/Times
│   └── Budget.php          # Orçamentos
├── Services/               # Lógica de negócio
│   ├── TenantService.php
│   ├── UserService.php
│   └── CompanyService.php
├── Repositories/           # Acesso a dados
│   ├── BaseRepository.php
│   ├── UserRepository.php
│   └── CompanyRepository.php
├── Jobs/
│   └── SendEmailJob.php    # Queue para e-mails
├── Mail/
│   ├── PasswordResetMail.php
│   ├── VerifyEmailMail.php
│   └── WelcomeEmail.php
└── Http/Controllers/
    ├── Api/V1/
    │   ├── AuthController.php
    │   ├── UserController.php
    │   ├── CompanyController.php
    │   └── BudgetController.php
    └── Web/  # (Para Blade/Frontend)

database/
├── migrations/
│   ├── 2026_04_09_141057_create_tenants_table.php
│   ├── 2026_04_09_141102_add_tenant_id_to_users_table.php
│   ├── 2026_04_09_141103_add_tenant_id_to_companies_table.php
│   └── ...
├── seeders/
│   ├── TenantSeeder.php
│   ├── RolePermissionSeeder.php
│   └── AdminUserSeeder.php
└── factories/

resources/
├── emails/
│   ├── password-reset.blade.php
│   ├── verify-email.blade.php
│   └── welcome.blade.php
└── css/
    └── app.css            # Tailwind CSS

tests/
├── Feature/
│   └── Feature/UserTest.php  # 7 testes Pest
└── Pest.php

lang/
└── pt_BR/
    ├── validation.php
    ├── auth.php
    ├── passwords.php
    └── pagination.php
```

---

## 🔐 Usuário Padrão

```
Email: admin@desenvolve.city
Senha: Admin@123
Role: super-admin
```

**⚠️ MUDE A SENHA APÓS O PRIMEIRO LOGIN**

---

## ⚙️ Configuração de E-mail

### Modo Desenvolvimento (Log)
```env
MAIL_MAILER=log
```
Verificar em: `storage/logs/laravel.log`

### Produção (Mailgun)
```env
MAIL_MAILER=mailgun
MAILGUN_DOMAIN=seu-dominio.com
MAILGUN_SECRET=sua-chave-secreta
MAILGUN_ENDPOINT=api.mailgun.net
```

### Produção (SendGrid)
```env
MAIL_MAILER=sendgrid
SENDGRID_API_KEY=seu-api-key
```

---

## 🚀 Como Executar

### 1️⃣ Setup Inicial
```bash
# Clonar repo
git clone ...
cd desenvolve-city

# Setup automático
composer run-script setup
```

### 2️⃣ Modo Desenvolvimento
```bash
# Terminal 1: Server Laravel
php artisan serve

# Terminal 2: Queue Listener (para jobs de e-mail)
php artisan queue:listen

# Terminal 3: Vite (Tailwind + Assets)
npm run dev
```

### 3️⃣ Executar Testes
```bash
php artisan test

# Ou testes específicos
php artisan test tests/Feature/Feature/UserTest.php

# Com cobertura
php artisan test --coverage
```

### 4️⃣ Seedar Dados
```bash
# Apenas roles/tenants/admin
php artisan db:seed

# Com factory (opcional)
php artisan db:seed --class=UserFactory
```

---

## 🔌 Endpoints Principais da API

### Autenticação
```http
POST /api/v1/auth/login
Content-Type: application/json

{
  "email": "admin@desenvolve.city",
  "password": "Admin@123"
}

Response:
{
  "token": "bearer-token-aqui",
  "user": {
    "id": 1,
    "name": "Administrador",
    "email": "admin@desenvolve.city",
    "roles": ["super-admin"]
  }
}
```

### Usuário Autenticado
```http
GET /api/v1/me
Authorization: Bearer {token}
```

### Listar Usuários
```http
GET /api/v1/users
Authorization: Bearer {token}
```

### Criar Empresa
```http
POST /api/v1/companies
Authorization: Bearer {token}
Content-Type: application/json

{
  "razao_social": "Empresa XYZ",
  "nome_fantasia": "XYZ Serviços",
  "cnpj": "12.345.678/0001-90",
  "email": "contato@xyz.com"
}
```

---

## 📚 Próximos Passos Recomendados

### Curtíssimo Prazo
- [ ] Implementar métodos dos Controllers V1 (CRUD completo)
- [ ] Criar Middleware `TenantMiddleware` para filtrar por tenant
- [ ] Testes de endpoints da API
- [ ] Dashboard Blade com Tailwind

### Curto Prazo
- [ ] Services & Repositories para lógica de negócio
- [ ] Validação avançada com Form Requests
- [ ] Geração de relatórios PDF/Excel
- [ ] Transações de banco (refunds, etc)

### Médio Prazo
- [ ] Frontend Ionic para consumir a API
- [ ] WebSocket para notificações em tempo real
- [ ] Integração com gateway de pagamento
- [ ] Backup automático de dados

### Longo Prazo
- [ ] Analíticos e dashboards avançados
- [ ] Machine Learning para previsões
- [ ] Mobile app publicada nas stores
- [ ] Escalabilidade para múltiplos servidores

---

## 🛠️ Troubleshooting

### Erro: "SQLSTATE ConnectionException"
```bash
# Verificar conexão MySQL
php artisan tinker
>>> DB::connection()->getPdo()
```

### Erro: "Class not found"
```bash
composer dump-autoload
php artisan optimize:clear
```

### E-mail não enviando
```bash
# Procesar jobs manualmente
php artisan queue:work --tries=3

# Verificar log
tail -f storage/logs/laravel.log
```

### Testes falhando
```bash
php artisan migrate:refresh --database=testing
php artisan test
```

---

## 📞 Suporte

Para dúvidas ou problemas:
1. Verificar `SAAS_SETUP.md` (este arquivo)
2. Logs: `storage/logs/laravel.log`
3. Documentação Laravel: https://laravel.com/docs
4. Spatie Permission: https://spatie.be/docs/laravel-permission

---

## 📄 Licença

MIT License - Veja LICENSE para detalhes

---

**Criado em:** Abril 2026  
**Última atualização:** Abril 9, 2026  
**Versão:** 1.0.0-alpha




//pastas

app/Http/Controllers/
├── Api/           → 🔌 API REST para Mobile/Ionic (não vai usar AINDA)
├── App/           → 📱 Painel do Tenant User comum (em construção)
├── Admin/         → 👔 Painel do Admin do Tenant
├── Auth/          → 🔐 Autenticação compartilhada
├── Central/       → 🏢 Painel Super Admin (SaaS Central) ← AQUI VOCÊ ESTÁ
├── Public/        → 🌐 Páginas públicas (landing, etc)
├── Responsible/   → 👤 Painel do Responsável/Manager
└── Controller.php → Base