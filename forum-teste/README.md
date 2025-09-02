# Fórum  

## Descrição  
Este projeto consiste em um fórum didático, desenvolvido para estudo de conceitos de desenvolvimento web.  
A aplicação permite cadastro de usuários, criação de postagens, curtidas, comentários e interações entre membros.  

## Funcionalidades  
- Cadastro e login de usuários  
- Criação de postagens  
- Curtidas e comentários em posts  
- Sessão baseada em cookies (SameSite=Lax)  
- Perfis de usuário (nome, avatar, bio)  
- Paginação e busca com filtros (ex.: `?page=1&per_page=10&q=termo` e `from:@usuario`)  
- Notificações e menções (@usuario)  

## Tecnologias Utilizadas  
- PHP (com PDO MySQL habilitado)  
- MySQL (armazenamento dos dados)  
- HTML, CSS e JavaScript (interface)  
- Apache ou outro servidor compatível com PHP  

## Como rodar  

1. **Crie o banco e as tabelas**:  
   - Importe `sql/schema.sql` no seu MySQL (ex.: via phpMyAdmin, Adminer ou CLI).  

2. **Configure conexão**:  
   - Ajuste credenciais em `config/db.php` (host, database, user, password).  
   - Certifique-se que o PHP tem a extensão PDO MySQL habilitada.  

3. **Estrutura para servidor PHP** (ex.: Apache + PHP):  
/var/www/html/forum/
api/
config/
public/
sql/

Aponte o navegador para `http://localhost/forum/public/`.  

4. **Fluxo**:  
- Na área superior, cadastre um usuário e faça login.  
- Crie postagens, curta e comente.  
- Sessão é baseada em cookies (SameSite=Lax).  

> **Observação:** Este projeto é didático.  
> Para produção, adicione: rate-limit, validação mais robusta, CSRF token, sanitização/markdown server-side, logs, paginação, upload de imagens, etc.  

## Novidades  
- **Paginação & Busca**: em `GET /api/posts.php` use `?page=1&per_page=10&q=termo` e suporte a `from:@usuario`.  
- **Perfis**: `GET /api/profile_get.php` e `POST /api/profile_update.php` (campos `name`, `avatar_url`, `bio`).  
- **Notificações & Menções**: menções com `@usuario` em posts/comentários criam notificações (`GET/POST /api/notifications.php`).  

Dica: no front, clique numa menção para filtrar por autor (atalho para `from:@usuario`).  

## Autores  
- Beatryz Ferreira de Lima
- Bruna Martins Marcelino 
- Gabriele Cristina Gomes de Oliveira 
- Iara Sophie Brasil Breyton 
- Jhennifer Soares do Nascimento 
- João Henrique de Oliveira Ribeiro 



## Licença  
Este projeto é de uso didático. O código pode ser adaptado para fins educacionais.  
