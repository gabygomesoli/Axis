# AXIS – Página de Simulados ENEM (PHP + MySQL)

Este mini-projeto contém a tela de **Simulados ENEM** do AXIS, com:

- Layout inspirado na timeline enviada (provas / gabaritos por ano).
- Integração com **MySQL**, puxando automaticamente os anos e arquivos cadastrados.
- Layout separado em `header.php` e `footer.php` para encaixar no restante do sistema.

## Estrutura de pastas

axis_simulados_enem/
├── includes/
│   ├── db.php          # conexão PDO com MySQL
│   ├── header.php      # header + início do layout AXIS
│   └── footer.php      # rodapé
├── public/
│   └── css/
│       └── style.css   # estilos da página
├── sql/
│   └── axis_simulados_enem.sql  # script para criar BD e dados de exemplo
└── simulados_enem.php  # página principal da timeline ENEM

## Como rodar

1. Copie a pasta `axis_simulados_enem` para o seu servidor local (XAMPP, WAMP, Laragon, etc)
   dentro da pasta pública, por exemplo:
   - `C:\xampp\htdocs\axis_simulados_enem`
   - ou `/var/www/html/axis_simulados_enem`

2. Crie o banco de dados:

   - Abra o **phpMyAdmin** ou o cliente MySQL.
   - Importe o arquivo: `sql/axis_simulados_enem.sql`

3. Ajuste a conexão com o banco:

   - Edite `includes/db.php` e altere:
     - `$host`, `$db`, `$user`, `$pass` de acordo com seu ambiente.

4. Ajuste as URLs dos PDFs:

   - No arquivo `sql/axis_simulados_enem.sql`, altere os campos `url` para apontar
     para o caminho real dos seus PDFs de prova/gabarito.
   - Ou altere diretamente na tabela `enem_arquivos` após importar.

5. Acesse no navegador:

   - `http://localhost/axis_simulados_enem/simulados_enem.php`

Se você já tem um layout AXIS maior, basta:

- Incluir `simulados_enem.php` dentro da sua estrutura de rotas.
- Ajustar os caminhos dos CSS e dos links no `header.php` ou até reutilizar
  o seu header/footer atuais e deixar este arquivo apenas com o `<section class="content-card">...</section>`.
