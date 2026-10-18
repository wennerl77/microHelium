-- =============================================================================
-- PROVA 1 - MARATONA TURING: PROBLEM BANK & POLYGON EXERCISES
-- Carga Idempotente das 22 questões com Enunciados, Limites, Casos Base e Geradores C++
-- =============================================================================

-- 1. Inserção / Atualização em problem_bank
INSERT INTO problem_bank (
    code, name, description, input_description, output_description,
    sample_input, sample_output, notes, time_limit, memory_limit,
    source, source_url, difficulty, tags, is_active, created_at, updated_at
) VALUES (
    'I01',
    'O resto da visita de Turing',
    'Alan Turing, célebre por decifrar códigos secretos e desenhar os fundamentos da computação, foi recebido com festa no campus do IFMG em Ouro Branco, aos pés da serra. Atraída pela palestra e pelo cheiro de broas e café da recepção mineira, uma grande multidão vinda de Lafaiete e Congonhas lotou o auditório.

Ada Lovelace, pioneira dos algoritmos, assumiu a missão de organizar os visitantes em grupos com exatamente o mesmo número de pessoas. Para coordenar as salas, Ada quer saber se o total de visitantes é par ou ímpar e quantas pessoas ficarão de fora dos grupos completos.

Dados o número de visitantes **N** e o tamanho de cada grupo **M**, ajude Ada a informar se **N** é par ou ímpar e qual é o resto da divisão de **N** por **M**.',
    'Uma linha com dois inteiros **N** e **M**, que são, respectivamente, o número de visitantes e o número de pessoas em cada grupo.',
    'Na primeira linha, imprima `PAR` se **N** for par ou `IMPAR` caso contrário. Na segunda linha, imprima o resto da divisão inteira de **N** por **M**.',
    '17 5
',
    'IMPAR
2
',
    '- `0 ≤ N ≤ 10^18`
- `1 ≤ M ≤ 10^18`',
    1,
    256,
    'Maratona Turing - Prova 1',
    NULL,
    'easy',
    '["maratona-turing", "matemática", "iniciante", "nivel-1"]',
    1,
    NOW(),
    NOW()
) ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    description = VALUES(description),
    input_description = VALUES(input_description),
    output_description = VALUES(output_description),
    sample_input = VALUES(sample_input),
    sample_output = VALUES(sample_output),
    notes = VALUES(notes),
    time_limit = VALUES(time_limit),
    memory_limit = VALUES(memory_limit),
    difficulty = VALUES(difficulty),
    tags = VALUES(tags),
    is_active = 1,
    updated_at = NOW();

INSERT INTO problem_bank (
    code, name, description, input_description, output_description,
    sample_input, sample_output, notes, time_limit, memory_limit,
    source, source_url, difficulty, tags, is_active, created_at, updated_at
) VALUES (
    'I02',
    'Média no campus',
    'A almirante Grace Hopper, lendária por caçar uma mariposa real presa nos relés do Mark II e popularizar o termo *bug*, visitou o campus do IFMG em Congonhas, à sombra dos Doze Profetas de pedra-sabão. Ela passou a tarde inspecionando códigos para garantir que nenhum inseto eletrônico passasse despercebido.

Ao final da oficina de programação, Grace reuniu as notas dos estudantes de uma turma, todas na mesma escala. Agora, a pioneira precisa consolidar a planilha final calculando a soma de todas as notas e a média aritmética da turma.

Dadas as notas, ajude Grace a calcular a soma e a média, ambas com duas casas decimais.',
    'A primeira linha contém um inteiro **N**, a quantidade de estudantes. A segunda linha contém **N** números reais **A_1, A_2, ..., A_N**, que são as notas dos estudantes, cada uma com até duas casas decimais.',
    'Na primeira linha, imprima a soma das notas com exatamente duas casas decimais. Na segunda linha, imprima a média com exatamente duas casas decimais.',
    '4
6 8 7 9
',
    '30.00
7.50
',
    '- `1 ≤ N ≤ 10^5`
- `0 ≤ A_i ≤ 100`',
    1,
    256,
    'Maratona Turing - Prova 1',
    NULL,
    'easy',
    '["maratona-turing", "vetor", "iniciante", "nivel-1"]',
    1,
    NOW(),
    NOW()
) ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    description = VALUES(description),
    input_description = VALUES(input_description),
    output_description = VALUES(output_description),
    sample_input = VALUES(sample_input),
    sample_output = VALUES(sample_output),
    notes = VALUES(notes),
    time_limit = VALUES(time_limit),
    memory_limit = VALUES(memory_limit),
    difficulty = VALUES(difficulty),
    tags = VALUES(tags),
    is_active = 1,
    updated_at = NOW();

INSERT INTO problem_bank (
    code, name, description, input_description, output_description,
    sample_input, sample_output, notes, time_limit, memory_limit,
    source, source_url, difficulty, tags, is_active, created_at, updated_at
) VALUES (
    'I03',
    'Vogais na estação',
    'Na histórica estação ferroviária de Conselheiro Lafaiete, por onde passam composições de minério e passageiros, a organização da Maratona Turing instalou placas de boas-vindas. Como o falar mineiro adora encurtar palavras e emendar vogais ("uai", "trem", "ocê"), os letreiros chamaram a atenção de quem desembarcava.

Ada Lovelace, fascinada por padrões poéticos e matemáticos na linguagem, decidiu conferir a sonoridade dos textos contando quantas vogais aparecem em cada frase. Para esta contagem, apenas as letras `a`, `e`, `i`, `o` e `u` (maiúsculas ou minúsculas, sem acento) são consideradas vogais.

Dada a frase da placa, ajude Ada calculando o número total de vogais.',
    'Uma linha com o texto **S** da placa, que pode conter espaços.',
    'Um único inteiro: a quantidade de vogais em **S**.',
    'Ouro Branco
',
    '5
',
    '- `1 ≤ |S| ≤ 10^5`
- **S** contém apenas caracteres ASCII. Letras acentuadas não aparecem.',
    1,
    256,
    'Maratona Turing - Prova 1',
    NULL,
    'easy',
    '["maratona-turing", "string", "iniciante", "nivel-1"]',
    1,
    NOW(),
    NOW()
) ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    description = VALUES(description),
    input_description = VALUES(input_description),
    output_description = VALUES(output_description),
    sample_input = VALUES(sample_input),
    sample_output = VALUES(sample_output),
    notes = VALUES(notes),
    time_limit = VALUES(time_limit),
    memory_limit = VALUES(memory_limit),
    difficulty = VALUES(difficulty),
    tags = VALUES(tags),
    is_active = 1,
    updated_at = NOW();

INSERT INTO problem_bank (
    code, name, description, input_description, output_description,
    sample_input, sample_output, notes, time_limit, memory_limit,
    source, source_url, difficulty, tags, is_active, created_at, updated_at
) VALUES (
    'I04',
    'A maior e a menor altitude',
    'Katherine Johnson, que calculou à mão as trajetórias espaciais na NASA que levaram a humanidade à Lua, foi desafiada a encarar as ladeiras da Estrada Real entre Ouro Branco e Ouro Preto. Após subir e descer serras íngremes, ela brincou que a topografia de Minas parecia mais desafiadora do que calcular órbitas planetárias.

Munida de seus instrumentos, Katherine registrou a variação de altitude em metros de cada mirante de parada em relação ao ponto de largada. Agora, a matemática quer identificar com precisão a menor e a maior variação de altitude registradas em toda a expedição.

Dadas as medições, ajude Katherine a descobrir qual foi a menor e qual foi a maior variação de altitude medida.',
    'A primeira linha contém um inteiro **N**, a quantidade de medições. A segunda linha contém **N** inteiros **A_1, A_2, ..., A_N**, que são as variações de altitude medidas.',
    'Na primeira linha, imprima o menor valor. Na segunda linha, imprima o maior valor.',
    '5
12 4 19 7 4
',
    '4
19
',
    '- `1 ≤ N ≤ 10^5`
- `-10^9 ≤ A_i ≤ 10^9`',
    1,
    256,
    'Maratona Turing - Prova 1',
    NULL,
    'medium',
    '["maratona-turing", "vetor", "iniciante", "nivel-1"]',
    1,
    NOW(),
    NOW()
) ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    description = VALUES(description),
    input_description = VALUES(input_description),
    output_description = VALUES(output_description),
    sample_input = VALUES(sample_input),
    sample_output = VALUES(sample_output),
    notes = VALUES(notes),
    time_limit = VALUES(time_limit),
    memory_limit = VALUES(memory_limit),
    difficulty = VALUES(difficulty),
    tags = VALUES(tags),
    is_active = 1,
    updated_at = NOW();

INSERT INTO problem_bank (
    code, name, description, input_description, output_description,
    sample_input, sample_output, notes, time_limit, memory_limit,
    source, source_url, difficulty, tags, is_active, created_at, updated_at
) VALUES (
    'I05',
    'Palavras no caminho para Ouro Preto',
    'Joan Clarke, a brilhante criptoanalista que desvendava mensagens secretas na Segunda Guerra ao lado de Alan Turing, passeava pelas ladeiras de pedra de Ouro Preto admirando as placas das antigas repúblicas estudantis da Praça Tiradentes.

Fascinada por quebra-cabeças linguísticos, Joan reparou que alguns nomes ficam rigorosamente idênticos quando lidos de trás para frente, formando palíndromos. Com rigor de criptógrafa, ela lembra que maiúsculas e minúsculas são caracteres distintos, de modo que `Radar` **não** é considerado um palíndromo.

Dada uma palavra **S**, ajude Joan escrevendo a palavra invertida e dizendo se **S** é um palíndromo.',
    'Uma linha com a palavra **S**, formada apenas por letras, sem espaços.',
    'Na primeira linha, imprima **S** invertida. Na segunda linha, imprima `SIM` se **S** for palíndromo ou `NAO` caso contrário.',
    'radar
',
    'radar
SIM
',
    '- `1 ≤ |S| ≤ 10^5`
- **S** contém apenas letras ASCII (maiúsculas e minúsculas).',
    1,
    256,
    'Maratona Turing - Prova 1',
    NULL,
    'medium',
    '["maratona-turing", "string", "iniciante", "nivel-1"]',
    1,
    NOW(),
    NOW()
) ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    description = VALUES(description),
    input_description = VALUES(input_description),
    output_description = VALUES(output_description),
    sample_input = VALUES(sample_input),
    sample_output = VALUES(sample_output),
    notes = VALUES(notes),
    time_limit = VALUES(time_limit),
    memory_limit = VALUES(memory_limit),
    difficulty = VALUES(difficulty),
    tags = VALUES(tags),
    is_active = 1,
    updated_at = NOW();

INSERT INTO problem_bank (
    code, name, description, input_description, output_description,
    sample_input, sample_output, notes, time_limit, memory_limit,
    source, source_url, difficulty, tags, is_active, created_at, updated_at
) VALUES (
    'I06',
    'Os divisores de von Neumann',
    'John von Neumann era famoso por seu cérebro prodigioso: calculava equações complexas de cabeça mais rápido que os primeiros computadores, mas vivia se distraindo ao fatorar placas de trânsito enquanto dirigia. Em visita a Congonhas, em frente ao Santuário de Bom Jesus de Matosinhos, ele avistou o número gravado no pedestal de um profeta de pedra-sabão.

Incapaz de resistir a um enigma aritmético, von Neumann decidiu dissecar o número em todos os seus fatores. Ele precisa listar todos os divisores inteiros positivos de **N** em ordem crescente, contar quantos são e determinar se **N** é um número primo (isto é, se possui exatamente dois divisores positivos: 1 e ele mesmo).

Dado o número **N**, ajude von Neumann informando quantos divisores **N** possui, listando-os em ordem crescente e dizendo se **N** é primo.',
    'Uma linha com um inteiro **N**, o número gravado na placa.',
    'Na primeira linha, imprima a quantidade de divisores positivos de **N**. Na segunda linha, imprima os divisores em ordem crescente, separados por espaço. Na terceira linha, imprima `PRIMO` ou `NAO PRIMO`.',
    '12
',
    '6
1 2 3 4 6 12
NAO PRIMO
',
    '- `1 ≤ N ≤ 10^12`
- O número 1 não é primo.',
    1,
    256,
    'Maratona Turing - Prova 1',
    NULL,
    'medium',
    '["maratona-turing", "matemática", "iniciante", "nivel-1"]',
    1,
    NOW(),
    NOW()
) ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    description = VALUES(description),
    input_description = VALUES(input_description),
    output_description = VALUES(output_description),
    sample_input = VALUES(sample_input),
    sample_output = VALUES(sample_output),
    notes = VALUES(notes),
    time_limit = VALUES(time_limit),
    memory_limit = VALUES(memory_limit),
    difficulty = VALUES(difficulty),
    tags = VALUES(tags),
    is_active = 1,
    updated_at = NOW();

INSERT INTO problem_bank (
    code, name, description, input_description, output_description,
    sample_input, sample_output, notes, time_limit, memory_limit,
    source, source_url, difficulty, tags, is_active, created_at, updated_at
) VALUES (
    'I07',
    'Somando os setores do IFMG',
    'Margaret Hamilton, a engenheira da NASA que chefiou a equipe do software de bordo da missão Apollo — empilhando volumes de código mais altos do que ela própria —, visitou o campus do IFMG em Ouro Branco e foi convidada a organizar o almoxarifado de informática da instituição.

Para colocar ordem no inventário de peças e cabos, Margaret estruturou uma matriz de contagem: cada linha representa um setor do campus e cada coluna representa um tipo de material, com a quantidade de itens guardada em cada célula. Ela precisa fechar o balanço calculando a soma total de materiais de cada setor e a soma acumulada de cada tipo de material em todos os setores.

Dada a tabela, ajude Margaret calculando a soma de cada linha e a soma de cada coluna.',
    'A primeira linha contém dois inteiros **N** e **M**, que são, respectivamente, o número de setores (linhas) e o número de tipos de material (colunas). Em seguida, **N** linhas contêm **M** inteiros cada, os valores **A_ij**, que são a quantidade do material **j** no setor **i**.',
    'Na primeira linha, imprima as **N** somas das linhas, separadas por espaço. Na segunda linha, imprima as **M** somas das colunas, separadas por espaço.',
    '2 3
1 2 3
4 5 6
',
    '6 15
5 7 9
',
    '- `1 ≤ N, M ≤ 500`
- `0 ≤ A_ij ≤ 10^6`',
    1,
    256,
    'Maratona Turing - Prova 1',
    NULL,
    'medium',
    '["maratona-turing", "matriz", "iniciante", "nivel-1"]',
    1,
    NOW(),
    NOW()
) ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    description = VALUES(description),
    input_description = VALUES(input_description),
    output_description = VALUES(output_description),
    sample_input = VALUES(sample_input),
    sample_output = VALUES(sample_output),
    notes = VALUES(notes),
    time_limit = VALUES(time_limit),
    memory_limit = VALUES(memory_limit),
    difficulty = VALUES(difficulty),
    tags = VALUES(tags),
    is_active = 1,
    updated_at = NOW();

INSERT INTO problem_bank (
    code, name, description, input_description, output_description,
    sample_input, sample_output, notes, time_limit, memory_limit,
    source, source_url, difficulty, tags, is_active, created_at, updated_at
) VALUES (
    'I08',
    'O valor mais frequente',
    'No aquecimento da Maratona Turing no campus de Ouro Branco, o nervosismo tomou conta dos laboratórios. Com medo de perder tempo no cronômetro, diversos competidores digitaram seus códigos de acesso de forma repetida no sistema, gerando uma lista cheia de duplicatas.

Alan Turing e Joan Clarke foram chamados à coordenação para analisar o relatório de conexões. Eles precisam descobrir qual código numérico apareceu com a maior frequência. Caso haja empate entre dois ou mais códigos com a mesma quantidade máxima de aparições, o critério de desempate deve escolher o de menor valor numérico.

Dados os códigos registrados, ajude a dupla a encontrar o código com maior frequência, desempatando pelo menor valor.',
    'A primeira linha contém um inteiro **N**, a quantidade de códigos registrados. A segunda linha contém **N** inteiros **A_1, A_2, ..., A_N**, que são os códigos, em ordem de registro.',
    'Um inteiro: o código que mais aparece. Em caso de empate, o menor deles.',
    '7
4 2 4 3 2 4 3
',
    '4
',
    '- `1 ≤ N ≤ 2·10^5`
- `-10^9 ≤ A_i ≤ 10^9`',
    1,
    256,
    'Maratona Turing - Prova 1',
    NULL,
    'medium',
    '["maratona-turing", "vetor", "iniciante", "nivel-1"]',
    1,
    NOW(),
    NOW()
) ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    description = VALUES(description),
    input_description = VALUES(input_description),
    output_description = VALUES(output_description),
    sample_input = VALUES(sample_input),
    sample_output = VALUES(sample_output),
    notes = VALUES(notes),
    time_limit = VALUES(time_limit),
    memory_limit = VALUES(memory_limit),
    difficulty = VALUES(difficulty),
    tags = VALUES(tags),
    is_active = 1,
    updated_at = NOW();

INSERT INTO problem_bank (
    code, name, description, input_description, output_description,
    sample_input, sample_output, notes, time_limit, memory_limit,
    source, source_url, difficulty, tags, is_active, created_at, updated_at
) VALUES (
    'I09',
    'Rotas sincronizadas',
    'Charles Babbage foi o pioneiro que projetou as primeiras máquinas computacionais mecânicas movidas a vapor e que ficou conhecido por sua aversão a qualquer barulho que atrapalhasse sua concentração. Convidado para a Maratona, ele assumiu a coordenação das vans de transporte que circulam pela BR-040 entre Ouro Branco e Conselheiro Lafaiete.

Buscando impor pontualidade britânica ao tráfego rodoviário, Babbage cronometrou que a primeira van passa pelo ponto de encontro a cada **N** minutos e a segunda a cada **M** minutos, ambas partindo juntas no instante zero. Ele precisa saber o maior intervalo de tempo que divide exatamente os dois períodos e o primeiro instante positivo em que as duas vans voltarão a passar juntas pelo ponto.

Dados **N** e **M**, ajude Babbage calculando o MDC e o MMC desses dois valores.',
    'Uma linha com dois inteiros **N** e **M**, que são, respectivamente, o intervalo em minutos da primeira e da segunda van.',
    'Na primeira linha, imprima o MDC de **N** e **M**. Na segunda linha, imprima o MMC de **N** e **M**.',
    '12 18
',
    '6
36
',
    '- `1 ≤ N, M ≤ 10^9`',
    1,
    256,
    'Maratona Turing - Prova 1',
    NULL,
    'medium',
    '["maratona-turing", "matemática", "iniciante", "nivel-1"]',
    1,
    NOW(),
    NOW()
) ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    description = VALUES(description),
    input_description = VALUES(input_description),
    output_description = VALUES(output_description),
    sample_input = VALUES(sample_input),
    sample_output = VALUES(sample_output),
    notes = VALUES(notes),
    time_limit = VALUES(time_limit),
    memory_limit = VALUES(memory_limit),
    difficulty = VALUES(difficulty),
    tags = VALUES(tags),
    is_active = 1,
    updated_at = NOW();

INSERT INTO problem_bank (
    code, name, description, input_description, output_description,
    sample_input, sample_output, notes, time_limit, memory_limit,
    source, source_url, difficulty, tags, is_active, created_at, updated_at
) VALUES (
    'I10',
    'A mensagem de Turing',
    'Para garantir que as estratégias das equipes e as receitas de pastel de feira não vazassem entre os campi de Ouro Branco e Ouro Preto antes da Maratona, os estudantes decidiram codificar seus avisos. Como não havia tempo nem peças para montar réplicas da máquina Enigma, recorreram a uma cifra clássica de substituição.

Alan Turing e Joan Clarke estabeleceram uma cifra circular com deslocamento **K**: cada letra da mensagem avança **K** posições no alfabeto (ou recua se **K** for negativo), de forma circular (depois de `z` vem `a`, e depois de `Z` vem `A`). Letras maiúsculas continuam maiúsculas, minúsculas permanecem minúsculas, e espaços, números e sinais de pontuação não sofrem alteração alguma.

Dados o deslocamento e a mensagem, ajude os dois escrevendo a mensagem cifrada.',
    'A primeira linha contém um inteiro **K**, o deslocamento. A segunda linha contém o texto **S**, a mensagem a ser cifrada, que pode conter espaços.',
    'Uma linha com a mensagem cifrada.',
    '3
Ada Lovelace!
',
    'Dgd Oryhodfh!
',
    '- `-10^9 ≤ K ≤ 10^9`
- `1 ≤ |S| ≤ 10^5`
- **S** contém apenas caracteres ASCII.',
    1,
    256,
    'Maratona Turing - Prova 1',
    NULL,
    'medium',
    '["maratona-turing", "string", "iniciante", "nivel-1"]',
    1,
    NOW(),
    NOW()
) ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    description = VALUES(description),
    input_description = VALUES(input_description),
    output_description = VALUES(output_description),
    sample_input = VALUES(sample_input),
    sample_output = VALUES(sample_output),
    notes = VALUES(notes),
    time_limit = VALUES(time_limit),
    memory_limit = VALUES(memory_limit),
    difficulty = VALUES(difficulty),
    tags = VALUES(tags),
    is_active = 1,
    updated_at = NOW();

INSERT INTO problem_bank (
    code, name, description, input_description, output_description,
    sample_input, sample_output, notes, time_limit, memory_limit,
    source, source_url, difficulty, tags, is_active, created_at, updated_at
) VALUES (
    'I11',
    'Pintura econômica',
    'Jean Bartik foi uma das destemidas mulheres que programaram o ENIAC na década de 1940, manipulando pesados cabos elétricos e chaves de força com as próprias mãos. Convidada para uma mostra de arte digital em Ouro Preto, Jean encontrou uma grade quadriculada de lâmpadas pretas e brancas desordenada pouco antes da inauguração.

Para ajustar o mural e atingir o desenho encomendado, Jean opera uma bancada onde pode realizar duas operações: escolher uma linha inteira e inverter todas as suas cores, ou escolher uma coluna inteira e inverter todas as suas cores (branco vira preto e preto vira branco). Como repetir a operação na mesma linha ou coluna anula o efeito, cada linha e cada coluna é acionada no máximo uma vez. Jean deseja atingir o padrão final com o menor número de operações possíveis, ou constatar que isso é impossível.

Dados o quadro atual e o quadro desejado, ajude Jean a descobrir o menor número de operações necessárias, ou informar que é impossível.',
    'A primeira linha contém dois inteiros **N** e **M**, que são, respectivamente, o número de linhas e de colunas do quadro. Em seguida, **N** linhas com **M** caracteres `0` (branco) ou `1` (preto) descrevem o quadro atual **A**. Depois, mais **N** linhas com **M** caracteres descrevem o quadro desejado **B**.',
    'Um inteiro: o menor número de operações para transformar **A** em **B**, ou `-1` se for impossível.',
    '2 2
00
00
11
11
',
    '2
',
    '- `1 ≤ N, M ≤ 500`',
    1,
    256,
    'Maratona Turing - Prova 1',
    NULL,
    'hard',
    '["maratona-turing", "matriz", "iniciante", "nivel-1"]',
    1,
    NOW(),
    NOW()
) ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    description = VALUES(description),
    input_description = VALUES(input_description),
    output_description = VALUES(output_description),
    sample_input = VALUES(sample_input),
    sample_output = VALUES(sample_output),
    notes = VALUES(notes),
    time_limit = VALUES(time_limit),
    memory_limit = VALUES(memory_limit),
    difficulty = VALUES(difficulty),
    tags = VALUES(tags),
    is_active = 1,
    updated_at = NOW();

INSERT INTO problem_bank (
    code, name, description, input_description, output_description,
    sample_input, sample_output, notes, time_limit, memory_limit,
    source, source_url, difficulty, tags, is_active, created_at, updated_at
) VALUES (
    'I12',
    'Consultas no caminho',
    'Konrad Zuse, pioneiro alemão que construiu os primeiros computadores funcionais na sala de seus pais usando chapas metálicas e tiras perfuradas de filme de 35mm, decidiu explorar as trilhas da Estrada Real em Congonhas carregando seus instrumentos de medição.

Metódico com seus registros de engenharia, Zuse dividiu a trilha em trechos consecutivos e mediu a variação de altitude de cada um, em metros (valores positivos nas subidas e negativos nas descidas). Para informar aos caminhantes o esforço do trajeto, ele precisa responder a várias consultas informando o saldo acumulado de altitude entre dois trechos **L** e **R**, incluindo ambos os extremos.

Dadas as variações de cada trecho e várias consultas, ajude Zuse respondendo a cada consulta com a soma das variações do trecho **L** ao trecho **R**.',
    'A primeira linha contém dois inteiros **N** e **Q**, que são, respectivamente, o número de trechos e o número de consultas. A segunda linha contém **N** inteiros **A_1, A_2, ..., A_N**, que são as variações de altitude dos trechos. Cada uma das próximas **Q** linhas contém dois inteiros **L** e **R**, que são, respectivamente, o primeiro e o último trecho da consulta.',
    'Para cada consulta, imprima em uma linha a soma de **A_L** até **A_R**, inclusive.',
    '5 3
2 4 6 8 10
1 3
2 5
4 4
',
    '12
28
8
',
    '- `1 ≤ N, Q ≤ 2·10^5`
- `-10^9 ≤ A_i ≤ 10^9`
- `1 ≤ L ≤ R ≤ N`',
    1,
    256,
    'Maratona Turing - Prova 1',
    NULL,
    'hard',
    '["maratona-turing", "vetor", "iniciante", "nivel-1"]',
    1,
    NOW(),
    NOW()
) ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    description = VALUES(description),
    input_description = VALUES(input_description),
    output_description = VALUES(output_description),
    sample_input = VALUES(sample_input),
    sample_output = VALUES(sample_output),
    notes = VALUES(notes),
    time_limit = VALUES(time_limit),
    memory_limit = VALUES(memory_limit),
    difficulty = VALUES(difficulty),
    tags = VALUES(tags),
    is_active = 1,
    updated_at = NOW();

INSERT INTO problem_bank (
    code, name, description, input_description, output_description,
    sample_input, sample_output, notes, time_limit, memory_limit,
    source, source_url, difficulty, tags, is_active, created_at, updated_at
) VALUES (
    'T01',
    'Pares na estrada',
    'Durante a viagem de van entre Ouro Branco e Conselheiro Lafaiete pela BR-040, Ada Lovelace observava com atenção as inúmeras placas de trânsito e marcos de quilometragem instalados ao longo da rodovia.

Sempre atenta a padrões numéricos, a primeira programadora da história anotou os valores das placas em seu caderno de viagem e decidiu classificá-los conforme a paridade: separando quantos números são pares e quantos são ímpares.

Dada a lista de números, ajude Ada contando quantos são pares e quantos são ímpares.',
    'A primeira linha contém um inteiro **N**, a quantidade de placas. A segunda linha contém **N** inteiros **A_1, A_2, ..., A_N**, que são os números das placas.',
    'Uma linha com dois inteiros separados por espaço: a quantidade de números pares e a quantidade de números ímpares, nessa ordem.',
    '5
1 2 3 4 6
',
    '3 2
',
    '- `1 ≤ N ≤ 10^6`
- `-10^9 ≤ A_i ≤ 10^9`',
    1,
    256,
    'Maratona Turing - Prova 1',
    NULL,
    'easy',
    '["maratona-turing", "vetor", "técnico", "nivel-2"]',
    1,
    NOW(),
    NOW()
) ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    description = VALUES(description),
    input_description = VALUES(input_description),
    output_description = VALUES(output_description),
    sample_input = VALUES(sample_input),
    sample_output = VALUES(sample_output),
    notes = VALUES(notes),
    time_limit = VALUES(time_limit),
    memory_limit = VALUES(memory_limit),
    difficulty = VALUES(difficulty),
    tags = VALUES(tags),
    is_active = 1,
    updated_at = NOW();

INSERT INTO problem_bank (
    code, name, description, input_description, output_description,
    sample_input, sample_output, notes, time_limit, memory_limit,
    source, source_url, difficulty, tags, is_active, created_at, updated_at
) VALUES (
    'T02',
    'Número de Newton',
    'Sir Isaac Newton, famoso tanto pela história da maçã quanto por formular as leis clássicas do movimento e cálculos de aproximação, visitou os laboratórios no campus de Ouro Branco e encontrou estudantes formulando enigmas aritméticos.

Para testar a agilidade de cálculo da turma, Newton propôs uma regra especial para classificar os equipamentos a partir de seu número de patrimônio **N**: primeiro somam-se todos os algarismos de **N**; em seguida, calcula-se o fatorial dessa soma. Como fatoriais crescem rapidamente e podem acumular muitos dígitos, o resultado deve ser apresentado por completo.

Dado o número de catálogo **N**, ajude a turma calculando a soma dos algarismos de **N** e o fatorial dessa soma.',
    'Uma linha com um inteiro **N**, o número de catálogo do equipamento.',
    'Na primeira linha, imprima a soma dos algarismos de **N**. Na segunda linha, imprima o fatorial dessa soma.',
    '1234
',
    '10
3628800
',
    '- `0 ≤ N ≤ 10^18`',
    1,
    256,
    'Maratona Turing - Prova 1',
    NULL,
    'easy',
    '["maratona-turing", "matemática", "técnico", "nivel-2"]',
    1,
    NOW(),
    NOW()
) ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    description = VALUES(description),
    input_description = VALUES(input_description),
    output_description = VALUES(output_description),
    sample_input = VALUES(sample_input),
    sample_output = VALUES(sample_output),
    notes = VALUES(notes),
    time_limit = VALUES(time_limit),
    memory_limit = VALUES(memory_limit),
    difficulty = VALUES(difficulty),
    tags = VALUES(tags),
    is_active = 1,
    updated_at = NOW();

INSERT INTO problem_bank (
    code, name, description, input_description, output_description,
    sample_input, sample_output, notes, time_limit, memory_limit,
    source, source_url, difficulty, tags, is_active, created_at, updated_at
) VALUES (
    'T03',
    'Anagramas do Alto Paraopeba',
    'O Alto Paraopeba reúne municípios com rica história e nomes sonoros, como Congonhas, Lafaiete e Ouro Branco. Durante o credenciamento da Maratona, Ada Lovelace reparou que os crachás impressos na pressa continham trocas divertidas de letras, formando palavras embaralhadas.

Para identificar quais palavras eram correspondentes, Ada estabeleceu uma verificação de anagramas: duas palavras são consideradas anagramas quando possuem rigorosamente as mesmas letras com as mesmas quantidades, em qualquer ordem, tratando letras maiúsculas e minúsculas como equivalentes.

Dadas duas palavras, ajude Ada a decidir se uma é anagrama da outra.',
    'Duas linhas. A primeira contém a palavra **S** e a segunda contém a palavra **T**, ambas formadas apenas por letras, sem espaços.',
    'Imprima `SIM` se **S** e **T** forem anagramas ou `NAO` caso contrário.',
    'Roma
Amor
',
    'SIM
',
    '- `1 ≤ |S|, |T| ≤ 10^5`
- **S** e **T** contêm apenas letras ASCII.',
    1,
    256,
    'Maratona Turing - Prova 1',
    NULL,
    'medium',
    '["maratona-turing", "string", "técnico", "nivel-2"]',
    1,
    NOW(),
    NOW()
) ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    description = VALUES(description),
    input_description = VALUES(input_description),
    output_description = VALUES(output_description),
    sample_input = VALUES(sample_input),
    sample_output = VALUES(sample_output),
    notes = VALUES(notes),
    time_limit = VALUES(time_limit),
    memory_limit = VALUES(memory_limit),
    difficulty = VALUES(difficulty),
    tags = VALUES(tags),
    is_active = 1,
    updated_at = NOW();

INSERT INTO problem_bank (
    code, name, description, input_description, output_description,
    sample_input, sample_output, notes, time_limit, memory_limit,
    source, source_url, difficulty, tags, is_active, created_at, updated_at
) VALUES (
    'T04',
    'Quadrado mágico de Ouro Preto',
    'Claude Shannon, pai da teoria da informação e célebre por andar de monociclo fazendo malabarismos pelos laboratórios da Bell Labs, passeava pelas feirinhas da Praça Tiradentes, em Ouro Preto, quando encontrou belos tabuleiros de pedra-sabão rotulados como "quadrados mágicos".

Apaixonado por enigmas matemáticos, Shannon decidiu validar se as peças eram autênticas: para ser um quadrado mágico legítimo de ordem **N**, a grade precisa conter todos os inteiros de 1 a $N^2$ exatamente uma vez, e todas as linhas, todas as colunas e as duas diagonais principais devem ter exatamente a mesma soma.

Dado um quadrado com **N** linhas e **N** colunas, ajude Shannon a dizer se ele é um quadrado mágico.',
    'A primeira linha contém um inteiro **N**, o tamanho do quadrado. Em seguida, **N** linhas contêm **N** inteiros cada, os valores **A_ij**, que são o número na linha **i** e coluna **j**.',
    'Imprima `SIM` se o quadrado for mágico ou `NAO` caso contrário.',
    '3
8 1 6
3 5 7
4 9 2
',
    'SIM
',
    '- `1 ≤ N ≤ 200`
- `1 ≤ A_ij ≤ N²`',
    1,
    256,
    'Maratona Turing - Prova 1',
    NULL,
    'medium',
    '["maratona-turing", "matriz", "técnico", "nivel-2"]',
    1,
    NOW(),
    NOW()
) ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    description = VALUES(description),
    input_description = VALUES(input_description),
    output_description = VALUES(output_description),
    sample_input = VALUES(sample_input),
    sample_output = VALUES(sample_output),
    notes = VALUES(notes),
    time_limit = VALUES(time_limit),
    memory_limit = VALUES(memory_limit),
    difficulty = VALUES(difficulty),
    tags = VALUES(tags),
    is_active = 1,
    updated_at = NOW();

INSERT INTO problem_bank (
    code, name, description, input_description, output_description,
    sample_input, sample_output, notes, time_limit, memory_limit,
    source, source_url, difficulty, tags, is_active, created_at, updated_at
) VALUES (
    'G01',
    'Encontrando a posição',
    'Donald Knuth, autor da monumental obra *The Art of Computer Programming* e famoso por seu extremo perfeccionismo — emitindo cheques reais de US$ 2,56 a quem encontrar qualquer falha em seus livros —, visitou a biblioteca do IFMG em Ouro Branco e ficou satisfeito ao encontrar o acervo rigorosamente ordenado por código.

Para avaliar a indexação da biblioteca, Knuth realiza consultas para diversos códigos de livros **X**. Para cada código, ele quer saber em qual posição da estante (começando em 1) encontra-se o primeiro exemplar. Se o código não estiver presente na estante, Knuth deseja saber em que posição ele deveria ser inserido para manter a ordenação crescente, sempre na primeira posição válida possível.

Dada a estante e as perguntas de Knuth, ajude-o a responder cada uma delas.',
    'A primeira linha contém dois inteiros **N** e **Q**, que são, respectivamente, o número de livros e o número de perguntas. A segunda linha contém **N** inteiros **A_1, A_2, ..., A_N**, que são os códigos dos livros em ordem não decrescente. Cada uma das próximas **Q** linhas contém um inteiro **X**, o código consultado.',
    'Para cada pergunta, imprima em uma linha a primeira posição (entre 1 e `N+1`) em que **X** pode ser colocado sem quebrar a ordem. Se **X** já existe, essa é a posição do primeiro exemplar.',
    '5 3
1 3 3 7 10
3
4
11
',
    '2
4
6
',
    '- `1 ≤ N, Q ≤ 2·10^5`
- `-10^9 ≤ A_i, X ≤ 10^9`',
    1,
    256,
    'Maratona Turing - Prova 1',
    NULL,
    'medium',
    '["maratona-turing", "vetor", "graduação", "nivel-3"]',
    1,
    NOW(),
    NOW()
) ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    description = VALUES(description),
    input_description = VALUES(input_description),
    output_description = VALUES(output_description),
    sample_input = VALUES(sample_input),
    sample_output = VALUES(sample_output),
    notes = VALUES(notes),
    time_limit = VALUES(time_limit),
    memory_limit = VALUES(memory_limit),
    difficulty = VALUES(difficulty),
    tags = VALUES(tags),
    is_active = 1,
    updated_at = NOW();

INSERT INTO problem_bank (
    code, name, description, input_description, output_description,
    sample_input, sample_output, notes, time_limit, memory_limit,
    source, source_url, difficulty, tags, is_active, created_at, updated_at
) VALUES (
    'G02',
    'Trecho mais variado',
    'Na histórica estação ferroviária de Conselheiro Lafaiete, um painel eletrônico exibe uma longa sequência contínua de letras para os passageiros. Claude Shannon, criador da teoria matemática da comunicação, observou o letreiro e resolveu analisar a variedade das sequências exibidas.

Shannon quer identificar o maior trecho contínuo do painel que possua variedade controlada de caracteres: ele busca o trecho consecutivo mais longo que contenha no máximo **K** letras distintas no total, sendo permitido que as letras se repitam qualquer número de vezes dentro desse intervalo.

Dado o painel, ajude Shannon a descobrir o comprimento do maior trecho consecutivo com no máximo **K** letras distintas.',
    'A primeira linha contém dois inteiros **N** e **K**, que são, respectivamente, o comprimento da sequência e o número máximo de letras distintas permitido. A segunda linha contém a sequência **S**, de tamanho **N**, formada por letras minúsculas.',
    'Um inteiro: o comprimento do maior trecho consecutivo de **S** com no máximo **K** letras distintas.',
    '8 2
abacccde
',
    '4
',
    '- `1 ≤ N ≤ 10^6`
- `1 ≤ K ≤ 26`
- **S** contém apenas letras minúsculas.',
    1,
    256,
    'Maratona Turing - Prova 1',
    NULL,
    'medium',
    '["maratona-turing", "string", "graduação", "nivel-3"]',
    1,
    NOW(),
    NOW()
) ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    description = VALUES(description),
    input_description = VALUES(input_description),
    output_description = VALUES(output_description),
    sample_input = VALUES(sample_input),
    sample_output = VALUES(sample_output),
    notes = VALUES(notes),
    time_limit = VALUES(time_limit),
    memory_limit = VALUES(memory_limit),
    difficulty = VALUES(difficulty),
    tags = VALUES(tags),
    is_active = 1,
    updated_at = NOW();

INSERT INTO problem_bank (
    code, name, description, input_description, output_description,
    sample_input, sample_output, notes, time_limit, memory_limit,
    source, source_url, difficulty, tags, is_active, created_at, updated_at
) VALUES (
    'G03',
    'Rede dos campi',
    'Tim Berners-Lee, pioneiro que concebeu a World Wide Web em 1989 para conectar pesquisadores ao redor do mundo, foi convidado para planejar a infraestrutura de dados da Maratona Turing, interligando a sede em Ouro Branco aos demais campi do IFMG.

Para garantir que a transmissão do placar e o envio de balões alcancem todas as equipes nas montanhas mineiras, Tim analisa a malha de canais de comunicação existentes, onde cada canal liga diretamente dois campi e opera nos dois sentidos. Ele precisa verificar se a rede formada é conexa — isto é, se é possível enviar uma mensagem a partir de qualquer campus e fazê-la chegar a qualquer outro (diretamente ou através de campi intermediários).

Dados os campi e os canais existentes, ajude Tim a verificar se é possível sair de qualquer campus e chegar a qualquer outro.',
    'A primeira linha contém dois inteiros **N** e **M**, que são, respectivamente, o número de campi (numerados de 1 a **N**) e o número de canais. Cada uma das próximas **M** linhas contém dois inteiros **U** e **V**, indicando um canal entre os campi **U** e **V**.',
    'Imprima `SIM` se todos os campi estiverem conectados entre si, ou `NAO` caso contrário.',
    '4 3
1 2
2 3
3 4
',
    'SIM
',
    '- `1 ≤ N ≤ 2·10^5`
- `0 ≤ M ≤ 3·10^5`
- `1 ≤ U, V ≤ N`
- `U ≠ V`; canais repetidos podem aparecer.',
    1,
    256,
    'Maratona Turing - Prova 1',
    NULL,
    'medium',
    '["maratona-turing", "grafo", "graduação", "nivel-3"]',
    1,
    NOW(),
    NOW()
) ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    description = VALUES(description),
    input_description = VALUES(input_description),
    output_description = VALUES(output_description),
    sample_input = VALUES(sample_input),
    sample_output = VALUES(sample_output),
    notes = VALUES(notes),
    time_limit = VALUES(time_limit),
    memory_limit = VALUES(memory_limit),
    difficulty = VALUES(difficulty),
    tags = VALUES(tags),
    is_active = 1,
    updated_at = NOW();

INSERT INTO problem_bank (
    code, name, description, input_description, output_description,
    sample_input, sample_output, notes, time_limit, memory_limit,
    source, source_url, difficulty, tags, is_active, created_at, updated_at
) VALUES (
    'G04',
    'O canhão de Newton',
    'No cume da Serra de Ouro Branco, diante de uma das vistas panorâmicas mais amplas de Minas Gerais, os estudantes de computação recordaram o famoso experimento mental de Sir Isaac Newton sobre disparar um projétil a partir do topo de uma montanha.

Para demonstrar o experimento aos competidores com segurança, a equipe técnica instalou um lançador mecânico a uma altura **H** do chão, disparando um projétil na horizontal com velocidade **V** sob aceleração da gravidade **G**, desprezando a resistência do ar. O tempo de queda $t$ até o solo satisfaz a equação $H = \\frac{G \\cdot t^2}{2}$, e a distância horizontal percorrida durante esse tempo é dada por $V \\cdot t$.

Dados a altura do canhão, a velocidade do disparo e a gravidade, ajude a equipe a calcular a distância horizontal percorrida até o projétil atingir o chão.',
    'Uma linha com três números reais **H**, **V** e **G**, que são, respectivamente, a altura do canhão, a velocidade horizontal do projétil e a aceleração da gravidade. Todas as unidades são consistentes entre si.',
    'Imprima a distância horizontal percorrida com exatamente seis casas decimais.',
    '20 10 10
',
    '20.000000
',
    '- `0 < H ≤ 10^6`
- `0 < V ≤ 10^4`
- `0 < G ≤ 10^4`',
    1,
    256,
    'Maratona Turing - Prova 1',
    NULL,
    'hard',
    '["maratona-turing", "matemática", "graduação", "nivel-3"]',
    1,
    NOW(),
    NOW()
) ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    description = VALUES(description),
    input_description = VALUES(input_description),
    output_description = VALUES(output_description),
    sample_input = VALUES(sample_input),
    sample_output = VALUES(sample_output),
    notes = VALUES(notes),
    time_limit = VALUES(time_limit),
    memory_limit = VALUES(memory_limit),
    difficulty = VALUES(difficulty),
    tags = VALUES(tags),
    is_active = 1,
    updated_at = NOW();

INSERT INTO problem_bank (
    code, name, description, input_description, output_description,
    sample_input, sample_output, notes, time_limit, memory_limit,
    source, source_url, difficulty, tags, is_active, created_at, updated_at
) VALUES (
    'G05',
    'O maior palíndromo do painel',
    'Barbara Liskov, referência mundial em arquitetura e qualidade de software — com princípios de abstração tão influentes que moldaram o desenvolvimento moderno —, visitou a mostra tecnológica em Congonhas, perto do Santuário de Bom Jesus de Matosinhos.

Ao acompanhar um painel digital luminoso que projetava uma longa sequência de caracteres minúsculos, Barbara resolveu buscar padrões de simetria no fluxo de texto: trechos contíguos que permanecem idênticos quando lidos de frente para trás ou de trás para frente (palíndromos). Ela quer descobrir o maior palíndromo consecutivo e o seu comprimento. Se houver mais de um palíndromo empatado com o tamanho máximo, prevalece aquele que aparece primeiro na sequência.

Dada a sequência do painel, ajude Barbara a descobrir o maior palíndromo consecutivo e seu tamanho.',
    'Uma linha com a sequência **S**, formada por letras minúsculas, sem espaços.',
    'Na primeira linha, imprima o maior palíndromo consecutivo de **S** (em caso de empate, o que começa primeiro). Na segunda linha, imprima o tamanho dele.',
    'babad
',
    'bab
3
',
    '- `1 ≤ |S| ≤ 2·10^5`
- **S** contém apenas letras minúsculas.',
    1,
    256,
    'Maratona Turing - Prova 1',
    NULL,
    'hard',
    '["maratona-turing", "string", "graduação", "nivel-3"]',
    1,
    NOW(),
    NOW()
) ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    description = VALUES(description),
    input_description = VALUES(input_description),
    output_description = VALUES(output_description),
    sample_input = VALUES(sample_input),
    sample_output = VALUES(sample_output),
    notes = VALUES(notes),
    time_limit = VALUES(time_limit),
    memory_limit = VALUES(memory_limit),
    difficulty = VALUES(difficulty),
    tags = VALUES(tags),
    is_active = 1,
    updated_at = NOW();

INSERT INTO problem_bank (
    code, name, description, input_description, output_description,
    sample_input, sample_output, notes, time_limit, memory_limit,
    source, source_url, difficulty, tags, is_active, created_at, updated_at
) VALUES (
    'G06',
    'Caminho de maior soma',
    'Edsger Dijkstra, lendário pioneiro dos algoritmos de menor caminho e célebre por sua aversão a atalhos improvisados (declarando guerra aberta ao comando `goto` em nome da disciplina na programação), visitou o trecho da Estrada Real entre Ouro Preto e Congonhas e resolveu disputar um jogo de trilha virtual criado para a Maratona.

O desafio acontece sobre um tabuleiro quadriculado de $N$ linhas e $M$ colunas, onde cada célula possui uma pontuação inteira (positiva ou negativa). Partindo da célula superior esquerda $(1, 1)$ com destino à célula inferior direita $(N, M)$, Dijkstra impôs a regra de caminhar a cada passo unicamente para a célula da direita ou para a célula de baixo. A pontuação final da caminhada é a soma de todas as células visitadas ao longo do trajeto, incluindo a inicial e a final.

Dada a grade, ajude Dijkstra descobrindo a maior pontuação possível ao final do caminho.',
    'A primeira linha contém dois inteiros **N** e **M**, que são, respectivamente, o número de linhas e o de colunas da grade. Em seguida, **N** linhas contêm **M** inteiros cada, os valores **A_ij**, que são a pontuação da célula na linha **i** e coluna **j**.',
    'Um inteiro: a maior soma possível entre os caminhos do canto superior esquerdo ao canto inferior direito.',
    '3 3
5 1 2
1 9 1
2 1 8
',
    '24
',
    '- `1 ≤ N, M ≤ 1000`
- `-10^6 ≤ A_ij ≤ 10^6`',
    2,
    256,
    'Maratona Turing - Prova 1',
    NULL,
    'hard',
    '["maratona-turing", "matriz", "graduação", "nivel-3"]',
    1,
    NOW(),
    NOW()
) ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    description = VALUES(description),
    input_description = VALUES(input_description),
    output_description = VALUES(output_description),
    sample_input = VALUES(sample_input),
    sample_output = VALUES(sample_output),
    notes = VALUES(notes),
    time_limit = VALUES(time_limit),
    memory_limit = VALUES(memory_limit),
    difficulty = VALUES(difficulty),
    tags = VALUES(tags),
    is_active = 1,
    updated_at = NOW();
