<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/init.php';
require_once __DIR__ . '/banco/database.php';

/** @var PDO $pdo */

$tipo_usuario = strtoupper($_SESSION['tipo_usuario'] ?? '');
$id_usuario = (int) ($_SESSION['id_usuario'] ?? 0);

// Redireciona se não estiver logado
if (!$id_usuario) {
    header("Location: login_cliente.php?erro=acesso_negado");
    exit;
}

$pedidos = [];

try {
    // Se for CLIENTE, vê os pedidos que ele solicitou. Se for PRESTADOR, vê os pedidos direcionados a ele.
    if ($tipo_usuario === 'CLIENTE') {
        $sql = "SELECT * FROM solicitacao WHERE id_cliente = :id ORDER BY id_solicitacao DESC";
    } elseif ($tipo_usuario === 'PRESTADOR') {
        // Exemplo: o prestador vê agendamentos atrelados aos serviços dele (ajuste conforme a modelagem exata do seu banco)
        $sql = "SELECT s.* FROM solicitacao s 
                INNER JOIN agendamento a ON s.id_solicitacao = a.id_solicitacao 
                WHERE a.id_prestador = :id ORDER BY s.id_solicitacao DESC";
    } else {
        // Admin não tem painel de pedidos próprio, volta para registros
        header("Location: registros.php");
        exit;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id_usuario]);
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
    <link rel="stylesheet" href="css/global.css">
</head>
<body class="fundo-claro">

    <header class="header-simples">
        <div class="header-container">
            <h1 class="logo-texto">HomeServices</h1>
            <nav class="menu-topo">
                <?php if ($tipo_usuario === 'CLIENTE'): ?>
                    <a href="buscar.php">Buscar Serviços</a>
                    <a href="pedidos.php">Meus Pedidos</a>
                    <a href="acompanhar_pedido.php">Acompanhar Pedido</a>
                <?php elseif ($tipo_usuario === 'PRESTADOR'): ?>
                    <a href="pedidos.php">Meus Serviços e Pedidos</a>
                    <a href="cadastro_servicos.php">Adicionar Serviço</a>
                <?php endif; ?>
                <a href="logout.php">Sair</a>
            </nav>
        </div>
    </header>

    <main class="container-pedidos">
        <h2>Meus Pedidos</h2>
        <p class="subtitulo">Acompanhe os serviços e orçamentos</p>

        <?php if (isset($_GET['sucesso']) && $_GET['sucesso'] == '1'): ?>
            <div class="alerta-sucesso">
                Orçamento enviado com sucesso! Ele já está listado na tabela abaixo.
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['sucesso']) && $_GET['sucesso'] == 'servico_criado'): ?>
            <div class="alerta-sucesso">
                Novo serviço adicionado ao seu perfil com sucesso!
            </div>
        <?php endif; ?>

        <?php if (empty($pedidos)): ?>
            <div class="vazio-box">
                <p>Nenhum serviço ou pedido encontrado no momento.</p>
                <?php if ($tipo_usuario === 'CLIENTE'): ?>
                    <a href="buscar.php" class="btn-solido btn-novo-pedido">Buscar profissionais agora</a>
                <?php endif; ?>
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