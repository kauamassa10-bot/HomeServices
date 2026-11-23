<?php
require_once __DIR__ . '/init.php';

if (!isset($_SESSION['id_usuario']) || strtoupper($_SESSION['tipo_usuario'] ?? '') !== 'CLIENTE') {
    header('Location: login_cliente.php?erro=acesso_negado');
    exit;
}

// Aceita ?id_servico= (novo) e ?id= (link atual do buscar.php)
$idServico = (int) ($_GET['id_servico'] ?? $_GET['id'] ?? 0);

$servico   = $idServico > 0 ? read($pdo, 'servico', 'id_servico = ? AND status_ativo = 1', [$idServico]) : false;
$prestador = $servico ? read($pdo, 'usuario', 'id_usuario = ?', [$servico['id_prestador']]) : false;
$categoria = $servico ? read($pdo, 'categoria', 'id_categoria = ?', [$servico['id_categoria']]) : false;

$prestadorValido = $prestador && strtoupper((string) $prestador['tipo_usuario']) === 'PRESTADOR';

if (!$servico || !$prestadorValido || !$categoria) {
    header('Location: buscar.php');
    exit;
}

// Mensagem de erro e dados digitados, enviados pelo processar_orcamento.php
$mensagem = $_SESSION['orcamento_erro'] ?? '';
$antigos  = $_SESSION['orcamento_old'] ?? [];
unset($_SESSION['orcamento_erro'], $_SESSION['orcamento_old']);

$descricao       = $antigos['desc_solicitacao'] ?? '';
$endereco        = $antigos['desc_endereco_atendimento'] ?? '';

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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

            <?php if ($mensagem !== ''): ?>
                <p class="subtitulo"><?= htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>

            <form method="POST" action="processa_orcamento.php" class="form-pedido-leitura">
                <input type="hidden" name="id_servico" value="<?= (int) $servico['id_servico'] ?>">

                <div class="campo-simples">
                    <input type="text" value="<?= htmlspecialchars($servico['nome_servico'], ENT_QUOTES, 'UTF-8') ?>" readonly>
                </div>

                <div class="campo-simples">
                    <textarea name="desc_solicitacao" placeholder="Descreva o serviço desejado detalhadamente..." required><?= htmlspecialchars($descricao, ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>

                <div class="linha-dupla">
                    <div class="campo-simples">
                        <input type="text" name="desc_endereco_atendimento" placeholder="Endereço / CEP" value="<?= htmlspecialchars($endereco, ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                </div>

                <div class="botoes-rodape">
                <button type="submit" class="btn-solido" style="width: 100%;">Prosseguir para agendamento</button>
                </div>
            </form>
        </section>

        <aside class="coluna-direita">
            <div class="card-resumo">
                <div class="detalhes-resumo">
                    <strong><?= htmlspecialchars($prestador['nome_usuario'], ENT_QUOTES, 'UTF-8') ?></strong>
                    <p><?= htmlspecialchars($servico['nome_servico'], ENT_QUOTES, 'UTF-8') ?></p>
                    <p><?= htmlspecialchars($categoria['nome_categoria'], ENT_QUOTES, 'UTF-8') ?></p>
                    <p><?= htmlspecialchars($servico['desc_servico'], ENT_QUOTES, 'UTF-8') ?></p>
                    <p>A partir de R$ <?= number_format((float) $servico['valor_base'], 2, ',', '.') ?></p>
                    <p><?= htmlspecialchars($prestador['telefone'], ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            </div>
        </aside>
    </main>

</body>

</html>