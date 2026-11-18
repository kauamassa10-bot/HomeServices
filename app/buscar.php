<?php

require_once __DIR__ . '/init.php';

$clientes = readAll($pdo, "usuario");
$categorias = readAll($pdo, "categoria");

$iniciaisUsuarios = pegarIniciais($clientes[0]["nome_usuario"]);


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

            <a href="">Início</a>
            <a href="">Serviços</a>
            <a href="">Contato</a>

        </nav>
        <div class="foto">
            <a href="perfil.php"><?= $iniciaisUsuarios; ?></a>
        </div>
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
            $idUsuario = array_column($clientes, null, 'id_usuario');
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
                    'nome_servico' => $servico['nome_servico'],
                    'desc_servico' => $servico['desc_servico'],
                    'valor_base' => $servico['valor_base'],
                    'nome_categoria' => $categoria['nome_categoria'],
                    'nome_prestador' => $prestador['nome_usuario'],
                    'total_avaliacoes' => $dadosNota['total'],
                    'nota_media' => $notaMedia,
                ];
            }
            ?>

            <div class="servicos">

                <?php
                // Aqui é onde os serviços serão exibidos
                if (!empty($servicos)) {
                    foreach ($servicos as $servico) {
                        // Calculando as estrelas e arredondando a nota para um número inteiro de 0 a 5
                        $notaArredondada = (int) round((float) $servico['nota_media']);
                        $estrelas = str_repeat('★', $notaArredondada) . str_repeat('☆', 5 - $notaArredondada);

                        echo '<div class="card-servico">';

                        echo '  <div class="card-foto">Foto</div>';

                        echo '  <div class="card-info">';
                        echo '    <h3>' . htmlspecialchars($servico['nome_servico'], ENT_QUOTES, 'UTF-8') . ' <span class="txt-primario">&middot; ' . htmlspecialchars($servico['nome_categoria'], ENT_QUOTES, 'UTF-8') . '</span></h3>';
                        echo '    <p class="txt-secundario">' . htmlspecialchars($servico['nome_prestador'], ENT_QUOTES, 'UTF-8') . '</p>';
                        echo '    <p class="txt-secundario">' . '<span class="estrelas-nota">' . $estrelas . '</span> ' . number_format((float) $servico['nota_media'], 1, ',', '.') . ' (' . $servico['total_avaliacoes'] . ' avaliações)</p>';
                        echo '    <p class="txt-secundario">A partir de R$ ' . number_format((float) $servico['valor_base'], 2, ',', '.') . '</p>';
                        echo '    <span class="badge-verificada">Verificado(a)</span>';
                        echo '  </div>';

                        echo '  <div class="card-acao">';
                        echo '    <a href="orcamento.php?id=' . $servico['id_servico'] . '" class="btn-orcamento">Pedir orçamento</a>';
                        echo '  </div>';

                        echo '</div>';
                    }
                } else {
                    echo '<p>Nenhum serviço encontrado.</p>';
                }
                ?>
            </div>
        </section>
    </main>


</body>

</html>