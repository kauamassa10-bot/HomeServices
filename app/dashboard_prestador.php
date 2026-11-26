<?php
require_once __DIR__ . '/init.php';

if (!isset($_SESSION['id_usuario']) || strtoupper($_SESSION['tipo_usuario'] ?? '') !== 'PRESTADOR') {
    header("Location: login_profissional.php?erro=acesso_negado");
    exit;
}

$idPrestador = (int) $_SESSION['id_usuario'];

$usuarioPrestador = read($pdo, 'usuario', 'id_usuario = ?', [$idPrestador]);
$nomeCompleto = $usuarioPrestador ? $usuarioPrestador['nome_usuario'] : 'Profissional';
$partesDoNome = explode(' ', trim($nomeCompleto));
$primeiroNome = $partesDoNome[0];
$iniciais     = pegarIniciais($nomeCompleto);

$listaAgendamentos = readAll($pdo, 'agendamento', 'id_prestador = ?', [$idPrestador]);
$listaPropostas    = readAll($pdo, 'proposta', 'id_prestador = ?', [$idPrestador]);
$listaAvaliacoes   = readAll($pdo, 'avaliacao', 'id_avaliado = ?', [$idPrestador]);

$totalPropostas = count($listaPropostas);

$somaDasNotas = 0;
foreach ($listaAvaliacoes as $avaliacao) {
    $somaDasNotas += (int) $avaliacao['numero_nota'];
}
$notaMedia = count($listaAvaliacoes) > 0 ? ($somaDasNotas / count($listaAvaliacoes)) : 0;

$totalConcluidos = 0;
$faturamentoMes  = 0;
$mesAtual        = date('Y-m');

foreach ($listaAgendamentos as $agendamento) {
    $st = strtoupper($agendamento['status_agendamento'] ?? '');
    if ($st !== 'CONCLUIDO' && $st !== 'AVALIADO') {
        continue;
    }

    $totalConcluidos++;

    $mesDoAgendamento = substr($agendamento['data_agendamento'], 0, 7);
    if ($mesDoAgendamento === $mesAtual) {
        $propostaDoAgendamento = read($pdo, 'proposta', 'id_proposta = ?', [$agendamento['id_proposta']]);
        if ($propostaDoAgendamento) {
            $faturamentoMes += (float) $propostaDoAgendamento['valor_proposta'];
        }
    }
}

$proximosAgendamentos = [];
foreach ($listaAgendamentos as $agendamento) {
    $status = strtoupper($agendamento['status_agendamento'] ?? '');
    if (in_array($status, ['CONCLUIDO', 'AVALIADO', 'CANCELADO'], true)) {
        continue;
    }

    $cliente     = read($pdo, 'usuario', 'id_usuario = ?', [$agendamento['id_cliente']]);
    $solicitacao = read($pdo, 'solicitacao', 'id_solicitacao = ?', [$agendamento['id_solicitacao']]);

    $agendamento['nome_cliente'] = $cliente ? $cliente['nome_usuario'] : 'Cliente';
    $agendamento['desc_endereco_atendimento'] = $solicitacao ? $solicitacao['desc_endereco_atendimento'] : '';
    $proximosAgendamentos[] = $agendamento;
}

usort($proximosAgendamentos, static function ($a, $b) {
    return strcmp($a['data_agendamento'] . $a['hora_agendamento'], $b['data_agendamento'] . $b['hora_agendamento']);
});
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard do Prestador - HomeServices</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/global.css">
</head>
<body>

    <header class="cabecalho">
        <div class="logo">HomeServices</div>
        <nav class="links">
            <a href="dashboard_prestador.php" class="active">Visão Geral</a>
            <a href="mural_solicitacoes.php">Solicitações</a>
            <a href="agendamentos_prestador.php">Meus Agendamentos</a>
            <a href="meus_servicos_lista.php">Meus Serviços</a>
            <a href="perfil.php" class="foto" title="<?= htmlspecialchars($nomeCompleto) ?>"><?= htmlspecialchars($iniciais) ?></a>
        </nav>
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
                                <a href="agendamentos_prestador.php" class="btn-acao sem-sublinhado">Ver Detalhes / Atualizar Status</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="mensagem-vazia">Nenhum agendamento próximo no momento.</p>
                <?php endif; ?>
            </div>
        </div>
    </main>

</body>
</html>
