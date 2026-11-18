<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/init.php';

// Verifica se tem sessão iniciada e se é prestador
if (!isset($_SESSION['id_usuario']) || strtoupper($_SESSION['tipo_usuario'] ?? '') !== 'PRESTADOR') {
    header("Location: login_profissional.php?erro=acesso_negado");
    exit;
}

// Iniciais para o Avatar (Cabeçalho)
$nomeCompleto = $_SESSION['nome_usuario'] ?? 'Profissional';
$partesNome = explode(' ', trim($nomeCompleto));
$primeiroNome = $partesNome[0];
$iniciais = strtoupper(substr($primeiroNome, 0, 1) . (isset($partesNome[1]) ? substr($partesNome[1], 0, 1) : ''));
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Agendamentos - HomeServices</title>
    <!-- Inclui o estilo do cabeçalho global e o específico dos agendamentos -->
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/agendamentos_prestador.css">
</head>

<body>

    <header class="dashboard-header">
        <div class="dashboard-logo">HomeServices</div>
        <nav class="dashboard-nav">
            <a href="dashboard_prestador.php">Visão Geral</a>
            <a href="mural_solicitacoes.php">Solicitações</a>
            <a href="meus_agendamentos.php" class="active">Meus Agendamentos</a> <!-- Ativo aqui -->
            <a href="meus_servicos_lista.php">Meus Serviços</a>
            <a href="perfil.php">Perfil</a>
        </nav>
        <div class="avatar" title="<?= htmlspecialchars($nomeCompleto) ?>">
            <?= htmlspecialchars($iniciais) ?>
        </div>
    </header>

    <main class="agendamentos-main">
        <h2 class="titulo-pagina">Meus Agendamentos</h2>

        <!-- Abas de Filtro -->
        <div class="tabs-container">
            <button class="tab-btn active">Em Aberto</button>
            <button class="tab-btn">A Caminho</button>
            <button class="tab-btn">Em Execução</button>
            <button class="tab-btn">Concluídos</button>
            <button class="tab-btn">Cancelados</button>
        </div>

        <!-- Grid de Agendamentos -->
        <div class="agendamentos-grid">

            <!-- Pedido 1: Pendente (Laranja) -->
            <div class="agendamento-card bg-pendente">
                <div class="info-pedido">
                    <h4>Pedido #1024 · Troca de tomadas</h4>
                    <p>Cliente: Maria Souza · Tel: (11) 98888-1234</p>
                    <p>Rua das Flores, 120, Centro, São Paulo - SP</p>
                    <p>Data/Hora: Qui, 12/11 às 09:00</p>
                    <p>Valor acordado: R$ 180,00</p>
                </div>

                <div class="acoes-pedido">
                    <div class="badge-status-top badge-laranja">Pendente</div>
                    <div class="titulo-status">Status do serviço</div>
                    <div class="botoes-status">
                        <button class="btn-passo laranja-ativo">Confirmar</button>
                        <button class="btn-passo inativo">A Caminho</button>
                        <button class="btn-passo inativo">Em Execução</button>
                        <button class="btn-passo inativo">Concluir</button>
                    </div>
                </div>
            </div>

            <!-- Pedido 2: Em Execução (Azul) -->
            <div class="agendamento-card bg-execucao">
                <div class="info-pedido">
                    <h4>Pedido #1025 · Pintura de sala</h4>
                    <p>Cliente: Carlos Lima · Tel: (11) 97777-5678</p>
                    <p>Av. Paulista, 900, Bela Vista, São Paulo - SP</p>
                    <p>Data/Hora: Sex, 13/11 às 14:00</p>
                    <p>Valor acordado: R$ 1.250,00</p>
                </div>

                <div class="acoes-pedido">
                    <div class="badge-status-top badge-azul">Em Execução</div>
                    <div class="titulo-status">Status do serviço</div>
                    <div class="botoes-status">
                        <!-- Passos anteriores ficam a verde claro, passo atual a azul -->
                        <button class="btn-passo verde-concluido">Confirmado</button>
                        <button class="btn-passo verde-concluido">A Caminho</button>
                        <button class="btn-passo azul-ativo">Em Execução</button>
                        <button class="btn-passo inativo">Concluir</button>
                    </div>
                </div>
            </div>

        </div>
    </main>

</body>

</html>