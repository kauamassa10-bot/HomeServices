<?php
require_once __DIR__ . "/init.php";

$id_agendamento = $_GET['id'] ?? null;

if ($id_agendamento) {
    $agendamento = read($pdo, 'agendamento', 'id_agendamento = ?', [$id_agendamento]);
} else {
    $agendamento = read($pdo, 'agendamento', '1=1 ORDER BY id_agendamento DESC LIMIT 1');
}

if (!$agendamento) {
    echo "Nenhum agendamento encontrado no banco de dados.";
    exit;
}

$solicitacao = read($pdo, 'solicitacao', 'id_solicitacao = ?', [$agendamento['id_solicitacao']]);
$prestador   = read($pdo, 'usuario', 'id_usuario = ?', [$agendamento['id_prestador']]);
$cliente     = read($pdo, 'usuario', 'id_usuario = ?', [$agendamento['id_cliente']]);
$proposta    = read($pdo, 'proposta', 'id_proposta = ?', [$agendamento['id_proposta']]);
$iniciaisAvatar = pegarIniciais($cliente['nome_usuario']);

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendamento - HomeServices</title>
</head>

<body>
    <header class="cabecalho">
        <div class="logo">HomeServices</div>
        <nav class="links">
            <a href="#">Categorias</a>
            <a href="#">Meus pedidos</a>
            <a href="#">Ajuda</a>
            <div class="foto"><?= $iniciaisAvatar ?></div>
        </nav>
    </header>
    
    <main>
        <div>
            
        </div>
    </main>

</body>

</html>