<?php
require_once __DIR__ . "/init.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_agendamento = filter_input(INPUT_POST, 'id_agendamento', FILTER_SANITIZE_NUMBER_INT);
    
    $data_escolhida = $_POST['data_escolhida'] ?? '';
    $hora_escolhida = $_POST['hora_escolhida'] ?? '';
    $observacoes = $_POST['observacoes'] ?? '';

    if (!$id_agendamento || !$data_escolhida || !$hora_escolhida) {
        header("Location: agendamento.php?id=" . $id_agendamento . "&erro=dados_invalidos");
        exit;
    }

    $dados = [
        'data_agendamento' => trim($data_escolhida),
        'hora_agendamento' => trim($hora_escolhida),
        'observacoes' => trim($observacoes),
        'status_agendamento' => 'confirmado'
    ];

    update($pdo, 'agendamento', $dados, 'id_agendamento = ?', [$id_agendamento]);

    header("Location: pedidos.php?sucesso=1");
    exit;
}

header("Location: buscar.php");
exit;