<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/init.php';
require_once __DIR__ . '/banco/database.php';

/** @var PDO $pdo */

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome  = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = trim($_POST['senha'] ?? '');

    if (empty($nome) || empty($email) || empty($senha)) {
        $erro = "Por favor, preencha todos os campos.";
    } else {
        // Verifica se o e-mail já está registado
        $sqlCheck = "SELECT id_usuario FROM usuario WHERE email = :email LIMIT 1";
        $stmtCheck = $pdo->prepare($sqlCheck);
        $stmtCheck->execute([':email' => $email]);
        
        if ($stmtCheck->fetch()) {
            $erro = "Este e-mail já está cadastrado.";
        } else {
            // Guarda a senha de forma segura com hash
            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
            $sqlInsert = "INSERT INTO usuario (nome_usuario, email, senha, tipo_usuario, status_ativo) 
                          VALUES (:nome, :email, :senha, 'CLIENTE', 1)";
            $stmtInsert = $pdo->prepare($sqlInsert);
            
            if ($stmtInsert->execute([':nome' => $nome, ':email' => $email, ':senha' => $senhaHash])) {
                // Redireciona para o login com mensagem de sucesso
                header("Location: login_cliente.php?sucesso=cadastro");
                exit;
            } else {
                $erro = "Erro ao realizar o cadastro. Tente novamente.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro Cliente - HomeServices</title>
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
                <a href="login_profissional.php">Sou profissional</a>
            </nav>
        </div>
    </header>

    <main class="container-login">
        <div class="card-login">
            <div class="topo-login">
                <span class="tag-admin">Área do Cliente</span>
                <h2>Crie sua conta</h2>
                <p>Cadastre-se para solicitar e acompanhar orçamentos</p>
            </div>

            <?php if (!empty($erro)): ?>
                <div class="alerta-erro"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>

            <form method="POST" action="" class="form-login" autocomplete="off">
                <div class="grupo-campo">
                    <label for="nome">Nome Completo</label>
                    <input type="text" id="nome" name="nome" placeholder="O seu nome" required>
                </div>

                <div class="grupo-campo">
                    <label for="email">E-mail</label>
                    <input type="email" id="email" name="email" placeholder="seu@email.com" required>
                </div>

                <div class="grupo-campo">
                    <label for="senha">Senha</label>
                    <input type="password" id="senha" name="senha" placeholder="Crie uma senha" required minlength="6">
                </div>

                <button type="submit" class="btn-solido btn-entrar">Cadastrar como Cliente</button>
            </form>

            <div style="margin-top: 15px; text-align: center; font-size: 0.9rem;">
                Já tem uma conta? <a href="login_cliente.php" style="color: #2b5cbf; font-weight: bold;">Faça login aqui</a>
            </div>
        </div>
    </main>
</body>
</html>