<?php
require_once __DIR__ . '/init.php';

if (!isset($_SESSION['id_usuario']) || strtoupper($_SESSION['tipo_usuario'] ?? '') !== 'CLIENTE') {
    header("Location: login_cliente.php?erro=acesso_negado");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idCliente     = (int) $_SESSION['id_usuario'];
    $idAgendamento = (int) ($_POST['id_agendamento'] ?? 0);
    $nota          = (int) ($_POST['nota'] ?? 0);
    $comentario    = trim($_POST['comentario'] ?? '');

    if ($idAgendamento > 0 && $nota >= 1 && $nota <= 5) {
        $agendamento = read($pdo, 'agendamento', 'id_agendamento = ? AND id_cliente = ?', [$idAgendamento, $idCliente]);

        if ($agendamento && strtoupper($agendamento['status_agendamento'] ?? '') === 'CONCLUIDO') {
            create($pdo, 'avaliacao', [
                'id_avaliador'    => $idCliente,
                'id_avaliado'     => (int) $agendamento['id_prestador'],
                'id_agendamento'  => $idAgendamento,
                'numero_nota'     => $nota,
                'desc_comentario' => $comentario,
                'data_avaliacao'  => date('Y-m-d'),
            ]);

            update(
                $pdo,
                'agendamento',
                ['status_agendamento' => 'AVALIADO'],
                'id_agendamento = ? AND id_cliente = ?',
                [$idAgendamento, $idCliente]
            );

            update(
                $pdo,
                'solicitacao',
                ['status_solicitacao' => 'AVALIADO'],
                'id_solicitacao = ?',
                [$agendamento['id_solicitacao']]
            );
        }
    }
}

header("Location: pedidos.php");
exit;
