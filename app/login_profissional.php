<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/init.php';

/** @var PDO $pdo */

if (isset($_SESSION['id_usuario']) && strtoupper($_SESSION['tipo_usuario'] ?? '') === 'PRESTADOR') {
    header("Location: dashboard_prestador.php");
    exit;
}

$erro = '';
$sucesso = '';

if (isset($_GET['sucesso']) && $_GET['sucesso'] === 'cadastro') {
    $sucesso = "Cadastro realizado com sucesso! Faça seu login abaixo.";
}

if (isset($_GET['erro']) && $_GET['erro'] === 'acesso_negado') {
    $erro = "Faça login para acessar esta página.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = trim($_POST['senha'] ?? '');

    if (!empty($email) && !empty($senha)) {
        $sql = "SELECT * FROM usuario WHERE email = :email AND tipo_usuario = 'PRESTADOR' AND status_ativo = 1 LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':email' => $email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && $usuario['senha'] === $senha) {
            $_SESSION['id_usuario'] = $usuario['id_usuario'];
            $_SESSION['tipo_usuario'] = $usuario['tipo_usuario'];
            $_SESSION['nome_usuario'] = $usuario['nome_usuario'];
            
            header("Location: dashboard_prestador.php");
            exit;
        } else {
            $erro = "E-mail ou senha incorretos, ou usuário inativo.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Profissional - HomeServices</title>
    <link rel="stylesheet" href="css/orcamento.css">
    <link rel="stylesheet" href="css/login.css">
    <link rel="stylesheet" href="css/global.css">
</head>
<body class="fundo-claro">

    <header class="header-simples">
        <div class="header-container">
            <h1 class="logo-texto">HomeServices</h1>
            <nav class="menu-topo">
                <a href="index.php">Início</a>
                <a href="login_cliente.php">Sou Cliente</a>
            </nav>
        </div>
    </header>

    <main class="container-login">
        <div class="card-login">
            <div class="topo-login">
                <span class="tag-admin">Portal do Profissional</span>
                <h2>Acesse sua conta</h2>
                <p>Gerencie seus serviços, orçamentos e clientes</p>
            </div>

            <?php if (!empty($sucesso)): ?>
                <div class="alerta-sucesso"><?= htmlspecialchars($sucesso) ?></div>
            <?php endif; ?>

            <?php if (!empty($erro)): ?>
                <div class="alerta-erro"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>

            <form method="POST" action="" class="form-login" autocomplete="off">
                <div class="grupo-campo">
                    <label for="email">E-mail Cadastrado</label>
                    <input type="email" id="email" name="email" placeholder="seu@email.com" required>
                </div>

                <div class="grupo-campo">
                    <label for="senha">Senha</label>
                    <input type="password" id="senha" name="senha" placeholder="••••••••" required>
                </div>

                <button type="submit" class="btn-solido">Entrar no Painel</button>
            </form>

            <div style="margin-top: 24px; text-align: center; font-size: 0.9rem; color: #64748b;">
                Ainda não é cadastrado? <a href="cadastro_profissional.php">Crie sua conta aqui</a>
            </div>
        </div>
    </main>

</body>
</html>