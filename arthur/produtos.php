<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit;
}

$conn = pg_connect("host=localhost dbname=produtos user=postgres password=123456");
if (!$conn) {
    die("Erro ao conectar com o banco de dados.");
}

$mensagem = "";
$tipo_msg = "";

// DELETE
if (isset($_GET['deletar'])) {
    $id = (int)$_GET['deletar'];
    $res = pg_query($conn, "DELETE FROM public.produto WHERE idproduto = $id");
    if ($res) { $mensagem = "Produto removido com sucesso!"; $tipo_msg = "success"; }
    else       { $mensagem = "Erro ao remover produto."; $tipo_msg = "error"; }
}

// TOGGLE STATUS
if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $res = pg_query($conn, "UPDATE public.produto SET produtostatus = NOT produtostatus WHERE idproduto = $id");
    if ($res) { $mensagem = "Status atualizado!"; $tipo_msg = "success"; }
}

// BUSCA
$busca = "";
$where = "";
if (isset($_GET['busca']) && $_GET['busca'] !== '') {
    $busca = pg_escape_string($conn, $_GET['busca']);
    $where = "WHERE produtonome ILIKE '%$busca%'";
}

$resultado = pg_query($conn, "SELECT * FROM public.produto $where ORDER BY idproduto DESC");

pg_close($conn);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produtos — ProdutoHub</title>
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
            --warning: #f59e0b;
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

        .sidebar-logo p {
            font-size: 11px;
            color: var(--muted);
            margin-top: 2px;
        }

        .nav {
            padding: 16px 12px;
            flex: 1;
        }

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
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 8px;
            color: var(--muted);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.15s;
            margin-bottom: 2px;
        }

        .nav a:hover, .nav a.active {
            background: rgba(124,58,237,0.15);
            color: var(--accent2);
        }

        .nav a.active {
            background: rgba(124,58,237,0.2);
        }

        .sidebar-bottom {
            padding: 16px 12px;
            border-top: 1px solid var(--border);
        }

        .user-chip {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 8px;
            background: var(--surface2);
        }

        .avatar {
            width: 32px; height: 32px;
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 14px; font-weight: 700; color: #fff;
            font-family: 'Syne', sans-serif;
            flex-shrink: 0;
        }

        .user-info { flex: 1; overflow: hidden; }
        .user-info strong { display: block; font-size: 13px; font-weight: 600; truncate: ellipsis; }
        .user-info span { font-size: 11px; color: var(--muted); }

        .btn-logout {
            display: block;
            text-align: center;
            margin-top: 10px;
            padding: 9px;
            background: rgba(239,68,68,0.1);
            border: 1px solid rgba(239,68,68,0.2);
            border-radius: 8px;
            color: #fca5a5;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.15s;
        }

        .btn-logout:hover {
            background: rgba(239,68,68,0.2);
            color: #fff;
        }

        /* MAIN */
        .main {
            margin-left: 220px;
            padding: 32px 36px;
            position: relative;
            z-index: 1;
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 32px;
        }

        .page-title h1 {
            font-family: 'Syne', sans-serif;
            font-size: 26px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .page-title p {
            color: var(--muted);
            font-size: 14px;
            margin-top: 2px;
        }

        .btn-novo {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 11px 20px;
            background: linear-gradient(135deg, var(--accent), #9333ea);
            border-radius: 10px;
            color: #fff;
            text-decoration: none;
            font-family: 'Syne', sans-serif;
            font-size: 14px;
            font-weight: 600;
            box-shadow: 0 4px 20px rgba(124,58,237,0.3);
            transition: transform 0.15s, box-shadow 0.15s;
        }

        .btn-novo:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 28px rgba(124,58,237,0.45);
        }

        /* TOAST */
        .toast {
            padding: 12px 18px;
            border-radius: 10px;
            font-size: 14px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-10px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .toast.success { background: rgba(34,197,94,0.12); border: 1px solid rgba(34,197,94,0.25); color: #86efac; }
        .toast.error   { background: rgba(239,68,68,0.12); border: 1px solid rgba(239,68,68,0.25); color: #fca5a5; }

        /* SEARCH + FILTER BAR */
        .toolbar {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
        }

        .search-wrap {
            position: relative;
            flex: 1;
            max-width: 340px;
        }

        .search-wrap span {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 15px;
        }

        .search-wrap input {
            width: 100%;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 10px;
            color: var(--text);
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            padding: 10px 14px 10px 40px;
            outline: none;
            transition: border-color 0.2s;
        }

        .search-wrap input:focus { border-color: var(--accent); }

        .btn-search {
            padding: 10px 18px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 10px;
            color: var(--text);
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.15s;
        }

        .btn-search:hover { border-color: var(--accent); color: var(--accent2); }

        /* TABLE */
        .table-wrap {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            overflow: hidden;
        }

        .table-header {
            padding: 16px 24px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .table-header span {
            font-size: 13px;
            color: var(--muted);
        }

        .table-header strong {
            font-family: 'Syne', sans-serif;
            font-size: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead th {
            padding: 14px 24px;
            text-align: left;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--muted);
            background: rgba(255,255,255,0.02);
            border-bottom: 1px solid var(--border);
        }

        tbody tr {
            transition: background 0.15s;
            animation: rowIn 0.4s ease both;
        }

        @keyframes rowIn {
            from { opacity: 0; transform: translateX(-10px); }
            to   { opacity: 1; transform: translateX(0); }
        }

        tbody tr:hover { background: rgba(124,58,237,0.05); }
        tbody tr + tr { border-top: 1px solid var(--border); }

        tbody td {
            padding: 16px 24px;
            font-size: 14px;
        }

        .id-badge {
            background: var(--surface2);
            border: 1px solid var(--border);
            border-radius: 6px;
            padding: 3px 8px;
            font-size: 12px;
            color: var(--muted);
            font-family: monospace;
        }

        .preco {
            font-family: 'Syne', sans-serif;
            font-weight: 600;
            color: var(--accent2);
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge.ativo {
            background: rgba(34,197,94,0.12);
            color: #86efac;
            border: 1px solid rgba(34,197,94,0.2);
        }

        .badge.inativo {
            background: rgba(100,116,139,0.12);
            color: var(--muted);
            border: 1px solid rgba(100,116,139,0.2);
        }

        .foto-thumb {
            width: 40px; height: 40px;
            border-radius: 8px;
            object-fit: cover;
            background: var(--surface2);
            border: 1px solid var(--border);
            display: flex; align-items: center; justify-content: center;
            font-size: 18px;
            overflow: hidden;
        }

        .foto-thumb img {
            width: 100%; height: 100%; object-fit: cover;
        }

        .actions {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .btn-action {
            padding: 6px 12px;
            border-radius: 7px;
            font-size: 12px;
            font-weight: 500;
            text-decoration: none;
            border: 1px solid transparent;
            cursor: pointer;
            transition: all 0.15s;
            background: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .btn-edit {
            background: rgba(124,58,237,0.1);
            border-color: rgba(124,58,237,0.25);
            color: var(--accent2);
        }
        .btn-edit:hover {
            background: rgba(124,58,237,0.2);
            color: #fff;
        }

        .btn-toggle {
            background: rgba(245,158,11,0.1);
            border-color: rgba(245,158,11,0.2);
            color: #fcd34d;
        }
        .btn-toggle:hover {
            background: rgba(245,158,11,0.2);
        }

        .btn-del {
            background: rgba(239,68,68,0.08);
            border-color: rgba(239,68,68,0.2);
            color: #fca5a5;
        }
        .btn-del:hover {
            background: rgba(239,68,68,0.2);
            color: #fff;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--muted);
        }

        .empty-state .icon { font-size: 48px; margin-bottom: 12px; }
        .empty-state p { font-size: 15px; }

        /* stats */
        .stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 20px 24px;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0; right: 0;
            width: 80px; height: 80px;
            background: radial-gradient(circle, rgba(124,58,237,0.15), transparent 70%);
            border-radius: 50%;
        }

        .stat-card .stat-icon {
            font-size: 22px;
            margin-bottom: 10px;
        }

        .stat-card .stat-value {
            font-family: 'Syne', sans-serif;
            font-size: 28px;
            font-weight: 800;
        }

        .stat-card .stat-label {
            font-size: 12px;
            color: var(--muted);
            margin-top: 2px;
        }
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
        <a href="produtos.php" class="active">🗂 Produtos</a>
        <a href="form_produto.php">➕ Novo Produto</a>
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
            <h1>Produtos</h1>
            <p>Gerencie o catálogo de produtos</p>
        </div>
        <a href="form_produto.php" class="btn-novo">+ Novo Produto</a>
    </div>

    <?php if ($mensagem): ?>
    <div class="toast <?= $tipo_msg ?>">
        <?= $tipo_msg === 'success' ? '✅' : '❌' ?>
        <?= htmlspecialchars($mensagem) ?>
    </div>
    <?php endif; ?>

    <?php
    // Stats rápidas
    $conn2 = pg_connect("host=localhost dbname=produtos user=postgres password=123456");
    $total_res = pg_query($conn2, "SELECT COUNT(*) as total, COUNT(*) FILTER (WHERE produtostatus=true) as ativos, COALESCE(SUM(produtopreco),0) as soma FROM public.produto");
    $stats = pg_fetch_assoc($total_res);
    pg_close($conn2);
    ?>
    <div class="stats">
        <div class="stat-card">
            <div class="stat-icon">🗂</div>
            <div class="stat-value"><?= $stats['total'] ?></div>
            <div class="stat-label">Total de produtos</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">✅</div>
            <div class="stat-value"><?= $stats['ativos'] ?></div>
            <div class="stat-label">Produtos ativos</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">💰</div>
            <div class="stat-value">R$ <?= number_format($stats['soma'], 2, ',', '.') ?></div>
            <div class="stat-label">Soma dos preços</div>
        </div>
    </div>

    <!-- BUSCA -->
    <form method="GET" class="toolbar">
        <div class="search-wrap">
            <span>🔍</span>
            <input type="text" name="busca" placeholder="Buscar produto pelo nome..." value="<?= htmlspecialchars($busca) ?>">
        </div>
        <button type="submit" class="btn-search">Buscar</button>
        <?php if ($busca): ?>
        <a href="produtos.php" class="btn-search">✕ Limpar</a>
        <?php endif; ?>
    </form>

    <!-- TABELA -->
    <div class="table-wrap">
        <div class="table-header">
            <strong>Lista de Produtos <?= $busca ? "— busca: \"$busca\"" : "" ?></strong>
            <span><?= pg_num_rows($resultado) ?> registro(s)</span>
        </div>

        <?php if (pg_num_rows($resultado) === 0): ?>
        <div class="empty-state">
            <div class="icon">📭</div>
            <p>Nenhum produto encontrado<?= $busca ? " para \"$busca\"" : "" ?>.</p>
        </div>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Foto</th>
                    <th>Nome</th>
                    <th>Preço</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($linha = pg_fetch_assoc($resultado)): ?>
            <tr>
                <td><span class="id-badge">#<?= $linha['idproduto'] ?></span></td>
                <td>
                    <div class="foto-thumb">
                        <?php if ($linha['produtofoto']): ?>
                            <img src="<?= htmlspecialchars($linha['produtofoto']) ?>" alt="foto" onerror="this.parentElement.innerHTML='📦'">
                        <?php else: ?>
                            📦
                        <?php endif; ?>
                    </div>
                </td>
                <td><strong><?= htmlspecialchars($linha['produtonome']) ?></strong></td>
                <td><span class="preco">R$ <?= number_format($linha['produtopreco'], 2, ',', '.') ?></span></td>
                <td>
                    <?php if ($linha['produtostatus'] === 't'): ?>
                        <span class="badge ativo">● Ativo</span>
                    <?php else: ?>
                        <span class="badge inativo">○ Inativo</span>
                    <?php endif; ?>
                </td>
                <td>
                    <div class="actions">
                        <a href="form_produto.php?id=<?= $linha['idproduto'] ?>" class="btn-action btn-edit">✏️ Editar</a>
                        <a href="produtos.php?toggle=<?= $linha['idproduto'] ?>" class="btn-action btn-toggle">⇄ Status</a>
                        <a href="produtos.php?deletar=<?= $linha['idproduto'] ?>" class="btn-action btn-del" onclick="return confirm('Remover este produto?')">🗑 Excluir</a>
                    </div>
                </td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</main>

</body>
</html>