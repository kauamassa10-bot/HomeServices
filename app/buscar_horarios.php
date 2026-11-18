<?php
require_once __DIR__ . "/init.php";

header('Content-Type: application/json');

$data = $_GET['data'] ?? '';
$id_prestador = $_GET['prestador'] ?? '';

if (!$data || !$id_prestador) {
    echo json_encode(['horarios' => []]);
    exit;
}

$horariosDeTrabalho = ['08:00', '09:00', '10:00', '11:00', '13:00', '14:00', '15:00', '16:00', '17:00'];

$agendamentos = readAll($pdo, 'agendamento', 'data_agendamento = ? AND id_prestador = ?', [$data, $id_prestador]);
$horariosOcupados = array_column($agendamentos, 'hora_agendamento');

$resultado = [];

foreach ($horariosDeTrabalho as $hora) {
    $estaOcupado = in_array($hora, $horariosOcupados); 
    
    $resultado[] = [
        'hora' => $hora,
        'ocupado' => $estaOcupado
    ];
}

echo json_encode(['horarios' => $resultado]);