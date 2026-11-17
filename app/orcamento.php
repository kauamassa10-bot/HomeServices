<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/init.php';



// Apenas CLIENTES podem solicitar orçamentos
if (!isset($_SESSION['id_usuario']) || strtoupper($_SESSION['tipo_usuario'] ?? '') !== 'CLIENTE') {
    header("Location: login_cliente.php?erro=acesso_negado");
    exit;
}

$servicoDesejado = read($pdo, 'servico', 'id_servico = ?', [$_GET['id'] ?? 0]);

$mensagem = '';
$id_cliente = (int) $_SESSION['id_usuario'];

$id_servico = (int) ($_GET['id'] ?? 0);
if ($id_servico <= 0) {
    $mensagem = "<div class='erro'>Serviço inválido. Por favor, tente novamente.</div>";
} else {
    if (!$id_servico) {
        $mensagem = "<div class='erro'>Serviço não encontrado. Por favor, tente novamente.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Novo Pedido - HomeServices</title>
    <link rel="stylesheet" href="css/orcamento.css">
    <link rel="stylesheet" href="css/global.css">
</head>

<body class="fundo-claro">

    <header class="header-simples">
        <div class="header-container">
            <h1 class="logo-texto">HomeServices</h1>
            <nav class="menu-topo">
                <a href="buscar.php">Buscar Serviços</a>
                <a href="pedidos.php">Meus Pedidos</a>
                <a href="acompanhar_pedido.php">Acompanhar Pedido</a>
                <a href="logout.php">Sair</a>
            </nav>
        </div>
    </header>

    <main class="layout-duas-colunas">
        <section class="coluna-esquerda">
            <h2>Solicitar Orçamento</h2>
            <p class="subtitulo">Preencha os detalhes do que você precisa</p>

            <?= $mensagem ?>

            <form method="POST" action="" class="form-pedido-leitura">

                <div class="campo-simples">
                    <label for="servDesejado">Nome do Serviço:</label>
                    <input type="text" name="servDesejado" value="<?= htmlspecialchars($servicoDesejado['nome_servico'] ?? '') ?>" required readonly>
                </div>

                <div class="campo-simples">
                    <textarea name="descricao" placeholder="Descreva o serviço desejado detalhadamente..." required></textarea>
                </div>

                <div class="linha-dupla">
                    <div class="campo-simples">
                        <input type="text" name="endereco" placeholder="Endereço / CEP" required>
                    </div>
                </div>

                <div class="botoes-rodape">
                    <button type="submit" class="btn-solido"><a href="agendamento.php">Prosseguir para agendamento</a></button>
                </div>
            </form>
        </section>

        <aside class="coluna-direita">
            <div class="card-resumo">
                <div class="detalhes-resumo">
                    <strong>Informações</strong>
                    <p>O seu pedido será enviado para profissionais da sua região.</p>
                    <p>Orçamento gratuito e sem compromisso.</p>
                </div>
            </div>
        </aside>
    </main>

</body>

</html>
