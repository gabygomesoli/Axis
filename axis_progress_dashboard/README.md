# AXIS - Página "Seu Progresso" (PHP + Python + MySQL)

Este pacote reproduz a tela do print com um dashboard simples.

## Estrutura
- `public/index.php` — página HTML/CSS (Chart.js) que renderiza os gráficos.
- `backend/config.php` — conexão MySQL.
- `backend/api/metrics.php` — endpoint JSON consumido pelo front.
- `db/schema.sql` — schema do MySQL.
- `seed/seed.py` — script Python para popular dados de exemplo.

## Passo a passo (local)
1. Crie o banco e tabelas:
```bash
mysql -u root -p < db/schema.sql
```

2. (Opcional) Popule com dados de exemplo via Python:
```bash
# requer: pip install mysql-connector-python
python3 seed/seed.py
```

3. Configure seu ambiente (Apache/Nginx + PHP). Aponte o DocumentRoot para `public/`
   ou acesse pelo caminho completo (ex.: http://localhost/axis_progress_dashboard/public/).

4. Ajuste variáveis de ambiente se necessário:
- `DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME`

## Observações
- O front usa Chart.js por CDN.
- Cores e layout aproximam o print enviado.
- Código simples, pronto para ser expandido (auth, múltiplos usuários, etc.).
