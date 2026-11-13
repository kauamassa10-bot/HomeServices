<?php
require_once __DIR__ . '/init.php';

$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome_usuario']);
    $email = trim($_POST['email']);
    $telefone = trim($_POST['telefone']);
    $senha = trim($_POST['senha']);

    $sql = "INSERT INTO usuario (nome_usuario, email, senha, telefone, tipo_usuario, status_ativo, data_cadastro) 
            VALUES (:nome, :email, :senha, :telefone, 'CLIENTE', 1, NOW())";

    $stmt = $pdo->prepare($sql);

    try {
        $stmt->execute([
            ':nome' => $nome,
            ':email' => $email,
            ':senha' => $senha,
            ':telefone' => $telefone
        ]);

        header("Location: registros.php?sucesso=cliente");
        exit;
    } catch (PDOException $e) {
        $mensagem = "<div class='erro'>Erro ao cadastrar cliente: " . $e->getMessage() . "</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Cliente - HomeServices</title>
    <link rel="stylesheet" href="css/orcamento.css">
    <link rel="stylesheet" href="css/cadastros.css">
</head>
<body class="fundo-claro">

    <header class="header-simples">
        <div class="header-container">
            <h1 class="logo-texto">HomeServices</h1>
            <nav class="menu-topo">
                <a href="orcamento.php">Orçamento</a>
                <a href="pedidos.php">Meus pedidos</a>
                <a href="cadastro_cliente.php">Novo Cliente</a>
                <a href="cadastro_profissional.php">Novo Profissional</a>
                <a href="cadastro_servicos.php">Novo Serviço</a>
                <a href="registros.php">Registros</a>
            </nav>
        </div>
    </header>

    <main class="layout-duas-colunas">
        <section class="coluna-esquerda">
            <h2>Cadastrar Cliente</h2>
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
                    <button type="submit" class="btn-solido" style="width: 100%;">Salvar Cliente</button>
                </div>
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