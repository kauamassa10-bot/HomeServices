<?php
session_start();
require_once __DIR__ . '/init.php';

// Se já estiver logado como admin, vai direto para registros.php
if (isset($_SESSION['id_usuario']) && ($_SESSION['tipo_usuario'] ?? '') === 'ADMIN') {
    header("Location: registros.php");
    exit;
}

$erro = '';

if (isset($_GET['erro']) && $_GET['erro'] === 'acesso_negado') {
    $erro = "Acesso restrito! Faça login como Administrador para acessar esta página.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = trim($_POST['senha'] ?? '');

    $sql = "SELECT * FROM usuario WHERE email = :email AND tipo_usuario = 'ADMIN' AND status_ativo = 1 LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':email' => $email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin && ($senha === $admin['senha'] || password_verify($senha, $admin['senha']))) {
        $_SESSION['id_usuario'] = $admin['id_usuario'];
        $_SESSION['nome_usuario'] = $admin['nome_usuario'];
        $_SESSION['tipo_usuario'] = $admin['tipo_usuario'];

        header("Location: registros.php");
        exit;
    } else {
        $erro = "E-mail ou senha de administrador inválidos.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Login Administrador - HomeServices</title>
    <link rel="stylesheet" href="css/orcamento.css">
    <link rel="stylesheet" href="css/login.css">
</head>
<body class="fundo-claro">

    <header class="header-simples">
        <div class="header-container">
            <h1 class="logo-texto">HomeServices</h1>
            <nav class="menu-topo">
                <a href="orcamento.php">Solicitar Orçamento</a>
                <a href="pedidos.php">Meus Pedidos</a>
            </nav>
        </div>
    </header>

    <main class="container-login">
        <div class="card-login">
            <div class="topo-login">
                <span class="tag-admin">Área Restrita</span>
                <h2>Acesso Administrativo</h2>
                <p>Entre com suas credenciais de administrador para gerenciar o sistema</p>
            </div>

            <?php if (!empty($erro)): ?>
                <div class="alerta-erro"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>

            <form method="POST" action="" class="form-login">
                <div class="grupo-campo">
                    <label for="email">E-mail do Administrador</label>
                    <input type="email" id="email" name="email" placeholder="admin@homeservices.com" required>
                </div>

                <div class="grupo-campo">
                    <label for="senha">Senha</label>
                    <input type="password" id="senha" name="senha" placeholder="••••••••" required>
                </div>

                <button type="submit" class="btn-solido btn-entrar">Entrar no Painel</button>
            </form>
        </div>
    </main>

</body>
</html>