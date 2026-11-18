<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/init.php';
require_once __DIR__ . '/banco/database.php';

/** @var PDO $pdo */

if (isset($_SESSION['id_usuario']) && strtoupper($_SESSION['tipo_usuario'] ?? '') === 'CLIENTE') {
    header("Location: buscar.php");
    exit;
}

$erro = '';
$sucesso = '';

if (isset($_GET['erro']) && $_GET['erro'] === 'acesso_negado') {
    $erro = "Faça login como Cliente para acessar esta página.";
}

if (isset($_GET['sucesso']) && $_GET['sucesso'] === 'cadastro') {
    $sucesso = "Cadastro realizado com sucesso! Faça login para continuar.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = trim($_POST['senha'] ?? '');

    $sql = "SELECT * FROM usuario WHERE email = :email AND tipo_usuario = 'CLIENTE' AND status_ativo = 1 ORDER BY id_usuario DESC LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':email' => $email]);
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cliente && ($senha === $cliente['senha'] || password_verify($senha, $cliente['senha']))) {
        session_regenerate_id(true);
        $_SESSION['id_usuario']   = $cliente['id_usuario'];
        $_SESSION['nome_usuario'] = $cliente['nome_usuario'];
        $_SESSION['tipo_usuario'] = $cliente['tipo_usuario'];

        header("Location: buscar.php");
        exit;
    } else {
        $erro = "E-mail ou senha de cliente inválidos.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Cliente - HomeServices</title>
    <link rel="stylesheet" href="css/orcamento.css">
    <link rel="stylesheet" href="css/login.css">
    <link rel="stylesheet" href="css/global.css">
</head>
<body class="fundo-claro">

    <header class="header-simples">
        <div class="header-container">
            <h1 class="logo-texto">HomeServices</h1>
            <a href="login_profissional.php">Sou profissional</a>]
            <a href="login_admin.php">Sou administrador</a>
        </div>
    </header>

    <main class="container-login">
        <div class="card-login">
            <div class="topo-login">
                <span class="tag-admin">Área do Cliente</span>
                <h2>Bem-vindo de volta</h2>
                <p>Entre com sua conta para solicitar orçamentos e acompanhar seus pedidos</p>
            </div>

            <?php if (!empty($sucesso)): ?>
                <div class="alerta-sucesso"><?= htmlspecialchars($sucesso) ?></div>
            <?php endif; ?>

            <?php if (!empty($erro)): ?>
                <div class="alerta-erro"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>

            <form method="POST" action="" class="form-login" autocomplete="off">
                <div class="grupo-campo">
                    <label for="email">E-mail</label>
                    <input type="email" id="email" name="email" placeholder="seu@email.com" required>
                </div>

                <div class="grupo-campo">
                    <label for="senha">Senha</label>
                    <input type="password" id="senha" name="senha" placeholder="••••••••" required>
                </div>

                <button type="submit" class="btn-solido btn-entrar">Entrar como Cliente</button>
            </form>

            <div style="margin-top: 15px; text-align: center; font-size: 0.9rem;">
                Ainda não tem cadastro? <a href="cadastro_cliente.php">Cadastre-se aqui</a>
            </div>
        </div>
    </main>

</body>
</html>