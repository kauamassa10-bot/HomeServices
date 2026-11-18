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
<html lang="pt-PT">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Serviços - HomeServices</title>
    <!-- Inclui o estilo do cabeçalho global e o específico desta tabela -->
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/lista_servicos.css">
</head>

<body>

    <header class="dashboard-header">
        <div class="dashboard-logo">HomeServices</div>
        <nav class="dashboard-nav">
            <a href="dashboard_prestador.php">Visão Geral</a>
            <a href="mural_solicitacoes.php">Solicitações</a>
            <a href="agendamentos_prestador.php">Meus Agendamentos</a>
            <a href="meus_servicos_lista.php" class="active">Meus Serviços</a> <!-- Ativo aqui -->
            <a href="perfil.php">Perfil</a>
        </nav>
        <div class="avatar" title="<?= htmlspecialchars($nomeCompleto) ?>">
            <?= htmlspecialchars($iniciais) ?>
        </div>
    </header>

    <main class="meus-servicos-main">

        <!-- Título e Botão Adicionar -->
        <div class="header-acoes">
            <h2 class="titulo-pagina">Meus Serviços</h2>
            <!-- Este botão redireciona para a página de formulário de cadastro de serviços -->
            <a href="cadastro_servicos.php" class="btn-novo-servico">+ Cadastrar Novo Serviço</a>
        </div>

        <!-- Tabela de Serviços -->
        <div class="tabela-container">

            <!-- Barra de ferramentas (ícones visuais) -->
            <div class="toolbar-tabela">
                <span title="Atualizar">↻</span>
                <span title="Layout">◫</span>
                <span title="Imprimir">⎙</span>
                <span title="Filtrar">▽</span>
                <span title="Ordenar">⇅</span>
                <span title="Lista">≣</span>
                <span title="Configurações">⊶</span>
                <span title="Pesquisar">🔍</span>
            </div>

            <table class="tabela-servicos">
                <thead>
                    <tr>
                        <th class="col-id"></th>
                        <th>⚑ Nome do Serviço</th>
                        <th>≡ Categoria</th>
                        <th>≡ Descrição</th>
                        <th>≡ Valor Base (R$)</th>
                        <th>⚙ Status</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Serviço 1 -->
                    <tr>
                        <td class="col-id">1</td>
                        <td>Troca de tomadas</td>
                        <td>Elétrica</td>
                        <td>Substituição de tomadas e espelhos</td>
                        <td>R$ 120,00</td>
                        <td><span class="badge-status status-ativo">Ativo</span></td>
                    </tr>

                    <!-- Serviço 2 -->
                    <tr>
                        <td class="col-id">2</td>
                        <td>Reparo de vazamento</td>
                        <td>Hidráulica</td>
                        <td>Conserto de canos e sifões</td>
                        <td>R$ 150,00</td>
                        <td><span class="badge-status status-ativo">Ativo</span></td>
                    </tr>

                    <!-- Serviço 3 -->
                    <tr>
                        <td class="col-id">3</td>
                        <td>Pintura de cômodo</td>
                        <td>Pintura</td>
                        <td>Pintura interna até 20 m²</td>
                        <td>R$ 600,00</td>
                        <td><span class="badge-status status-ativo">Ativo</span></td>
                    </tr>

                    <!-- Serviço 4 -->
                    <tr>
                        <td class="col-id">4</td>
                        <td>Instalação de chuveiro</td>
                        <td>Elétrica</td>
                        <td>Instalação e teste</td>
                        <td>R$ 90,00</td>
                        <td><span class="badge-status status-inativo">Inativo</span></td>
                    </tr>
                </tbody>
            </table>

            <!-- Linha inferior de adição rápida -->
            <div class="linha-adicionar" onclick="window.location.href='cadastro_servicos.php';"
                title="Adicionar nova linha">
                +
            </div>

        </div>
    </main>

</body>

</html>