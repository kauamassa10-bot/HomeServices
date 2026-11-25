<?php
require_once __DIR__ . '/init.php';

if (!isset($_SESSION['id_usuario']) || strtoupper($_SESSION['tipo_usuario'] ?? '') !== 'CLIENTE') {
    header("Location: login_cliente.php?erro=acesso_negado");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idCliente     = (int) $_SESSION['id_usuario'];
    $idAgendamento = (int) ($_POST['id_agendamento'] ?? 0);
    $mensagem      = trim($_POST['mensagem'] ?? '');

    if ($idAgendamento > 0 && $mensagem !== '') {
        $agendamento = read($pdo, 'agendamento', 'id_agendamento = ? AND id_cliente = ?', [$idAgendamento, $idCliente]);

        if ($agendamento) {
            $dataHora = date('d/m H:i');
            $novaLinha = "[{$dataHora}] Cliente: {$mensagem}";
            $obsAtual = trim((string) ($agendamento['observacoes'] ?? ''));

            $obsAtualizada = $obsAtual !== '' ? ($obsAtual . "\n" . $novaLinha) : $novaLinha;

            update(
                $pdo,
                'agendamento',
                ['observacoes' => $obsAtualizada],
                'id_agendamento = ? AND id_cliente = ?',
                [$idAgendamento, $idCliente]
            );
        }
    }

    header("Location: acompanhar_pedido.php?id=" . $idAgendamento);
    exit;
}

header("Location: pedidos.php");
exit;
