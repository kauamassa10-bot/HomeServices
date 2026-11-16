<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/init.php';
require_once __DIR__ . '/banco/database.php';

/** @var PDO $pdo */

$mensagem = '';
$isAdmin = (strtoupper($_SESSION['tipo_usuario'] ?? '') === 'ADMIN');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome     = trim($_POST['nome_usuario'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $senha    = trim($_POST['senha'] ?? '');

    // Verifica se o e-mail já está cadastrado para evitar contas duplicadas
    $check = $pdo->prepare("SELECT id_usuario FROM usuario WHERE email = :email LIMIT 1");
    $check->execute([':email' => $email]);

    if ($check->fetch()) {
        $mensagem = "<div class='erro'>Este e-mail já está cadastrado. Tente fazer login ou use outro e-mail.</div>";
    } else {
        $sql = "INSERT INTO usuario (nome_usuario, email, senha, telefone, tipo_usuario, status_ativo, data_cadastro) 
                VALUES (:nome, :email, :senha, :telefone, 'CLIENTE', 1, NOW())";

        $stmt = $pdo->prepare($sql);

        try {
            $stmt->execute([
                ':nome'     => $nome,
                ':email'    => $email,
                ':senha'    => $senha,
                ':telefone' => $telefone
            ]);

            // Se for ADMIN cadastrando, volta para o painel; se for visitante, vai para o login do cliente
            if ($isAdmin) {
                header("Location: registros.php?sucesso=cliente");
            } else {
                header("Location: login_cliente.php?sucesso=cadastro");
            }
            exit;
        } catch (PDOException $e) {
            $mensagem = "<div class='erro'>Erro ao cadastrar cliente: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Cliente - HomeServices</title>
    <link rel="stylesheet" href="css/orcamento.css">
    <link rel="stylesheet" href="css/cadastros.css">
    <link rel="stylesheet" href="css/global.css">
</head>
<body class="fundo-claro">

    <header class="header-simples">
        <div class="header-container">
            <h1 class="logo-texto">HomeServices</h1>
            <?php if ($isAdmin): ?>
                <nav class="menu-topo">
                    <a href="registros.php">Registros</a>
                    <a href="cadastro_cliente.php">Novo Cliente</a>
                    <a href="cadastro_profissional.php">Novo Profissional</a>
                    <a href="cadastro_servicos.php">Novo Serviço</a>
                    <a href="logout.php">Sair</a>
                </nav>
            <?php endif; ?>
        </div>
    </header>

    <main class="layout-duas-colunas">
        <section class="coluna-esquerda">
            <h2><?= $isAdmin ? 'Cadastrar Cliente' : 'Criar Conta de Cliente' ?></h2>
            <p class="subtitulo">Preencha os dados para registrar um novo cliente</p>

            <?= $mensagem ?>

            <form method="POST" action="" class="form-pedido-leitura">
                <div class="campo-simples">
                    <input type="text" name="nome_usuario" placeholder="Nome completo" required>
                </div>

                <div class="campo-simples">
                    <input type="email" name="email" placeholder="E-mail (ex: ana@email.com)" required>
                </div>

                <div class="linha-dupla">
                    <div class="campo-simples">
                        <input type="text" name="telefone" placeholder="Telefone (ex: 11999998888)" required>
                    </div>
                    <div class="campo-simples">
                        <input type="password" name="senha" placeholder="Senha de acesso" required>
                    </div>
                </div>

                <div class="botoes-rodape">
                    <button type="submit" class="btn-solido" style="width: 100%;">
                        <?= $isAdmin ? 'Salvar Cliente' : 'Concluir Cadastro' ?>
                    </button>
                </div>

                <?php if (!$isAdmin): ?>
                    <div style="margin-top: 15px; text-align: center; font-size: 0.9rem;">
                        Já possui uma conta? <a href="login_cliente.php">Faça login aqui</a>
                    </div>
                <?php endif; ?>
            </form>
        </section>

        <aside class="coluna-direita">
            <div class="card-resumo">
                <div class="detalhes-resumo">
                    <strong>Cadastro de Clientes</strong>
                    <p>Os clientes cadastrados ficam habilitados para solicitar orçamentos e agendar serviços.</p>
                </div>
            </div>
        </aside>
    </main>

</body>
</html>