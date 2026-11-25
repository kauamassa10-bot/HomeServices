<?php
require_once __DIR__ . '/init.php';

if (!isset($_SESSION['id_usuario']) || strtoupper($_SESSION['tipo_usuario'] ?? '') !== 'CLIENTE') {
    header("Location: login_cliente.php?erro=acesso_negado");
    exit;
}

$idCliente = (int) $_SESSION['id_usuario'];
$dadosCliente = read($pdo, 'usuario', 'id_usuario = ?', [$idCliente]) ?: [];

$nomeCompleto = $dadosCliente['nome_usuario'] ?? ($_SESSION['nome_usuario'] ?? 'Cliente');
$emailUsuario = !empty($dadosCliente['email']) ? $dadosCliente['email'] : 'Não informado';
$telefoneUsuario = !empty($dadosCliente['telefone']) ? $dadosCliente['telefone'] : 'Não informado';
$iniciais = pegarIniciais($nomeCompleto);

$solicitacoes = readAll($pdo, 'solicitacao', 'id_cliente = ?', [$idCliente]);
$agendamentos = readAll($pdo, 'agendamento', 'id_cliente = ?', [$idCliente]);
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil do Cliente - HomeServices</title>
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/perfil.css">
</head>

<body>

    <header class="dashboard-header">
        <div class="dashboard-logo">HomeServices</div>
        <nav class="dashboard-nav">
            <a href="buscar.php">Buscar Serviços</a>
            <a href="pedidos.php">Meus Pedidos</a>
            <div class="avatar" title="<?= htmlspecialchars($nomeCompleto) ?>">
            <?= htmlspecialchars($iniciais) ?>
        </div>
        </nav>
        
    </header>

    <main class="perfil-main">
        <h2 class="titulo-pagina">Meu Perfil</h2>

        <div class="perfil-container">

            <aside class="card-perfil">
                <div class="avatar-grande">
                    <?= htmlspecialchars($iniciais) ?>
                </div>

                <div class="info-grupo">
                    <strong>Nome</strong>
                    <p><?= htmlspecialchars($nomeCompleto) ?></p>
                </div>

                <div class="info-grupo">
                    <strong>E-mail</strong>
                    <p><?= htmlspecialchars($emailUsuario) ?></p>
                </div>

                <div class="info-grupo">
                    <strong>Telefone</strong>
                    <p><?= htmlspecialchars($telefoneUsuario) ?></p>
                </div>

                <div class="info-grupo">
                    <strong>Tipo de conta</strong>
                    <p>Cliente</p>
                </div>

                <a href="logout.php" class="btn-editar-perfil">Sair da Conta</a>
            </aside>

            <section class="avaliacoes-section">
                <h3>Histórico de Serviços · <?= count($solicitacoes) ?> solicitação(ões) · <?= count($agendamentos) ?> agendamento(s)</h3>

                <div class="lista-avaliacoes">
                    <?php if (count($agendamentos) > 0): ?>
                        <?php foreach ($agendamentos as $ag): ?>
                            <?php
                                $prestador = read($pdo, 'usuario', 'id_usuario = ?', [$ag['id_prestador']]) ?: [];
                                $solicitacao = read($pdo, 'solicitacao', 'id_solicitacao = ?', [$ag['id_solicitacao']]) ?: [];
                                $dataAg = !empty($ag['data_agendamento']) ? date('d/m/Y', strtotime($ag['data_agendamento'])) : '';
                                $horaAg = substr($ag['hora_agendamento'] ?? '', 0, 5);
                            ?>
                            <div class="card-avaliacao">
                                <div class="header-avaliacao">
                                    Pedido #<?= (int) $ag['id_agendamento'] ?> · <?= htmlspecialchars($solicitacao['desc_titulo'] ?? 'Serviço') ?> · <?= $dataAg ?> às <?= $horaAg ?>
                                </div>
                                <p class="texto-avaliacao">
                                    Profissional: <?= htmlspecialchars($prestador['nome_usuario'] ?? 'Profissional') ?> · Status: <strong><?= htmlspecialchars($ag['status_agendamento'] ?? '') ?></strong>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="mensagem-vazia">Nenhum serviço agendado ainda.</p>
                    <?php endif; ?>
                </div>
            </section>

        </div>
    </main>

</body>

</html>
