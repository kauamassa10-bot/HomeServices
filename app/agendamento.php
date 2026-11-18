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
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/agendamento.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
</head>
<body>
    <header class="cabecalho">
        <a href="buscar.html" class="logo">HomeServices</a>
        <nav class="links">
            <a href="buscar.html">Categorias</a>
            <a href="pedidos.html">Meus pedidos</a>
            <a href="#">Ajuda</a>
            <a href="perfil.html" class="foto"><?= $iniciaisAvatar ?></a>
        </nav>
    </header>
    
    <main class="conteudo">
        <div class="titulo">
            <h1>Agende o serviço</h1>
            <p>Escolha a melhor data e horário para receber o <?= htmlspecialchars($prestador['nome_usuario']) ?>.</p>
        </div>

        <form action="salvar_agendamento.php" method="POST" id="formAgendamento">
            <input type="hidden" name="id_agendamento" value="<?= $agendamento['id_agendamento'] ?>">
            <input type="hidden" name="data_escolhida" id="input_data">
            <input type="hidden" name="hora_escolhida" id="input_hora">

            <div class="bloco bloco-agendamento">
                <div class="caixa selecao-tempo">
                    <h3>1. Escolha a Data</h3>
                    <input type="text" id="calendario-inline" style="display: none;">
                    
                    <h3 class="mt-40">2. Escolha o Horário</h3>
                    <div class="grade-horarios" id="container-horarios">
                        <p style="color: #718096; font-size: 14px;">Selecione uma data primeiro.</p>
                    </div>

                    <h3 class="mt-40">3. Observações</h3>
                    <textarea name="observacoes" class="campo-texto" placeholder="Instruções para o profissional..."></textarea>
                </div>

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

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/pt.js"></script>
    <script>
        const inputData = document.getElementById('input_data');
        const inputHora = document.getElementById('input_hora');
        const containerHorarios = document.getElementById('container-horarios');
        const textoResumo = document.getElementById('resumo_data_hora');
        const idPrestador = <?= $prestador['id_usuario'] ?>;

        flatpickr("#calendario-inline", {
            inline: true,
            locale: "pt",
            minDate: "today",
            onChange: function(selectedDates, dateStr) {
                inputData.value = dateStr;
                inputHora.value = '';
                buscarHorarios(dateStr);
            }
        });

        async function buscarHorarios(dataEscolhida) {
            containerHorarios.innerHTML = '<p style="color: #718096; font-size: 14px;">A carregar horários...</p>';
            
            try {
                const resposta = await fetch(`buscar_horarios.php?data=${dataEscolhida}&prestador=${idPrestador}`);
                const dados = await resposta.json();
                
                containerHorarios.innerHTML = '';

                if(dados.horarios.length === 0) {
                    containerHorarios.innerHTML = '<p style="color: #718096; font-size: 14px;">Nenhum horário disponível.</p>';
                    return;
                }

                dados.horarios.forEach(horario => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = horario.ocupado ? 'horario-slot ocupado' : 'horario-slot';
                    btn.innerText = horario.hora;
                    
                    if(!horario.ocupado) {
                        btn.addEventListener('click', function() {
                            document.querySelectorAll('.horario-slot').forEach(b => b.classList.remove('ativo'));
                            this.classList.add('ativo');
                            inputHora.value = this.innerText;
                            atualizarResumo();
                        });
                    }
                    containerHorarios.appendChild(btn);
                });
                atualizarResumo();
            } catch (erro) {
                containerHorarios.innerHTML = '<p style="color: #e53e3e; font-size: 14px;">Erro ao carregar horários.</p>';
            }
        }

        function atualizarResumo() {
            if (inputData.value && inputHora.value) {
                const dataParts = inputData.value.split('-');
                textoResumo.innerText = `${dataParts[2]}/${dataParts[1]}/${dataParts[0]} às ${inputHora.value}`;
            } else {
                textoResumo.innerText = "Selecione ao lado";
            }
        }
    </script>
</body>
</html>