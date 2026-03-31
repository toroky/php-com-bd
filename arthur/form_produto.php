<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit;
}

$conn = pg_connect("host=localhost dbname=produtos user=postgres password=123456");
if (!$conn) die("Erro ao conectar com o banco de dados.");

$erro     = "";
$mensagem = "";
$produto  = ['idproduto'=>'', 'produtonome'=>'', 'produtopreco'=>'', 'produtofoto'=>'', 'produtostatus'=>false];
$editando = false;

// Carrega produto para edição
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $res = pg_query($conn, "SELECT * FROM public.produto WHERE idproduto = $id");
    if ($res && pg_num_rows($res) > 0) {
        $produto  = pg_fetch_assoc($res);
        $editando = true;
    }
}

// SALVAR
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome   = pg_escape_string($conn, trim($_POST['produtonome'] ?? ''));
    $preco  = floatval(str_replace(',', '.', $_POST['produtopreco'] ?? '0'));
    $foto   = pg_escape_string($conn, trim($_POST['produtofoto'] ?? ''));
    $status = isset($_POST['produtostatus']) ? 'true' : 'false';
    $id_edit = (int)($_POST['id_edit'] ?? 0);

    if ($nome === '') {
        $erro = "O nome do produto é obrigatório.";
    } else {
        if ($id_edit > 0) {
            // UPDATE
            $res = pg_query($conn, "UPDATE public.produto SET produtonome='$nome', produtopreco=$preco, produtofoto='$foto', produtostatus=$status WHERE idproduto=$id_edit");
            if ($res) { $mensagem = "Produto atualizado com sucesso!"; }
            else       { $erro = "Erro ao atualizar produto."; }
        } else {
            // INSERT
            $res = pg_query($conn, "INSERT INTO public.produto (produtonome, produtopreco, produtofoto, produtostatus) VALUES ('$nome', $preco, '$foto', $status)");
            if ($res) { $mensagem = "Produto cadastrado com sucesso!"; }
            else       { $erro = "Erro ao cadastrar produto."; }
        }

        if (!$erro) {
            pg_close($conn);
            header("Location: produtos.php");
            exit;
        }
    }
}

pg_close($conn);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $editando ? 'Editar' : 'Novo' ?> Produto — ProdutoHub</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg: #0a0a0f;
            --surface: #13131a;
            --surface2: #1a1a24;
            --border: #1e1e2e;
            --accent: #7c3aed;
            --accent2: #a78bfa;
            --text: #e2e8f0;
            --muted: #64748b;
            --danger: #ef4444;
            --success: #22c55e;
        }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'DM Sans', sans-serif;
            min-height: 100vh;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0; left: 0; right: 0; height: 300px;
            background: radial-gradient(ellipse 70% 100% at 50% -20%, rgba(124,58,237,0.15) 0%, transparent 70%);
            pointer-events: none;
            z-index: 0;
        }

        /* SIDEBAR */
        .sidebar {
            position: fixed;
            left: 0; top: 0; bottom: 0;
            width: 220px;
            background: var(--surface);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            z-index: 100;
        }

        .sidebar-logo {
            padding: 28px 24px 20px;
            border-bottom: 1px solid var(--border);
        }

        .sidebar-logo h2 {
            font-family: 'Syne', sans-serif;
            font-weight: 800;
            font-size: 18px;
            background: linear-gradient(135deg, #fff 40%, var(--accent2));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .sidebar-logo p { font-size: 11px; color: var(--muted); margin-top: 2px; }

        .nav { padding: 16px 12px; flex: 1; }

        .nav-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--muted);
            padding: 0 12px;
            margin-bottom: 8px;
            margin-top: 16px;
        }

        .nav a {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 12px; border-radius: 8px;
            color: var(--muted); text-decoration: none;
            font-size: 14px; font-weight: 500;
            transition: all 0.15s; margin-bottom: 2px;
        }

        .nav a:hover, .nav a.active { background: rgba(124,58,237,0.15); color: var(--accent2); }
        .nav a.active { background: rgba(124,58,237,0.2); }

        .sidebar-bottom {
            padding: 16px 12px;
            border-top: 1px solid var(--border);
        }

        .user-chip {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 12px; border-radius: 8px;
            background: var(--surface2);
        }

        .avatar {
            width: 32px; height: 32px;
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 14px; font-weight: 700; color: #fff;
            font-family: 'Syne', sans-serif; flex-shrink: 0;
        }

        .user-info { flex: 1; }
        .user-info strong { display: block; font-size: 13px; font-weight: 600; }
        .user-info span { font-size: 11px; color: var(--muted); }

        .btn-logout {
            display: block; text-align: center;
            margin-top: 10px; padding: 9px;
            background: rgba(239,68,68,0.1);
            border: 1px solid rgba(239,68,68,0.2);
            border-radius: 8px; color: #fca5a5;
            text-decoration: none; font-size: 13px; font-weight: 500;
            transition: all 0.15s;
        }

        .btn-logout:hover { background: rgba(239,68,68,0.2); color: #fff; }

        /* MAIN */
        .main {
            margin-left: 220px;
            padding: 32px 36px;
            position: relative; z-index: 1;
        }

        .topbar {
            display: flex; align-items: center;
            justify-content: space-between;
            margin-bottom: 32px;
        }

        .page-title h1 {
            font-family: 'Syne', sans-serif;
            font-size: 26px; font-weight: 800; letter-spacing: -0.5px;
        }

        .page-title p { color: var(--muted); font-size: 14px; margin-top: 2px; }

        .btn-voltar {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 10px 18px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 10px; color: var(--text);
            text-decoration: none; font-size: 14px;
            transition: all 0.15s;
        }

        .btn-voltar:hover { border-color: var(--accent); color: var(--accent2); }

        /* FORM CARD */
        .form-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 36px;
            max-width: 640px;
            animation: fadeUp 0.5s ease both;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .form-section {
            font-family: 'Syne', sans-serif;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--accent2);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border);
        }

        .field { margin-bottom: 22px; }

        label {
            display: block;
            font-size: 12px; font-weight: 500;
            color: var(--muted);
            text-transform: uppercase; letter-spacing: 0.08em;
            margin-bottom: 8px;
        }

        input[type="text"],
        input[type="number"],
        input[type="url"] {
            width: 100%;
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 10px;
            color: var(--text);
            font-family: 'DM Sans', sans-serif;
            font-size: 15px;
            padding: 13px 16px;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(124,58,237,0.15);
        }

        .field-hint { font-size: 12px; color: var(--muted); margin-top: 6px; }

        /* TOGGLE SWITCH */
        .toggle-wrap {
            display: flex; align-items: center; gap: 14px;
        }

        .toggle-wrap label { margin-bottom: 0; text-transform: none; font-size: 14px; color: var(--text); }

        .toggle {
            position: relative;
            width: 48px; height: 26px;
        }

        .toggle input { opacity: 0; width: 0; height: 0; }

        .slider {
            position: absolute; inset: 0;
            background: var(--border);
            border-radius: 26px;
            cursor: pointer;
            transition: 0.25s;
        }

        .slider::before {
            content: '';
            position: absolute;
            width: 18px; height: 18px;
            left: 4px; bottom: 4px;
            background: var(--muted);
            border-radius: 50%;
            transition: 0.25s;
        }

        .toggle input:checked + .slider { background: rgba(124,58,237,0.4); }
        .toggle input:checked + .slider::before {
            transform: translateX(22px);
            background: var(--accent2);
        }

        /* PREVIEW */
        .foto-preview {
            width: 80px; height: 80px;
            border-radius: 12px;
            border: 2px dashed var(--border);
            display: flex; align-items: center; justify-content: center;
            font-size: 32px;
            overflow: hidden;
            transition: border-color 0.2s;
            margin-top: 12px;
        }

        .foto-preview img { width: 100%; height: 100%; object-fit: cover; }

        /* ACTIONS */
        .form-actions {
            display: flex; gap: 12px; align-items: center;
            padding-top: 8px;
        }

        .btn-salvar {
            padding: 13px 28px;
            background: linear-gradient(135deg, var(--accent), #9333ea);
            border: none; border-radius: 10px;
            color: #fff;
            font-family: 'Syne', sans-serif;
            font-size: 14px; font-weight: 700;
            cursor: pointer;
            box-shadow: 0 4px 20px rgba(124,58,237,0.3);
            transition: transform 0.15s, box-shadow 0.15s;
        }

        .btn-salvar:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 28px rgba(124,58,237,0.45);
        }

        .btn-cancelar {
            padding: 13px 20px;
            background: var(--surface2); border: 1px solid var(--border);
            border-radius: 10px; color: var(--muted);
            text-decoration: none; font-size: 14px; font-weight: 500;
            transition: all 0.15s;
        }

        .btn-cancelar:hover { border-color: var(--muted); color: var(--text); }

        /* TOAST */
        .toast {
            padding: 12px 18px; border-radius: 10px;
            font-size: 14px; margin-bottom: 20px;
            display: flex; align-items: center; gap: 10px;
            max-width: 640px;
        }

        .toast.error { background: rgba(239,68,68,0.12); border: 1px solid rgba(239,68,68,0.25); color: #fca5a5; }
    </style>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
    <div class="sidebar-logo">
        <h2>📦 ProdutoHub</h2>
        <p>Sistema de produtos</p>
    </div>
    <nav class="nav">
        <div class="nav-label">Menu</div>
        <a href="produtos.php">🗂 Produtos</a>
        <a href="form_produto.php" class="active">➕ Novo Produto</a>
    </nav>
    <div class="sidebar-bottom">
        <div class="user-chip">
            <div class="avatar"><?= strtoupper(substr($_SESSION['usuario'], 0, 1)) ?></div>
            <div class="user-info">
                <strong><?= htmlspecialchars($_SESSION['usuario']) ?></strong>
                <span>Administrador</span>
            </div>
        </div>
        <a href="logout.php" class="btn-logout">🚪 Sair do sistema</a>
    </div>
</aside>

<!-- MAIN -->
<main class="main">
    <div class="topbar">
        <div class="page-title">
            <h1><?= $editando ? 'Editar Produto' : 'Novo Produto' ?></h1>
            <p><?= $editando ? 'Atualize os dados do produto #' . $produto['idproduto'] : 'Preencha os dados para cadastrar' ?></p>
        </div>
        <a href="produtos.php" class="btn-voltar">← Voltar à lista</a>
    </div>

    <?php if ($erro): ?>
    <div class="toast error">❌ <?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>

    <div class="form-card">
        <div class="form-section">📦 Informações do Produto</div>

        <form method="POST" action="">
            <input type="hidden" name="id_edit" value="<?= $produto['idproduto'] ?>">

            <div class="field">
                <label>Nome do Produto *</label>
                <input type="text" name="produtonome"
                       value="<?= htmlspecialchars($produto['produtonome']) ?>"
                       placeholder="Ex: Notebook Dell Inspiron 15"
                       required>
            </div>

            <div class="field">
                <label>Preço (R$)</label>
                <input type="number" name="produtopreco" step="0.01" min="0"
                       value="<?= $produto['produtopreco'] ?>"
                       placeholder="0.00">
            </div>

            <div class="field">
                <label>URL da Foto</label>
                <input type="url" name="produtofoto" id="fotoInput"
                       value="<?= htmlspecialchars($produto['produtofoto']) ?>"
                       placeholder="https://exemplo.com/imagem.jpg"
                       oninput="previewFoto(this.value)">
                <p class="field-hint">Insira uma URL de imagem para exibir a foto do produto</p>
                <div class="foto-preview" id="fotoPreview">
                    <?php if ($produto['produtofoto']): ?>
                        <img src="<?= htmlspecialchars($produto['produtofoto']) ?>" alt="preview" id="previewImg" onerror="this.parentElement.innerHTML='📦'">
                    <?php else: ?>
                        📦
                    <?php endif; ?>
                </div>
            </div>

            <div class="field">
                <label>Status</label>
                <div class="toggle-wrap">
                    <label class="toggle">
                        <input type="checkbox" name="produtostatus"
                               <?= ($produto['produtostatus'] === 't' || $produto['produtostatus'] === true) ? 'checked' : '' ?>>
                        <span class="slider"></span>
                    </label>
                    <label>Produto ativo</label>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-salvar">
                    <?= $editando ? '💾 Salvar alterações' : '➕ Cadastrar produto' ?>
                </button>
                <a href="produtos.php" class="btn-cancelar">Cancelar</a>
            </div>
        </form>
    </div>
</main>

<script>
function previewFoto(url) {
    const div = document.getElementById('fotoPreview');
    if (!url) { div.innerHTML = '📦'; return; }
    div.innerHTML = '<img src="' + url + '" alt="preview" onerror="this.parentElement.innerHTML=\'📦\'">';
}
</script>
</body>
</html>