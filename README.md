📦 ProdutoHub
Sistema web de gerenciamento de produtos desenvolvido com PHP e PostgreSQL, rodando sobre XAMPP.

🗂 Estrutura de Arquivos
htdocs/arthur/
├── index.php          # Tela de login
├── produtos.php       # Lista/grid de produtos (CRUD)
├── form_produto.php   # Formulário de cadastro e edição
├── logout.php         # Encerra a sessão
└── setup.sql          # Script SQL para criar o banco

⚙️ Requisitos

XAMPP (Apache + PHP)
PostgreSQL instalado
Extensões PHP ativas no php.ini: extension=pgsql e extension=pdo_pgsql
libpq.dll copiado para C:\xampp\php\


🚀 Como Configurar

Abra o PgAdmin e crie um banco chamado produtos
Com o banco selecionado, abra o Query Tool e execute o setup.sql
Copie os arquivos .php para C:\xampp\htdocs\arthur\
Inicie o Apache no painel do XAMPP
Acesse http://localhost/arthur/index.php


🔐 Acesso Padrão
CampoValorUsuárioadminSenha123456

📋 Funcionalidades

Login com sessão PHP ($_SESSION)
Logout com destruição de sessão (session_destroy)
Listagem de produtos em tabela
Busca de produtos por nome via $_GET
Cadastro e edição de produtos via $_POST
Exclusão de produtos
Toggle de status (ativo / inativo)
Cards com estatísticas (total, ativos, soma de preços)
Preview de foto via URL


🗄 Banco de Dados
Tabela usuario
ColunaTipoDescriçãoidusuariointeger (PK)ID auto incrementousernamevarchar(50)Nome de usuáriopasswordvarchar(100)SenhastatusbooleanAtivo/Inativo
Tabela produto
ColunaTipoDescriçãoidprodutointeger (PK)ID auto incrementoprodutonomevarchar(100)Nome do produtoprodutoprecorealPreçoprodutofotovarchar(150)URL da imagemprodutostatusbooleanAtivo/Inativo

🛠 Tecnologias

PHP 8.x + PostgreSQL + XAMPP
HTML5 + CSS3 (sem frameworks)
Google Fonts: Syne + DM Sans


⚠️ Solução de Problemas
pg_connect() undefined → Ative extension=pgsql no php.ini e reinicie o Apache.
database "produtos" does not exist → Crie o banco produtos no PgAdmin.
relation "public.usuario" does not exist → Execute o setup.sql no Query Tool do PgAdmin.
Erro ao conectar mesmo com extensão ativa → Copie o libpq.dll de C:\Program Files\PostgreSQL\<versão>\bin\ para C:\xampp\php\.
