# Legiscola — o que fizemos nesta noite (10/09/2026)

Resumo do trabalho feito na sessão da noite: ajustes do fluxo diretor → admin → aluno, PWA no celular e avisos/promoções regionais.

---

## 1. Fluxo do catálogo regional (ajustes pedidos)

Modelo de negócio alinhado:

- **Diretor** vende/libera curso ou evento (palestra) para a câmara  
- **Admin** compra/usa a licença e só define as **datas**  
- **Diretor** cadastra curso + aulas no catálogo  
- Ao abrir a turma, as **aulas já vêm puxadas**  
- Em eventos/palestras, arquivos ficam visíveis para **aluno inscrito** e **admin**

### O que foi implementado

| Item | Decisão / resultado |
|------|---------------------|
| Professor no catálogo | Só o **nome** (`professor_nome`) — sem vincular docente cadastrado |
| PDF da nota fiscal | Upload/download na licença (disco **privado**) |
| Turma sem aulas | **Bloqueada** — não abre curso do catálogo sem aulas |
| Vídeo/material regional | Aviso claro no **admin** (turma + formulário de aula) |
| Dashboard admin | Aviso: “você tem conteúdos liberados até data X” |
| Presença presencial | Aulas do catálogo respeitam turma presencial (não forçam online) |
| Checklist pós-abertura | Painel na turma com conteúdo regional + prazo de certificado |
| Aviso à câmara | E-mail aos admins quando o diretor libera licença ativa |
| Material da palestra | Aluno inscrito e admin veem vídeo/material até o prazo |
| Migration de correção | Aulas antigas de catálogo em turma presencial → `is_online = false` |

### Arquivos principais

- `app/Services/LicenseActivationService.php`
- `app/Services/CatalogLicenseService.php`
- `app/Http/Controllers/Director/LicencaController.php`
- `app/Mail/CatalogLicenseReleasedMail.php`
- `app/Http/Controllers/Admin/DashboardController.php`
- `app/Models/Event.php` (`catalogContent()`)
- Views admin/aluno de turma, aula, evento e dashboard
- Migration: `2026_09_10_100000_fix_is_online_das_aulas_de_catalogo_em_turmas_presenciais.php`

---

## 2. PWA (app no celular sem reescrever o sistema)

Caminho escolhido: **PWA** (não Flutter/React Native). Capacitor/lojas ficam para depois, se precisar.

### Áreas com PWA

| Área | Onde instala | Abre em |
|------|----------------|---------|
| Aluno | `/aluno` | `/aluno` |
| Professor | `/docente` | `/docente` |
| Diretor | `/diretor` | `/diretor` |
| Portal da câmara | `/` | `/` |

### O que entrou

- Manifest dinâmico: `/manifest.webmanifest?area=aluno|professor|diretor|portal`
- Service worker: `/sw.js`
- Página offline: `/offline.html`
- Ícones PWA a partir de `public/img/logo-curto.png` (192, 512, maskable)
- Meta tags Apple/Android + banner “Instalar”
- Menu mobile do aluno com itens principais + “Mais”
- Safe-area para notch/barra do celular

### Push notification?

**Ainda não.** PWA sozinha não envia push. Dá para fazer depois (Web Push):

- Android: funciona bem  
- iPhone: só com app instalado na tela inicial (iOS 16.4+), com limites  

### Como testar instalação

1. Produção com **HTTPS** (instalação de verdade quase não funciona em HTTP local)  
2. Entrar na área desejada no celular  
3. Android: Instalar / “Adicionar à tela inicial”  
4. iPhone (Safari): Compartilhar → Adicionar à Tela de Início  
5. Rebuild front se necessário: `npm run build` ou `npm run dev`

### Arquivos principais

- `app/Support/PwaArea.php`
- `app/Http/Controllers/PwaManifestController.php`
- `resources/js/aluno-pwa.js` (registro SW + banner)
- `resources/views/components/pwa/meta.blade.php`
- `resources/views/components/pwa/install.blade.php`
- `public/sw.js`, `public/offline.html`, `public/img/pwa-*.png`
- Layouts: aluno, diretor, professor, portal
- Testes: `tests/Feature/AlunoPwaTest.php`

---

## 3. Avisos / promoções do diretor → admin

Pedido: banner tipo “curso X com desconto”, só para câmaras ativas da UF; diretor escolhe geral ou específico; no dashboard do admin; fechar sem voltar todo dia.

### Regras

- **Quem vê:** só tenants **ativos** das UFs do diretor  
- **Alcance:** geral (toda a região) **ou** câmaras específicas  
- **Dashboard admin:** banner ao entrar  
- **Clique:** abre ficha do curso com aulas / vídeo / material  
- **Fechar ou abrir:** some do dashboard  
- **Não reaparece todo dia**  
- **Só volta** se o diretor **editar** o aviso  
- Some também se desativar ou passar da validade  

### Onde usar

- Diretor: menu **Avisos** → `/diretor/promos`  
- Admin: dashboard → “Ver curso e aulas” / “Fechar”  
- Detalhe: `/admin/avisos-regionais/{promo}`

### Arquivos principais

- Migration: `2026_09_10_210000_create_catalog_promos_tables.php`
- Models: `CatalogPromo`, `CatalogPromoDismissal`
- `app/Services/CatalogPromoService.php`
- Controllers: `Director\PromoController`, `Admin\PromoController`
- Views: `resources/views/director/promos/*`, `resources/views/admin/promos/show.blade.php`
- Testes: `tests/Feature/CatalogPromoTest.php` (6 passando)

### Detalhe técnico importante

O `User` do diretor tem `tenant_id` null. Com `TenantScope` ativo no painel do admin, a relação do diretor sumia da query. Foi corrigido com `withoutGlobalScopes([TenantScope::class])` nas consultas de promo.

---

## 4. Testes

Rodados com sucesso nesta noite (entre outros):

- Suíte Catalog / Director (catálogo, licenças, agenda ICS, etc.)
- `AlunoPwaTest` — manifests das 4 áreas + SW + offline
- `CatalogPromoTest` — geral, específico, dismiss, reedição, aulas, tenant inativo

Falhas antigas do fork (`Cnae`, `Empresa`, etc.) **não** são desta sessão.

---

## 5. O que ficou de fora / próximo (se quiser)

- **Capacitor** + publicação App Store / Play Store  
- **Push notification** na PWA  
- Sync de aulas novas do catálogo em turmas já abertas  
- Storage em nuvem (R2/S3) para vídeos/materiais quando o volume crescer  
- PR9 Central consolidada (catálogo/financeiro) — Central foi considerada ok  

---

## 6. Como validar rápido amanhã

1. `php artisan migrate` (se ainda não rodou as migrations de hoje)  
2. Diretor: criar aviso em **Avisos** e ver no dashboard do admin da UF  
3. Admin: fechar aviso → não deve voltar no dia seguinte; editar no diretor → volta  
4. Celular (HTTPS): instalar PWA do aluno / portal  
5. Abrir turma presencial do catálogo → aluno **não** deve autoconfirmar presença online  

---

*Arquivo gerado ao final da sessão da noite de 10/09/2026.*
