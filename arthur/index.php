<?php
session_start();

// Se já estiver logado, redireciona para produtos
if (isset($_SESSION['usuario'])) {
    header("Location: produtos.php");
    exit;
}

$erro = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $conn = pg_connect("host=localhost dbname=produtos user=postgres password=123456");

    if (!$conn) {
        $erro = "Erro ao conectar com o banco de dados.";
    } else {
        $username_safe = pg_escape_string($conn, $username);
        $password_safe = pg_escape_string($conn, $password);

        $resultado = pg_query($conn, "SELECT * FROM public.usuario WHERE username = '$username_safe' AND password = '$password_safe' AND status = true");

        if ($resultado && pg_num_rows($resultado) > 0) {
            $user = pg_fetch_assoc($resultado);
            $_SESSION['usuario'] = $user['username'];
            $_SESSION['idusuario'] = $user['idusuario'];
            pg_close($conn);
            header("Location: produtos.php");
            exit;
        } else {
            $erro = "Usuário ou senha inválidos.";
        }
        pg_close($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — ProdutoHub</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg: #0a0a0f;
            --surface: #13131a;
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
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        /* Animated background */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background:
                radial-gradient(ellipse 80% 60% at 20% 20%, rgba(124,58,237,0.18) 0%, transparent 60%),
                radial-gradient(ellipse 60% 50% at 80% 80%, rgba(167,139,250,0.10) 0%, transparent 60%);
            z-index: 0;
            animation: bgPulse 8s ease-in-out infinite alternate;
        }

        @keyframes bgPulse {
            0% { opacity: 0.7; transform: scale(1); }
            100% { opacity: 1; transform: scale(1.05); }
        }

        .grid-bg {
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(124,58,237,0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(124,58,237,0.05) 1px, transparent 1px);
            background-size: 40px 40px;
            z-index: 0;
        }

        .login-wrap {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 420px;
            padding: 20px;
            animation: fadeUp 0.6s cubic-bezier(.16,1,.3,1) both;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(30px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .brand {
            text-align: center;
            margin-bottom: 40px;
        }

        .brand-icon {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            margin-bottom: 16px;
            box-shadow: 0 0 40px rgba(124,58,237,0.4);
        }

        .brand h1 {
            font-family: 'Syne', sans-serif;
            font-size: 28px;
            font-weight: 800;
            letter-spacing: -0.5px;
            background: linear-gradient(135deg, #fff 40%, var(--accent2));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .brand p {
            color: var(--muted);
            font-size: 14px;
            margin-top: 6px;
        }

        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 36px 32px;
            backdrop-filter: blur(20px);
            box-shadow: 0 24px 64px rgba(0,0,0,0.4), 0 0 0 1px rgba(124,58,237,0.1);
        }

        .field {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-size: 12px;
            font-weight: 500;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 8px;
        }

        .input-wrap {
            position: relative;
        }

        .input-wrap span {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 16px;
            pointer-events: none;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 10px;
            color: var(--text);
            font-family: 'DM Sans', sans-serif;
            font-size: 15px;
            padding: 13px 14px 13px 42px;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(124,58,237,0.2);
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, var(--accent), #9333ea);
            border: none;
            border-radius: 10px;
            color: #fff;
            font-family: 'Syne', sans-serif;
            font-size: 15px;
            font-weight: 700;
            letter-spacing: 0.02em;
            cursor: pointer;
            transition: transform 0.15s, box-shadow 0.15s;
            box-shadow: 0 4px 24px rgba(124,58,237,0.35);
            margin-top: 8px;
        }

        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 32px rgba(124,58,237,0.5);
        }

        .btn-login:active { transform: translateY(0); }

        .erro-box {
            background: rgba(239,68,68,0.1);
            border: 1px solid rgba(239,68,68,0.3);
            border-radius: 10px;
            color: #fca5a5;
            font-size: 13px;
            padding: 12px 14px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
            animation: shake 0.4s ease;
        }

        @keyframes shake {
            0%,100% { transform: translateX(0); }
            20%,60% { transform: translateX(-6px); }
            40%,80% { transform: translateX(6px); }
        }

        .hint {
            text-align: center;
            color: var(--muted);
            font-size: 12px;
            margin-top: 20px;
        }

        .hint code {
            background: var(--border);
            padding: 2px 6px;
            border-radius: 4px;
            color: var(--accent2);
            font-size: 11px;
        }
    </style>
</head>
<body>
<div class="grid-bg"></div>

<div class="login-wrap">
    <div class="brand">
        <div class="brand-icon">📦</div>
        <h1>ProdutoHub</h1>
        <p>Sistema de gerenciamento de produtos</p>
    </div>

    <div class="card">
        <?php if ($erro): ?>
        <div class="erro-box">⚠️ <?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="field">
                <label>Usuário</label>
                <div class="input-wrap">
                    <span>👤</span>
                    <input type="text" name="username" placeholder="Digite seu usuário" required autocomplete="username">
                </div>
            </div>
            <div class="field">
                <label>Senha</label>
                <div class="input-wrap">
                    <span>🔒</span>
                    <input type="password" name="password" placeholder="Digite sua senha" required autocomplete="current-password">
                </div>
            </div>
            <button type="submit" class="btn-login">Entrar no sistema →</button>
        </form>

        <p class="hint">Use <code>admin</code> / <code>123456</code> para teste</p>
    </div>
</div>
</body>
</html>