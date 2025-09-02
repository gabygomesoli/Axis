# Fórum
## Como rodar

1. **Crie o banco e as tabelas**:
   - Importe `sql/schema.sql` no seu MySQL (ex.: via phpMyAdmin, Adminer ou CLI).

2. **Configure conexão**:
   - Ajuste credenciais em `config/db.php` (host, database, user, password).
   - Certifique-se que o PHP tem a extensão PDO MySQL habilitada.

3. **Estrutura para servidor PHP** (ex.: Apache + PHP):
   ```
   /var/www/html/forum/
     api/
     config/
     public/
     sql/
   ```
   Aponte o navegador para `http://localhost/forum/public/`.

4. **Fluxo**:
   - Na área superior, cadastre um usuário e faça login.
   - Crie postagens, curta e comente.
   - Sessão é baseada em cookies (SameSite=Lax).

> **Observação:** Este projeto é didático. Para produção, adicione: rate-limit, validação mais robusta, CSRF token, sanitização/markdown server-side, logs, paginação, upload de imagens, etc.


## Novidades
- **Paginação & Busca**: em `GET /api/posts.php` use `?page=1&per_page=10&q=termo` e suporte a `from:@usuario`.
- **Perfis**: `GET /api/profile_get.php` e `POST /api/profile_update.php` (campos `name`, `avatar_url`, `bio`).
- **Notificações & Menções**: menções com `@usuario` em posts/comentários criam notificações (`GET/POST /api/notifications.php`).

> Dica: no front, clique numa menção para filtrar por autor (atalho para `from:@usuario`).
