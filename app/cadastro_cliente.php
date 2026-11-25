<?php
require_once __DIR__ . '/init.php';

$mensagem = '';
$isAdmin = (strtoupper($_SESSION['tipo_usuario'] ?? '') === 'ADMIN');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome     = trim($_POST['nome_usuario'] ?? $_POST['nome'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $senha    = trim($_POST['senha'] ?? '');

    if ($nome === '' || $email === '' || $senha === '') {
        $mensagem = 'Preencha todos os campos obrigatórios.';
    } else {
        // Verifica se o e-mail já está cadastrado usando read() do CRUD
        $jaExiste = read($pdo, 'usuario', 'email = ?', [$email]);

        if ($jaExiste) {
            $mensagem = 'Este e-mail já está cadastrado. Tente fazer login ou use outro e-mail.';
        } else {
            try {
                create($pdo, 'usuario', [
                    'nome_usuario'  => $nome,
                    'email'         => $email,
                    'senha'         => $senha,
                    'telefone'      => $telefone,
                    'tipo_usuario'  => 'CLIENTE',
                    'status_ativo'  => 1,
                    'data_cadastro' => date('Y-m-d'),
                ]);

                if ($isAdmin) {
                    header("Location: registros.php?sucesso=cliente");
                } else {
                    header("Location: login_cliente.php?sucesso=cadastro");
                }
                exit;
            } catch (Throwable $e) {
                $mensagem = 'Erro ao cadastrar cliente. Tente novamente.';
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
    <title>Cadastrar Cliente - HomeServices</title>
    <link rel="stylesheet" href="css/orcamento.css">
    <link rel="stylesheet" href="css/login.css">
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

            <?php if ($mensagem !== ''): ?>
                <div class="alerta-erro"><?= htmlspecialchars($mensagem) ?></div>
            <?php endif; ?>

            <form method="POST" action="" class="form-login" autocomplete="off">
                <div class="grupo-campo">
                    <label for="nome_usuario">Nome Completo</label>
                    <input type="text" id="nome_usuario" name="nome_usuario" placeholder="O seu nome" required>
                </div>

                <div class="grupo-campo">
                    <label for="email">E-mail</label>
                    <input type="email" id="email" name="email" placeholder="seu@email.com" required>
                </div>

                <div class="grupo-campo">
                    <label for="telefone">Telefone</label>
                    <input type="tel" id="telefone" name="telefone" placeholder="(11) 99999-9999" required>
                </div>

                <div class="grupo-campo">
                    <label for="senha">Senha</label>
                    <input type="password" id="senha" name="senha" placeholder="Crie uma senha" required minlength="6">
                </div>

                <div class="botoes-rodape">
                    <button type="submit" class="btn-solido btn-largura-total">
                        <?= $isAdmin ? 'Salvar Cliente' : 'Concluir Cadastro' ?>
                    </button>
                </div>

                <?php if (!$isAdmin): ?>
                    <p class="subtitulo">
                        Já possui uma conta? <a href="login_cliente.php">Faça login aqui</a>
                    </p>
                <?php endif; ?>
            </form>
        </section>
    </main>
</body>
</html>
