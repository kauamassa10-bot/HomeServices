<?php
declare(strict_types=1);

require_once __DIR__ . '/init.php';

$databaseStatus = ($pdo instanceof PDO) ? 'Conectado' : 'Indisponível';

if (isset($_SESSION['id_usuario'])) {
    $tipo = strtoupper($_SESSION['tipo_usuario'] ?? '');
    if ($tipo === 'CLIENTE') {
        header("Location: buscar.php");
        exit;
    } elseif ($tipo === 'PRESTADOR') {
        header("Location: dashboard_prestador.php");
        exit;
    } elseif ($tipo === 'ADMIN') {
        header("Location: registros.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HomeServices - Conectando Clientes a Profissionais</title>
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/index.css?v=2">
</head>
<body>

    <header class="header-simples">
        <div class="header-container">
            <h1 class="logo-texto"><a href="index.php">HomeServices</a></h1>
            <nav class="menu-topo">
                <a href="#sobre">Nossa Proposta</a>
                <a href="#investir">Por que Investir</a>
                <a href="#equipe">Nossa Equipe</a>
            </nav>
        </div>
    </header>

    <main>
        <img src="imagens/Code_Generated_Image (1).png" alt="Símbolo HomeServices" class="logo-casa-topo">

        <section class="hero">
            <h2>Encontre o profissional certo ou ofereça os seus serviços.</h2>
            <p>A HomeServices liga clientes que precisam de ajuda em casa a profissionais qualificados da sua região. Rápido, prático e seguro.</p>
            
            <div class="botoes-entrada">
                <a href="login_cliente.php" class="btn-grande btn-cliente">Sou Cliente</a>
                <a href="login_profissional.php" class="btn-grande btn-profissional">Sou Profissional</a>
            </div>
        </section>

        <img src="imagens/homeservices.png" alt="HomeServices" class="logo-wordmark-base">
    </main>

    <div class="sobre-wrapper">

        <section id="sobre" class="sobre-container">
            <div class="sobre-cabecalho">
                <span class="tag-pitch">Sobre a HomeServices</span>
                <h3>Todos os serviços residenciais reunidos em uma única plataforma</h3>
                <p>
                    Eliminamos a demora e a insegurança na hora de contratar reparos domésticos.
                    Conectamos clientes que precisam de agilidade a profissionais qualificados em elétrica, hidráulica, pintura e manutenção geral.
                </p>
            </div>

            <div class="grid-pitch">
                <article class="card-pitch destaque-cliente">
                    <span class="numero-card">01</span>
                    <h4>Facilidade Total para o Cliente</h4>
                    <p>
                        Em vez de buscar contatos soltos sem garantia, o cliente encontra várias especialidades no mesmo lugar, pede orçamentos personalizados e agenda no melhor horário.
                    </p>
                </article>

                <article class="card-pitch destaque-prestador">
                    <span class="numero-card">02</span>
                    <h4>Mais Clientes para o Prestador</h4>
                    <p>
                        Profissionais autônomos ganham visibilidade imediata, recebem pedidos reais da sua categoria e organizam propostas e atendimentos em um painel simples.
                    </p>
                </article>

                <article class="card-pitch destaque-investidor">
                    <span class="numero-card">03</span>
                    <h4>Fluxo Completo e Seguro</h4>
                    <p>
                        Do pedido de orçamento até a avaliação final: proposta transparente, agendamento confirmado, mensagens diretas e reputação baseada em atendimentos reais.
                    </p>
                </article>
            </div>
        </section>

        <section id="investir" class="faixa-investimento">
            <div class="investimento-conteudo">
                <div class="investimento-topo">
                    <span class="tag-investidor">Oportunidade de Mercado</span>
                    <h3>Por que investir na HomeServices?</h3>
                    <p>
                        O mercado de manutenção residencial movimenta milhões diariamente, mas ainda depende de indicações informais. A HomeServices digitaliza essa ponta a ponta com um modelo escalável e recorrente.
                    </p>
                </div>

                <div class="metricas-pitch">
                    <div class="item-metrica">
                        <span class="destaque-dado">100%</span>
                        <strong>Demanda Recorrente</strong>
                        <span>Todo lar precisa de reparos constantes ao longo do ano, garantindo retorno frequente de clientes à plataforma.</span>
                    </div>

                    <div class="item-metrica">
                        <span class="destaque-dado">+Categorias</span>
                        <strong>Alta Escalabilidade</strong>
                        <span>Arquitetura pronta para expandir novas áreas de serviço e atender novas regiões sem custos físicos.</span>
                    </div>

                    <div class="item-metrica">
                        <span class="destaque-dado">5.0 ★</span>
                        <strong>Ecossistema de Confiança</strong>
                        <span>Avaliações verificadas que destacam os melhores profissionais e aumentam a taxa de fechamento.</span>
                    </div>
                </div>
            </div>
        </section>

        <section id="equipe" class="sobre-container">
            <div class="bloco-equipe">
                <span class="tag-pitch">Quem Faz Acontecer</span>
                <h3>Nossa Equipe</h3>
                <p>
                    Somos um time multidisciplinar focado em transformar problemas reais do cotidiano em uma solução digital moderna, intuitiva e pronta para escalar.
                </p>

                 <div class="grid-equipe">
                    <div class="card-membro">
                        <strong>Kauã</strong>
                        <span>Gerente · Back-End PHP (Prestador), HTML &amp; Correção de Bugs</span>
                    </div>
                    <div class="card-membro">
                        <strong>Yago</strong>
                        <span>Back-End PHP &amp; HTML (Fluxo do Cliente)</span>
                    </div>
                    <div class="card-membro">
                        <strong>Lennon</strong>
                        <span>Painel Administrativo &amp; Estilização CSS</span>
                    </div>
                    <div class="card-membro">
                        <strong>Ryan</strong>
                        <span>Design UI/UX &amp; Estilização CSS</span>
                    </div>
                </div
            </div>
        </section>

    </div>

    <footer class="footer-simples">
        <p>© <?= date('Y') ?> HomeServices. Todos os direitos reservados.</p>
        <a href="login_admin.php">Acesso Restrito (Área Administrativa)</a>
        
        <div class="status-db <?= $databaseStatus !== 'Conectado' ? 'erro' : '' ?>">
            Status do DB: <?= htmlspecialchars($databaseStatus, ENT_QUOTES, 'UTF-8') ?>
        </div>
    </footer>

</body>
</html>
