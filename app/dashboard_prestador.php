<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/init.php';

// Verifica se está logado e se é prestador
if (!isset($_SESSION['id_usuario']) || strtoupper($_SESSION['tipo_usuario'] ?? '') !== 'PRESTADOR') {
    header("Location: login_profissional.php?erro=acesso_negado");
    exit;
}

// Extrair o primeiro nome e gerar as iniciais
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
    <title>Dashboard do Prestador - HomeServices</title>
    <!-- Inclui os estilos globais e os específicos do dashboard -->
    <link rel="stylesheet" href="css/global.css"> 
    <link rel="stylesheet" href="css/dashboard.css"> 
</head>
<body>

    <header class="dashboard-header">
        <div class="dashboard-logo">HomeServices</div>
        <nav class="dashboard-nav">
            <a href="mural_solicitacoes.php" class="active">Solicitações</a>
            <a href="#">Meus Agendamentos</a>
            <a href="#">Meus Serviços</a>
            <a href="#">Perfil</a>
        </nav>
        <div class="avatar" title="<?= htmlspecialchars($nomeCompleto) ?>">
            <?= htmlspecialchars($iniciais) ?>
        </div>
    </header>

    <main class="dashboard-main">
        <h2 class="saudacao">Olá, <?= htmlspecialchars($primeiroNome) ?>. Visão geral do mês</h2>

        <div class="cards-grid">
            <div class="card-resumo card-verde">
                <span class="titulo-card">Serviços Concluídos</span>
                <span class="valor-card">42</span>
            </div>
            <div class="card-resumo card-azul">
                <span class="titulo-card">Propostas Enviadas</span>
                <span class="valor-card">18</span>
            </div>
            <div class="card-resumo card-laranja">
                <span class="titulo-card">Faturamento do Mês</span>
                <span class="valor-card">R$ 6.480,00</span>
            </div>
            <div class="card-resumo card-amarelo">
                <span class="titulo-card">Nota Média</span>
                <span class="valor-card">4,8 de 5 (estrelas)</span>
            </div>
        </div>

        <div class="agendamentos-container">
            <h3>Próximos Agendamentos</h3>

            <div class="lista-agendamentos">
                
                <!-- Agendamento 1 -->
                <div class="agendamento-item">
                    <div class="agendamento-info">
                        <strong>Qui, 12/11 · 09:00</strong>
                        <span>Cliente: Maria Souza</span>
                        <span>Rua das Flores, 120, Centro</span>
                    </div>
                    <div class="agendamento-acoes">
                        <span class="badge badge-pendente">Pendente</span>
                        <button class="btn-acao">Ver Detalhes / Atualizar Status</button>
                    </div>
                </div>

                <!-- Agendamento 2 -->
                <div class="agendamento-item">
                    <div class="agendamento-info">
                        <strong>Sex, 13/11 · 14:00</strong>
                        <span>Cliente: Carlos Lima</span>
                        <span>Av. Paulista, 900, Bela Vista</span>
                    </div>
                    <div class="agendamento-acoes">
                        <span class="badge badge-execucao">Em Execução</span>
                        <button class="btn-acao">Ver Detalhes / Atualizar Status</button>
                    </div>
                </div>

                <!-- Agendamento 3 -->
                <div class="agendamento-item">
                    <div class="agendamento-info">
                        <strong>Sáb, 14/11 · 10:30</strong>
                        <span>Cliente: Ana Paula</span>
                        <span>Rua Verde, 45, Vila Mariana</span>
                    </div>
                    <div class="agendamento-acoes">
                        <span class="badge badge-confirmado">Confirmado</span>
                        <button class="btn-acao">Ver Detalhes / Atualizar Status</button>
                    </div>
                </div>

            </div>
        </div>
    </main>

</body>
</html>