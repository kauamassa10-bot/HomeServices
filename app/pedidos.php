<?php
require_once __DIR__ . '/init.php';

$id_cliente = 1; // Mesmo ID fixo do cliente logado
$pedidos = [];

try {
    $sql = "SELECT * FROM solicitacao WHERE id_cliente = :id_cliente ORDER BY id_solicitacao DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id_cliente' => $id_cliente]);
    $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Erro ao carregar pedidos: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Meus Pedidos - HomeServices</title>
    <link rel="stylesheet" href="css/orcamento.css">
    <link rel="stylesheet" href="css/pedidos.css">
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

    <main class="container-pedidos">
        <h2>Meus Pedidos</h2>
        <p class="subtitulo">Acompanhe os orçamentos que você solicitou</p>

        <?php if (isset($_GET['sucesso'])): ?>
            <div class="alerta-sucesso">
                Orçamento enviado com sucesso! Ele já está listado na tabela abaixo.
            </div>
        <?php endif; ?>

        <?php if (empty($pedidos)): ?>
            <div class="vazio-box">
                <p>Você ainda não solicitou nenhum serviço.</p>
                <a href="orcamento.php" class="btn-solido btn-novo-pedido">Solicitar um serviço agora</a>
            </div>
        <?php else: ?>
            <table class="tabela-pedidos">
                <thead>
                    <tr>
                        <th>#ID</th>
                        <th>Serviço</th>
                        <th>Descrição</th>
                        <th>Endereço / CEP</th>
                        <th>Data Desejada</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pedidos as $pedido): ?>
                        <tr>
                            <td><?= htmlspecialchars($pedido['id_solicitacao']) ?></td>
                            <td><strong><?= htmlspecialchars($pedido['desc_titulo']) ?></strong></td>
                            <td><?= htmlspecialchars($pedido['desc_solicitacao']) ?></td>
                            <td><?= htmlspecialchars($pedido['desc_endereco_atendimento']) ?></td>
                            <td><?= date('d/m/Y', strtotime($pedido['data_solicitacao'])) ?></td>
                            <td><?= htmlspecialchars(ucfirst($pedido['status_solicitacao'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </main>

</body>
</html>