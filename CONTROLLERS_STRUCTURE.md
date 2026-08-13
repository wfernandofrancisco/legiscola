# 🏗️ **Estrutura de Controllers - Guia Completo**

## 📁 Por que existem essas pastas em `app/Http/Controllers/`?

Seu projeto SaaS multi-tenant precisa de **4 painéis diferentes**, cada um com seus próprios controllers:

```
app/Http/Controllers/
├── Auth/              ← Autenticação (Login, Register, Password Reset)
├── Central/           ← VOCÊ (Super Admin) - Gerencia TUDO
├── Admin/             ← Admin do Tenant - Gerencia seu tenant
├── Responsible/       ← Responsável/Manager - Supervisiona
├── App/               ← User Normal - Usa a aplicação (EM CONSTRUÇÃO)
├── Api/               ← REST API para Mobile/Ionic (SEM USAR AGORA)
├── Public/            ← Páginas públicas (Landing page, etc)
└── Controller.php     ← Base class
```

---

## 🎯 **EXPLICAÇÃO DE CADA PASTA**

### 1️⃣ **Auth/** - Autenticação Compartilhada
```
├── controllers específicos para Login, Register, Logout
├── Password Reset
└── Email Verification
```

**Acesso:** Todos os usuários (público)  
**Exemplo:**
```php
POST /login
GET /register
POST /forgot-password
```

---

### 2️⃣ **Central/** - Painel do Super Admin (VOCÊ)
```
├── DashboardController     ← Dashboard principal
├── CompanyController       ← CRUD de empresas/clientes
├── RoleController          ← CRUD de roles ✅ CRIADO
├── PermissionController    ← CRUD de permissions ✅ CRIADO
├── UserController          ← CRUD de usuários
└── ...outros recursos
```

**Acesso:** Apenas você (Super Admin)  
**O que você vê:** Todas as empresas, todos os usuários, relatórios globais  
**Base de dados:** SEM TenantScope - vê tudo

**Rotas:**
```
/central/
├── /roles              → Gerenciar roles do sistema
├── /permissions        → Gerenciar permissions
├── /companies          → Gerenciar empresas/clientes
└── /users              → Gerenciar usuários globais
```

---

### 3️⃣ **Admin/** - Painel do Admin do Tenant
```
├── DashboardController      ← Dashboard do tenant
├── CompanyController        ← Gerenciar empresas DO TENANT
├── UserController           ← Gerenciar usuários DO TENANT
├── BudgetController         ← Gerenciar orçamentos
└── ReportController         ← Relatórios
```

**Acesso:** Admin do tenant específico  
**O que vê:** Apenas dados do SEU tenant  
**Base de dados:** COM TenantScope - filtra automaticamente por tenant

**Rotas:**
```
/:tenant/admin/
├── /dashboard          → Dashboard do tenant
├── /companies          → Empresas do seu tenant
├── /users              → Usuários do seu tenant
├── /budgets            → Orçamentos
└── /reports            → Relatórios
```

---

## 📊 **RESUMO VISUAL**

| Pasta | Acesso | O que vê | TenantScope |
|-------|--------|----------|-------------|
| `Central/` | Super Admin | TUDO | ❌ Não |
| `Admin/` | Tenant Admin | Seu tenant | ✅ Sim |
| `Responsible/` | Manager | Seu tenant | ✅ Sim |
| `App/` | User Normal | Seu tenant | ✅ Sim |
| `Api/` | Mobile | Seu tenant | ✅ Sim |
| `Public/` | Público | Páginas | ❌ N/A |
| `Auth/` | Público | Login | ❌ N/A |

---

## 💡 **IMPORTANTE: APP/ NÃO É PARA A API**

```
❌ ERRADO:
App/ = API que você consome do frontend

✅ CORRETO:
App/ = Painel web do usuário normal
Api/ = API que o Ionic/Mobile consome
```
