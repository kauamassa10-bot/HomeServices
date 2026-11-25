<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/init.php';

if (!isset($_SESSION['id_usuario']) || strtoupper($_SESSION['tipo_usuario'] ?? '') !== 'CLIENTE') {
    header("Location: login_cliente.php?erro=acesso_negado");
    exit;
}

$usuarios = readAll($pdo, "usuario");
$categorias = readAll($pdo, "categoria");

// $iniciaisUsuarios = pegarIniciais($_SESSION['nome_usuario'] ?? '');
$nomeCompleto = $dadosUsuario['nome_usuario'] ?? ($_SESSION['nome_usuario'] ?? 'Usuário');
$iniciais = pegarIniciais($nomeCompleto);
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HomeServices | Buscar</title>
    <link rel="stylesheet" href="css/buscar.css">
    <link rel="stylesheet" href="css/global.css">
</head>

<body>
    <header class="cabecalho">
        <div class="logo">
            <p>HomeServices</p>
        </div>
        <nav class="links">
            <a href="buscar.php">Buscar Serviços</a>
            <a href="pedidos.php">Meus Pedidos</a>
            <a href="perfil_cliente.php" class="foto" title="<?= htmlspecialchars($nomeCompleto) ?>"><?= htmlspecialchars($iniciais) ?></a>

        </nav>
    </header>

    <main>
        <section class="busca">
            <h2>Buscar Serviços</h2>
            <div class="formulario-busca">
                <form method="GET" action="">
                    <label for="pesquisa">Pesquisar:</label>
                    <input type="text" id="pesquisa" name="pesquisa" placeholder="Digite o serviço desejado">
                    <button type="submit" class="btn-busca">Buscar</button>
                </form>
            </div>
        </section>
        <div class="categorias">
            <a href="buscar.php">Todos</a>
            <?php foreach ($categorias as $categoria): ?>
                <a href="buscar.php?categoria=<?= urlencode($categoria['nome_categoria']); ?>">
                    <?= htmlspecialchars($categoria['nome_categoria'], ENT_QUOTES, 'UTF-8'); ?>
                </a>
            <?php endforeach; ?>
        </div>
        <section class="sidebar">
            <form method="GET" action="">
                <aside>
                    <div class="gp-filtros">
                        <h4>Avaliação</h4>
                        <label for="maior-avaliacao">
                            <input type="radio" name="pref-avaliacao" id="maior-avaliacao" value="maior-4"> Mais
                            que 4 estrelas
                        </label>
                        <label for="menor-avaliacao">
                            <input type="radio" name="pref-avaliacao" id="menor-avaliacao" value="menor-4"> Menor que 4
                            estrelas
                        </label>
                    </div>

                    <div class="gp-filtros">
                        <h4>Faixa de preço</h4>
                        <label for="entre-150-e-350">
                            <input type="radio" name="pref-preco" id="entre-150-e-350" value="150-350"> Entre R$150,00 a
                            R$350,00
                        </label>
                        <label for="entre-350-e-500">
                            <input type="radio" name="pref-preco" id="entre-350-e-500" value="350-500"> Entre R$350,00 a
                            R$500,00
                        </label>
                    </div>
                    <!-- Botão para aplicar os filtros -->
                    <button type="submit" class="btn-filtros">Aplicar Filtros</button>
                    <button type="reset" class="btn-filtros">Reiniciar Filtros</button>
                </aside>
            </form>

        </section>
        <section class="conteudo-principal">
            <?php

            //  Filtros para o banco aplicar direto na tabela servico
            $where = ['status_ativo = 1'];
            $params = [];

            if (!empty($_GET['pesquisa'])) {
                $where[] = 'nome_servico LIKE ?';
                $params[] = '%' . $_GET['pesquisa'] . '%';
            }

            if (!empty($_GET['categoria'])) {
                $categoriaSelect = read($pdo, 'categoria', 'nome_categoria = ?', [$_GET['categoria']]);
                $where[] = 'id_categoria = ?';
                $params[] = $categoriaSelect ? $categoriaSelect['id_categoria'] : 0; // 0 = nenhum resultado
            }

            $faixas = [
                '150-350' => [150, 350],
                '350-500' => [350, 500]
            ];
            $faixaSelect = $_GET['pref-preco'] ?? '';
            if (isset($faixas[$faixaSelect])) {
                $where[] = 'valor_base BETWEEN ? AND ?';
                array_push($params, ...$faixas[$faixaSelect]);
            }

            $servicosBrutos = readAll($pdo, 'servico', implode(' AND ', $where), $params);

            // Avaliações dos prestadores encontrados
            $idsPrestadores = array_values(array_unique(array_column($servicosBrutos, 'id_prestador')));
            $avaliacoes = [];

            if ($idsPrestadores !== []) {
                $marcadores = implode(', ', array_fill(0, count($idsPrestadores), '?')); // Cria uma lista de marcadores para a cláusula IN (?, ?, ...)
                $avaliacoes = readAll($pdo, 'avaliacao', "id_avaliado IN ({$marcadores})", $idsPrestadores);
            }

            // 3) Total e soma das notas por prestador (equivale ao COUNT/AVG do GROUP BY)
            $notasPorPrestador = [];
            foreach ($avaliacoes as $avaliacao) {
                $idAvaliacao = $avaliacao['id_avaliado'];
                $notasPorPrestador[$idAvaliacao]['total'] = ($notasPorPrestador[$idAvaliacao]['total'] ?? 0) + 1;
                $notasPorPrestador[$idAvaliacao]['soma'] = ($notasPorPrestador[$idAvaliacao]['soma'] ?? 0) + (float) $avaliacao['numero_nota'];
            }

            // fazendo o "JOIN"
            $idUsuario = array_column($usuarios, null, 'id_usuario');
            $idCategorias = array_column($categorias, null, 'id_categoria');

            
            $servicos = [];
            $prefAvaliacao = $_GET['pref-avaliacao'] ?? '';

            foreach ($servicosBrutos as $servico) {
                $prestador = $idUsuario[$servico['id_prestador']] ?? null;
                $categoria = $idCategorias[$servico['id_categoria']] ?? null;

                if ($prestador === null || $categoria === null) {
                    continue; 
                }

                $dadosNota = $notasPorPrestador[$servico['id_prestador']] ?? ['total' => 0, 'soma' => 0.0];
                $notaMedia = $dadosNota['total'] > 0 ? $dadosNota['soma'] / $dadosNota['total'] : 0.0;

                if ($prefAvaliacao === 'maior-4' && $notaMedia < 4) {
                    continue;
                }
                if ($prefAvaliacao === 'menor-4' && $notaMedia >= 4) {
                    continue;
                }

                $servicos[] = [
                    'id_servico' => $servico['id_servico'],
                    'id_prestador' => $servico['id_prestador'],
                    'id_categoria' => $servico['id_categoria'],
                    'nome_servico' => $servico['nome_servico'],
                    'desc_servico' => $servico['desc_servico'],
                    'valor_base' => $servico['valor_base'],
                    'nome_categoria' => $categoria['nome_categoria'],
                    'nome_prestador' => $prestador['nome_usuario'],
                    'total_avaliacoes' => $dadosNota['total'],
                    'nota_media' => $notaMedia,
                ];
            }

            //Preparando variáveis para armazenar as propostas mais recentes por prestador e categoria
            $propostasPorChave = [];
            $idClienteLogado = (int) $_SESSION['id_usuario'];

            //Buscando todas as solicitações do cliente logado
            $solicitacoesCliente = readAll($pdo, 'solicitacao', 'id_cliente = ?', [$idClienteLogado]);


                //Verifica se há solicitações do cliente para buscar as propostas relacionadas
            if ($solicitacoesCliente !== []) {

                //Buscando todas as propostas relacionadas às solicitações do cliente
                $idsSolicitacao = array_column($solicitacoesCliente, 'id_solicitacao'); //extrai os IDs das solicitações do cliente
                $marcadoresSolicitacao = implode(', ', array_fill(0, count($idsSolicitacao), '?')); // gerando (?, ?, ...) para a cláusula IN
                $propostasRecebidas = readAll(
                    $pdo,
                    'proposta',
                    "id_solicitacao IN ({$marcadoresSolicitacao})", //IDs vão como parâmetros, então continua seguro.
                    $idsSolicitacao
                ); // Busca todas as propostas relacionadas às solicitações do cliente
                $solicitacaoPorId = array_column($solicitacoesCliente, null, 'id_solicitacao'); // criando um array associativo para acessar as solicitações pelo ID

                foreach ($propostasRecebidas as $propostaItem) { // Percorre cada proposta
                    $solicitacaoItem = $solicitacaoPorId[$propostaItem['id_solicitacao']] ?? null; // Busca a solicitação correspondente à proposta atual
                    if ($solicitacaoItem === null) {
                        continue; // Para cada proposta, descobre a solicitação dela. Se não achar, pula.
                    }

                    $chave = $propostaItem['id_prestador'] . '-' . $solicitacaoItem['id_categoria']; // A proposta traz o prestador, mas a categoria só existe na solicitação.
                    $atual = $propostasPorChave[$chave] ?? null; // Verifica se já existe uma proposta armazenada para essa combinação de prestador e categoria
                    $novaEnviada = strtoupper((string) $propostaItem['status_proposta']) === 'ENVIADA'; // Verifica se a proposta atual está com status "ENVIADA"
                    $atualEnviada = $atual !== null && strtoupper((string) $atual['status_proposta']) === 'ENVIADA'; // Verifica se a proposta armazenada anteriormente está com status "ENVIADA"
                    $maisNova = $atual === null || (int) $propostaItem['id_proposta'] > (int) $atual['id_proposta']; // Verifica se a proposta atual é mais recente do que a armazenada anteriormente 

                    if ($atual === null || ($novaEnviada && !$atualEnviada) || ($novaEnviada === $atualEnviada && $maisNova)) {
                        $propostasPorChave[$chave] = $propostaItem; // Armazena a proposta atual como a mais recente para essa combinação de prestador e categoria, se ela for mais nova ou se for a primeira encontrada.
                    }
                }
            }
            ?>

            <div class="servicos">

                <?php if (!empty($servicos)): ?>
                    <?php foreach ($servicos as $servico): ?>
                        <?php
                        $notaArredondada = (int) round((float) $servico['nota_media']);
                        $estrelas = str_repeat('★', $notaArredondada) . str_repeat('☆', 5 - $notaArredondada);
                        $chaveProposta = $servico['id_prestador'] . '-' . $servico['id_categoria'];
                        $propostaCard = $propostasPorChave[$chaveProposta] ?? null; // Obtém a proposta mais recente para o prestador e categoria do serviço atual
                        $propostaEnviada = $propostaCard !== null && strtoupper((string) $propostaCard['status_proposta']) === 'ENVIADA'; // Verifica se a proposta mais recente está com status "ENVIADA"
                        ?>
                        <div class="card-servico">
                            <div class="card-info">
                                <h3>
                                    <?= htmlspecialchars($servico['nome_servico'], ENT_QUOTES, 'UTF-8') ?>
                                    <span class="txt-primario">&middot; <?= htmlspecialchars($servico['nome_categoria'], ENT_QUOTES, 'UTF-8') ?></span>
                                </h3>
                                <p class="txt-secundario"><?= htmlspecialchars($servico['nome_prestador'], ENT_QUOTES, 'UTF-8') ?></p>
                                <p class="txt-secundario">
                                    <span class="estrelas-nota"><?= $estrelas ?></span>
                                    <?= number_format((float) $servico['nota_media'], 1, ',', '.') ?>
                                    (<?= (int) $servico['total_avaliacoes'] ?> avaliações)
                                </p>
                                <?php if ($propostaCard): ?>
                                    <p class="txt-secundario">
                                        Proposta: R$ <?= number_format((float) $propostaCard['valor_proposta'], 2, ',', '.') ?>
                                        · <?= htmlspecialchars($propostaCard['status_proposta'], ENT_QUOTES, 'UTF-8') ?>
                                    </p>
                                <?php else: ?>
                                    <p class="txt-secundario">A partir de R$ <?= number_format((float) $servico['valor_base'], 2, ',', '.') ?></p>
                                <?php endif; ?>
                                <?php if ($propostaEnviada): ?>
                                    <a href="agendamento.php?id_proposta=<?= (int) $propostaCard['id_proposta'] ?>" class="btn-orcamento">Agendar</a>
                                <?php endif; ?>
                            </div>
                            <div class="card-acao">
                                <a href="orcamento.php?id_prestador=<?= (int) $servico['id_prestador'] ?>&id_servico=<?= (int) $servico['id_servico'] ?>" class="btn-orcamento">Pedir Orçamento</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Nenhum serviço encontrado.</p>
                <?php endif; ?>
            </div>
        </section>
    </main>


</body>

</html>