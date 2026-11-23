<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/init.php';

/** @var PDO $pdo */

if (!isset($_SESSION['id_usuario']) || strtoupper($_SESSION['tipo_usuario'] ?? '') !== 'PRESTADOR') {
    header("Location: login_profissional.php?erro=acesso_negado");
    exit;
}

$idPrestador = (int) $_SESSION['id_usuario'];
$nomeCompleto = $_SESSION['nome_usuario'] ?? 'Profissional';
$partesNome = explode(' ', trim($nomeCompleto));
$primeiroNome = $partesNome[0];
$iniciais = strtoupper(substr($primeiroNome, 0, 1) . (isset($partesNome[1]) ? substr($partesNome[1], 0, 1) : ''));

// 1. Total de serviços concluídos
$stmtConcluidos = $pdo->prepare("SELECT COUNT(*) FROM agendamento WHERE id_prestador = :id AND status_agendamento = 'CONCLUIDO'");
$stmtConcluidos->execute([':id' => $idPrestador]);
$totalConcluidos = (int) $stmtConcluidos->fetchColumn();

// 2. Total de propostas enviadas
$stmtPropostas = $pdo->prepare("SELECT COUNT(*) FROM proposta WHERE id_prestador = :id");
$stmtPropostas->execute([':id' => $idPrestador]);
$totalPropostas = (int) $stmtPropostas->fetchColumn();

// 3. Faturamento do mês (agendamentos concluídos no mês atual)
$stmtFaturamento = $pdo->prepare("
    SELECT COALESCE(SUM(p.valor_proposta), 0)
    FROM agendamento a
    INNER JOIN proposta p ON p.id_proposta = a.id_proposta
    WHERE a.id_prestador = :id
      AND a.status_agendamento = 'CONCLUIDO'
      AND MONTH(a.data_agendamento) = MONTH(CURRENT_DATE())
      AND YEAR(a.data_agendamento) = YEAR(CURRENT_DATE())
");
$stmtFaturamento->execute([':id' => $idPrestador]);
$faturamentoMes = (float) $stmtFaturamento->fetchColumn();

// 4. Nota média das avaliações
$stmtNota = $pdo->prepare("SELECT COALESCE(AVG(numero_nota), 0) FROM avaliacao WHERE id_avaliado = :id");
$stmtNota->execute([':id' => $idPrestador]);
$notaMedia = (float) $stmtNota->fetchColumn();

// 5. Próximos agendamentos (não concluídos e não cancelados)
$stmtAgendamentos = $pdo->prepare("
    SELECT a.id_agendamento, a.data_agendamento, a.hora_agendamento, a.status_agendamento,
           u.nome_usuario AS nome_cliente,
           s.desc_endereco_atendimento
    FROM agendamento a
    INNER JOIN usuario u ON u.id_usuario = a.id_cliente
    INNER JOIN solicitacao s ON s.id_solicitacao = a.id_solicitacao
    WHERE a.id_prestador = :id
      AND a.status_agendamento NOT IN ('CONCLUIDO', 'CANCELADO')
    ORDER BY a.data_agendamento ASC, a.hora_agendamento ASC
");
$stmtAgendamentos->execute([':id' => $idPrestador]);
$proximosAgendamentos = $stmtAgendamentos->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard do Prestador - HomeServices</title>
    <link rel="stylesheet" href="css/global.css"> 
    <link rel="stylesheet" href="css/dashboard.css"> 
</head>
<body>

    <header class="dashboard-header">
        <div class="dashboard-logo">HomeServices</div>
        <nav class="dashboard-nav">
            <a href="dashboard_prestador.php" class="active">Visão Geral</a>
            <a href="mural_solicitacoes.php">Solicitações</a>
            <a href="agendamentos_prestador.php">Meus Agendamentos</a>
            <a href="meus_servicos_lista.php">Meus Serviços</a>
        </nav>
        <div class="avatar" title="<?= htmlspecialchars($nomeCompleto) ?>">
            <a href="perfil.php"><?= htmlspecialchars($iniciais) ?></a>
        </div>
    </header>

    <main class="dashboard-main">
        <h2 class="saudacao">Olá, <?= htmlspecialchars($primeiroNome) ?>. Visão geral do mês</h2>

        <div class="cards-grid">
            <div class="card-resumo card-verde">
                <span class="titulo-card">Serviços Concluídos</span>
                <span class="valor-card"><?= $totalConcluidos ?></span>
            </div>
            <div class="card-resumo card-azul">
                <span class="titulo-card">Propostas Enviadas</span>
                <span class="valor-card"><?= $totalPropostas ?></span>
            </div>
            <div class="card-resumo card-laranja">
                <span class="titulo-card">Faturamento do Mês</span>
                <span class="valor-card">R$ <?= number_format($faturamentoMes, 2, ',', '.') ?></span>
            </div>
            <div class="card-resumo card-amarelo">
                <span class="titulo-card">Nota Média</span>
                <span class="valor-card"><?= number_format($notaMedia, 1, ',', '.') ?> de 5 (estrelas)</span>
            </div>
        </div>

        <div class="agendamentos-container">
            <h3>Próximos Agendamentos</h3>

            <div class="lista-agendamentos">
                <?php if (count($proximosAgendamentos) > 0): ?>
                    <?php foreach ($proximosAgendamentos as $ag): ?>
                        <?php
                            $dataFormatada = date('d/m', strtotime($ag['data_agendamento']));
                            $horaFormatada = substr($ag['hora_agendamento'], 0, 5);
                            $status = strtoupper($ag['status_agendamento']);
                            $classeBadge = 'badge-pendente';
                            $textoBadge = 'Pendente';

                            if ($status === 'CONFIRMADO' || $status === 'AGENDADO') {
                                $classeBadge = 'badge-confirmado';
                                $textoBadge = 'Confirmado';
                            } elseif ($status === 'EM_EXECUCAO' || $status === 'A_CAMINHO') {
                                $classeBadge = 'badge-execucao';
                                $textoBadge = ($status === 'A_CAMINHO') ? 'A Caminho' : 'Em Execução';
                            }
                        ?>
                        <div class="agendamento-item">
                            <div class="agendamento-info">
                                <strong><?= htmlspecialchars($dataFormatada) ?> · <?= htmlspecialchars($horaFormatada) ?></strong>
                                <span>Cliente: <?= htmlspecialchars($ag['nome_cliente']) ?></span>
                                <span><?= htmlspecialchars($ag['desc_endereco_atendimento']) ?></span>
                            </div>
                            <div class="agendamento-acoes">
                                <span class="badge <?= $classeBadge ?>"><?= $textoBadge ?></span>
                                <a href="agendamentos_prestador.php" class="btn-acao" style="text-decoration: none;">Ver Detalhes / Atualizar Status</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color: #666; padding: 15px 0;">Nenhum agendamento próximo no momento.</p>
                <?php endif; ?>
            </div>
        </div>
    </main>

</body>
</html>
