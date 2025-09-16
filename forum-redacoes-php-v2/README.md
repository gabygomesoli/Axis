# Fórum de Redações (Folha de Almaço) — PHP + JS + MySQL
- Editor e exibição em formato de **folha de almaço** (linhas azuis + margem vermelha).
- Limite: **66 colunas × 30 linhas**; o backend aplica quebra dura.
- Redações com **título**, **comentários** e **curtidas**.
- Paginação e busca (`q` e `from:@usuario`).

## Como rodar
1) Importe `sql/schema.sql` no MySQL.  
2) Ajuste `config/db.php`.  
3) Abra `public/index.html` via Apache/Nginx + PHP.

## Endpoints
- `POST /api/register.php`, `POST /api/login.php`, `POST /api/logout.php`, `GET /api/me.php`
- `GET /api/essays.php?page=1&per_page=5&q=...` / `POST /api/essays.php`
- `GET /api/comments.php?essay_id=ID` / `POST /api/comments.php`
- `POST /api/like.php` {essay_id}


## Recursos adicionados
- **Exportar PDF** mantendo o papel almaço (`GET /api/export_pdf.php?essay_id=ID`) — usa FPDF (PHP puro).
- **Rubrica ENEM (5 competências)**: sugestão automática (heurística) + salvamento manual (`GET/POST /api/rubric.php`).
- **Modelos (Proposta + Coletânea)**: `GET /api/templates.php` (lista e detalhes), `POST /api/templates.php` (criar).
- **Moderação e Status**: `POST /api/status.php` (moderador/admin) — `em_correcao`, `corrigida`, `publicada`.
- **Upload imagem** de folha manuscrita atrelada à redação (`POST /api/upload_scan.php`), salva em `/public/uploads`.

> Para marcar usuários como moderadores: `UPDATE users SET role='moderator' WHERE username='seu_user';`
