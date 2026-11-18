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
    <title>Mural de Solicitações - HomeServices</title>
    <!-- Inclui o estilo do cabeçalho global do dashboard e o específico desta página -->
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/solicitacoes.css">
</head>

<body>

    <header class="dashboard-header">
        <div class="dashboard-logo">HomeServices</div>
        <nav class="dashboard-nav">
            <a href="dashboard_prestador.php">Visão Geral</a>
            <a href="mural_solicitacoes.php" class="active">Solicitações</a>
            <a href="agendamentos_prestador.php">Meus Agendamentos</a>
            <a href="meus_servicos_lista.php">Meus Serviços</a>
            <a href="perfil.php">Perfil</a>
        </nav>
        </nav>
        <div class="avatar" title="<?= htmlspecialchars($nomeCompleto) ?>">
            <?= htmlspecialchars($iniciais) ?>
        </div>
    </header>

    <main class="solicitacoes-main">
        <h2 class="titulo-pagina">Mural de Solicitações</h2>

        <!-- Barra de Filtros -->
        <div class="filtros-container">
            <input type="text" class="input-filtro" placeholder="Categoria: Elétrica, Hidráulica, Pintura">
            <input type="text" class="input-filtro" placeholder="Cidade / Bairro">
            <input type="text" class="input-filtro" placeholder="Data de publicação">
            <button type="button" class="btn-filtrar">Filtrar</button>
        </div>

        <!-- Grelha de Solicitações -->
        <div class="solicitacoes-grid">

            <!-- Pedido 1 -->
            <div class="card-solicitacao">
                <div class="conteudo-solicitacao">
                    <h4>Troca de tomadas da cozinha</h4>
                    <div class="detalhe-solicitacao categoria">Elétrica · Cliente: Ana Paula</div>
                    <div class="detalhe-solicitacao local">Vila Mariana, São Paulo</div>
                    <div class="detalhe-solicitacao descricao">Tomadas queimando, preciso trocar 4 unidades.</div>
                    <div class="detalhe-solicitacao data">Publicado em 10/11</div>
                </div>
                <a href="enviar_proposta.php?id=1" class="btn-proposta"
                    style="display: block; text-align: center; text-decoration: none; box-sizing: border-box;">Fazer
                    Proposta</a>
            </div>

            <!-- Pedido 2 -->
            <div class="card-solicitacao">
                <div class="conteudo-solicitacao">
                    <h4>Vazamento embaixo da pia</h4>
                    <div class="detalhe-solicitacao categoria">Hidráulica · Cliente: Carlos Lima</div>
                    <div class="detalhe-solicitacao local">Centro, Campinas</div>
                    <div class="detalhe-solicitacao descricao">Cano goteja há dois dias e molha o armário.</div>
                    <div class="detalhe-solicitacao data">Publicado em 10/11</div>
                </div>
                <a href="enviar_proposta.php?id=2" class="btn-proposta"
                    style="display: block; text-align: center; text-decoration: none; box-sizing: border-box;">Fazer
                    Proposta</a>
            </div>

            <!-- Pedido 3 -->
            <div class="card-solicitacao">
                <div class="conteudo-solicitacao">
                    <h4>Pintura de sala e corredor</h4>
                    <div class="detalhe-solicitacao categoria">Pintura · Cliente: Paulo Reis</div>
                    <div class="detalhe-solicitacao local">Bela Vista, São Paulo</div>
                    <div class="detalhe-solicitacao descricao">Cerca de 40 m², paredes brancas para cinza.</div>
                    <div class="detalhe-solicitacao data">Publicado em 09/11</div>
                </div>
                <a href="enviar_proposta.php?id=3" class="btn-proposta"
                    style="display: block; text-align: center; text-decoration: none; box-sizing: border-box;">Fazer
                    Proposta</a>
            </div>

            <!-- Pedido 4 -->
            <div class="card-solicitacao">
                <div class="conteudo-solicitacao">
                    <h4>Instalação de chuveiro</h4>
                    <div class="detalhe-solicitacao categoria">Elétrica · Cliente: Júlia Alves</div>
                    <div class="detalhe-solicitacao local">Moema, São Paulo</div>
                    <div class="detalhe-solicitacao descricao">Chuveiro novo, fiação já existente.</div>
                    <div class="detalhe-solicitacao data">Publicado em 09/11</div>
                </div>
                <a href="enviar_proposta.php?id=4" class="btn-proposta"
                    style="display: block; text-align: center; text-decoration: none; box-sizing: border-box;">Fazer
                    Proposta</a>
            </div>

            <!-- Pedido 5 -->
            <div class="card-solicitacao">
                <div class="conteudo-solicitacao">
                    <h4>Desentupimento de ralo</h4>
                    <div class="detalhe-solicitacao categoria">Hidráulica · Cliente: Rita Gomes</div>
                    <div class="detalhe-solicitacao local">Jardins, São Paulo</div>
                    <div class="detalhe-solicitacao descricao">Ralo do banheiro com água acumulada.</div>
                    <div class="detalhe-solicitacao data">Publicado em 08/11</div>
                </div>
                <a href="enviar_proposta.php?id=5" class="btn-proposta"
                    style="display: block; text-align: center; text-decoration: none; box-sizing: border-box;">Fazer
                    Proposta</a>
            </div>

            <!-- Pedido 6 -->
            <div class="card-solicitacao">
                <div class="conteudo-solicitacao">
                    <h4>Pintura de portão</h4>
                    <div class="detalhe-solicitacao categoria">Pintura · Cliente: Marcos Dias</div>
                    <div class="detalhe-solicitacao local">Tatuapé, São Paulo</div>
                    <div class="detalhe-solicitacao descricao">Portão de ferro com ferrugem.</div>
                    <div class="detalhe-solicitacao data">Publicado em 08/11</div>
                </div>
                <a href="enviar_proposta.php?id=6" class="btn-proposta"
                    style="display: block; text-align: center; text-decoration: none; box-sizing: border-box;">Fazer
                    Proposta</a>
            </div>

        </div>

        </div>
    </main>

</body>

</html>