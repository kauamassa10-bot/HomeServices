<?php
require_once __DIR__ . "/init.php";

$id_agendamento = $_GET['id'] ?? null;

if ($id_agendamento) {
    $agendamento = read($pdo, 'agendamento', 'id_agendamento = ?', [$id_agendamento]);
} else {
    $agendamento = read($pdo, 'agendamento', '1=1 ORDER BY id_agendamento DESC LIMIT 1');
}

if (!$agendamento) {
    echo "Nenhum agendamento encontrado no banco de dados.";
    exit;
}

$solicitacao = read($pdo, 'solicitacao', 'id_solicitacao = ?', [$agendamento['id_solicitacao']]);
$prestador   = read($pdo, 'usuario', 'id_usuario = ?', [$agendamento['id_prestador']]);
$cliente     = read($pdo, 'usuario', 'id_usuario = ?', [$agendamento['id_cliente']]);
$proposta    = read($pdo, 'proposta', 'id_proposta = ?', [$agendamento['id_proposta']]);
$iniciaisAvatar = pegarIniciais($cliente['nome_usuario']);

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendamento - HomeServices</title>
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
            <h1>Agende o serviço</h1>
            <p>Escolha a melhor data e horário para receber o <?= htmlspecialchars($prestador['nome_usuario']) ?>.</p>
        </div>

        <form action="salvar_agendamento.php" method="POST" id="formAgendamento">
            <!-- Inputs escondidos para o backend -->
            <input type="hidden" name="id_agendamento" value="<?= $agendamento['id_agendamento'] ?>">
            <input type="hidden" name="data_escolhida" id="input_data">
            <input type="hidden" name="hora_escolhida" id="input_hora">

            <div class="bloco bloco-agendamento">
                
                <!-- COLUNA ESQUERDA: DATAS E HORAS -->
                <div class="caixa selecao-tempo">
                    <h3>1. Escolha a Data</h3>
                    <div class="dias-semana">
                        <!-- Exemplo estático: no futuro, gere isto com um ciclo foreach no PHP -->
                        <button type="button" class="dia-card" data-data="14-11-2026">
                            <span class="dia-nome">Sáb</span>
                            <span class="dia-numero">14</span>
                            <span class="dia-mes">Nov</span>
                        </button>
                        <button type="button" class="dia-card" data-data="15-11-2026">
                            <span class="dia-nome">Dom</span>
                            <span class="dia-numero">15</span>
                            <span class="dia-mes">Nov</span>
                        </button>
                    </div>

                    <h3 class="mt-40">2. Escolha o Horário</h3>
                    <div class="grade-horarios">
                        <button type="button" class="horario-slot">08:00</button>
                        <button type="button" class="horario-slot">10:00</button>
                        <button type="button" class="horario-slot">14:00</button>
                    </div>

                    <h3 class="mt-40">3. Observações</h3>
                    <textarea name="observacoes" class="campo-texto" placeholder="Instruções para o profissional..."></textarea>
                </div>

                <!-- COLUNA DIREITA: RESUMO -->
                <div class="caixa resumo-agendamento">
                    <h3>Resumo do Serviço</h3>
                    <div class="detalhes-resumo">
                        <div class="item-resumo">
                            <span>Serviço</span>
                            <strong><?= htmlspecialchars($solicitacao['desc_titulo'] ?? 'Serviço Padrão') ?></strong>
                        </div>
                        <div class="item-resumo">
                            <span>Profissional</span>
                            <strong><?= htmlspecialchars($prestador['nome_usuario']) ?></strong>
                        </div>
                        <div class="item-resumo">
                            <span>Data e Hora</span>
                            <strong class="destaque-verde" id="resumo_data_hora">Selecione ao lado</strong>
                        </div>
                    </div>

                    <div class="divisor-resumo"></div>

                    <div class="valor-total">
                        <span>Total Estimado</span>
                        <strong>R$ <?= number_format((float)($proposta['valor_proposta'] ?? 0), 2, ',', '.') ?></strong>
                    </div>

                    <button type="submit" class="botao">Confirmar Agendamento</button>
                </div>
            </div>
        </form>
    </main>

    <!-- SCRIPT PARA SELEÇÃO DE BOTÕES -->
    <script>
        const botoesData = document.querySelectorAll('.dia-card');
        const inputData = document.getElementById('input_data');
        const botoesHora = document.querySelectorAll('.horario-slot:not(.ocupado)');
        const inputHora = document.getElementById('input_hora');
        const textoResumo = document.getElementById('resumo_data_hora');

        function atualizarResumo() {
            if (inputData.value && inputHora.value) {
                const dataAtiva = document.querySelector('.dia-card.ativo');
                const dia = dataAtiva.querySelector('.dia-numero').innerText;
                const mes = dataAtiva.querySelector('.dia-mes').innerText;
                textoResumo.innerText = `${dia} ${mes} às ${inputHora.value}`;
            }
        }

        botoesData.forEach(botao => {
            botao.addEventListener('click', function() {
                botoesData.forEach(b => b.classList.remove('ativo'));
                this.classList.add('ativo');
                inputData.value = this.getAttribute('data-data');
                atualizarResumo();
            });
        });

        botoesHora.forEach(botao => {
            botao.addEventListener('click', function() {
                botoesHora.forEach(b => b.classList.remove('ativo'));
                this.classList.add('ativo');
                inputHora.value = this.innerText;
                atualizarResumo();
            });
        });
    </script>

</body>

</html>