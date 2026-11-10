USE HomeServices;


INSERT INTO usuario (id_usuario, nome_usuario, email, senha, telefone, tipo_usuario, status_ativo, data_cadastro) VALUES
(1001, 'Carlos Eduardo Rocha', 'carlos.eletrica@email.com', '123456', '11987654321', 'PRESTADOR', true, '2026-10-29 10:15:00'),
(1002, 'Roberto Encanamentos', 'roberto.hidraulica@email.com', '123456', '11977778888', 'PRESTADOR', true, '2026-10-29 10:20:00'),
(2001, 'Ana Maria Silva', 'ana.maria@email.com', '123456', '11911112222', 'CLIENTE', true, '2026-10-29 10:30:00'),
(2002, 'João Pedro Santos', 'joao.pedro@email.com', '123456', '11933334444', 'CLIENTE', true, '2026-10-29 10:35:00');

INSERT INTO categoria (id_categoria, nome_categoria, desc_categoria, status_ativo) VALUES
(10, 'Elétrica', 'Instalações e manutenções elétricas residenciais', true),
(20, 'Hidráulica', 'Reparos em canos, vazamentos, torneiras e sifões', true),
(30, 'Pintura', 'Pintura residencial interna e externa', true);

INSERT INTO servico (id_servico, id_prestador, id_categoria, nome_servico, desc_servico, valor_base, status_ativo) VALUES
(501, 1001, 10, 'Troca de Fiação Residencial', 'Instalação e substituição de fiação de chuveiro e disjuntores', 250.00, true),
(502, 1002, 20, 'Desentupimento e Reparo de Canos', 'Conserto de vazamentos e desentupimento geral em pias e banheiros', 180.00, true);

INSERT INTO solicitacao (id_solicitacao, id_cliente, id_categoria, desc_titulo, desc_solicitacao, desc_endereco_atendimento, status_solicitacao, data_solicitacao) VALUES
(8001, 2001, 10, 'Troca de fiação de chuveiro', 'Preciso trocar a fiação do chuveiro para 6mm com novo disjuntor NEMA 40A', 'Rua das Flores, 123 - Apt 42 - São Paulo/SP', 'EM_ORCAMENTO', '2026-10-29'),
(8002, 2002, 20, 'Vazamento sob a pia da cozinha', 'Pia pingando constantemente no sifão e infiltrando no armário', 'Av. Paulista, 1000 - Bela Vista - São Paulo/SP', 'ABERTA', '2026-10-29');

INSERT INTO proposta (id_proposta, id_solicitacao, id_prestador, valor_proposta, desc_proposta, status_proposta, data_proposta) VALUES
(12001, 8001, 1001, 220.00, 'Incluso disjuntor NEMA 40A, cabo 6mm flexível e teste de carga', 'ACEITA', '2026-10-29');

INSERT INTO agendamento (id_agendamento, id_solicitacao, id_proposta, id_cliente, id_prestador, data_agendamento, status_agendamento, data_criacao) VALUES
(3001, 8001, 12001, 2001, 1001, '2026-10-31 09:00:00', 'CONCLUIDO', '2026-10-29');

INSERT INTO avaliacao (id_avaliacao, id_agendamento, id_avaliador, id_avaliado, numero_nota, desc_comentario, data_avaliacao) VALUES
(4001, 3001, 2001, 1001, 5, 'Serviço excelente, muito pontual, caprichoso e limpo!', '2026-10-31');
