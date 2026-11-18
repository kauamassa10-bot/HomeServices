<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/init.php';


/** @var PDO $pdo */

// Se já estiver logado como prestador, pula o login e vai para o painel dele
if (isset($_SESSION['id_usuario']) && strtoupper($_SESSION['tipo_usuario'] ?? '') === 'PRESTADOR') {
    header("Location: dashboard_prestador.php");
    exit;
}

$mensagem = '';

// Mensagem de sucesso vinda do cadastro
if (isset($_GET['sucesso']) && $_GET['sucesso'] === 'cadastro') {
    $mensagem = "<div class='alerta-sucesso' style='color: green; margin-bottom: 15px; text-align: center; font-weight: bold;'>Cadastro realizado com sucesso! Faça seu login abaixo.</div>";
}
// Mensagem de erro de acesso
if (isset($_GET['erro']) && $_GET['erro'] === 'acesso_negado') {
    $mensagem = "<div class='alerta-erro' style='color: red; margin-bottom: 15px; text-align: center;'>Faça login para acessar esta página.</div>";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = trim($_POST['senha'] ?? '');

    if (!empty($email) && !empty($senha)) {
        // Busca o usuário no banco garantindo que ele é um prestador
        $sql = "SELECT * FROM usuario WHERE email = :email AND tipo_usuario = 'PRESTADOR' AND status_ativo = 1 LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':email' => $email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verifica a senha (comparação direta baseada no seu script de cadastro)
        if ($usuario && $usuario['senha'] === $senha) {
            $_SESSION['id_usuario'] = $usuario['id_usuario'];
            $_SESSION['tipo_usuario'] = $usuario['tipo_usuario'];
            $_SESSION['nome_usuario'] = $usuario['nome_usuario'];
            
            // Login bem-sucedido, manda para a tela de gerenciar serviços
            header("Location: dashboard_prestador.php");
            exit;
        } else {
            $mensagem = "<div class='alerta-erro' style='color: red; margin-bottom: 15px; text-align: center;'>E-mail ou senha incorretos, ou usuário inativo.</div>";
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
    <link rel="stylesheet" href="css/cadastros.css">
    <link rel="stylesheet" href="css/global.css">
</head>
<body class="fundo-claro">

    <header class="header-simples">
        <div class="header-container">
            <h1 class="logo-texto">HomeServices</h1>
            <nav class="menu-topo">
                <a href="index.php">Início</a>
                <a href="cadastro_profissional.php">Quero ser um profissional</a>
            </nav>
        </div>
    </header>

    <main class="layout-duas-colunas" style="justify-content: center; margin-top: 50px;">
        <section class="coluna-esquerda" style="max-width: 500px; margin: 0 auto;">
            <h2 style="text-align: center;">Portal do Profissional</h2>
            <p class="subtitulo" style="text-align: center;">Acesse sua conta para gerenciar seus serviços e pedidos</p>

            <?= $mensagem ?>

            <form method="POST" action="" class="form-pedido-leitura">
                <div class="campo-simples">
                    <label>E-mail</label>
                    <input type="email" name="email" placeholder="Seu e-mail cadastrado" required>
                </div>

                <div class="campo-simples">
                    <label>Senha</label>
                    <input type="password" name="senha" placeholder="Sua senha" required>
                </div>

                <div class="botoes-rodape" style="margin-top: 20px;">
                    <button type="submit" class="btn-solido" style="width: 100%;">Entrar</button>
                </div>

                <div style="margin-top: 20px; text-align: center; font-size: 0.9rem;">
                    Ainda não é cadastrado? <a href="cadastro_profissional.php" style="color: #0284c7; font-weight: bold;">Crie sua conta aqui</a>
                </div>
            </form>
        </section>
    </main>

</body>
</html>