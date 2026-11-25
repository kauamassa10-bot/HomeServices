<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/init.php';
require_once __DIR__ . '/banco/database.php';

/** @var PDO $pdo */

$mensagem = '';
// Verifica se o usuário atual logado é um Administrador
$isAdmin = (strtoupper($_SESSION['tipo_usuario'] ?? '') === 'ADMIN');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome     = trim($_POST['nome_usuario'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $senha    = trim($_POST['senha'] ?? '');

    // Verifica se o e-mail já existe no banco
    $check = $pdo->prepare("SELECT id_usuario FROM usuario WHERE email = :email LIMIT 1");
    $check->execute([':email' => $email]);

    if ($check->fetch()) {
        $mensagem = "<div class='erro' style='color: red; margin-bottom: 15px;'>Este e-mail já está cadastrado. Tente fazer login ou use outro e-mail.</div>";
    } else {
        $sql = "INSERT INTO usuario (nome_usuario, email, senha, telefone, tipo_usuario, status_ativo, data_cadastro) 
                VALUES (:nome, :email, :senha, :telefone, 'PRESTADOR', 1, NOW())";

        $stmt = $pdo->prepare($sql);

        try {
            $stmt->execute([
                ':nome'     => $nome,
                ':email'    => $email,
                ':senha'    => $senha,
                ':telefone' => $telefone
            ]);

            // Redirecionamento inteligente
            if ($isAdmin) {
                // Se for o dono do sistema cadastrando
                header("Location: registros.php?sucesso=profissional");
            } else {
                // Se for o próprio profissional se cadastrando
                header("Location: login_profissional.php?sucesso=cadastro");
            }
            exit;
        } catch (PDOException $e) {
            $mensagem = "<div class='erro' style='color: red; margin-bottom: 15px;'>Erro ao cadastrar profissional: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Profissional - HomeServices</title>
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
            <?php else: ?>
                 <nav class="menu-topo">
                     <a href="index.php">Início</a>
                     <a href="login_profissional.php">Fazer Login</a>
                 </nav>
            <?php endif; ?>
        </div>
    </header>

    <main class="layout-duas-colunas">
        <section class="coluna-esquerda">
            <h2><?= $isAdmin ? 'Cadastrar Profissional' : 'Cadastro de Profissional' ?></h2>
            <p class="subtitulo">Registre-se como prestador de serviços na plataforma</p>

            <?= $mensagem ?>

            <form method="POST" action="" class="form-pedido-leitura">
                <div class="campo-simples">
                    <label>Nome Completo ou Empresa</label>
                    <input type="text" name="nome_usuario" placeholder="Ex: João Silva Encanamentos" required>
                </div>

                <div class="campo-simples">
                    <label>E-mail Profissional</label>
                    <input type="email" name="email" placeholder="contato@exemplo.com" required>
                </div>

                <div class="linha-dupla">
                    <div class="campo-simples">
                        <label>Telefone / WhatsApp</label>
                        <input type="text" name="telefone" placeholder="(11) 99999-9999" required>
                    </div>
                    <div class="campo-simples">
                        <label>Senha de Acesso</label>
                        <input type="password" name="senha" placeholder="Crie uma senha" required>
                    </div>
                </div>

                <div class="botoes-rodape">
                    <button type="submit" class="btn-solido" style="width: 100%;">
                        <?= $isAdmin ? 'Salvar Profissional' : 'Concluir Cadastro' ?>
                    </button>
                </div>

                <?php if (!$isAdmin): ?>
                    <div style="margin-top: 15px; text-align: center; font-size: 0.9rem;">
                        Já possui conta profissional? <a href="login_profissional.php" style="color: #0284c7; font-weight: bold;">Faça login aqui</a>
                    </div>
                <?php endif; ?>
            </form>
        </section>

        <aside class="coluna-direita">
            <div class="card-resumo">
                <div class="detalhes-resumo">
                    <strong>Vantagens da Plataforma</strong>
                    <p>Após o cadastro, você terá acesso ao Portal do Profissional onde poderá:</p>
                    <ul style="margin-top: 10px; padding-left: 20px; color: #ffffff; font-size: 0.9rem; line-height: 1.6;">
                        <li>Cadastrar e divulgar todos os seus serviços;</li>
                        <li>Receber pedidos de orçamentos de clientes;</li>
                        <li>Acompanhar o status dos seus atendimentos.</li>
                    </ul>
                </div>
            </div>
        </aside>
    </main>

</body>
</html>