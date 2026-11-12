<?php

require_once __DIR__ . '/init.php';

$cliente = read($pdo, "usuario");

$iniciaisUsuarios = pegarIniciais($cliente["nome_usuario"]);
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HomeServices | Buscar</title>
    <link rel="stylesheet" href="css/buscar.css">
</head>

<body>
    <header>
        <h1>HomeServices - Buscar</h1>
        <nav>
            <a href="">Início</a>
            <a href="">Serviços</a>
            <a href="">Contato</a>
        </nav>
        <div class="avatar">
            <?= $iniciaisUsuarios; ?>
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
        <section class="categorias">
            <div class="botao-categorias">
                <?php
                $categorias = readAll($pdo, "categoria");
                echo '<a href="buscar.php">Todos</a>';
                foreach ($categorias as $categoria) {
                    echo '<a href="buscar.php?categoria=' . urlencode($categoria['nome_categoria']) . '">' . htmlspecialchars($categoria['nome_categoria'], ENT_QUOTES, 'UTF-8') . '</a>';
                }
                ?>
            </div>
        </section>
        <section class="sidebar">
            <form method="GET" action="">
                <aside>
                    <div class="gp-filtros">
                        <h4>Avaliação</h4>
                        <label for="maior-avaliacao">
                            <input type="radio" name="pref-avaliacao" id="maior-avaliacao" value="maior-4"> Mais
                            que 4 estrelas
                        </label>
                        <br>
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
                            <input type="radio" name="pref-preco" id="entre-350-e-500" value="350-500"> Entre R$350,00 a R$500,00
                        </label>
                    </div>

                    <!-- Botão para aplicar os filtros -->
                    <button type="submit" class="btn-filtros">Aplicar Filtros</button>
                </aside>
            </form>

        </section>
        <section class="conteudo-principal">
            <?php
            // Mantendo a query original base com JOINs, para não perder a busca após o ciclo de filtros
            $sqlBusca = "
        SELECT 
            s.id_servico,
            s.nome_servico,
            s.desc_servico,
            s.valor_base,
            c.nome_categoria,
            u.nome_usuario AS nome_prestador,
            COUNT(a.id_avaliacao) as total_avaliacoes,
            IFNULL(AVG(a.numero_nota), 0) as nota_media
        FROM servico s
        INNER JOIN usuario u ON s.id_prestador = u.id_usuario
        INNER JOIN categoria c ON s.id_categoria = c.id_categoria
        LEFT JOIN avaliacao a ON u.id_usuario = a.id_avaliado
        WHERE s.status_ativo = 1
    ";
            $params = [];

            // Aplicando filtros de pesquisa dinâmica
            if (isset($_GET['pesquisa']) && !empty($_GET['pesquisa'])) {
                $sqlBusca .= " AND s.nome_servico LIKE ?";
                $params[] = "%" . $_GET['pesquisa'] . "%";
            } elseif (isset($_GET['categoria']) && !empty($_GET['categoria'])) {
                $sqlBusca .= " AND c.nome_categoria = ?";
                $params[] = $_GET['categoria'];
            }

            if(isset($_GET['pref-preco'])) {
                if($_GET['pref-preco'] === '150-350') {
                    $sqlBusca .= " AND s.valor_base BETWEEN 150 AND 350 ";
                } elseif($_GET['pref-preco'] === '350-500') {
                    $sqlBusca .= " AND s.valor_base BETWEEN 350 AND 500 ";
                }
            } 

            $sqlBusca .= " GROUP BY s.id_servico, s.nome_servico, s.desc_servico, s.valor_base, c.nome_categoria, u.nome_usuario, u.id_usuario";

            //HAVING sempre depois do GROUP BY para não gerar conflito com o WHERE
            if(isset($_GET['pref-avaliacao'])) {
                if($_GET['pref-avaliacao'] === 'maior-4') {
                    $sqlBusca .= " HAVING nota_media >= 4 ";                  
                } elseif($_GET['pref-avaliacao'] === 'menor-4') {
                    $sqlBusca .= " HAVING nota_media < 4 ";
                }
            }

            $stmtBusca = $pdo->prepare($sqlBusca);
            $stmtBusca->execute($params);
            $servicos = $stmtBusca->fetchAll(PDO::FETCH_ASSOC);
            ?>

            <div class="servicos">

                <?php
                // Aqui é onde os serviços serão exibidos
                if (!empty($servicos)) {
                    foreach ($servicos as $servico) {

                        echo '<h3>' . htmlspecialchars($servico['nome_servico'], ENT_QUOTES, 'UTF-8') . '</h3>';
                        echo '<p>Prestador: ' . htmlspecialchars($servico['nome_prestador'], ENT_QUOTES, 'UTF-8') . '</p>';
                        echo '<p>' . htmlspecialchars($servico['desc_servico'], ENT_QUOTES, 'UTF-8') . '</p>';
                        echo '<p>Categoria: ' . htmlspecialchars($servico['nome_categoria'], ENT_QUOTES, 'UTF-8') . '</p>';
                        echo '<p>Valor Base: R$ ' . number_format((float) $servico['valor_base'], 2, ',', '.') . '</p>';
                        echo '<p>Nota: ' . number_format((float) $servico['nota_media'], 1, ',', '.') . ' (' . $servico['total_avaliacoes'] . ' avaliações)</p>';
                        echo '<button><a href="orcamento.php?id=' . $servico['id_servico'] . '">Solicitar Serviço</a></button>';
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