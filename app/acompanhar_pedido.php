<?php
require_once __DIR__ . '/banco/crud.php';
require_once __DIR__ . 'init.php';
require_once __DIR__ . 'database.php';

$id_agendamento = $_GET['id'] ?? 3001; 
$agendamento = read($pdo, 'agendamento', "id_agendamento = $id_agendamento");

$solicitacao = read($pdo, 'solicitacao', "id_solicitacao = {$agendamento['id_solicitacao']}");
$prestador   = read($pdo, 'usuario', "id_usuario = {$agendamento['id_prestador']}");
$cliente     = read($pdo, 'usuario', "id_usuario = {$agendamento['id_cliente']}");

$iniciaisAvatar = pegarIniciais($cliente['nome_usuario'] ?? 'Cliente');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
}
?>
<!DOCTYPE html>
<html lang="pt-br"> 
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acompanhar Pedido</title>
    <link rel="stylesheet" href="css/style.css">
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

    <main class="conteudo">
        <div class="titulo">
            <h1>Pedido #<?= $agendamento['id_agendamento'] ?></h1>
            <p><?= htmlspecialchars($solicitacao['desc_titulo'] ?? 'Serviço') ?> com <?= htmlspecialchars($prestador['nome_usuario'] ?? 'Profissional') ?></p>
        </div>

        <div class="etapas">
            <div class="etapa ativo">1. Solicitado</div>
            <div class="etapa ativo">2. Confirmado</div>
            <div class="etapa ativo">3. A caminho</div>
            <div class="etapa">4. Em execução</div>
            <div class="etapa">5. Concluído</div>
        </div>

        <div class="caixa">
            <p>Status atual: <strong><?= htmlspecialchars($agendamento['status_agendamento']) ?></strong></p>
        </div>
    </main>

</body>
</html>