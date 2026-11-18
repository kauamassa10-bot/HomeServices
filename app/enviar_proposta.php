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

// Extrair o nome e as iniciais para o cabeçalho
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
    <title>Enviar Proposta - HomeServices</title>
    <!-- Inclui o estilo do cabeçalho global do dashboard e o específico desta página -->
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/proposta.css">
</head>
<body>

    <header class="dashboard-header">
        <div class="dashboard-logo">HomeServices</div>
        <nav class="dashboard-nav">
            <a href="mural_solicitacoes.php">Solicitações</a>
            <a href="agendamentos_prestador.php" class="active">Meus Agendamentos</a>
            <a href="meus_servicos_lista.php">Meus Serviços</a>
            <a href="perfil.php">Perfil</a>
        </nav>
        <div class="avatar" title="<?= htmlspecialchars($nomeCompleto) ?>">
            <?= htmlspecialchars($iniciais) ?>
        </div>
    </header>

    <main class="proposta-main">
        <div class="card-form-proposta">
            <h2>Enviar Proposta</h2>

            <!-- Resumo do Pedido -->
            <div class="resumo-solicitacao">
                <strong>Troca de tomadas da cozinha</strong>
                <p>Tomadas queimando, preciso trocar 4 unidades.</p>
                <p>Endereço: Rua Verde, 45, Vila Mariana, São Paulo</p>
            </div>

            <!-- Formulário -->
            <form action="#" method="POST">
                
                <div class="form-group">
                    <label for="valor_proposta">Valor da Proposta (R$)</label>
                    <input type="text" id="valor_proposta" name="valor_proposta" class="form-control" placeholder="R$ 0,00" required>
                </div>

                <div class="form-group">
                    <label for="descricao_proposta">Descrição da Proposta / O que está incluso</label>
                    <textarea id="descricao_proposta" name="descricao_proposta" class="form-control" placeholder="Ex: materiais, garantia, tempo estimado" required></textarea>
                </div>

                <div class="botoes-acao">
                    <a href="mural_solicitacoes.php" class="btn-cancelar">Cancelar</a>
                    <button type="submit" class="btn-enviar-proposta">Enviar Proposta ao Cliente</button>
                </div>
            </form>
        </div>
    </main>

</body>
</html>