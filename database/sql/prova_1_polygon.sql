-- PROVA 1 - MARATONA TURING: POLYGON EXERCISES (rascunhos)
-- Requer @prova_owner_id (definido por ProvaPolygonSeeder).

-- polygon_exercises nao possui chave unica: remove antes para manter idempotencia.
DELETE FROM polygon_exercises WHERE short_name IN ('I01', 'I02', 'I03', 'I04', 'I05', 'I06', 'I07', 'I08', 'I09', 'I10', 'I11', 'I12', 'T01', 'T02', 'T03', 'T04', 'G01', 'G02', 'G03', 'G04', 'G05', 'G06');

-- 2. Inserção / Atualização em polygon_exercises (Rascunhos Polygon com C++ ACCEPT)
INSERT INTO polygon_exercises (
    contest_id, name, short_name, basename, time_limit, memory_limit, output_limit,
    description, input_description, output_description, notes,
    color_name, color_hex, generator_source, generator_script,
    manual_tests, solution_accept, solution_tle, solution_wrong,
    additional_solutions, status, calibration_results,
    published_problem_id, published_bank_code, created_by, created_at, updated_at
) VALUES (
    NULL,
    'O resto da visita de Turing',
    'I01',
    'i01_o_resto_da_visita_de_turing',
    1,
    256,
    1024,
    'Alan Turing, célebre por decifrar códigos secretos e desenhar os fundamentos da computação, foi recebido com festa no campus do IFMG em Ouro Branco, aos pés da serra. Atraída pela palestra e pelo cheiro de broas e café da recepção mineira, uma grande multidão vinda de Lafaiete e Congonhas lotou o auditório.

Ada Lovelace, pioneira dos algoritmos, assumiu a missão de organizar os visitantes em grupos com exatamente o mesmo número de pessoas. Para coordenar as salas, Ada quer saber se o total de visitantes é par ou ímpar e quantas pessoas ficarão de fora dos grupos completos.

Dados o número de visitantes **N** e o tamanho de cada grupo **M**, ajude Ada a informar se **N** é par ou ímpar e qual é o resto da divisão de **N** por **M**.',
    'Uma linha com dois inteiros **N** e **M**, que são, respectivamente, o número de visitantes e o número de pessoas em cada grupo.',
    'Na primeira linha, imprima `PAR` se **N** for par ou `IMPAR` caso contrário. Na segunda linha, imprima o resto da divisão inteira de **N** por **M**.',
    '- `0 ≤ N ≤ 10^18`
- `1 ≤ M ≤ 10^18`',
    'Azul',
    '#3B82F6',
    '#include "testlib.h"
#include <iostream>
#include <string>

using namespace std;

int main(int argc, char* argv[]) {
    registerGen(argc, argv, 1);
    string type = opt<string>(1);

    long long n = 0, m = 1;

    if (type == "manual") {
        n = opt<long long>(2);
        m = opt<long long>(3);
    } else if (type == "zero") {
        n = 0;
        m = opt<long long>(2);
    } else if (type == "small") {
        n = rnd.next(0LL, 100LL);
        m = rnd.next(1LL, 100LL);
    } else if (type == "medium") {
        n = rnd.next(0LL, 1000000000LL);
        m = rnd.next(1LL, 1000000000LL);
    } else if (type == "max") {
        n = opt<long long>(2, 1000000000000000000LL);
        m = opt<long long>(3, 1000000000000000000LL);
    } else if (type == "n_less_m") {
        m = rnd.next(2LL, 1000000000000000000LL);
        n = rnd.next(0LL, m - 1);
    } else if (type == "n_eq_m") {
        n = m = rnd.next(1LL, 1000000000000000000LL);
    } else if (type == "mult") {
        m = rnd.next(1LL, 1000000000LL);
        long long k = rnd.next(0LL, 1000000000LL);
        n = k * m;
    } else {
        n = rnd.next(0LL, 1000000000000000000LL);
        m = rnd.next(1LL, 1000000000000000000LL);
    }

    cout << n << " " << m << "\\n";
    return 0;
}
',
    'gen manual 0 1 > $
gen manual 0 1000000000000000000 > $
gen manual 1 1 > $
gen manual 2 2 > $
gen manual 1 2 > $
gen manual 2 1 > $
gen manual 1000000000000000000 1000000000000000000 > $
gen manual 1000000000000000000 1 > $
gen manual 999999999999999999 1000000000000000000 > $
gen manual 1000000000000000000 999999999999999999 > $
gen small 1 > $
gen small 2 > $
gen small 3 > $
gen small 4 > $
gen small 5 > $
gen small 6 > $
gen small 7 > $
gen small 8 > $
gen small 9 > $
gen small 10 > $
gen small 11 > $
gen small 12 > $
gen small 13 > $
gen small 14 > $
gen n_less_m 100 > $
gen n_less_m 200 > $
gen n_less_m 300 > $
gen n_less_m 400 > $
gen n_less_m 500 > $
gen n_less_m 600 > $
gen n_less_m 700 > $
gen n_less_m 800 > $
gen n_less_m 900 > $
gen n_less_m 1000 > $
gen n_less_m 1100 > $
gen n_less_m 1200 > $
gen n_less_m 1300 > $
gen n_less_m 1400 > $
gen n_less_m 1500 > $
gen n_less_m 1600 > $
gen n_less_m 1700 > $
gen n_less_m 1800 > $
gen n_less_m 1900 > $
gen n_eq_m 77 > $
gen n_eq_m 154 > $
gen n_eq_m 231 > $
gen n_eq_m 308 > $
gen n_eq_m 385 > $
gen n_eq_m 462 > $
gen n_eq_m 539 > $
gen n_eq_m 616 > $
gen n_eq_m 693 > $
gen n_eq_m 770 > $
gen n_eq_m 847 > $
gen n_eq_m 924 > $
gen n_eq_m 1001 > $
gen n_eq_m 1078 > $
gen mult 13 > $
gen mult 26 > $
gen mult 39 > $
gen mult 52 > $
gen mult 65 > $
gen mult 78 > $
gen mult 91 > $
gen mult 104 > $
gen mult 117 > $
gen mult 130 > $
gen mult 143 > $
gen mult 156 > $
gen mult 169 > $
gen mult 182 > $
gen mult 195 > $
gen mult 208 > $
gen mult 221 > $
gen mult 234 > $
gen mult 247 > $
gen random 31 > $
gen random 62 > $
gen random 93 > $
gen random 124 > $
gen random 155 > $
gen random 186 > $
gen random 217 > $
gen random 248 > $
gen random 279 > $
gen random 310 > $
gen random 341 > $
gen random 372 > $
gen random 403 > $
gen random 434 > $
gen random 465 > $
gen random 496 > $
gen random 527 > $
gen random 558 > $
gen random 589 > $
gen random 620 > $
gen random 651 > $
gen random 682 > $
gen random 713 > $
gen random 744 > $',
    '[{"input": "17 5\\n", "output": "IMPAR\\n2\\n", "is_sample": true}, {"input": "24 6\\n", "output": "PAR\\n0\\n", "is_sample": true}, {"input": "0 7\\n", "output": "PAR\\n0\\n", "is_sample": true}]',
    '#include <iostream>

using namespace std;

int main() {
    ios_base::sync_with_stdio(false);
    cin.tie(NULL);

    long long n, m;
    if (cin >> n >> m) {
        if (n % 2 == 0) {
            cout << "PAR\\n";
        } else {
            cout << "IMPAR\\n";
        }
        cout << (n % m) << "\\n";
    }

    return 0;
}
',
    NULL,
    NULL,
    NULL,
    'draft',
    NULL,
    NULL,
    NULL,
    @prova_owner_id,
    NOW(),
    NOW()
) ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    time_limit = VALUES(time_limit),
    memory_limit = VALUES(memory_limit),
    description = VALUES(description),
    input_description = VALUES(input_description),
    output_description = VALUES(output_description),
    notes = VALUES(notes),
    color_name = VALUES(color_name),
    color_hex = VALUES(color_hex),
    generator_source = VALUES(generator_source),
    generator_script = VALUES(generator_script),
    manual_tests = VALUES(manual_tests),
    solution_accept = VALUES(solution_accept),
    updated_at = NOW();

INSERT INTO polygon_exercises (
    contest_id, name, short_name, basename, time_limit, memory_limit, output_limit,
    description, input_description, output_description, notes,
    color_name, color_hex, generator_source, generator_script,
    manual_tests, solution_accept, solution_tle, solution_wrong,
    additional_solutions, status, calibration_results,
    published_problem_id, published_bank_code, created_by, created_at, updated_at
) VALUES (
    NULL,
    'Média no campus',
    'I02',
    'i02_m_dia_no_campus',
    1,
    256,
    1024,
    'A almirante Grace Hopper, lendária por caçar uma mariposa real presa nos relés do Mark II e popularizar o termo *bug*, visitou o campus do IFMG em Congonhas, à sombra dos Doze Profetas de pedra-sabão. Ela passou a tarde inspecionando códigos para garantir que nenhum inseto eletrônico passasse despercebido.

Ao final da oficina de programação, Grace reuniu as notas dos estudantes de uma turma, todas na mesma escala. Agora, a pioneira precisa consolidar a planilha final calculando a soma de todas as notas e a média aritmética da turma.

Dadas as notas, ajude Grace a calcular a soma e a média, ambas com duas casas decimais.',
    'A primeira linha contém um inteiro **N**, a quantidade de estudantes. A segunda linha contém **N** números reais **A_1, A_2, ..., A_N**, que são as notas dos estudantes, cada uma com até duas casas decimais.',
    'Na primeira linha, imprima a soma das notas com exatamente duas casas decimais. Na segunda linha, imprima a média com exatamente duas casas decimais.',
    '- `1 ≤ N ≤ 10^5`
- `0 ≤ A_i ≤ 100`',
    'Verde',
    '#10B981',
    '#include "testlib.h"
#include <iostream>
#include <vector>

using namespace std;

int main(int argc, char* argv[]) {
    registerGen(argc, argv, 1);
    string type = opt<string>(1);
    int n = opt<int>(2, 10);

    vector<int> a(n);
    if (type == "zero") {
        for (int i = 0; i < n; ++i) a[i] = 0;
    } else if (type == "max") {
        for (int i = 0; i < n; ++i) a[i] = 100;
    } else if (type == "alternating") {
        for (int i = 0; i < n; ++i) a[i] = (i % 2 == 0) ? 0 : 100;
    } else {
        for (int i = 0; i < n; ++i) a[i] = rnd.next(0, 100);
    }

    cout << n << "\\n";
    for (int i = 0; i < n; ++i) {
        cout << a[i] << (i + 1 == n ? "" : " ");
    }
    cout << "\\n";
    return 0;
}
',
    'gen zero 1 > $
gen max 1 > $
gen random 1 > $
gen zero 2 > $
gen max 2 > $
gen alternating 2 > $
gen zero 10 > $
gen max 10 > $
gen alternating 10 > $
gen random 10 > $
gen zero 100000 > $
gen max 100000 > $
gen alternating 100000 > $
gen random 100000 > $
gen random 3 > $
gen alternating 3 > $
gen random 5 > $
gen alternating 5 > $
gen random 8 > $
gen alternating 8 > $
gen random 15 > $
gen alternating 15 > $
gen random 25 > $
gen alternating 25 > $
gen random 50 > $
gen alternating 50 > $
gen random 75 > $
gen alternating 75 > $
gen random 100 > $
gen alternating 100 > $
gen random 250 > $
gen alternating 250 > $
gen random 500 > $
gen alternating 500 > $
gen random 1000 > $
gen alternating 1000 > $
gen random 5000 > $
gen alternating 5000 > $
gen random 10000 > $
gen alternating 10000 > $
gen random 50000 > $
gen alternating 50000 > $
gen random 100000 > $
gen alternating 100000 > $
gen random 42822 > $
gen random 43795 > $
gen random 44768 > $
gen random 45741 > $
gen random 46714 > $
gen random 47687 > $
gen random 48660 > $
gen random 49633 > $
gen random 50606 > $
gen random 51579 > $
gen random 52552 > $
gen random 53525 > $
gen random 54498 > $
gen random 55471 > $
gen random 56444 > $
gen random 57417 > $
gen random 58390 > $
gen random 59363 > $
gen random 60336 > $
gen random 61309 > $
gen random 62282 > $
gen random 63255 > $
gen random 64228 > $
gen random 65201 > $
gen random 66174 > $
gen random 67147 > $
gen random 68120 > $
gen random 69093 > $
gen random 70066 > $
gen random 71039 > $
gen random 72012 > $
gen random 72985 > $
gen random 73958 > $
gen random 74931 > $
gen random 75904 > $
gen random 76877 > $
gen random 77850 > $
gen random 78823 > $
gen random 79796 > $
gen random 80769 > $
gen random 81742 > $
gen random 82715 > $
gen random 83688 > $
gen random 84661 > $
gen random 85634 > $
gen random 86607 > $
gen random 87580 > $
gen random 88553 > $
gen random 89526 > $
gen random 90499 > $
gen random 91472 > $
gen random 92445 > $
gen random 93418 > $
gen random 94391 > $
gen random 95364 > $
gen random 96337 > $
gen random 97310 > $
gen random 98283 > $
gen random 99256 > $
gen random 239 > $
gen random 1212 > $',
    '[{"input": "4\\n6 8 7 9\\n", "output": "30.00\\n7.50\\n", "is_sample": true}, {"input": "3\\n10 0 5\\n", "output": "15.00\\n5.00\\n", "is_sample": true}, {"input": "1\\n7.25\\n", "output": "7.25\\n7.25\\n", "is_sample": true}]',
    '#include <iostream>
#include <iomanip>

using namespace std;

int main() {
    ios_base::sync_with_stdio(false);
    cin.tie(NULL);

    int n;
    if (cin >> n) {
        double sum = 0.0;
        for (int i = 0; i < n; ++i) {
            double val;
            cin >> val;
            sum += val;
        }
        double mean = sum / n;
        cout << fixed << setprecision(2);
        cout << sum << "\\n";
        cout << mean << "\\n";
    }

    return 0;
}
',
    NULL,
    NULL,
    NULL,
    'draft',
    NULL,
    NULL,
    NULL,
    @prova_owner_id,
    NOW(),
    NOW()
) ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    time_limit = VALUES(time_limit),
    memory_limit = VALUES(memory_limit),
    description = VALUES(description),
    input_description = VALUES(input_description),
    output_description = VALUES(output_description),
    notes = VALUES(notes),
    color_name = VALUES(color_name),
    color_hex = VALUES(color_hex),
    generator_source = VALUES(generator_source),
    generator_script = VALUES(generator_script),
    manual_tests = VALUES(manual_tests),
    solution_accept = VALUES(solution_accept),
    updated_at = NOW();

INSERT INTO polygon_exercises (
    contest_id, name, short_name, basename, time_limit, memory_limit, output_limit,
    description, input_description, output_description, notes,
    color_name, color_hex, generator_source, generator_script,
    manual_tests, solution_accept, solution_tle, solution_wrong,
    additional_solutions, status, calibration_results,
    published_problem_id, published_bank_code, created_by, created_at, updated_at
) VALUES (
    NULL,
    'Vogais na estação',
    'I03',
    'i03_vogais_na_esta_o',
    1,
    256,
    1024,
    'Na histórica estação ferroviária de Conselheiro Lafaiete, por onde passam composições de minério e passageiros, a organização da Maratona Turing instalou placas de boas-vindas. Como o falar mineiro adora encurtar palavras e emendar vogais ("uai", "trem", "ocê"), os letreiros chamaram a atenção de quem desembarcava.

Ada Lovelace, fascinada por padrões poéticos e matemáticos na linguagem, decidiu conferir a sonoridade dos textos contando quantas vogais aparecem em cada frase. Para esta contagem, apenas as letras `a`, `e`, `i`, `o` e `u` (maiúsculas ou minúsculas, sem acento) são consideradas vogais.

Dada a frase da placa, ajude Ada calculando o número total de vogais.',
    'Uma linha com o texto **S** da placa, que pode conter espaços.',
    'Um único inteiro: a quantidade de vogais em **S**.',
    '- `1 ≤ |S| ≤ 10^5`
- **S** contém apenas caracteres ASCII. Letras acentuadas não aparecem.',
    'Amarelo',
    '#F59E0B',
    '#include "testlib.h"
#include <iostream>
#include <string>

using namespace std;

int main(int argc, char* argv[]) {
    registerGen(argc, argv, 1);
    string type = opt<string>(1);
    int len = opt<int>(2, 20);

    string vowels = "aeiouAEIOU";
    string consonants = "bcdfghjklmnpqrstvwxyzBCDFGHJKLMNPQRSTVWXYZ";
    string spaces = "    ";

    string s = "";
    if (type == "only_vowels") {
        for (int i = 0; i < len; ++i) s += vowels[rnd.next(0, (int)vowels.size() - 1)];
    } else if (type == "no_vowels") {
        for (int i = 0; i < len; ++i) s += consonants[rnd.next(0, (int)consonants.size() - 1)];
    } else if (type == "all_spaces") {
        for (int i = 0; i < len; ++i) s += \' \';
    } else {
        string pool = vowels + consonants + spaces;
        for (int i = 0; i < len; ++i) s += pool[rnd.next(0, (int)pool.size() - 1)];
    }

    if (s.empty()) s = "A";
    cout << s << "\\n";
    return 0;
}
',
    'gen only_vowels 1 > $
gen no_vowels 1 > $
gen all_spaces 1 > $
gen only_vowels 100000 > $
gen no_vowels 100000 > $
gen all_spaces 100000 > $
gen mixed 100000 > $
gen only_vowels 2 > $
gen no_vowels 2 > $
gen mixed 2 > $
gen only_vowels 5 > $
gen no_vowels 5 > $
gen mixed 5 > $
gen only_vowels 10 > $
gen no_vowels 10 > $
gen mixed 10 > $
gen only_vowels 50 > $
gen no_vowels 50 > $
gen mixed 50 > $
gen only_vowels 100 > $
gen no_vowels 100 > $
gen mixed 100 > $
gen only_vowels 500 > $
gen no_vowels 500 > $
gen mixed 500 > $
gen only_vowels 1000 > $
gen no_vowels 1000 > $
gen mixed 1000 > $
gen only_vowels 10000 > $
gen no_vowels 10000 > $
gen mixed 10000 > $
gen only_vowels 50000 > $
gen no_vowels 50000 > $
gen mixed 50000 > $
gen only_vowels 100000 > $
gen no_vowels 100000 > $
gen mixed 100000 > $
gen mixed 36899 > $
gen mixed 37896 > $
gen mixed 38893 > $
gen mixed 39890 > $
gen mixed 40887 > $
gen mixed 41884 > $
gen mixed 42881 > $
gen mixed 43878 > $
gen mixed 44875 > $
gen mixed 45872 > $
gen mixed 46869 > $
gen mixed 47866 > $
gen mixed 48863 > $
gen mixed 49860 > $
gen mixed 50857 > $
gen mixed 51854 > $
gen mixed 52851 > $
gen mixed 53848 > $
gen mixed 54845 > $
gen mixed 55842 > $
gen mixed 56839 > $
gen mixed 57836 > $
gen mixed 58833 > $
gen mixed 59830 > $
gen mixed 60827 > $
gen mixed 61824 > $
gen mixed 62821 > $
gen mixed 63818 > $
gen mixed 64815 > $
gen mixed 65812 > $
gen mixed 66809 > $
gen mixed 67806 > $
gen mixed 68803 > $
gen mixed 69800 > $
gen mixed 70797 > $
gen mixed 71794 > $
gen mixed 72791 > $
gen mixed 73788 > $
gen mixed 74785 > $
gen mixed 75782 > $
gen mixed 76779 > $
gen mixed 77776 > $
gen mixed 78773 > $
gen mixed 79770 > $
gen mixed 80767 > $
gen mixed 81764 > $
gen mixed 82761 > $
gen mixed 83758 > $
gen mixed 84755 > $
gen mixed 85752 > $
gen mixed 86749 > $
gen mixed 87746 > $
gen mixed 88743 > $
gen mixed 89740 > $
gen mixed 90737 > $
gen mixed 91734 > $
gen mixed 92731 > $
gen mixed 93728 > $
gen mixed 94725 > $
gen mixed 95722 > $
gen mixed 96719 > $
gen mixed 97716 > $
gen mixed 98713 > $
gen mixed 99710 > $
gen mixed 717 > $
gen mixed 1714 > $
gen mixed 2711 > $
gen mixed 3708 > $',
    '[{"input": "Ouro Branco\\n", "output": "5\\n", "is_sample": true}, {"input": "TURING 2026!\\n", "output": "2\\n", "is_sample": true}, {"input": "bcdfg 123!\\n", "output": "0\\n", "is_sample": true}]',
    '#include <iostream>
#include <string>
#include <cctype>

using namespace std;

int main() {
    ios_base::sync_with_stdio(false);
    cin.tie(NULL);

    string s;
    if (getline(cin, s)) {
        long long count = 0;
        for (char c : s) {
            char lower_c = tolower(static_cast<unsigned char>(c));
            if (lower_c == \'a\' || lower_c == \'e\' || lower_c == \'i\' || lower_c == \'o\' || lower_c == \'u\') {
                count++;
            }
        }
        cout << count << "\\n";
    }

    return 0;
}
',
    NULL,
    NULL,
    NULL,
    'draft',
    NULL,
    NULL,
    NULL,
    @prova_owner_id,
    NOW(),
    NOW()
) ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    time_limit = VALUES(time_limit),
    memory_limit = VALUES(memory_limit),
    description = VALUES(description),
    input_description = VALUES(input_description),
    output_description = VALUES(output_description),
    notes = VALUES(notes),
    color_name = VALUES(color_name),
    color_hex = VALUES(color_hex),
    generator_source = VALUES(generator_source),
    generator_script = VALUES(generator_script),
    manual_tests = VALUES(manual_tests),
    solution_accept = VALUES(solution_accept),
    updated_at = NOW();

INSERT INTO polygon_exercises (
    contest_id, name, short_name, basename, time_limit, memory_limit, output_limit,
    description, input_description, output_description, notes,
    color_name, color_hex, generator_source, generator_script,
    manual_tests, solution_accept, solution_tle, solution_wrong,
    additional_solutions, status, calibration_results,
    published_problem_id, published_bank_code, created_by, created_at, updated_at
) VALUES (
    NULL,
    'A maior e a menor altitude',
    'I04',
    'i04_a_maior_e_a_menor_altitude',
    1,
    256,
    1024,
    'Katherine Johnson, que calculou à mão as trajetórias espaciais na NASA que levaram a humanidade à Lua, foi desafiada a encarar as ladeiras da Estrada Real entre Ouro Branco e Ouro Preto. Após subir e descer serras íngremes, ela brincou que a topografia de Minas parecia mais desafiadora do que calcular órbitas planetárias.

Munida de seus instrumentos, Katherine registrou a variação de altitude em metros de cada mirante de parada em relação ao ponto de largada. Agora, a matemática quer identificar com precisão a menor e a maior variação de altitude registradas em toda a expedição.

Dadas as medições, ajude Katherine a descobrir qual foi a menor e qual foi a maior variação de altitude medida.',
    'A primeira linha contém um inteiro **N**, a quantidade de medições. A segunda linha contém **N** inteiros **A_1, A_2, ..., A_N**, que são as variações de altitude medidas.',
    'Na primeira linha, imprima o menor valor. Na segunda linha, imprima o maior valor.',
    '- `1 ≤ N ≤ 10^5`
- `-10^9 ≤ A_i ≤ 10^9`',
    'Vermelho',
    '#EF4444',
    '#include "testlib.h"
#include <iostream>
#include <vector>
#include <algorithm>

using namespace std;

int main(int argc, char* argv[]) {
    registerGen(argc, argv, 1);
    string type = opt<string>(1);
    int n = opt<int>(2, 10);

    vector<long long> a(n);
    if (type == "all_equal") {
        long long val = opt<long long>(3, 42);
        for (int i = 0; i < n; ++i) a[i] = val;
    } else if (type == "extremes") {
        for (int i = 0; i < n; ++i) a[i] = (i % 2 == 0) ? -1000000000LL : 1000000000LL;
    } else if (type == "sorted_asc") {
        for (int i = 0; i < n; ++i) a[i] = rnd.next(-1000000000LL, 1000000000LL);
        sort(a.begin(), a.end());
    } else if (type == "sorted_desc") {
        for (int i = 0; i < n; ++i) a[i] = rnd.next(-1000000000LL, 1000000000LL);
        sort(a.rbegin(), a.rend());
    } else {
        for (int i = 0; i < n; ++i) a[i] = rnd.next(-1000000000LL, 1000000000LL);
    }

    cout << n << "\\n";
    for (int i = 0; i < n; ++i) {
        cout << a[i] << (i + 1 == n ? "" : " ");
    }
    cout << "\\n";
    return 0;
}
',
    'gen all_equal 1 -1000000000 > $
gen all_equal 1 1000000000 > $
gen all_equal 1 0 > $
gen extremes 2 > $
gen extremes 100000 > $
gen all_equal 100000 0 > $
gen all_equal 100000 -1000000000 > $
gen all_equal 100000 1000000000 > $
gen sorted_asc 100000 > $
gen sorted_desc 100000 > $
gen random 100000 > $
gen random 2 > $
gen sorted_asc 2 > $
gen sorted_desc 2 > $
gen random 5 > $
gen sorted_asc 5 > $
gen sorted_desc 5 > $
gen random 10 > $
gen sorted_asc 10 > $
gen sorted_desc 10 > $
gen random 50 > $
gen sorted_asc 50 > $
gen sorted_desc 50 > $
gen random 100 > $
gen sorted_asc 100 > $
gen sorted_desc 100 > $
gen random 500 > $
gen sorted_asc 500 > $
gen sorted_desc 500 > $
gen random 1000 > $
gen sorted_asc 1000 > $
gen sorted_desc 1000 > $
gen random 5000 > $
gen sorted_asc 5000 > $
gen sorted_desc 5000 > $
gen random 20000 > $
gen sorted_asc 20000 > $
gen sorted_desc 20000 > $
gen random 50000 > $
gen sorted_asc 50000 > $
gen sorted_desc 50000 > $
gen random 100000 > $
gen sorted_asc 100000 > $
gen sorted_desc 100000 > $
gen random 40094 > $
gen random 41005 > $
gen random 41916 > $
gen random 42827 > $
gen random 43738 > $
gen random 44649 > $
gen random 45560 > $
gen random 46471 > $
gen random 47382 > $
gen random 48293 > $
gen random 49204 > $
gen random 50115 > $
gen random 51026 > $
gen random 51937 > $
gen random 52848 > $
gen random 53759 > $
gen random 54670 > $
gen random 55581 > $
gen random 56492 > $
gen random 57403 > $
gen random 58314 > $
gen random 59225 > $
gen random 60136 > $
gen random 61047 > $
gen random 61958 > $
gen random 62869 > $
gen random 63780 > $
gen random 64691 > $
gen random 65602 > $
gen random 66513 > $
gen random 67424 > $
gen random 68335 > $
gen random 69246 > $
gen random 70157 > $
gen random 71068 > $
gen random 71979 > $
gen random 72890 > $
gen random 73801 > $
gen random 74712 > $
gen random 75623 > $
gen random 76534 > $
gen random 77445 > $
gen random 78356 > $
gen random 79267 > $
gen random 80178 > $
gen random 81089 > $
gen random 82000 > $
gen random 82911 > $
gen random 83822 > $
gen random 84733 > $
gen random 85644 > $
gen random 86555 > $
gen random 87466 > $
gen random 88377 > $
gen random 89288 > $
gen random 90199 > $
gen random 91110 > $
gen random 92021 > $
gen random 92932 > $
gen random 93843 > $
gen random 94754 > $',
    '[{"input": "5\\n12 4 19 7 4\\n", "output": "4\\n19\\n", "is_sample": true}, {"input": "3\\n-8 -2 -15\\n", "output": "-15\\n-2\\n", "is_sample": true}, {"input": "1\\n42\\n", "output": "42\\n42\\n", "is_sample": true}]',
    '#include <iostream>
#include <algorithm>

using namespace std;

int main() {
    ios_base::sync_with_stdio(false);
    cin.tie(NULL);

    int n;
    if (cin >> n && n > 0) {
        long long min_val, max_val;
        long long x;
        cin >> x;
        min_val = max_val = x;
        for (int i = 1; i < n; ++i) {
            cin >> x;
            if (x < min_val) min_val = x;
            if (x > max_val) max_val = x;
        }
        cout << min_val << "\\n";
        cout << max_val << "\\n";
    }

    return 0;
}
',
    NULL,
    NULL,
    NULL,
    'draft',
    NULL,
    NULL,
    NULL,
    @prova_owner_id,
    NOW(),
    NOW()
) ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    time_limit = VALUES(time_limit),
    memory_limit = VALUES(memory_limit),
    description = VALUES(description),
    input_description = VALUES(input_description),
    output_description = VALUES(output_description),
    notes = VALUES(notes),
    color_name = VALUES(color_name),
    color_hex = VALUES(color_hex),
    generator_source = VALUES(generator_source),
    generator_script = VALUES(generator_script),
    manual_tests = VALUES(manual_tests),
    solution_accept = VALUES(solution_accept),
    updated_at = NOW();

INSERT INTO polygon_exercises (
    contest_id, name, short_name, basename, time_limit, memory_limit, output_limit,
    description, input_description, output_description, notes,
    color_name, color_hex, generator_source, generator_script,
    manual_tests, solution_accept, solution_tle, solution_wrong,
    additional_solutions, status, calibration_results,
    published_problem_id, published_bank_code, created_by, created_at, updated_at
) VALUES (
    NULL,
    'Palavras no caminho para Ouro Preto',
    'I05',
    'i05_palavras_no_caminho_para_ouro_preto',
    1,
    256,
    1024,
    'Joan Clarke, a brilhante criptoanalista que desvendava mensagens secretas na Segunda Guerra ao lado de Alan Turing, passeava pelas ladeiras de pedra de Ouro Preto admirando as placas das antigas repúblicas estudantis da Praça Tiradentes.

Fascinada por quebra-cabeças linguísticos, Joan reparou que alguns nomes ficam rigorosamente idênticos quando lidos de trás para frente, formando palíndromos. Com rigor de criptógrafa, ela lembra que maiúsculas e minúsculas são caracteres distintos, de modo que `Radar` **não** é considerado um palíndromo.

Dada uma palavra **S**, ajude Joan escrevendo a palavra invertida e dizendo se **S** é um palíndromo.',
    'Uma linha com a palavra **S**, formada apenas por letras, sem espaços.',
    'Na primeira linha, imprima **S** invertida. Na segunda linha, imprima `SIM` se **S** for palíndromo ou `NAO` caso contrário.',
    '- `1 ≤ |S| ≤ 10^5`
- **S** contém apenas letras ASCII (maiúsculas e minúsculas).',
    'Roxo',
    '#8B5CF6',
    '#include "testlib.h"
#include <iostream>
#include <string>
#include <algorithm>

using namespace std;

int main(int argc, char* argv[]) {
    registerGen(argc, argv, 1);
    string type = opt<string>(1);
    int len = opt<int>(2, 10);

    string s = "";
    if (type == "palindrome") {
        int half = len / 2;
        string h = "";
        for (int i = 0; i < half; ++i) h += (char)rnd.next(\'a\', \'z\');
        string rev = h;
        reverse(rev.begin(), rev.end());
        s = h + (len % 2 == 1 ? string(1, (char)rnd.next(\'a\', \'z\')) : "") + rev;
    } else if (type == "all_same") {
        char c = (char)rnd.next(\'a\', \'z\');
        s = string(len, c);
    } else {
        for (int i = 0; i < len; ++i) s += (char)rnd.next(\'a\', \'z\');
    }

    if (s.empty()) s = "a";
    cout << s << "\\n";
    return 0;
}
',
    'gen palindrome 1 > $
gen all_same 1 > $
gen random 1 > $
gen palindrome 2 > $
gen random 2 > $
gen all_same 100000 > $
gen palindrome 100000 > $
gen random 100000 > $
gen palindrome 3 > $
gen random 3 > $
gen all_same 3 > $
gen palindrome 5 > $
gen random 5 > $
gen all_same 5 > $
gen palindrome 10 > $
gen random 10 > $
gen all_same 10 > $
gen palindrome 50 > $
gen random 50 > $
gen all_same 50 > $
gen palindrome 100 > $
gen random 100 > $
gen all_same 100 > $
gen palindrome 500 > $
gen random 500 > $
gen all_same 500 > $
gen palindrome 1000 > $
gen random 1000 > $
gen all_same 1000 > $
gen palindrome 10000 > $
gen random 10000 > $
gen all_same 10000 > $
gen palindrome 50000 > $
gen random 50000 > $
gen all_same 50000 > $
gen palindrome 100000 > $
gen random 100000 > $
gen all_same 100000 > $
gen random 37364 > $
gen random 38347 > $
gen random 39330 > $
gen random 40313 > $
gen random 41296 > $
gen random 42279 > $
gen random 43262 > $
gen random 44245 > $
gen random 45228 > $
gen random 46211 > $
gen random 47194 > $
gen random 48177 > $
gen random 49160 > $
gen random 50143 > $
gen random 51126 > $
gen random 52109 > $
gen random 53092 > $
gen random 54075 > $
gen random 55058 > $
gen random 56041 > $
gen random 57024 > $
gen random 58007 > $
gen random 58990 > $
gen random 59973 > $
gen random 60956 > $
gen random 61939 > $
gen random 62922 > $
gen random 63905 > $
gen random 64888 > $
gen random 65871 > $
gen random 66854 > $
gen random 67837 > $
gen random 68820 > $
gen random 69803 > $
gen random 70786 > $
gen random 71769 > $
gen random 72752 > $
gen random 73735 > $
gen random 74718 > $
gen random 75701 > $
gen random 76684 > $
gen random 77667 > $
gen random 78650 > $
gen random 79633 > $
gen random 80616 > $
gen random 81599 > $
gen random 82582 > $
gen random 83565 > $
gen random 84548 > $
gen random 85531 > $
gen random 86514 > $
gen random 87497 > $
gen random 88480 > $
gen random 89463 > $
gen random 90446 > $
gen random 91429 > $
gen random 92412 > $
gen random 93395 > $
gen random 94378 > $
gen random 95361 > $
gen random 96344 > $
gen random 97327 > $
gen random 98310 > $
gen random 99293 > $
gen random 286 > $
gen random 1269 > $
gen random 2252 > $',
    '[{"input": "radar\\n", "output": "radar\\nSIM\\n", "is_sample": true}, {"input": "Turing\\n", "output": "gniruT\\nNAO\\n", "is_sample": true}, {"input": "A\\n", "output": "A\\nSIM\\n", "is_sample": true}]',
    '#include <iostream>
#include <string>
#include <algorithm>

using namespace std;

int main() {
    ios_base::sync_with_stdio(false);
    cin.tie(NULL);

    string s;
    if (cin >> s) {
        string rev = s;
        reverse(rev.begin(), rev.end());
        cout << rev << "\\n";
        if (s == rev) {
            cout << "SIM\\n";
        } else {
            cout << "NAO\\n";
        }
    }

    return 0;
}
',
    NULL,
    NULL,
    NULL,
    'draft',
    NULL,
    NULL,
    NULL,
    @prova_owner_id,
    NOW(),
    NOW()
) ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    time_limit = VALUES(time_limit),
    memory_limit = VALUES(memory_limit),
    description = VALUES(description),
    input_description = VALUES(input_description),
    output_description = VALUES(output_description),
    notes = VALUES(notes),
    color_name = VALUES(color_name),
    color_hex = VALUES(color_hex),
    generator_source = VALUES(generator_source),
    generator_script = VALUES(generator_script),
    manual_tests = VALUES(manual_tests),
    solution_accept = VALUES(solution_accept),
    updated_at = NOW();

INSERT INTO polygon_exercises (
    contest_id, name, short_name, basename, time_limit, memory_limit, output_limit,
    description, input_description, output_description, notes,
    color_name, color_hex, generator_source, generator_script,
    manual_tests, solution_accept, solution_tle, solution_wrong,
    additional_solutions, status, calibration_results,
    published_problem_id, published_bank_code, created_by, created_at, updated_at
) VALUES (
    NULL,
    'Os divisores de von Neumann',
    'I06',
    'i06_os_divisores_de_von_neumann',
    1,
    256,
    1024,
    'John von Neumann era famoso por seu cérebro prodigioso: calculava equações complexas de cabeça mais rápido que os primeiros computadores, mas vivia se distraindo ao fatorar placas de trânsito enquanto dirigia. Em visita a Congonhas, em frente ao Santuário de Bom Jesus de Matosinhos, ele avistou o número gravado no pedestal de um profeta de pedra-sabão.

Incapaz de resistir a um enigma aritmético, von Neumann decidiu dissecar o número em todos os seus fatores. Ele precisa listar todos os divisores inteiros positivos de **N** em ordem crescente, contar quantos são e determinar se **N** é um número primo (isto é, se possui exatamente dois divisores positivos: 1 e ele mesmo).

Dado o número **N**, ajude von Neumann informando quantos divisores **N** possui, listando-os em ordem crescente e dizendo se **N** é primo.',
    'Uma linha com um inteiro **N**, o número gravado na placa.',
    'Na primeira linha, imprima a quantidade de divisores positivos de **N**. Na segunda linha, imprima os divisores em ordem crescente, separados por espaço. Na terceira linha, imprima `PRIMO` ou `NAO PRIMO`.',
    '- `1 ≤ N ≤ 10^12`
- O número 1 não é primo.',
    'Laranja',
    '#F97316',
    '#include "testlib.h"
#include <iostream>
#include <string>

using namespace std;

int main(int argc, char* argv[]) {
    registerGen(argc, argv, 1);
    string type = opt<string>(1);

    long long n = 1;
    if (type == "manual") {
        n = opt<long long>(2);
    } else if (type == "small") {
        n = rnd.next(1LL, 100LL);
    } else if (type == "prime") {
        // Known big prime
        long long primes[] = {2, 3, 5, 7, 11, 13, 999999999989LL, 100000000003LL, 1000000007LL, 99999999997LL};
        n = primes[rnd.next(0, 9)];
    } else if (type == "square") {
        long long r = rnd.next(1LL, 1000000LL);
        n = r * r;
    } else if (type == "highly_composite") {
        // High number of divisors
        long long hc[] = {720720, 1441440, 2162160, 3603600, 7207200, 9979200, 1000000000000LL, 735134400000LL};
        n = hc[rnd.next(0, 7)];
    } else if (type == "max") {
        n = 1000000000000LL;
    } else {
        n = rnd.next(1LL, 1000000000000LL);
    }

    cout << n << "\\n";
    return 0;
}
',
    'gen manual 1 > $
gen manual 2 > $
gen manual 3 > $
gen manual 4 > $
gen manual 1000000000000 > $
gen prime 1 > $
gen square 1 > $
gen highly_composite 1 > $
gen small 1 > $
gen small 2 > $
gen small 3 > $
gen small 4 > $
gen small 5 > $
gen small 6 > $
gen small 7 > $
gen small 8 > $
gen small 9 > $
gen small 10 > $
gen small 11 > $
gen small 12 > $
gen small 13 > $
gen small 14 > $
gen small 15 > $
gen small 16 > $
gen small 17 > $
gen small 18 > $
gen small 19 > $
gen square 1 > $
gen square 2 > $
gen square 3 > $
gen square 4 > $
gen square 5 > $
gen square 6 > $
gen square 7 > $
gen square 8 > $
gen square 9 > $
gen square 10 > $
gen square 11 > $
gen square 12 > $
gen square 13 > $
gen square 14 > $
gen square 15 > $
gen square 16 > $
gen square 17 > $
gen square 18 > $
gen square 19 > $
gen prime 1 > $
gen prime 2 > $
gen prime 3 > $
gen prime 4 > $
gen prime 5 > $
gen prime 6 > $
gen prime 7 > $
gen prime 8 > $
gen prime 9 > $
gen prime 10 > $
gen prime 11 > $
gen prime 12 > $
gen prime 13 > $
gen prime 14 > $
gen prime 15 > $
gen prime 16 > $
gen prime 17 > $
gen prime 18 > $
gen prime 19 > $
gen highly_composite 1 > $
gen highly_composite 2 > $
gen highly_composite 3 > $
gen highly_composite 4 > $
gen highly_composite 5 > $
gen highly_composite 6 > $
gen highly_composite 7 > $
gen highly_composite 8 > $
gen highly_composite 9 > $
gen highly_composite 10 > $
gen highly_composite 11 > $
gen highly_composite 12 > $
gen highly_composite 13 > $
gen highly_composite 14 > $
gen highly_composite 15 > $
gen highly_composite 16 > $
gen highly_composite 17 > $
gen highly_composite 18 > $
gen highly_composite 19 > $
gen random 77 > $
gen random 154 > $
gen random 231 > $
gen random 308 > $
gen random 385 > $
gen random 462 > $
gen random 539 > $
gen random 616 > $
gen random 693 > $
gen random 770 > $
gen random 847 > $
gen random 924 > $
gen random 1001 > $
gen random 1078 > $
gen random 1155 > $
gen random 1232 > $
gen random 1309 > $
gen random 1386 > $
gen random 1463 > $
gen random 1540 > $
gen random 1617 > $
gen random 1694 > $
gen random 1771 > $
gen random 1848 > $
gen random 1925 > $
gen random 2002 > $
gen random 2079 > $
gen random 2156 > $
gen random 2233 > $',
    '[{"input": "12\\n", "output": "6\\n1 2 3 4 6 12\\nNAO PRIMO\\n", "is_sample": true}, {"input": "13\\n", "output": "2\\n1 13\\nPRIMO\\n", "is_sample": true}, {"input": "1\\n", "output": "1\\n1\\nNAO PRIMO\\n", "is_sample": true}]',
    '#include <iostream>
#include <vector>
#include <algorithm>

using namespace std;

int main() {
    ios_base::sync_with_stdio(false);
    cin.tie(NULL);

    long long n;
    if (cin >> n) {
        vector<long long> small_divs, large_divs;
        for (long long d = 1; d * d <= n; ++d) {
            if (n % d == 0) {
                small_divs.push_back(d);
                if (d * d != n) {
                    large_divs.push_back(n / d);
                }
            }
        }
        vector<long long> all_divs = std::move(small_divs);
        all_divs.insert(all_divs.end(), large_divs.rbegin(), large_divs.rend());

        cout << all_divs.size() << "\\n";
        for (size_t i = 0; i < all_divs.size(); ++i) {
            cout << all_divs[i] << (i + 1 == all_divs.size() ? "" : " ");
        }
        cout << "\\n";

        if (all_divs.size() == 2) {
            cout << "PRIMO\\n";
        } else {
            cout << "NAO PRIMO\\n";
        }
    }

    return 0;
}
',
    NULL,
    NULL,
    NULL,
    'draft',
    NULL,
    NULL,
    NULL,
    @prova_owner_id,
    NOW(),
    NOW()
) ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    time_limit = VALUES(time_limit),
    memory_limit = VALUES(memory_limit),
    description = VALUES(description),
    input_description = VALUES(input_description),
    output_description = VALUES(output_description),
    notes = VALUES(notes),
    color_name = VALUES(color_name),
    color_hex = VALUES(color_hex),
    generator_source = VALUES(generator_source),
    generator_script = VALUES(generator_script),
    manual_tests = VALUES(manual_tests),
    solution_accept = VALUES(solution_accept),
    updated_at = NOW();

INSERT INTO polygon_exercises (
    contest_id, name, short_name, basename, time_limit, memory_limit, output_limit,
    description, input_description, output_description, notes,
    color_name, color_hex, generator_source, generator_script,
    manual_tests, solution_accept, solution_tle, solution_wrong,
    additional_solutions, status, calibration_results,
    published_problem_id, published_bank_code, created_by, created_at, updated_at
) VALUES (
    NULL,
    'Somando os setores do IFMG',
    'I07',
    'i07_somando_os_setores_do_ifmg',
    1,
    256,
    1024,
    'Margaret Hamilton, a engenheira da NASA que chefiou a equipe do software de bordo da missão Apollo — empilhando volumes de código mais altos do que ela própria —, visitou o campus do IFMG em Ouro Branco e foi convidada a organizar o almoxarifado de informática da instituição.

Para colocar ordem no inventário de peças e cabos, Margaret estruturou uma matriz de contagem: cada linha representa um setor do campus e cada coluna representa um tipo de material, com a quantidade de itens guardada em cada célula. Ela precisa fechar o balanço calculando a soma total de materiais de cada setor e a soma acumulada de cada tipo de material em todos os setores.

Dada a tabela, ajude Margaret calculando a soma de cada linha e a soma de cada coluna.',
    'A primeira linha contém dois inteiros **N** e **M**, que são, respectivamente, o número de setores (linhas) e o número de tipos de material (colunas). Em seguida, **N** linhas contêm **M** inteiros cada, os valores **A_ij**, que são a quantidade do material **j** no setor **i**.',
    'Na primeira linha, imprima as **N** somas das linhas, separadas por espaço. Na segunda linha, imprima as **M** somas das colunas, separadas por espaço.',
    '- `1 ≤ N, M ≤ 500`
- `0 ≤ A_ij ≤ 10^6`',
    'Ciano',
    '#06B6D4',
    '#include "testlib.h"
#include <iostream>
#include <vector>

using namespace std;

int main(int argc, char* argv[]) {
    registerGen(argc, argv, 1);
    string type = opt<string>(1);
    int n = opt<int>(2, 3);
    int m = opt<int>(3, 3);

    cout << n << " " << m << "\\n";
    for (int i = 0; i < n; ++i) {
        for (int j = 0; j < m; ++j) {
            long long val = 0;
            if (type == "zeros") {
                val = 0;
            } else if (type == "max") {
                val = 1000000;
            } else {
                val = rnd.next(0LL, 1000000LL);
            }
            cout << val << (j + 1 == m ? "" : " ");
        }
        cout << "\\n";
    }
    return 0;
}
',
    'gen zeros 1 1 > $
gen max 1 1 > $
gen random 1 1 > $
gen random 1 500 > $
gen random 500 1 > $
gen zeros 500 500 > $
gen max 500 500 > $
gen random 500 500 > $
gen random 2 2 > $
gen zeros 2 2 > $
gen max 2 2 > $
gen random 3 3 > $
gen zeros 3 3 > $
gen max 3 3 > $
gen random 5 5 > $
gen zeros 5 5 > $
gen max 5 5 > $
gen random 10 10 > $
gen zeros 10 10 > $
gen max 10 10 > $
gen random 20 20 > $
gen zeros 20 20 > $
gen max 20 20 > $
gen random 50 50 > $
gen zeros 50 50 > $
gen max 50 50 > $
gen random 100 100 > $
gen zeros 100 100 > $
gen max 100 100 > $
gen random 200 200 > $
gen zeros 200 200 > $
gen max 200 200 > $
gen random 300 300 > $
gen zeros 300 300 > $
gen max 300 300 > $
gen random 500 500 > $
gen zeros 500 500 > $
gen max 500 500 > $
gen random 147 179 > $
gen random 164 210 > $
gen random 181 241 > $
gen random 198 272 > $
gen random 215 303 > $
gen random 232 334 > $
gen random 249 365 > $
gen random 266 396 > $
gen random 283 427 > $
gen random 300 458 > $
gen random 317 489 > $
gen random 334 20 > $
gen random 351 51 > $
gen random 368 82 > $
gen random 385 113 > $
gen random 402 144 > $
gen random 419 175 > $
gen random 436 206 > $
gen random 453 237 > $
gen random 470 268 > $
gen random 487 299 > $
gen random 4 330 > $
gen random 21 361 > $
gen random 38 392 > $
gen random 55 423 > $
gen random 72 454 > $
gen random 89 485 > $
gen random 106 16 > $
gen random 123 47 > $
gen random 140 78 > $
gen random 157 109 > $
gen random 174 140 > $
gen random 191 171 > $
gen random 208 202 > $
gen random 225 233 > $
gen random 242 264 > $
gen random 259 295 > $
gen random 276 326 > $
gen random 293 357 > $
gen random 310 388 > $
gen random 327 419 > $
gen random 344 450 > $
gen random 361 481 > $
gen random 378 12 > $
gen random 395 43 > $
gen random 412 74 > $
gen random 429 105 > $
gen random 446 136 > $
gen random 463 167 > $
gen random 480 198 > $
gen random 497 229 > $
gen random 14 260 > $
gen random 31 291 > $
gen random 48 322 > $
gen random 65 353 > $
gen random 82 384 > $
gen random 99 415 > $
gen random 116 446 > $
gen random 133 477 > $
gen random 150 8 > $
gen random 167 39 > $
gen random 184 70 > $
gen random 201 101 > $
gen random 218 132 > $
gen random 235 163 > $
gen random 252 194 > $
gen random 269 225 > $',
    '[{"input": "2 3\\n1 2 3\\n4 5 6\\n", "output": "6 15\\n5 7 9\\n", "is_sample": true}, {"input": "1 4\\n7 0 2 1\\n", "output": "10\\n7 0 2 1\\n", "is_sample": true}, {"input": "3 1\\n2\\n5\\n4\\n", "output": "2 5 4\\n11\\n", "is_sample": true}]',
    '#include <iostream>
#include <vector>

using namespace std;

int main() {
    ios_base::sync_with_stdio(false);
    cin.tie(NULL);

    int n, m;
    if (!(cin >> n >> m)) return 0;

    vector<long long> row_sum(n, 0);
    vector<long long> col_sum(m, 0);

    for (int i = 0; i < n; ++i) {
        for (int j = 0; j < m; ++j) {
            long long val;
            cin >> val;
            row_sum[i] += val;
            col_sum[j] += val;
        }
    }

    for (int i = 0; i < n; ++i) {
        cout << row_sum[i] << (i + 1 == n ? "" : " ");
    }
    cout << "\\n";

    for (int j = 0; j < m; ++j) {
        cout << col_sum[j] << (j + 1 == m ? "" : " ");
    }
    cout << "\\n";

    return 0;
}
',
    NULL,
    NULL,
    NULL,
    'draft',
    NULL,
    NULL,
    NULL,
    @prova_owner_id,
    NOW(),
    NOW()
) ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    time_limit = VALUES(time_limit),
    memory_limit = VALUES(memory_limit),
    description = VALUES(description),
    input_description = VALUES(input_description),
    output_description = VALUES(output_description),
    notes = VALUES(notes),
    color_name = VALUES(color_name),
    color_hex = VALUES(color_hex),
    generator_source = VALUES(generator_source),
    generator_script = VALUES(generator_script),
    manual_tests = VALUES(manual_tests),
    solution_accept = VALUES(solution_accept),
    updated_at = NOW();

INSERT INTO polygon_exercises (
    contest_id, name, short_name, basename, time_limit, memory_limit, output_limit,
    description, input_description, output_description, notes,
    color_name, color_hex, generator_source, generator_script,
    manual_tests, solution_accept, solution_tle, solution_wrong,
    additional_solutions, status, calibration_results,
    published_problem_id, published_bank_code, created_by, created_at, updated_at
) VALUES (
    NULL,
    'O valor mais frequente',
    'I08',
    'i08_o_valor_mais_frequente',
    1,
    256,
    1024,
    'No aquecimento da Maratona Turing no campus de Ouro Branco, o nervosismo tomou conta dos laboratórios. Com medo de perder tempo no cronômetro, diversos competidores digitaram seus códigos de acesso de forma repetida no sistema, gerando uma lista cheia de duplicatas.

Alan Turing e Joan Clarke foram chamados à coordenação para analisar o relatório de conexões. Eles precisam descobrir qual código numérico apareceu com a maior frequência. Caso haja empate entre dois ou mais códigos com a mesma quantidade máxima de aparições, o critério de desempate deve escolher o de menor valor numérico.

Dados os códigos registrados, ajude a dupla a encontrar o código com maior frequência, desempatando pelo menor valor.',
    'A primeira linha contém um inteiro **N**, a quantidade de códigos registrados. A segunda linha contém **N** inteiros **A_1, A_2, ..., A_N**, que são os códigos, em ordem de registro.',
    'Um inteiro: o código que mais aparece. Em caso de empate, o menor deles.',
    '- `1 ≤ N ≤ 2·10^5`
- `-10^9 ≤ A_i ≤ 10^9`',
    'Rosa',
    '#EC4899',
    '#include "testlib.h"
#include <iostream>
#include <vector>

using namespace std;

int main(int argc, char* argv[]) {
    registerGen(argc, argv, 1);
    string type = opt<string>(1);
    int n = opt<int>(2, 10);

    vector<long long> a(n);
    if (type == "all_same") {
        long long val = opt<long long>(3, 42);
        for (int i = 0; i < n; ++i) a[i] = val;
    } else if (type == "all_distinct") {
        for (int i = 0; i < n; ++i) a[i] = i - n / 2;
    } else if (type == "tie") {
        // Exact tie between -5 and 5
        for (int i = 0; i < n; ++i) a[i] = (i % 2 == 0) ? -5 : 5;
    } else {
        long long mod = opt<long long>(3, 100);
        for (int i = 0; i < n; ++i) a[i] = rnd.next(-mod, mod);
    }

    cout << n << "\\n";
    for (int i = 0; i < n; ++i) {
        cout << a[i] << (i + 1 == n ? "" : " ");
    }
    cout << "\\n";
    return 0;
}
',
    'gen all_same 1 100 > $
gen all_same 1 -1000000000 > $
gen all_distinct 2 > $
gen tie 2 > $
gen all_same 200000 1000000000 > $
gen all_distinct 200000 > $
gen tie 200000 > $
gen random 200000 1000000000 > $
gen random 5 10 > $
gen tie 5 10 > $
gen all_same 5 10 > $
gen random 10 10 > $
gen tie 10 10 > $
gen all_same 10 10 > $
gen random 50 10 > $
gen tie 50 10 > $
gen all_same 50 10 > $
gen random 100 10 > $
gen tie 100 10 > $
gen all_same 100 10 > $
gen random 500 10 > $
gen tie 500 10 > $
gen all_same 500 10 > $
gen random 1000 10 > $
gen tie 1000 10 > $
gen all_same 1000 10 > $
gen random 10000 10 > $
gen tie 10000 10 > $
gen all_same 10000 10 > $
gen random 50000 10 > $
gen tie 50000 10 > $
gen all_same 50000 10 > $
gen random 100000 10 > $
gen tie 100000 10 > $
gen all_same 100000 10 > $
gen random 200000 10 > $
gen tie 200000 10 > $
gen all_same 200000 10 > $
gen random 75972 50 > $
gen random 77971 50 > $
gen random 79970 50 > $
gen random 81969 50 > $
gen random 83968 50 > $
gen random 85967 50 > $
gen random 87966 50 > $
gen random 89965 50 > $
gen random 91964 50 > $
gen random 93963 50 > $
gen random 95962 50 > $
gen random 97961 50 > $
gen random 99960 50 > $
gen random 101959 50 > $
gen random 103958 50 > $
gen random 105957 50 > $
gen random 107956 50 > $
gen random 109955 50 > $
gen random 111954 50 > $
gen random 113953 50 > $
gen random 115952 50 > $
gen random 117951 50 > $
gen random 119950 50 > $
gen random 121949 50 > $
gen random 123948 50 > $
gen random 125947 50 > $
gen random 127946 50 > $
gen random 129945 50 > $
gen random 131944 50 > $
gen random 133943 50 > $
gen random 135942 50 > $
gen random 137941 50 > $
gen random 139940 50 > $
gen random 141939 50 > $
gen random 143938 50 > $
gen random 145937 50 > $
gen random 147936 50 > $
gen random 149935 50 > $
gen random 151934 50 > $
gen random 153933 50 > $
gen random 155932 50 > $
gen random 157931 50 > $
gen random 159930 50 > $
gen random 161929 50 > $
gen random 163928 50 > $
gen random 165927 50 > $
gen random 167926 50 > $
gen random 169925 50 > $
gen random 171924 50 > $
gen random 173923 50 > $
gen random 175922 50 > $
gen random 177921 50 > $
gen random 179920 50 > $
gen random 181919 50 > $
gen random 183918 50 > $
gen random 185917 50 > $
gen random 187916 50 > $
gen random 189915 50 > $
gen random 191914 50 > $
gen random 193913 50 > $
gen random 195912 50 > $
gen random 197911 50 > $
gen random 199910 50 > $
gen random 1919 50 > $
gen random 3918 50 > $
gen random 5917 50 > $
gen random 7916 50 > $',
    '[{"input": "7\\n4 2 4 3 2 4 3\\n", "output": "4\\n", "is_sample": true}, {"input": "6\\n5 1 5 1 7 7\\n", "output": "1\\n", "is_sample": true}, {"input": "4\\n9 8 7 6\\n", "output": "6\\n", "is_sample": true}]',
    '#include <iostream>
#include <vector>
#include <algorithm>

using namespace std;

int main() {
    ios_base::sync_with_stdio(false);
    cin.tie(NULL);

    int n;
    if (!(cin >> n) || n <= 0) return 0;

    vector<long long> a(n);
    for (int i = 0; i < n; ++i) {
        cin >> a[i];
    }

    sort(a.begin(), a.end());

    long long best_val = a[0];
    int max_freq = 1;
    int cur_freq = 1;

    for (int i = 1; i < n; ++i) {
        if (a[i] == a[i - 1]) {
            cur_freq++;
        } else {
            cur_freq = 1;
        }

        if (cur_freq > max_freq) {
            max_freq = cur_freq;
            best_val = a[i];
        }
    }

    cout << best_val << "\\n";

    return 0;
}
',
    NULL,
    NULL,
    NULL,
    'draft',
    NULL,
    NULL,
    NULL,
    @prova_owner_id,
    NOW(),
    NOW()
) ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    time_limit = VALUES(time_limit),
    memory_limit = VALUES(memory_limit),
    description = VALUES(description),
    input_description = VALUES(input_description),
    output_description = VALUES(output_description),
    notes = VALUES(notes),
    color_name = VALUES(color_name),
    color_hex = VALUES(color_hex),
    generator_source = VALUES(generator_source),
    generator_script = VALUES(generator_script),
    manual_tests = VALUES(manual_tests),
    solution_accept = VALUES(solution_accept),
    updated_at = NOW();

INSERT INTO polygon_exercises (
    contest_id, name, short_name, basename, time_limit, memory_limit, output_limit,
    description, input_description, output_description, notes,
    color_name, color_hex, generator_source, generator_script,
    manual_tests, solution_accept, solution_tle, solution_wrong,
    additional_solutions, status, calibration_results,
    published_problem_id, published_bank_code, created_by, created_at, updated_at
) VALUES (
    NULL,
    'Rotas sincronizadas',
    'I09',
    'i09_rotas_sincronizadas',
    1,
    256,
    1024,
    'Charles Babbage foi o pioneiro que projetou as primeiras máquinas computacionais mecânicas movidas a vapor e que ficou conhecido por sua aversão a qualquer barulho que atrapalhasse sua concentração. Convidado para a Maratona, ele assumiu a coordenação das vans de transporte que circulam pela BR-040 entre Ouro Branco e Conselheiro Lafaiete.

Buscando impor pontualidade britânica ao tráfego rodoviário, Babbage cronometrou que a primeira van passa pelo ponto de encontro a cada **N** minutos e a segunda a cada **M** minutos, ambas partindo juntas no instante zero. Ele precisa saber o maior intervalo de tempo que divide exatamente os dois períodos e o primeiro instante positivo em que as duas vans voltarão a passar juntas pelo ponto.

Dados **N** e **M**, ajude Babbage calculando o MDC e o MMC desses dois valores.',
    'Uma linha com dois inteiros **N** e **M**, que são, respectivamente, o intervalo em minutos da primeira e da segunda van.',
    'Na primeira linha, imprima o MDC de **N** e **M**. Na segunda linha, imprima o MMC de **N** e **M**.',
    '- `1 ≤ N, M ≤ 10^9`',
    'Lima',
    '#84CC16',
    '#include "testlib.h"
#include <iostream>

using namespace std;

int main(int argc, char* argv[]) {
    registerGen(argc, argv, 1);
    string type = opt<string>(1);

    long long n = 1, m = 1;
    if (type == "manual") {
        n = opt<long long>(2);
        m = opt<long long>(3);
    } else if (type == "equal") {
        n = m = opt<long long>(2, 100);
    } else if (type == "coprime") {
        n = 999999937LL; // prime
        m = 999999929LL; // prime
    } else if (type == "multiple") {
        m = rnd.next(1LL, 1000LL);
        long long k = rnd.next(1LL, 1000000LL);
        n = k * m;
    } else if (type == "max") {
        n = 1000000000LL;
        m = 1000000000LL;
    } else {
        n = rnd.next(1LL, 1000000000LL);
        m = rnd.next(1LL, 1000000000LL);
    }

    cout << n << " " << m << "\\n";
    return 0;
}
',
    'gen manual 1 1 > $
gen manual 1 1000000000 > $
gen manual 1000000000 1 > $
gen manual 1000000000 1000000000 > $
gen coprime 1 > $
gen max 1 > $
gen multiple 1 > $
gen multiple 2 > $
gen multiple 3 > $
gen multiple 4 > $
gen multiple 5 > $
gen multiple 6 > $
gen multiple 7 > $
gen multiple 8 > $
gen multiple 9 > $
gen multiple 10 > $
gen multiple 11 > $
gen multiple 12 > $
gen multiple 13 > $
gen multiple 14 > $
gen multiple 15 > $
gen multiple 16 > $
gen multiple 17 > $
gen multiple 18 > $
gen multiple 19 > $
gen equal 50 > $
gen equal 100 > $
gen equal 150 > $
gen equal 200 > $
gen equal 250 > $
gen equal 300 > $
gen equal 350 > $
gen equal 400 > $
gen equal 450 > $
gen equal 500 > $
gen equal 550 > $
gen equal 600 > $
gen equal 650 > $
gen equal 700 > $
gen equal 750 > $
gen equal 800 > $
gen equal 850 > $
gen equal 900 > $
gen equal 950 > $
gen random 37 > $
gen random 74 > $
gen random 111 > $
gen random 148 > $
gen random 185 > $
gen random 222 > $
gen random 259 > $
gen random 296 > $
gen random 333 > $
gen random 370 > $
gen random 407 > $
gen random 444 > $
gen random 481 > $
gen random 518 > $
gen random 555 > $
gen random 592 > $
gen random 629 > $
gen random 666 > $
gen random 703 > $
gen random 740 > $
gen random 777 > $
gen random 814 > $
gen random 851 > $
gen random 888 > $
gen random 925 > $
gen random 962 > $
gen random 999 > $
gen random 1036 > $
gen random 1073 > $
gen random 7081 > $
gen random 7178 > $
gen random 7275 > $
gen random 7372 > $
gen random 7469 > $
gen random 7566 > $
gen random 7663 > $
gen random 7760 > $
gen random 7857 > $
gen random 7954 > $
gen random 8051 > $
gen random 8148 > $
gen random 8245 > $
gen random 8342 > $
gen random 8439 > $
gen random 8536 > $
gen random 8633 > $
gen random 8730 > $
gen random 8827 > $
gen random 8924 > $
gen random 9021 > $
gen random 9118 > $
gen random 9215 > $
gen random 9312 > $
gen random 9409 > $
gen random 9506 > $
gen random 9603 > $
gen random 9700 > $
gen random 9797 > $
gen random 9894 > $
gen random 9991 > $
gen random 10088 > $',
    '[{"input": "12 18\\n", "output": "6\\n36\\n", "is_sample": true}, {"input": "7 13\\n", "output": "1\\n91\\n", "is_sample": true}, {"input": "20 5\\n", "output": "5\\n20\\n", "is_sample": true}]',
    '#include <iostream>
#include <numeric>

using namespace std;

int main() {
    ios_base::sync_with_stdio(false);
    cin.tie(NULL);

    long long n, m;
    if (cin >> n >> m) {
        long long g = std::gcd(n, m);
        long long l = (n / g) * m;
        cout << g << "\\n";
        cout << l << "\\n";
    }

    return 0;
}
',
    NULL,
    NULL,
    NULL,
    'draft',
    NULL,
    NULL,
    NULL,
    @prova_owner_id,
    NOW(),
    NOW()
) ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    time_limit = VALUES(time_limit),
    memory_limit = VALUES(memory_limit),
    description = VALUES(description),
    input_description = VALUES(input_description),
    output_description = VALUES(output_description),
    notes = VALUES(notes),
    color_name = VALUES(color_name),
    color_hex = VALUES(color_hex),
    generator_source = VALUES(generator_source),
    generator_script = VALUES(generator_script),
    manual_tests = VALUES(manual_tests),
    solution_accept = VALUES(solution_accept),
    updated_at = NOW();

INSERT INTO polygon_exercises (
    contest_id, name, short_name, basename, time_limit, memory_limit, output_limit,
    description, input_description, output_description, notes,
    color_name, color_hex, generator_source, generator_script,
    manual_tests, solution_accept, solution_tle, solution_wrong,
    additional_solutions, status, calibration_results,
    published_problem_id, published_bank_code, created_by, created_at, updated_at
) VALUES (
    NULL,
    'A mensagem de Turing',
    'I10',
    'i10_a_mensagem_de_turing',
    1,
    256,
    1024,
    'Para garantir que as estratégias das equipes e as receitas de pastel de feira não vazassem entre os campi de Ouro Branco e Ouro Preto antes da Maratona, os estudantes decidiram codificar seus avisos. Como não havia tempo nem peças para montar réplicas da máquina Enigma, recorreram a uma cifra clássica de substituição.

Alan Turing e Joan Clarke estabeleceram uma cifra circular com deslocamento **K**: cada letra da mensagem avança **K** posições no alfabeto (ou recua se **K** for negativo), de forma circular (depois de `z` vem `a`, e depois de `Z` vem `A`). Letras maiúsculas continuam maiúsculas, minúsculas permanecem minúsculas, e espaços, números e sinais de pontuação não sofrem alteração alguma.

Dados o deslocamento e a mensagem, ajude os dois escrevendo a mensagem cifrada.',
    'A primeira linha contém um inteiro **K**, o deslocamento. A segunda linha contém o texto **S**, a mensagem a ser cifrada, que pode conter espaços.',
    'Uma linha com a mensagem cifrada.',
    '- `-10^9 ≤ K ≤ 10^9`
- `1 ≤ |S| ≤ 10^5`
- **S** contém apenas caracteres ASCII.',
    'Teal',
    '#14B8A6',
    '#include "testlib.h"
#include <iostream>
#include <string>

using namespace std;

int main(int argc, char* argv[]) {
    registerGen(argc, argv, 1);
    string type = opt<string>(1);
    long long k = opt<long long>(2, 3);
    int len = opt<int>(3, 20);

    string pool = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789 ,.!-?";
    string s = "";

    if (type == "alpha_only") {
        for (int i = 0; i < len; ++i) s += (char)rnd.next(\'a\', \'z\');
    } else if (type == "all_same") {
        s = string(len, \'A\');
    } else {
        for (int i = 0; i < len; ++i) s += pool[rnd.next(0, (int)pool.size() - 1)];
    }

    if (s.empty()) s = "Hello";

    cout << k << "\\n";
    cout << s << "\\n";
    return 0;
}
',
    'gen alpha_only 0 1 > $
gen alpha_only 26 1 > $
gen alpha_only -26 1 > $
gen alpha_only 1000000000 1 > $
gen alpha_only -1000000000 1 > $
gen mixed 0 100000 > $
gen mixed 13 100000 > $
gen mixed 1000000000 100000 > $
gen mixed -1000000000 100000 > $
gen mixed -1000000000 5 > $
gen mixed -1000000000 50 > $
gen mixed -1000000000 500 > $
gen mixed -1000000000 5000 > $
gen mixed -1000000000 50000 > $
gen mixed -26 5 > $
gen mixed -26 50 > $
gen mixed -26 500 > $
gen mixed -26 5000 > $
gen mixed -26 50000 > $
gen mixed -13 5 > $
gen mixed -13 50 > $
gen mixed -13 500 > $
gen mixed -13 5000 > $
gen mixed -13 50000 > $
gen mixed -1 5 > $
gen mixed -1 50 > $
gen mixed -1 500 > $
gen mixed -1 5000 > $
gen mixed -1 50000 > $
gen mixed 0 5 > $
gen mixed 0 50 > $
gen mixed 0 500 > $
gen mixed 0 5000 > $
gen mixed 0 50000 > $
gen mixed 1 5 > $
gen mixed 1 50 > $
gen mixed 1 500 > $
gen mixed 1 5000 > $
gen mixed 1 50000 > $
gen mixed 13 5 > $
gen mixed 13 50 > $
gen mixed 13 500 > $
gen mixed 13 5000 > $
gen mixed 13 50000 > $
gen mixed 26 5 > $
gen mixed 26 50 > $
gen mixed 26 500 > $
gen mixed 26 5000 > $
gen mixed 26 50000 > $
gen mixed 100 5 > $
gen mixed 100 50 > $
gen mixed 100 500 > $
gen mixed 100 5000 > $
gen mixed 100 50000 > $
gen mixed 1000000000 5 > $
gen mixed 1000000000 50 > $
gen mixed 1000000000 500 > $
gen mixed 1000000000 5000 > $
gen mixed 1000000000 50000 > $
gen mixed 59 58243 > $
gen mixed 60 59230 > $
gen mixed 61 60217 > $
gen mixed 62 61204 > $
gen mixed 63 62191 > $
gen mixed 64 63178 > $
gen mixed 65 64165 > $
gen mixed 66 65152 > $
gen mixed 67 66139 > $
gen mixed 68 67126 > $
gen mixed 69 68113 > $
gen mixed 70 69100 > $
gen mixed 71 70087 > $
gen mixed 72 71074 > $
gen mixed 73 72061 > $
gen mixed 74 73048 > $
gen mixed 75 74035 > $
gen mixed 76 75022 > $
gen mixed 77 76009 > $
gen mixed 78 76996 > $
gen mixed 79 77983 > $
gen mixed 80 78970 > $
gen mixed 81 79957 > $
gen mixed 82 80944 > $
gen mixed 83 81931 > $
gen mixed 84 82918 > $
gen mixed 85 83905 > $
gen mixed 86 84892 > $
gen mixed 87 85879 > $
gen mixed 88 86866 > $
gen mixed 89 87853 > $
gen mixed 90 88840 > $
gen mixed 91 89827 > $
gen mixed 92 90814 > $
gen mixed 93 91801 > $
gen mixed 94 92788 > $
gen mixed 95 93775 > $
gen mixed 96 94762 > $
gen mixed 97 95749 > $
gen mixed 98 96736 > $
gen mixed 99 97723 > $
gen mixed 100 98710 > $
gen mixed 101 99697 > $
gen mixed 102 694 > $
gen mixed 103 1681 > $
gen mixed 104 2668 > $',
    '[{"input": "3\\nAda Lovelace!\\n", "output": "Dgd Oryhodfh!\\n", "is_sample": true}, {"input": "-1\\nAbc Z!\\n", "output": "Zab Y!\\n", "is_sample": true}, {"input": "26\\nOuro Branco 2026\\n", "output": "Ouro Branco 2026\\n", "is_sample": true}]',
    '#include <iostream>
#include <string>

using namespace std;

int main() {
    ios_base::sync_with_stdio(false);
    cin.tie(NULL);

    long long k;
    if (!(cin >> k)) return 0;

    string dummy;
    getline(cin, dummy);

    string s;
    if (!getline(cin, s)) return 0;

    if (!s.empty() && s.back() == \'\\r\') {
        s.pop_back();
    }

    int shift = (int)((k % 26 + 26) % 26);

    for (char &c : s) {
        if (c >= \'a\' && c <= \'z\') {
            c = (char)(\'a\' + (c - \'a\' + shift) % 26);
        } else if (c >= \'A\' && c <= \'Z\') {
            c = (char)(\'A\' + (c - \'A\' + shift) % 26);
        }
    }

    cout << s << "\\n";

    return 0;
}
',
    NULL,
    NULL,
    NULL,
    'draft',
    NULL,
    NULL,
    NULL,
    @prova_owner_id,
    NOW(),
    NOW()
) ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    time_limit = VALUES(time_limit),
    memory_limit = VALUES(memory_limit),
    description = VALUES(description),
    input_description = VALUES(input_description),
    output_description = VALUES(output_description),
    notes = VALUES(notes),
    color_name = VALUES(color_name),
    color_hex = VALUES(color_hex),
    generator_source = VALUES(generator_source),
    generator_script = VALUES(generator_script),
    manual_tests = VALUES(manual_tests),
    solution_accept = VALUES(solution_accept),
    updated_at = NOW();

INSERT INTO polygon_exercises (
    contest_id, name, short_name, basename, time_limit, memory_limit, output_limit,
    description, input_description, output_description, notes,
    color_name, color_hex, generator_source, generator_script,
    manual_tests, solution_accept, solution_tle, solution_wrong,
    additional_solutions, status, calibration_results,
    published_problem_id, published_bank_code, created_by, created_at, updated_at
) VALUES (
    NULL,
    'Pintura econômica',
    'I11',
    'i11_pintura_econ_mica',
    1,
    256,
    1024,
    'Jean Bartik foi uma das destemidas mulheres que programaram o ENIAC na década de 1940, manipulando pesados cabos elétricos e chaves de força com as próprias mãos. Convidada para uma mostra de arte digital em Ouro Preto, Jean encontrou uma grade quadriculada de lâmpadas pretas e brancas desordenada pouco antes da inauguração.

Para ajustar o mural e atingir o desenho encomendado, Jean opera uma bancada onde pode realizar duas operações: escolher uma linha inteira e inverter todas as suas cores, ou escolher uma coluna inteira e inverter todas as suas cores (branco vira preto e preto vira branco). Como repetir a operação na mesma linha ou coluna anula o efeito, cada linha e cada coluna é acionada no máximo uma vez. Jean deseja atingir o padrão final com o menor número de operações possíveis, ou constatar que isso é impossível.

Dados o quadro atual e o quadro desejado, ajude Jean a descobrir o menor número de operações necessárias, ou informar que é impossível.',
    'A primeira linha contém dois inteiros **N** e **M**, que são, respectivamente, o número de linhas e de colunas do quadro. Em seguida, **N** linhas com **M** caracteres `0` (branco) ou `1` (preto) descrevem o quadro atual **A**. Depois, mais **N** linhas com **M** caracteres descrevem o quadro desejado **B**.',
    'Um inteiro: o menor número de operações para transformar **A** em **B**, ou `-1` se for impossível.',
    '- `1 ≤ N, M ≤ 500`',
    'Índigo',
    '#6366F1',
    '#include "testlib.h"
#include <iostream>
#include <vector>
#include <string>

using namespace std;

int main(int argc, char* argv[]) {
    registerGen(argc, argv, 1);
    string type = opt<string>(1);
    int n = opt<int>(2, 3);
    int m = opt<int>(3, 3);

    cout << n << " " << m << "\\n";

    vector<string> a(n, string(m, \'0\'));
    vector<string> b(n, string(m, \'0\'));

    if (type == "same") {
        for (int i = 0; i < n; ++i) {
            for (int j = 0; j < m; ++j) {
                a[i][j] = b[i][j] = (char)(\'0\' + rnd.next(0, 1));
            }
        }
    } else if (type == "solvable") {
        vector<int> r(n), c(m);
        for (int i = 0; i < n; ++i) r[i] = rnd.next(0, 1);
        for (int j = 0; j < m; ++j) c[j] = rnd.next(0, 1);
        for (int i = 0; i < n; ++i) {
            for (int j = 0; j < m; ++j) {
                a[i][j] = (char)(\'0\' + rnd.next(0, 1));
                b[i][j] = (char)(\'0\' + ((a[i][j] - \'0\') ^ r[i] ^ c[j]));
            }
        }
    } else {
        for (int i = 0; i < n; ++i) {
            for (int j = 0; j < m; ++j) {
                a[i][j] = (char)(\'0\' + rnd.next(0, 1));
                b[i][j] = (char)(\'0\' + rnd.next(0, 1));
            }
        }
    }

    for (int i = 0; i < n; ++i) cout << a[i] << "\\n";
    for (int i = 0; i < n; ++i) cout << b[i] << "\\n";
    return 0;
}
',
    'gen same 1 1 > $
gen solvable 1 1 > $
gen random 1 1 > $
gen same 500 500 > $
gen solvable 500 500 > $
gen random 500 500 > $
gen solvable 2 2 > $
gen random 2 2 > $
gen same 2 2 > $
gen solvable 3 3 > $
gen random 3 3 > $
gen same 3 3 > $
gen solvable 4 5 > $
gen random 4 5 > $
gen same 4 5 > $
gen solvable 10 10 > $
gen random 10 10 > $
gen same 10 10 > $
gen solvable 20 20 > $
gen random 20 20 > $
gen same 20 20 > $
gen solvable 50 50 > $
gen random 50 50 > $
gen same 50 50 > $
gen solvable 100 100 > $
gen random 100 100 > $
gen same 100 100 > $
gen solvable 200 200 > $
gen random 200 200 > $
gen same 200 200 > $
gen solvable 350 350 > $
gen random 350 350 > $
gen same 350 350 > $
gen solvable 500 500 > $
gen random 500 500 > $
gen same 500 500 > $
gen solvable 469 45 > $
gen solvable 482 74 > $
gen solvable 495 103 > $
gen solvable 8 132 > $
gen solvable 21 161 > $
gen solvable 34 190 > $
gen solvable 47 219 > $
gen solvable 60 248 > $
gen solvable 73 277 > $
gen solvable 86 306 > $
gen solvable 99 335 > $
gen solvable 112 364 > $
gen solvable 125 393 > $
gen solvable 138 422 > $
gen solvable 151 451 > $
gen solvable 164 480 > $
gen solvable 177 9 > $
gen solvable 190 38 > $
gen solvable 203 67 > $
gen solvable 216 96 > $
gen solvable 229 125 > $
gen solvable 242 154 > $
gen solvable 255 183 > $
gen solvable 268 212 > $
gen solvable 281 241 > $
gen solvable 294 270 > $
gen solvable 307 299 > $
gen solvable 320 328 > $
gen solvable 333 357 > $
gen solvable 346 386 > $
gen solvable 359 415 > $
gen solvable 372 444 > $
gen solvable 385 473 > $
gen solvable 398 2 > $
gen solvable 411 31 > $
gen solvable 424 60 > $
gen solvable 437 89 > $
gen solvable 450 118 > $
gen solvable 463 147 > $
gen solvable 476 176 > $
gen solvable 489 205 > $
gen solvable 2 234 > $
gen solvable 15 263 > $
gen solvable 28 292 > $
gen solvable 41 321 > $
gen solvable 54 350 > $
gen solvable 67 379 > $
gen solvable 80 408 > $
gen solvable 93 437 > $
gen solvable 106 466 > $
gen solvable 119 495 > $
gen solvable 132 24 > $
gen solvable 145 53 > $
gen solvable 158 82 > $
gen solvable 171 111 > $
gen solvable 184 140 > $
gen solvable 197 169 > $
gen solvable 210 198 > $
gen solvable 223 227 > $
gen solvable 236 256 > $
gen solvable 249 285 > $
gen solvable 262 314 > $
gen solvable 275 343 > $
gen solvable 288 372 > $
gen solvable 301 401 > $
gen solvable 314 430 > $
gen solvable 327 459 > $
gen solvable 340 488 > $
gen solvable 353 17 > $',
    '[{"input": "2 2\\n00\\n00\\n11\\n11\\n", "output": "2\\n", "is_sample": true}, {"input": "2 3\\n000\\n000\\n101\\n001\\n", "output": "-1\\n", "is_sample": true}, {"input": "3 3\\n111\\n111\\n111\\n000\\n000\\n000\\n", "output": "3\\n", "is_sample": true}]',
    '#include <iostream>
#include <vector>
#include <string>
#include <algorithm>

using namespace std;

int main() {
    ios_base::sync_with_stdio(false);
    cin.tie(NULL);

    int n, m;
    if (!(cin >> n >> m)) return 0;

    vector<string> a(n), b(n);
    for (int i = 0; i < n; ++i) {
        cin >> a[i];
    }
    for (int i = 0; i < n; ++i) {
        cin >> b[i];
    }

    vector<vector<int>> d(n, vector<int>(m));
    for (int i = 0; i < n; ++i) {
        for (int j = 0; j < m; ++j) {
            d[i][j] = (a[i][j] != b[i][j]) ? 1 : 0;
        }
    }

    int ans = -1;

    for (int r0 = 0; r0 <= 1; ++r0) {
        vector<int> r(n, 0);
        vector<int> c(m, 0);
        r[0] = r0;

        for (int j = 0; j < m; ++j) {
            c[j] = d[0][j] ^ r[0];
        }

        bool ok = true;
        for (int i = 1; i < n; ++i) {
            r[i] = d[i][0] ^ c[0];
            for (int j = 1; j < m; ++j) {
                if ((r[i] ^ c[j]) != d[i][j]) {
                    ok = false;
                    break;
                }
            }
            if (!ok) break;
        }

        if (ok) {
            int cur_ops = 0;
            for (int i = 0; i < n; ++i) cur_ops += r[i];
            for (int j = 0; j < m; ++j) cur_ops += c[j];

            if (ans == -1 || cur_ops < ans) {
                ans = cur_ops;
            }
        }
    }

    cout << ans << "\\n";

    return 0;
}
',
    NULL,
    NULL,
    NULL,
    'draft',
    NULL,
    NULL,
    NULL,
    @prova_owner_id,
    NOW(),
    NOW()
) ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    time_limit = VALUES(time_limit),
    memory_limit = VALUES(memory_limit),
    description = VALUES(description),
    input_description = VALUES(input_description),
    output_description = VALUES(output_description),
    notes = VALUES(notes),
    color_name = VALUES(color_name),
    color_hex = VALUES(color_hex),
    generator_source = VALUES(generator_source),
    generator_script = VALUES(generator_script),
    manual_tests = VALUES(manual_tests),
    solution_accept = VALUES(solution_accept),
    updated_at = NOW();

INSERT INTO polygon_exercises (
    contest_id, name, short_name, basename, time_limit, memory_limit, output_limit,
    description, input_description, output_description, notes,
    color_name, color_hex, generator_source, generator_script,
    manual_tests, solution_accept, solution_tle, solution_wrong,
    additional_solutions, status, calibration_results,
    published_problem_id, published_bank_code, created_by, created_at, updated_at
) VALUES (
    NULL,
    'Consultas no caminho',
    'I12',
    'i12_consultas_no_caminho',
    1,
    256,
    1024,
    'Konrad Zuse, pioneiro alemão que construiu os primeiros computadores funcionais na sala de seus pais usando chapas metálicas e tiras perfuradas de filme de 35mm, decidiu explorar as trilhas da Estrada Real em Congonhas carregando seus instrumentos de medição.

Metódico com seus registros de engenharia, Zuse dividiu a trilha em trechos consecutivos e mediu a variação de altitude de cada um, em metros (valores positivos nas subidas e negativos nas descidas). Para informar aos caminhantes o esforço do trajeto, ele precisa responder a várias consultas informando o saldo acumulado de altitude entre dois trechos **L** e **R**, incluindo ambos os extremos.

Dadas as variações de cada trecho e várias consultas, ajude Zuse respondendo a cada consulta com a soma das variações do trecho **L** ao trecho **R**.',
    'A primeira linha contém dois inteiros **N** e **Q**, que são, respectivamente, o número de trechos e o número de consultas. A segunda linha contém **N** inteiros **A_1, A_2, ..., A_N**, que são as variações de altitude dos trechos. Cada uma das próximas **Q** linhas contém dois inteiros **L** e **R**, que são, respectivamente, o primeiro e o último trecho da consulta.',
    'Para cada consulta, imprima em uma linha a soma de **A_L** até **A_R**, inclusive.',
    '- `1 ≤ N, Q ≤ 2·10^5`
- `-10^9 ≤ A_i ≤ 10^9`
- `1 ≤ L ≤ R ≤ N`',
    'Âmbar',
    '#D97706',
    '#include "testlib.h"
#include <iostream>
#include <vector>

using namespace std;

int main(int argc, char* argv[]) {
    registerGen(argc, argv, 1);
    string type = opt<string>(1);
    int n = opt<int>(2, 10);
    int q = opt<int>(3, 10);

    cout << n << " " << q << "\\n";
    for (int i = 1; i <= n; ++i) {
        long long val = rnd.next(-1000000000LL, 1000000000LL);
        cout << val << (i == n ? "" : " ");
    }
    cout << "\\n";

    for (int i = 0; i < q; ++i) {
        int l = 1, r = 1;
        if (type == "point") {
            l = r = rnd.next(1, n);
        } else if (type == "full") {
            l = 1;
            r = n;
        } else {
            l = rnd.next(1, n);
            r = rnd.next(l, n);
        }
        cout << l << " " << r << "\\n";
    }
    return 0;
}
',
    'gen point 1 1 > $
gen full 1 1 > $
gen random 1 1 > $
gen point 200000 200000 > $
gen full 200000 200000 > $
gen random 200000 200000 > $
gen random 2 2 > $
gen point 2 2 > $
gen full 2 2 > $
gen random 5 5 > $
gen point 5 5 > $
gen full 5 5 > $
gen random 10 10 > $
gen point 10 10 > $
gen full 10 10 > $
gen random 50 50 > $
gen point 50 50 > $
gen full 50 50 > $
gen random 100 100 > $
gen point 100 100 > $
gen full 100 100 > $
gen random 500 500 > $
gen point 500 500 > $
gen full 500 500 > $
gen random 1000 1000 > $
gen point 1000 1000 > $
gen full 1000 1000 > $
gen random 5000 1000 > $
gen point 5000 1000 > $
gen full 5000 1000 > $
gen random 20000 1000 > $
gen point 20000 1000 > $
gen full 20000 1000 > $
gen random 100000 1000 > $
gen point 100000 1000 > $
gen full 100000 1000 > $
gen random 200000 1000 > $
gen point 200000 1000 > $
gen full 200000 1000 > $
gen random 77971 100 > $
gen random 79970 100 > $
gen random 81969 100 > $
gen random 83968 100 > $
gen random 85967 100 > $
gen random 87966 100 > $
gen random 89965 100 > $
gen random 91964 100 > $
gen random 93963 100 > $
gen random 95962 100 > $
gen random 97961 100 > $
gen random 99960 100 > $
gen random 101959 100 > $
gen random 103958 100 > $
gen random 105957 100 > $
gen random 107956 100 > $
gen random 109955 100 > $
gen random 111954 100 > $
gen random 113953 100 > $
gen random 115952 100 > $
gen random 117951 100 > $
gen random 119950 100 > $
gen random 121949 100 > $
gen random 123948 100 > $
gen random 125947 100 > $
gen random 127946 100 > $
gen random 129945 100 > $
gen random 131944 100 > $
gen random 133943 100 > $
gen random 135942 100 > $
gen random 137941 100 > $
gen random 139940 100 > $
gen random 141939 100 > $
gen random 143938 100 > $
gen random 145937 100 > $
gen random 147936 100 > $
gen random 149935 100 > $
gen random 151934 100 > $
gen random 153933 100 > $
gen random 155932 100 > $
gen random 157931 100 > $
gen random 159930 100 > $
gen random 161929 100 > $
gen random 163928 100 > $
gen random 165927 100 > $
gen random 167926 100 > $
gen random 169925 100 > $
gen random 171924 100 > $
gen random 173923 100 > $
gen random 175922 100 > $
gen random 177921 100 > $
gen random 179920 100 > $
gen random 181919 100 > $
gen random 183918 100 > $
gen random 185917 100 > $
gen random 187916 100 > $
gen random 189915 100 > $
gen random 191914 100 > $
gen random 193913 100 > $
gen random 195912 100 > $
gen random 197911 100 > $
gen random 199910 100 > $
gen random 1919 100 > $
gen random 3918 100 > $
gen random 5917 100 > $
gen random 7916 100 > $',
    '[{"input": "5 3\\n2 4 6 8 10\\n1 3\\n2 5\\n4 4\\n", "output": "12\\n28\\n8\\n", "is_sample": true}, {"input": "3 2\\n-1 5 -2\\n1 3\\n2 2\\n", "output": "2\\n5\\n", "is_sample": true}, {"input": "1 1\\n42\\n1 1\\n", "output": "42\\n", "is_sample": true}]',
    '#include <iostream>
#include <vector>

using namespace std;

int main() {
    ios_base::sync_with_stdio(false);
    cin.tie(NULL);

    int n, q;
    if (!(cin >> n >> q)) return 0;

    vector<long long> pref(n + 1, 0);
    for (int i = 1; i <= n; ++i) {
        long long val;
        cin >> val;
        pref[i] = pref[i - 1] + val;
    }

    while (q--) {
        int l, r;
        cin >> l >> r;
        cout << (pref[r] - pref[l - 1]) << "\\n";
    }

    return 0;
}
',
    NULL,
    NULL,
    NULL,
    'draft',
    NULL,
    NULL,
    NULL,
    @prova_owner_id,
    NOW(),
    NOW()
) ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    time_limit = VALUES(time_limit),
    memory_limit = VALUES(memory_limit),
    description = VALUES(description),
    input_description = VALUES(input_description),
    output_description = VALUES(output_description),
    notes = VALUES(notes),
    color_name = VALUES(color_name),
    color_hex = VALUES(color_hex),
    generator_source = VALUES(generator_source),
    generator_script = VALUES(generator_script),
    manual_tests = VALUES(manual_tests),
    solution_accept = VALUES(solution_accept),
    updated_at = NOW();

INSERT INTO polygon_exercises (
    contest_id, name, short_name, basename, time_limit, memory_limit, output_limit,
    description, input_description, output_description, notes,
    color_name, color_hex, generator_source, generator_script,
    manual_tests, solution_accept, solution_tle, solution_wrong,
    additional_solutions, status, calibration_results,
    published_problem_id, published_bank_code, created_by, created_at, updated_at
) VALUES (
    NULL,
    'Pares na estrada',
    'T01',
    't01_pares_na_estrada',
    1,
    256,
    1024,
    'Durante a viagem de van entre Ouro Branco e Conselheiro Lafaiete pela BR-040, Ada Lovelace observava com atenção as inúmeras placas de trânsito e marcos de quilometragem instalados ao longo da rodovia.

Sempre atenta a padrões numéricos, a primeira programadora da história anotou os valores das placas em seu caderno de viagem e decidiu classificá-los conforme a paridade: separando quantos números são pares e quantos são ímpares.

Dada a lista de números, ajude Ada contando quantos são pares e quantos são ímpares.',
    'A primeira linha contém um inteiro **N**, a quantidade de placas. A segunda linha contém **N** inteiros **A_1, A_2, ..., A_N**, que são os números das placas.',
    'Uma linha com dois inteiros separados por espaço: a quantidade de números pares e a quantidade de números ímpares, nessa ordem.',
    '- `1 ≤ N ≤ 10^6`
- `-10^9 ≤ A_i ≤ 10^9`',
    'Esmeralda',
    '#059669',
    '#include "testlib.h"
#include <iostream>

using namespace std;

int main(int argc, char* argv[]) {
    registerGen(argc, argv, 1);
    string type = opt<string>(1);
    int n = opt<int>(2, 10);

    cout << n << "\\n";
    for (int i = 0; i < n; ++i) {
        long long val = 0;
        if (type == "all_even") {
            val = rnd.next(-500000000LL, 500000000LL) * 2;
        } else if (type == "all_odd") {
            val = rnd.next(-500000000LL, 500000000LL) * 2 + 1;
        } else if (type == "zeros") {
            val = 0;
        } else {
            val = rnd.next(-1000000000LL, 1000000000LL);
        }
        cout << val << (i + 1 == n ? "" : " ");
    }
    cout << "\\n";
    return 0;
}
',
    'gen all_even 1 > $
gen all_odd 1 > $
gen zeros 1 > $
gen all_even 1000000 > $
gen all_odd 1000000 > $
gen zeros 1000000 > $
gen random 1000000 > $
gen random 2 > $
gen all_even 2 > $
gen all_odd 2 > $
gen random 5 > $
gen all_even 5 > $
gen all_odd 5 > $
gen random 10 > $
gen all_even 10 > $
gen all_odd 10 > $
gen random 50 > $
gen all_even 50 > $
gen all_odd 50 > $
gen random 100 > $
gen all_even 100 > $
gen all_odd 100 > $
gen random 500 > $
gen all_even 500 > $
gen all_odd 500 > $
gen random 1000 > $
gen all_even 1000 > $
gen all_odd 1000 > $
gen random 10000 > $
gen all_even 10000 > $
gen all_odd 10000 > $
gen random 50000 > $
gen all_even 50000 > $
gen all_odd 50000 > $
gen random 100000 > $
gen all_even 100000 > $
gen all_odd 100000 > $
gen random 500000 > $
gen all_even 500000 > $
gen all_odd 500000 > $
gen random 1000000 > $
gen all_even 1000000 > $
gen all_odd 1000000 > $
gen random 428849 > $
gen random 438822 > $
gen random 448795 > $
gen random 458768 > $
gen random 468741 > $
gen random 478714 > $
gen random 488687 > $
gen random 498660 > $
gen random 508633 > $
gen random 518606 > $
gen random 528579 > $
gen random 538552 > $
gen random 548525 > $
gen random 558498 > $
gen random 568471 > $
gen random 578444 > $
gen random 588417 > $
gen random 598390 > $
gen random 608363 > $
gen random 618336 > $
gen random 628309 > $
gen random 638282 > $
gen random 648255 > $
gen random 658228 > $
gen random 668201 > $
gen random 678174 > $
gen random 688147 > $
gen random 698120 > $
gen random 708093 > $
gen random 718066 > $
gen random 728039 > $
gen random 738012 > $
gen random 747985 > $
gen random 757958 > $
gen random 767931 > $
gen random 777904 > $
gen random 787877 > $
gen random 797850 > $
gen random 807823 > $
gen random 817796 > $
gen random 827769 > $
gen random 837742 > $
gen random 847715 > $
gen random 857688 > $
gen random 867661 > $
gen random 877634 > $
gen random 887607 > $
gen random 897580 > $
gen random 907553 > $
gen random 917526 > $
gen random 927499 > $
gen random 937472 > $
gen random 947445 > $
gen random 957418 > $
gen random 967391 > $
gen random 977364 > $
gen random 987337 > $
gen random 997310 > $
gen random 7293 > $
gen random 17266 > $
gen random 27239 > $
gen random 37212 > $',
    '[{"input": "5\\n1 2 3 4 6\\n", "output": "3 2\\n", "is_sample": true}, {"input": "4\\n-2 -1 0 7\\n", "output": "2 2\\n", "is_sample": true}, {"input": "1\\n9\\n", "output": "0 1\\n", "is_sample": true}]',
    '#include <iostream>

using namespace std;

int main() {
    ios_base::sync_with_stdio(false);
    cin.tie(NULL);

    int n;
    if (!(cin >> n)) return 0;

    int even_count = 0;
    int odd_count = 0;

    for (int i = 0; i < n; i++) {
        long long a;
        cin >> a;
        if (a % 2 == 0) {
            even_count++;
        } else {
            odd_count++;
        }
    }

    cout << even_count << " " << odd_count << "\\n";

    return 0;
}
',
    NULL,
    NULL,
    NULL,
    'draft',
    NULL,
    NULL,
    NULL,
    @prova_owner_id,
    NOW(),
    NOW()
) ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    time_limit = VALUES(time_limit),
    memory_limit = VALUES(memory_limit),
    description = VALUES(description),
    input_description = VALUES(input_description),
    output_description = VALUES(output_description),
    notes = VALUES(notes),
    color_name = VALUES(color_name),
    color_hex = VALUES(color_hex),
    generator_source = VALUES(generator_source),
    generator_script = VALUES(generator_script),
    manual_tests = VALUES(manual_tests),
    solution_accept = VALUES(solution_accept),
    updated_at = NOW();

INSERT INTO polygon_exercises (
    contest_id, name, short_name, basename, time_limit, memory_limit, output_limit,
    description, input_description, output_description, notes,
    color_name, color_hex, generator_source, generator_script,
    manual_tests, solution_accept, solution_tle, solution_wrong,
    additional_solutions, status, calibration_results,
    published_problem_id, published_bank_code, created_by, created_at, updated_at
) VALUES (
    NULL,
    'Número de Newton',
    'T02',
    't02_n_mero_de_newton',
    1,
    256,
    1024,
    'Sir Isaac Newton, famoso tanto pela história da maçã quanto por formular as leis clássicas do movimento e cálculos de aproximação, visitou os laboratórios no campus de Ouro Branco e encontrou estudantes formulando enigmas aritméticos.

Para testar a agilidade de cálculo da turma, Newton propôs uma regra especial para classificar os equipamentos a partir de seu número de patrimônio **N**: primeiro somam-se todos os algarismos de **N**; em seguida, calcula-se o fatorial dessa soma. Como fatoriais crescem rapidamente e podem acumular muitos dígitos, o resultado deve ser apresentado por completo.

Dado o número de catálogo **N**, ajude a turma calculando a soma dos algarismos de **N** e o fatorial dessa soma.',
    'Uma linha com um inteiro **N**, o número de catálogo do equipamento.',
    'Na primeira linha, imprima a soma dos algarismos de **N**. Na segunda linha, imprima o fatorial dessa soma.',
    '- `0 ≤ N ≤ 10^18`',
    'Violeta',
    '#7C3AED',
    '#include "testlib.h"
#include <iostream>
#include <string>

using namespace std;

int main(int argc, char* argv[]) {
    registerGen(argc, argv, 1);
    string type = opt<string>(1);

    string s = "";
    if (type == "manual") {
        s = opt<string>(2);
    } else if (type == "zero") {
        s = "0";
    } else if (type == "nines") {
        int len = opt<int>(2, 18);
        s = string(len, \'9\');
    } else if (type == "power10") {
        int zeros = opt<int>(2, 18);
        s = "1" + string(zeros, \'0\');
    } else {
        long long n = rnd.next(0LL, 1000000000000000000LL);
        s = to_string(n);
    }

    cout << s << "\\n";
    return 0;
}
',
    'gen zero 1 > $
gen manual 1 > $
gen manual 2 > $
gen manual 3 > $
gen manual 4 > $
gen manual 5 > $
gen manual 1000000000000000000 > $
gen power10 18 > $
gen nines 18 > $
gen power10 1 > $
gen power10 2 > $
gen power10 3 > $
gen power10 4 > $
gen power10 5 > $
gen power10 6 > $
gen power10 7 > $
gen power10 8 > $
gen power10 9 > $
gen power10 10 > $
gen power10 11 > $
gen power10 12 > $
gen power10 13 > $
gen power10 14 > $
gen power10 15 > $
gen power10 16 > $
gen power10 17 > $
gen power10 18 > $
gen nines 1 > $
gen nines 2 > $
gen nines 3 > $
gen nines 4 > $
gen nines 5 > $
gen nines 6 > $
gen nines 7 > $
gen nines 8 > $
gen nines 9 > $
gen nines 10 > $
gen nines 11 > $
gen nines 12 > $
gen nines 13 > $
gen nines 14 > $
gen nines 15 > $
gen nines 16 > $
gen nines 17 > $
gen nines 18 > $
gen random 5895 > $
gen random 6026 > $
gen random 6157 > $
gen random 6288 > $
gen random 6419 > $
gen random 6550 > $
gen random 6681 > $
gen random 6812 > $
gen random 6943 > $
gen random 7074 > $
gen random 7205 > $
gen random 7336 > $
gen random 7467 > $
gen random 7598 > $
gen random 7729 > $
gen random 7860 > $
gen random 7991 > $
gen random 8122 > $
gen random 8253 > $
gen random 8384 > $
gen random 8515 > $
gen random 8646 > $
gen random 8777 > $
gen random 8908 > $
gen random 9039 > $
gen random 9170 > $
gen random 9301 > $
gen random 9432 > $
gen random 9563 > $
gen random 9694 > $
gen random 9825 > $
gen random 9956 > $
gen random 10087 > $
gen random 10218 > $
gen random 10349 > $
gen random 10480 > $
gen random 10611 > $
gen random 10742 > $
gen random 10873 > $
gen random 11004 > $
gen random 11135 > $
gen random 11266 > $
gen random 11397 > $
gen random 11528 > $
gen random 11659 > $
gen random 11790 > $
gen random 11921 > $
gen random 12052 > $
gen random 12183 > $
gen random 12314 > $
gen random 12445 > $
gen random 12576 > $
gen random 12707 > $
gen random 12838 > $
gen random 12969 > $
gen random 13100 > $
gen random 13231 > $
gen random 13362 > $
gen random 13493 > $
gen random 13624 > $',
    '[{"input": "1234\\n", "output": "10\\n3628800\\n", "is_sample": true}, {"input": "0\\n", "output": "0\\n1\\n", "is_sample": true}, {"input": "99\\n", "output": "18\\n6402373705728000\\n", "is_sample": true}]',
    '#include <iostream>
#include <string>
#include <vector>
#include <cctype>

using namespace std;

int main() {
    ios_base::sync_with_stdio(false);
    cin.tie(NULL);

    string s;
    if (!(cin >> s)) return 0;

    int sum = 0;
    for (char c : s) {
        if (isdigit(c)) {
            sum += c - \'0\';
        }
    }

    cout << sum << "\\n";

    vector<int> digits;
    digits.push_back(1);

    for (int i = 2; i <= sum; i++) {
        int carry = 0;
        for (size_t j = 0; j < digits.size(); j++) {
            long long prod = 1LL * digits[j] * i + carry;
            digits[j] = prod % 10;
            carry = prod / 10;
        }
        while (carry > 0) {
            digits.push_back(carry % 10);
            carry /= 10;
        }
    }

    for (int i = (int)digits.size() - 1; i >= 0; i--) {
        cout << digits[i];
    }
    cout << "\\n";

    return 0;
}
',
    NULL,
    NULL,
    NULL,
    'draft',
    NULL,
    NULL,
    NULL,
    @prova_owner_id,
    NOW(),
    NOW()
) ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    time_limit = VALUES(time_limit),
    memory_limit = VALUES(memory_limit),
    description = VALUES(description),
    input_description = VALUES(input_description),
    output_description = VALUES(output_description),
    notes = VALUES(notes),
    color_name = VALUES(color_name),
    color_hex = VALUES(color_hex),
    generator_source = VALUES(generator_source),
    generator_script = VALUES(generator_script),
    manual_tests = VALUES(manual_tests),
    solution_accept = VALUES(solution_accept),
    updated_at = NOW();

INSERT INTO polygon_exercises (
    contest_id, name, short_name, basename, time_limit, memory_limit, output_limit,
    description, input_description, output_description, notes,
    color_name, color_hex, generator_source, generator_script,
    manual_tests, solution_accept, solution_tle, solution_wrong,
    additional_solutions, status, calibration_results,
    published_problem_id, published_bank_code, created_by, created_at, updated_at
) VALUES (
    NULL,
    'Anagramas do Alto Paraopeba',
    'T03',
    't03_anagramas_do_alto_paraopeba',
    1,
    256,
    1024,
    'O Alto Paraopeba reúne municípios com rica história e nomes sonoros, como Congonhas, Lafaiete e Ouro Branco. Durante o credenciamento da Maratona, Ada Lovelace reparou que os crachás impressos na pressa continham trocas divertidas de letras, formando palavras embaralhadas.

Para identificar quais palavras eram correspondentes, Ada estabeleceu uma verificação de anagramas: duas palavras são consideradas anagramas quando possuem rigorosamente as mesmas letras com as mesmas quantidades, em qualquer ordem, tratando letras maiúsculas e minúsculas como equivalentes.

Dadas duas palavras, ajude Ada a decidir se uma é anagrama da outra.',
    'Duas linhas. A primeira contém a palavra **S** e a segunda contém a palavra **T**, ambas formadas apenas por letras, sem espaços.',
    'Imprima `SIM` se **S** e **T** forem anagramas ou `NAO` caso contrário.',
    '- `1 ≤ |S|, |T| ≤ 10^5`
- **S** e **T** contêm apenas letras ASCII.',
    'Fúcsia',
    '#C026D3',
    '#include "testlib.h"
#include <iostream>
#include <string>
#include <algorithm>

using namespace std;

int main(int argc, char* argv[]) {
    registerGen(argc, argv, 1);
    string type = opt<string>(1);
    int len = opt<int>(2, 10);

    string s = "", t = "";
    for (int i = 0; i < len; ++i) s += (char)rnd.next(\'a\', \'z\');

    if (type == "anagram") {
        t = s;
        shuffle(t.begin(), t.end());
    } else if (type == "diff_len") {
        t = s.substr(0, max(1, len - 1));
    } else if (type == "one_diff") {
        t = s;
        t[0] = (t[0] == \'z\' ? \'a\' : t[0] + 1);
        shuffle(t.begin(), t.end());
    } else {
        for (int i = 0; i < len; ++i) t += (char)rnd.next(\'a\', \'z\');
    }

    cout << s << "\\n";
    cout << t << "\\n";
    return 0;
}
',
    'gen anagram 1 > $
gen diff_len 2 > $
gen one_diff 1 > $
gen anagram 100000 > $
gen diff_len 100000 > $
gen one_diff 100000 > $
gen random 100000 > $
gen anagram 2 > $
gen one_diff 2 > $
gen diff_len 2 > $
gen anagram 5 > $
gen one_diff 5 > $
gen diff_len 5 > $
gen anagram 10 > $
gen one_diff 10 > $
gen diff_len 10 > $
gen anagram 50 > $
gen one_diff 50 > $
gen diff_len 50 > $
gen anagram 100 > $
gen one_diff 100 > $
gen diff_len 100 > $
gen anagram 500 > $
gen one_diff 500 > $
gen diff_len 500 > $
gen anagram 1000 > $
gen one_diff 1000 > $
gen diff_len 1000 > $
gen anagram 5000 > $
gen one_diff 5000 > $
gen diff_len 5000 > $
gen anagram 20000 > $
gen one_diff 20000 > $
gen diff_len 20000 > $
gen anagram 50000 > $
gen one_diff 50000 > $
gen diff_len 50000 > $
gen anagram 100000 > $
gen one_diff 100000 > $
gen diff_len 100000 > $
gen random 39490 > $
gen random 40477 > $
gen random 41464 > $
gen random 42451 > $
gen random 43438 > $
gen random 44425 > $
gen random 45412 > $
gen random 46399 > $
gen random 47386 > $
gen random 48373 > $
gen random 49360 > $
gen random 50347 > $
gen random 51334 > $
gen random 52321 > $
gen random 53308 > $
gen random 54295 > $
gen random 55282 > $
gen random 56269 > $
gen random 57256 > $
gen random 58243 > $
gen random 59230 > $
gen random 60217 > $
gen random 61204 > $
gen random 62191 > $
gen random 63178 > $
gen random 64165 > $
gen random 65152 > $
gen random 66139 > $
gen random 67126 > $
gen random 68113 > $
gen random 69100 > $
gen random 70087 > $
gen random 71074 > $
gen random 72061 > $
gen random 73048 > $
gen random 74035 > $
gen random 75022 > $
gen random 76009 > $
gen random 76996 > $
gen random 77983 > $
gen random 78970 > $
gen random 79957 > $
gen random 80944 > $
gen random 81931 > $
gen random 82918 > $
gen random 83905 > $
gen random 84892 > $
gen random 85879 > $
gen random 86866 > $
gen random 87853 > $
gen random 88840 > $
gen random 89827 > $
gen random 90814 > $
gen random 91801 > $
gen random 92788 > $
gen random 93775 > $
gen random 94762 > $
gen random 95749 > $
gen random 96736 > $
gen random 97723 > $
gen random 98710 > $
gen random 99697 > $
gen random 694 > $
gen random 1681 > $
gen random 2668 > $',
    '[{"input": "Roma\\nAmor\\n", "output": "SIM\\n", "is_sample": true}, {"input": "Ada\\nTuring\\n", "output": "NAO\\n", "is_sample": true}, {"input": "AABB\\nABBA\\n", "output": "SIM\\n", "is_sample": true}]',
    '#include <iostream>
#include <string>
#include <vector>
#include <cctype>

using namespace std;

int main() {
    ios_base::sync_with_stdio(false);
    cin.tie(NULL);

    string s, t;
    if (!(cin >> s >> t)) return 0;

    if (s.length() != t.length()) {
        cout << "NAO\\n";
        return 0;
    }

    int freq[26] = {0};
    for (char c : s) {
        freq[tolower(static_cast<unsigned char>(c)) - \'a\']++;
    }
    for (char c : t) {
        freq[tolower(static_cast<unsigned char>(c)) - \'a\']--;
    }

    bool anagram = true;
    for (int i = 0; i < 26; i++) {
        if (freq[i] != 0) {
            anagram = false;
            break;
        }
    }

    if (anagram) {
        cout << "SIM\\n";
    } else {
        cout << "NAO\\n";
    }

    return 0;
}
',
    NULL,
    NULL,
    NULL,
    'draft',
    NULL,
    NULL,
    NULL,
    @prova_owner_id,
    NOW(),
    NOW()
) ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    time_limit = VALUES(time_limit),
    memory_limit = VALUES(memory_limit),
    description = VALUES(description),
    input_description = VALUES(input_description),
    output_description = VALUES(output_description),
    notes = VALUES(notes),
    color_name = VALUES(color_name),
    color_hex = VALUES(color_hex),
    generator_source = VALUES(generator_source),
    generator_script = VALUES(generator_script),
    manual_tests = VALUES(manual_tests),
    solution_accept = VALUES(solution_accept),
    updated_at = NOW();

INSERT INTO polygon_exercises (
    contest_id, name, short_name, basename, time_limit, memory_limit, output_limit,
    description, input_description, output_description, notes,
    color_name, color_hex, generator_source, generator_script,
    manual_tests, solution_accept, solution_tle, solution_wrong,
    additional_solutions, status, calibration_results,
    published_problem_id, published_bank_code, created_by, created_at, updated_at
) VALUES (
    NULL,
    'Quadrado mágico de Ouro Preto',
    'T04',
    't04_quadrado_m_gico_de_ouro_preto',
    1,
    256,
    1024,
    'Claude Shannon, pai da teoria da informação e célebre por andar de monociclo fazendo malabarismos pelos laboratórios da Bell Labs, passeava pelas feirinhas da Praça Tiradentes, em Ouro Preto, quando encontrou belos tabuleiros de pedra-sabão rotulados como "quadrados mágicos".

Apaixonado por enigmas matemáticos, Shannon decidiu validar se as peças eram autênticas: para ser um quadrado mágico legítimo de ordem **N**, a grade precisa conter todos os inteiros de 1 a $N^2$ exatamente uma vez, e todas as linhas, todas as colunas e as duas diagonais principais devem ter exatamente a mesma soma.

Dado um quadrado com **N** linhas e **N** colunas, ajude Shannon a dizer se ele é um quadrado mágico.',
    'A primeira linha contém um inteiro **N**, o tamanho do quadrado. Em seguida, **N** linhas contêm **N** inteiros cada, os valores **A_ij**, que são o número na linha **i** e coluna **j**.',
    'Imprima `SIM` se o quadrado for mágico ou `NAO` caso contrário.',
    '- `1 ≤ N ≤ 200`
- `1 ≤ A_ij ≤ N²`',
    'Dourado',
    '#EAB308',
    '#include "testlib.h"
#include <iostream>
#include <vector>
#include <numeric>

using namespace std;

int main(int argc, char* argv[]) {
    registerGen(argc, argv, 1);
    string type = opt<string>(1);
    int n = opt<int>(2, 3);

    vector<vector<long long>> a(n, vector<long long>(n, 0));

    if (type == "magic_odd" && n % 2 == 1) {
        // Siamese method for odd magic square
        int num = 1;
        int i = 0, j = n / 2;
        while (num <= n * n) {
            a[i][j] = num++;
            int ni = (i - 1 + n) % n;
            int nj = (j + 1) % n;
            if (a[ni][nj] != 0) {
                i = (i + 1) % n;
            } else {
                i = ni;
                j = nj;
            }
        }
    } else if (type == "permutation") {
        vector<int> p(n * n);
        iota(p.begin(), p.end(), 1);
        shuffle(p.begin(), p.end());
        int idx = 0;
        for (int i = 0; i < n; ++i) {
            for (int j = 0; j < n; ++j) a[i][j] = p[idx++];
        }
    } else {
        for (int i = 0; i < n; ++i) {
            for (int j = 0; j < n; ++j) a[i][j] = rnd.next(1, n * n);
        }
    }

    cout << n << "\\n";
    for (int i = 0; i < n; ++i) {
        for (int j = 0; j < n; ++j) cout << a[i][j] << (j + 1 == n ? "" : " ");
        cout << "\\n";
    }
    return 0;
}
',
    'gen magic_odd 1 > $
gen magic_odd 3 > $
gen magic_odd 5 > $
gen magic_odd 7 > $
gen magic_odd 9 > $
gen magic_odd 15 > $
gen magic_odd 25 > $
gen magic_odd 51 > $
gen magic_odd 101 > $
gen magic_odd 199 > $
gen permutation 200 > $
gen random 200 > $
gen magic_odd 1 > $
gen permutation 1 > $
gen random 1 > $
gen permutation 2 > $
gen random 2 > $
gen magic_odd 3 > $
gen permutation 3 > $
gen random 3 > $
gen permutation 4 > $
gen random 4 > $
gen magic_odd 5 > $
gen permutation 5 > $
gen random 5 > $
gen permutation 6 > $
gen random 6 > $
gen magic_odd 7 > $
gen permutation 7 > $
gen random 7 > $
gen permutation 8 > $
gen random 8 > $
gen magic_odd 9 > $
gen permutation 9 > $
gen random 9 > $
gen permutation 10 > $
gen random 10 > $
gen magic_odd 11 > $
gen permutation 11 > $
gen random 11 > $
gen permutation 12 > $
gen random 12 > $
gen magic_odd 13 > $
gen permutation 13 > $
gen random 13 > $
gen permutation 14 > $
gen random 14 > $
gen magic_odd 15 > $
gen permutation 15 > $
gen random 15 > $
gen permutation 16 > $
gen random 16 > $
gen magic_odd 17 > $
gen permutation 17 > $
gen random 17 > $
gen permutation 18 > $
gen random 18 > $
gen magic_odd 19 > $
gen permutation 19 > $
gen random 19 > $
gen permutation 20 > $
gen random 20 > $
gen magic_odd 21 > $
gen permutation 21 > $
gen random 21 > $
gen permutation 22 > $
gen random 22 > $
gen magic_odd 23 > $
gen permutation 23 > $
gen random 23 > $
gen permutation 24 > $
gen random 24 > $
gen magic_odd 25 > $
gen permutation 25 > $
gen random 25 > $
gen permutation 26 > $
gen random 26 > $
gen magic_odd 27 > $
gen permutation 27 > $
gen random 27 > $
gen permutation 28 > $
gen random 28 > $
gen magic_odd 29 > $
gen permutation 29 > $
gen random 29 > $
gen permutation 30 > $
gen random 30 > $
gen magic_odd 31 > $
gen permutation 31 > $
gen random 31 > $
gen permutation 32 > $
gen random 32 > $
gen magic_odd 33 > $
gen permutation 33 > $
gen random 33 > $
gen permutation 34 > $
gen random 34 > $
gen permutation 80 > $
gen permutation 87 > $
gen permutation 94 > $
gen permutation 101 > $
gen permutation 108 > $
gen permutation 115 > $
gen permutation 122 > $
gen permutation 129 > $',
    '[{"input": "3\\n8 1 6\\n3 5 7\\n4 9 2\\n", "output": "SIM\\n", "is_sample": true}, {"input": "2\\n1 2\\n3 4\\n", "output": "NAO\\n", "is_sample": true}, {"input": "1\\n1\\n", "output": "SIM\\n", "is_sample": true}]',
    '#include <iostream>
#include <vector>

using namespace std;

int main() {
    ios_base::sync_with_stdio(false);
    cin.tie(NULL);

    int n;
    if (!(cin >> n)) return 0;

    int total_elements = n * n;
    vector<bool> seen(total_elements + 1, false);
    vector<vector<long long>> a(n, vector<long long>(n));

    bool valid_permutation = true;

    for (int i = 0; i < n; i++) {
        for (int j = 0; j < n; j++) {
            cin >> a[i][j];
            if (a[i][j] < 1 || a[i][j] > total_elements || seen[a[i][j]]) {
                valid_permutation = false;
            } else {
                seen[a[i][j]] = true;
            }
        }
    }

    if (!valid_permutation) {
        cout << "NAO\\n";
        return 0;
    }

    long long target_sum = 0;
    for (int j = 0; j < n; j++) {
        target_sum += a[0][j];
    }

    // Check row sums
    for (int i = 1; i < n; i++) {
        long long row_sum = 0;
        for (int j = 0; j < n; j++) {
            row_sum += a[i][j];
        }
        if (row_sum != target_sum) {
            cout << "NAO\\n";
            return 0;
        }
    }

    // Check col sums
    for (int j = 0; j < n; j++) {
        long long col_sum = 0;
        for (int i = 0; i < n; i++) {
            col_sum += a[i][j];
        }
        if (col_sum != target_sum) {
            cout << "NAO\\n";
            return 0;
        }
    }

    // Check main diagonal
    long long diag1_sum = 0;
    for (int i = 0; i < n; i++) {
        diag1_sum += a[i][i];
    }
    if (diag1_sum != target_sum) {
        cout << "NAO\\n";
        return 0;
    }

    // Check secondary diagonal
    long long diag2_sum = 0;
    for (int i = 0; i < n; i++) {
        diag2_sum += a[i][n - 1 - i];
    }
    if (diag2_sum != target_sum) {
        cout << "NAO\\n";
        return 0;
    }

    cout << "SIM\\n";
    return 0;
}
',
    NULL,
    NULL,
    NULL,
    'draft',
    NULL,
    NULL,
    NULL,
    @prova_owner_id,
    NOW(),
    NOW()
) ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    time_limit = VALUES(time_limit),
    memory_limit = VALUES(memory_limit),
    description = VALUES(description),
    input_description = VALUES(input_description),
    output_description = VALUES(output_description),
    notes = VALUES(notes),
    color_name = VALUES(color_name),
    color_hex = VALUES(color_hex),
    generator_source = VALUES(generator_source),
    generator_script = VALUES(generator_script),
    manual_tests = VALUES(manual_tests),
    solution_accept = VALUES(solution_accept),
    updated_at = NOW();

INSERT INTO polygon_exercises (
    contest_id, name, short_name, basename, time_limit, memory_limit, output_limit,
    description, input_description, output_description, notes,
    color_name, color_hex, generator_source, generator_script,
    manual_tests, solution_accept, solution_tle, solution_wrong,
    additional_solutions, status, calibration_results,
    published_problem_id, published_bank_code, created_by, created_at, updated_at
) VALUES (
    NULL,
    'Encontrando a posição',
    'G01',
    'g01_encontrando_a_posi_o',
    1,
    256,
    1024,
    'Donald Knuth, autor da monumental obra *The Art of Computer Programming* e famoso por seu extremo perfeccionismo — emitindo cheques reais de US$ 2,56 a quem encontrar qualquer falha em seus livros —, visitou a biblioteca do IFMG em Ouro Branco e ficou satisfeito ao encontrar o acervo rigorosamente ordenado por código.

Para avaliar a indexação da biblioteca, Knuth realiza consultas para diversos códigos de livros **X**. Para cada código, ele quer saber em qual posição da estante (começando em 1) encontra-se o primeiro exemplar. Se o código não estiver presente na estante, Knuth deseja saber em que posição ele deveria ser inserido para manter a ordenação crescente, sempre na primeira posição válida possível.

Dada a estante e as perguntas de Knuth, ajude-o a responder cada uma delas.',
    'A primeira linha contém dois inteiros **N** e **Q**, que são, respectivamente, o número de livros e o número de perguntas. A segunda linha contém **N** inteiros **A_1, A_2, ..., A_N**, que são os códigos dos livros em ordem não decrescente. Cada uma das próximas **Q** linhas contém um inteiro **X**, o código consultado.',
    'Para cada pergunta, imprima em uma linha a primeira posição (entre 1 e `N+1`) em que **X** pode ser colocado sem quebrar a ordem. Se **X** já existe, essa é a posição do primeiro exemplar.',
    '- `1 ≤ N, Q ≤ 2·10^5`
- `-10^9 ≤ A_i, X ≤ 10^9`',
    'Carmesim',
    '#DC2626',
    '#include "testlib.h"
#include <iostream>
#include <vector>
#include <algorithm>

using namespace std;

int main(int argc, char* argv[]) {
    registerGen(argc, argv, 1);
    string type = opt<string>(1);
    int n = opt<int>(2, 10);
    int q = opt<int>(3, 10);

    vector<long long> a(n);
    for (int i = 0; i < n; ++i) a[i] = rnd.next(-1000000000LL, 1000000000LL);
    sort(a.begin(), a.end());

    cout << n << " " << q << "\\n";
    for (int i = 0; i < n; ++i) cout << a[i] << (i + 1 == n ? "" : " ");
    cout << "\\n";

    for (int i = 0; i < q; ++i) {
        long long x = 0;
        if (type == "existing") {
            x = a[rnd.next(0, n - 1)];
        } else if (type == "outside_min") {
            x = -1000000000LL;
        } else if (type == "outside_max") {
            x = 1000000000LL;
        } else {
            x = rnd.next(-1000000000LL, 1000000000LL);
        }
        cout << x << "\\n";
    }
    return 0;
}
',
    'gen existing 1 1 > $
gen outside_min 1 1 > $
gen outside_max 1 1 > $
gen existing 200000 200000 > $
gen outside_min 200000 200000 > $
gen outside_max 200000 200000 > $
gen random 200000 200000 > $
gen existing 2 2 > $
gen random 2 2 > $
gen existing 5 5 > $
gen random 5 5 > $
gen existing 10 10 > $
gen random 10 10 > $
gen existing 50 50 > $
gen random 50 50 > $
gen existing 100 100 > $
gen random 100 100 > $
gen existing 500 500 > $
gen random 500 500 > $
gen existing 1000 500 > $
gen random 1000 500 > $
gen existing 5000 500 > $
gen random 5000 500 > $
gen existing 20000 500 > $
gen random 20000 500 > $
gen existing 50000 500 > $
gen random 50000 500 > $
gen existing 100000 500 > $
gen random 100000 500 > $
gen existing 200000 500 > $
gen random 200000 500 > $
gen random 61979 100 > $
gen random 63978 100 > $
gen random 65977 100 > $
gen random 67976 100 > $
gen random 69975 100 > $
gen random 71974 100 > $
gen random 73973 100 > $
gen random 75972 100 > $
gen random 77971 100 > $
gen random 79970 100 > $
gen random 81969 100 > $
gen random 83968 100 > $
gen random 85967 100 > $
gen random 87966 100 > $
gen random 89965 100 > $
gen random 91964 100 > $
gen random 93963 100 > $
gen random 95962 100 > $
gen random 97961 100 > $
gen random 99960 100 > $
gen random 101959 100 > $
gen random 103958 100 > $
gen random 105957 100 > $
gen random 107956 100 > $
gen random 109955 100 > $
gen random 111954 100 > $
gen random 113953 100 > $
gen random 115952 100 > $
gen random 117951 100 > $
gen random 119950 100 > $
gen random 121949 100 > $
gen random 123948 100 > $
gen random 125947 100 > $
gen random 127946 100 > $
gen random 129945 100 > $
gen random 131944 100 > $
gen random 133943 100 > $
gen random 135942 100 > $
gen random 137941 100 > $
gen random 139940 100 > $
gen random 141939 100 > $
gen random 143938 100 > $
gen random 145937 100 > $
gen random 147936 100 > $
gen random 149935 100 > $
gen random 151934 100 > $
gen random 153933 100 > $
gen random 155932 100 > $
gen random 157931 100 > $
gen random 159930 100 > $
gen random 161929 100 > $
gen random 163928 100 > $
gen random 165927 100 > $
gen random 167926 100 > $
gen random 169925 100 > $
gen random 171924 100 > $
gen random 173923 100 > $
gen random 175922 100 > $
gen random 177921 100 > $
gen random 179920 100 > $
gen random 181919 100 > $
gen random 183918 100 > $
gen random 185917 100 > $
gen random 187916 100 > $
gen random 189915 100 > $
gen random 191914 100 > $
gen random 193913 100 > $
gen random 195912 100 > $
gen random 197911 100 > $
gen random 199910 100 > $
gen random 1919 100 > $
gen random 3918 100 > $
gen random 5917 100 > $
gen random 7916 100 > $',
    '[{"input": "5 3\\n1 3 3 7 10\\n3\\n4\\n11\\n", "output": "2\\n4\\n6\\n", "is_sample": true}, {"input": "4 2\\n-5 -2 0 8\\n-10\\n0\\n", "output": "1\\n3\\n", "is_sample": true}, {"input": "1 2\\n5\\n5\\n6\\n", "output": "1\\n2\\n", "is_sample": true}]',
    '#include <iostream>
#include <vector>
#include <algorithm>

using namespace std;

int main() {
    ios_base::sync_with_stdio(false);
    cin.tie(NULL);

    int n, q;
    if (!(cin >> n >> q)) return 0;

    vector<long long> a(n);
    for (int i = 0; i < n; ++i) {
        cin >> a[i];
    }

    while (q--) {
        long long x;
        cin >> x;
        auto it = lower_bound(a.begin(), a.end(), x);
        int pos = (int)(it - a.begin()) + 1;
        cout << pos << "\\n";
    }

    return 0;
}
',
    NULL,
    NULL,
    NULL,
    'draft',
    NULL,
    NULL,
    NULL,
    @prova_owner_id,
    NOW(),
    NOW()
) ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    time_limit = VALUES(time_limit),
    memory_limit = VALUES(memory_limit),
    description = VALUES(description),
    input_description = VALUES(input_description),
    output_description = VALUES(output_description),
    notes = VALUES(notes),
    color_name = VALUES(color_name),
    color_hex = VALUES(color_hex),
    generator_source = VALUES(generator_source),
    generator_script = VALUES(generator_script),
    manual_tests = VALUES(manual_tests),
    solution_accept = VALUES(solution_accept),
    updated_at = NOW();

INSERT INTO polygon_exercises (
    contest_id, name, short_name, basename, time_limit, memory_limit, output_limit,
    description, input_description, output_description, notes,
    color_name, color_hex, generator_source, generator_script,
    manual_tests, solution_accept, solution_tle, solution_wrong,
    additional_solutions, status, calibration_results,
    published_problem_id, published_bank_code, created_by, created_at, updated_at
) VALUES (
    NULL,
    'Trecho mais variado',
    'G02',
    'g02_trecho_mais_variado',
    1,
    256,
    1024,
    'Na histórica estação ferroviária de Conselheiro Lafaiete, um painel eletrônico exibe uma longa sequência contínua de letras para os passageiros. Claude Shannon, criador da teoria matemática da comunicação, observou o letreiro e resolveu analisar a variedade das sequências exibidas.

Shannon quer identificar o maior trecho contínuo do painel que possua variedade controlada de caracteres: ele busca o trecho consecutivo mais longo que contenha no máximo **K** letras distintas no total, sendo permitido que as letras se repitam qualquer número de vezes dentro desse intervalo.

Dado o painel, ajude Shannon a descobrir o comprimento do maior trecho consecutivo com no máximo **K** letras distintas.',
    'A primeira linha contém dois inteiros **N** e **K**, que são, respectivamente, o comprimento da sequência e o número máximo de letras distintas permitido. A segunda linha contém a sequência **S**, de tamanho **N**, formada por letras minúsculas.',
    'Um inteiro: o comprimento do maior trecho consecutivo de **S** com no máximo **K** letras distintas.',
    '- `1 ≤ N ≤ 10^6`
- `1 ≤ K ≤ 26`
- **S** contém apenas letras minúsculas.',
    'Cobalto',
    '#2563EB',
    '#include "testlib.h"
#include <iostream>
#include <string>

using namespace std;

int main(int argc, char* argv[]) {
    registerGen(argc, argv, 1);
    string type = opt<string>(1);
    int n = opt<int>(2, 10);
    int k = opt<int>(3, 2);

    string s = "";
    if (type == "all_same") {
        s = string(n, \'a\');
    } else if (type == "cyclic") {
        for (int i = 0; i < n; ++i) s += (char)(\'a\' + (i % 26));
    } else {
        int alphabet_size = min(26, max(1, k + 2));
        for (int i = 0; i < n; ++i) s += (char)(\'a\' + rnd.next(0, alphabet_size - 1));
    }

    cout << n << " " << k << "\\n";
    cout << s << "\\n";
    return 0;
}
',
    'gen all_same 1 1 > $
gen all_same 1 26 > $
gen cyclic 26 26 > $
gen all_same 1000000 1 > $
gen cyclic 1000000 26 > $
gen random 1000000 5 > $
gen random 1000000 26 > $
gen random 10 1 > $
gen random 100 1 > $
gen random 1000 1 > $
gen random 10000 1 > $
gen random 100000 1 > $
gen random 1000000 1 > $
gen random 10 2 > $
gen random 100 2 > $
gen random 1000 2 > $
gen random 10000 2 > $
gen random 100000 2 > $
gen random 1000000 2 > $
gen random 10 3 > $
gen random 100 3 > $
gen random 1000 3 > $
gen random 10000 3 > $
gen random 100000 3 > $
gen random 1000000 3 > $
gen random 10 5 > $
gen random 100 5 > $
gen random 1000 5 > $
gen random 10000 5 > $
gen random 100000 5 > $
gen random 1000000 5 > $
gen random 10 10 > $
gen random 100 10 > $
gen random 1000 10 > $
gen random 10000 10 > $
gen random 100000 10 > $
gen random 1000000 10 > $
gen random 10 15 > $
gen random 100 15 > $
gen random 1000 15 > $
gen random 10000 15 > $
gen random 100000 15 > $
gen random 1000000 15 > $
gen random 10 20 > $
gen random 100 20 > $
gen random 1000 20 > $
gen random 10000 20 > $
gen random 100000 20 > $
gen random 1000000 20 > $
gen random 10 26 > $
gen random 100 26 > $
gen random 1000 26 > $
gen random 10000 26 > $
gen random 100000 26 > $
gen random 1000000 26 > $
gen random 548525 4 > $
gen random 558498 5 > $
gen random 568471 6 > $
gen random 578444 7 > $
gen random 588417 8 > $
gen random 598390 9 > $
gen random 608363 10 > $
gen random 618336 11 > $
gen random 628309 12 > $
gen random 638282 13 > $
gen random 648255 14 > $
gen random 658228 15 > $
gen random 668201 16 > $
gen random 678174 17 > $
gen random 688147 18 > $
gen random 698120 19 > $
gen random 708093 20 > $
gen random 718066 21 > $
gen random 728039 22 > $
gen random 738012 23 > $
gen random 747985 24 > $
gen random 757958 25 > $
gen random 767931 26 > $
gen random 777904 1 > $
gen random 787877 2 > $
gen random 797850 3 > $
gen random 807823 4 > $
gen random 817796 5 > $
gen random 827769 6 > $
gen random 837742 7 > $
gen random 847715 8 > $
gen random 857688 9 > $
gen random 867661 10 > $
gen random 877634 11 > $
gen random 887607 12 > $
gen random 897580 13 > $
gen random 907553 14 > $
gen random 917526 15 > $
gen random 927499 16 > $
gen random 937472 17 > $
gen random 947445 18 > $
gen random 957418 19 > $
gen random 967391 20 > $
gen random 977364 21 > $
gen random 987337 22 > $
gen random 997310 23 > $
gen random 7293 24 > $
gen random 17266 25 > $
gen random 27239 26 > $
gen random 37212 1 > $',
    '[{"input": "8 2\\nabacccde\\n", "output": "4\\n", "is_sample": true}, {"input": "5 1\\naaaaa\\n", "output": "5\\n", "is_sample": true}, {"input": "6 3\\nabcabc\\n", "output": "6\\n", "is_sample": true}]',
    '#include <iostream>
#include <string>
#include <vector>
#include <algorithm>

using namespace std;

int main() {
    ios_base::sync_with_stdio(false);
    cin.tie(NULL);

    int n, k;
    if (!(cin >> n >> k)) return 0;

    string s;
    cin >> s;

    vector<int> freq(26, 0);
    int distinct_count = 0;
    int max_len = 0;
    int l = 0;

    for (int r = 0; r < n; ++r) {
        int char_idx = s[r] - \'a\';
        if (freq[char_idx] == 0) {
            distinct_count++;
        }
        freq[char_idx]++;

        while (distinct_count > k) {
            int left_char_idx = s[l] - \'a\';
            freq[left_char_idx]--;
            if (freq[left_char_idx] == 0) {
                distinct_count--;
            }
            l++;
        }

        max_len = max(max_len, r - l + 1);
    }

    cout << max_len << "\\n";

    return 0;
}
',
    NULL,
    NULL,
    NULL,
    'draft',
    NULL,
    NULL,
    NULL,
    @prova_owner_id,
    NOW(),
    NOW()
) ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    time_limit = VALUES(time_limit),
    memory_limit = VALUES(memory_limit),
    description = VALUES(description),
    input_description = VALUES(input_description),
    output_description = VALUES(output_description),
    notes = VALUES(notes),
    color_name = VALUES(color_name),
    color_hex = VALUES(color_hex),
    generator_source = VALUES(generator_source),
    generator_script = VALUES(generator_script),
    manual_tests = VALUES(manual_tests),
    solution_accept = VALUES(solution_accept),
    updated_at = NOW();

INSERT INTO polygon_exercises (
    contest_id, name, short_name, basename, time_limit, memory_limit, output_limit,
    description, input_description, output_description, notes,
    color_name, color_hex, generator_source, generator_script,
    manual_tests, solution_accept, solution_tle, solution_wrong,
    additional_solutions, status, calibration_results,
    published_problem_id, published_bank_code, created_by, created_at, updated_at
) VALUES (
    NULL,
    'Rede dos campi',
    'G03',
    'g03_rede_dos_campi',
    1,
    256,
    1024,
    'Tim Berners-Lee, pioneiro que concebeu a World Wide Web em 1989 para conectar pesquisadores ao redor do mundo, foi convidado para planejar a infraestrutura de dados da Maratona Turing, interligando a sede em Ouro Branco aos demais campi do IFMG.

Para garantir que a transmissão do placar e o envio de balões alcancem todas as equipes nas montanhas mineiras, Tim analisa a malha de canais de comunicação existentes, onde cada canal liga diretamente dois campi e opera nos dois sentidos. Ele precisa verificar se a rede formada é conexa — isto é, se é possível enviar uma mensagem a partir de qualquer campus e fazê-la chegar a qualquer outro (diretamente ou através de campi intermediários).

Dados os campi e os canais existentes, ajude Tim a verificar se é possível sair de qualquer campus e chegar a qualquer outro.',
    'A primeira linha contém dois inteiros **N** e **M**, que são, respectivamente, o número de campi (numerados de 1 a **N**) e o número de canais. Cada uma das próximas **M** linhas contém dois inteiros **U** e **V**, indicando um canal entre os campi **U** e **V**.',
    'Imprima `SIM` se todos os campi estiverem conectados entre si, ou `NAO` caso contrário.',
    '- `1 ≤ N ≤ 2·10^5`
- `0 ≤ M ≤ 3·10^5`
- `1 ≤ U, V ≤ N`
- `U ≠ V`; canais repetidos podem aparecer.',
    'Grafite',
    '#4B5563',
    '#include "testlib.h"
#include <iostream>
#include <vector>

using namespace std;

int main(int argc, char* argv[]) {
    registerGen(argc, argv, 1);
    string type = opt<string>(1);
    int n = opt<int>(2, 5);
    int m = opt<int>(3, 4);

    vector<pair<int, int>> edges;
    if (type == "empty") {
        m = 0;
    } else if (type == "star") {
        m = n - 1;
        for (int i = 2; i <= n; ++i) edges.push_back({1, i});
    } else if (type == "line") {
        m = n - 1;
        for (int i = 1; i < n; ++i) edges.push_back({i, i + 1});
    } else {
        for (int i = 0; i < m; ++i) {
            int u = rnd.next(1, n);
            int v = rnd.next(1, n);
            while (u == v && n > 1) v = rnd.next(1, n);
            edges.push_back({u, v});
        }
    }

    cout << n << " " << m << "\\n";
    for (auto& e : edges) {
        cout << e.first << " " << e.second << "\\n";
    }
    return 0;
}
',
    'gen empty 1 0 > $
gen empty 200000 0 > $
gen star 200000 199999 > $
gen line 200000 199999 > $
gen random 200000 300000 > $
gen star 2 4 > $
gen line 2 4 > $
gen random 2 4 > $
gen star 5 10 > $
gen line 5 10 > $
gen random 5 10 > $
gen star 10 20 > $
gen line 10 20 > $
gen random 10 20 > $
gen star 50 100 > $
gen line 50 100 > $
gen random 50 100 > $
gen star 100 200 > $
gen line 100 200 > $
gen random 100 200 > $
gen star 500 1000 > $
gen line 500 1000 > $
gen random 500 1000 > $
gen star 1000 2000 > $
gen line 1000 2000 > $
gen random 1000 2000 > $
gen star 5000 10000 > $
gen line 5000 10000 > $
gen random 5000 10000 > $
gen star 20000 40000 > $
gen line 20000 40000 > $
gen random 20000 40000 > $
gen star 50000 100000 > $
gen line 50000 100000 > $
gen random 50000 100000 > $
gen star 100000 200000 > $
gen line 100000 200000 > $
gen random 100000 200000 > $
gen star 200000 300000 > $
gen line 200000 300000 > $
gen random 200000 300000 > $
gen random 81969 81969 > $
gen random 83968 83968 > $
gen random 85967 85967 > $
gen random 87966 87966 > $
gen random 89965 89965 > $
gen random 91964 91964 > $
gen random 93963 93963 > $
gen random 95962 95962 > $
gen random 97961 97961 > $
gen random 99960 99960 > $
gen random 101959 101959 > $
gen random 103958 103958 > $
gen random 105957 105957 > $
gen random 107956 107956 > $
gen random 109955 109955 > $
gen random 111954 111954 > $
gen random 113953 113953 > $
gen random 115952 115952 > $
gen random 117951 117951 > $
gen random 119950 119950 > $
gen random 121949 121949 > $
gen random 123948 123948 > $
gen random 125947 125947 > $
gen random 127946 127946 > $
gen random 129945 129945 > $
gen random 131944 131944 > $
gen random 133943 133943 > $
gen random 135942 135942 > $
gen random 137941 137941 > $
gen random 139940 139940 > $
gen random 141939 141939 > $
gen random 143938 143938 > $
gen random 145937 145937 > $
gen random 147936 147936 > $
gen random 149935 149935 > $
gen random 151934 151934 > $
gen random 153933 153933 > $
gen random 155932 155932 > $
gen random 157931 157931 > $
gen random 159930 159930 > $
gen random 161929 161929 > $
gen random 163928 163928 > $
gen random 165927 165927 > $
gen random 167926 167926 > $
gen random 169925 169925 > $
gen random 171924 171924 > $
gen random 173923 173923 > $
gen random 175922 175922 > $
gen random 177921 177921 > $
gen random 179920 179920 > $
gen random 181919 181919 > $
gen random 183918 183918 > $
gen random 185917 185917 > $
gen random 187916 187916 > $
gen random 189915 189915 > $
gen random 191914 191914 > $
gen random 193913 193913 > $
gen random 195912 195912 > $
gen random 197911 197911 > $
gen random 199910 199910 > $
gen random 1919 1919 > $
gen random 3918 3918 > $
gen random 5917 5917 > $
gen random 7916 7916 > $',
    '[{"input": "4 3\\n1 2\\n2 3\\n3 4\\n", "output": "SIM\\n", "is_sample": true}, {"input": "4 2\\n1 2\\n3 4\\n", "output": "NAO\\n", "is_sample": true}, {"input": "1 0\\n", "output": "SIM\\n", "is_sample": true}]',
    '#include <iostream>
#include <vector>
#include <numeric>

using namespace std;

struct DSU {
    vector<int> parent;
    int components;

    DSU(int n) : parent(n + 1), components(n) {
        iota(parent.begin(), parent.end(), 0);
    }

    int find(int i) {
        if (parent[i] == i)
            return i;
        return parent[i] = find(parent[i]);
    }

    void unite(int i, int j) {
        int root_i = find(i);
        int root_j = find(j);
        if (root_i != root_j) {
            parent[root_i] = root_j;
            components--;
        }
    }
};

int main() {
    ios_base::sync_with_stdio(false);
    cin.tie(NULL);

    int n, m;
    if (!(cin >> n >> m)) return 0;

    DSU dsu(n);

    for (int i = 0; i < m; ++i) {
        int u, v;
        cin >> u >> v;
        dsu.unite(u, v);
    }

    if (dsu.components == 1) {
        cout << "SIM\\n";
    } else {
        cout << "NAO\\n";
    }

    return 0;
}
',
    NULL,
    NULL,
    NULL,
    'draft',
    NULL,
    NULL,
    NULL,
    @prova_owner_id,
    NOW(),
    NOW()
) ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    time_limit = VALUES(time_limit),
    memory_limit = VALUES(memory_limit),
    description = VALUES(description),
    input_description = VALUES(input_description),
    output_description = VALUES(output_description),
    notes = VALUES(notes),
    color_name = VALUES(color_name),
    color_hex = VALUES(color_hex),
    generator_source = VALUES(generator_source),
    generator_script = VALUES(generator_script),
    manual_tests = VALUES(manual_tests),
    solution_accept = VALUES(solution_accept),
    updated_at = NOW();

INSERT INTO polygon_exercises (
    contest_id, name, short_name, basename, time_limit, memory_limit, output_limit,
    description, input_description, output_description, notes,
    color_name, color_hex, generator_source, generator_script,
    manual_tests, solution_accept, solution_tle, solution_wrong,
    additional_solutions, status, calibration_results,
    published_problem_id, published_bank_code, created_by, created_at, updated_at
) VALUES (
    NULL,
    'O canhão de Newton',
    'G04',
    'g04_o_canh_o_de_newton',
    1,
    256,
    1024,
    'No cume da Serra de Ouro Branco, diante de uma das vistas panorâmicas mais amplas de Minas Gerais, os estudantes de computação recordaram o famoso experimento mental de Sir Isaac Newton sobre disparar um projétil a partir do topo de uma montanha.

Para demonstrar o experimento aos competidores com segurança, a equipe técnica instalou um lançador mecânico a uma altura **H** do chão, disparando um projétil na horizontal com velocidade **V** sob aceleração da gravidade **G**, desprezando a resistência do ar. O tempo de queda $t$ até o solo satisfaz a equação $H = \\frac{G \\cdot t^2}{2}$, e a distância horizontal percorrida durante esse tempo é dada por $V \\cdot t$.

Dados a altura do canhão, a velocidade do disparo e a gravidade, ajude a equipe a calcular a distância horizontal percorrida até o projétil atingir o chão.',
    'Uma linha com três números reais **H**, **V** e **G**, que são, respectivamente, a altura do canhão, a velocidade horizontal do projétil e a aceleração da gravidade. Todas as unidades são consistentes entre si.',
    'Imprima a distância horizontal percorrida com exatamente seis casas decimais.',
    '- `0 < H ≤ 10^6`
- `0 < V ≤ 10^4`
- `0 < G ≤ 10^4`',
    'Bronze',
    '#B45309',
    '#include "testlib.h"
#include <iostream>
#include <iomanip>

using namespace std;

int main(int argc, char* argv[]) {
    registerGen(argc, argv, 1);
    string type = opt<string>(1);

    double h = 1.0, v = 1.0, g = 9.8;
    if (type == "min") {
        h = 0.01; v = 0.01; g = 0.01;
    } else if (type == "max") {
        h = 1000000.0; v = 10000.0; g = 10000.0;
    } else {
        h = rnd.next(0.01, 1000000.0);
        v = rnd.next(0.01, 10000.0);
        g = rnd.next(0.01, 10000.0);
    }

    cout << fixed << setprecision(6) << h << " " << v << " " << g << "\\n";
    return 0;
}
',
    'gen min 1 > $
gen max 1 > $
gen random 43 > $
gen random 86 > $
gen random 129 > $
gen random 172 > $
gen random 215 > $
gen random 258 > $
gen random 301 > $
gen random 344 > $
gen random 387 > $
gen random 430 > $
gen random 473 > $
gen random 516 > $
gen random 559 > $
gen random 602 > $
gen random 645 > $
gen random 688 > $
gen random 731 > $
gen random 774 > $
gen random 817 > $
gen random 860 > $
gen random 903 > $
gen random 946 > $
gen random 989 > $
gen random 1032 > $
gen random 1075 > $
gen random 1118 > $
gen random 1161 > $
gen random 1204 > $
gen random 1247 > $
gen random 1290 > $
gen random 1333 > $
gen random 1376 > $
gen random 1419 > $
gen random 1462 > $
gen random 1505 > $
gen random 1548 > $
gen random 1591 > $
gen random 1634 > $
gen random 1677 > $
gen random 1720 > $
gen random 1763 > $
gen random 1806 > $
gen random 1849 > $
gen random 1892 > $
gen random 1935 > $
gen random 1978 > $
gen random 2021 > $
gen random 2064 > $
gen random 2107 > $
gen random 2150 > $
gen random 2193 > $
gen random 2236 > $
gen random 2279 > $
gen random 2322 > $
gen random 2365 > $
gen random 2408 > $
gen random 2451 > $
gen random 2494 > $
gen random 2537 > $
gen random 2580 > $
gen random 2623 > $
gen random 2666 > $
gen random 2709 > $
gen random 2752 > $
gen random 2795 > $
gen random 2838 > $
gen random 2881 > $
gen random 2924 > $
gen random 2967 > $
gen random 3010 > $
gen random 3053 > $
gen random 3096 > $
gen random 3139 > $
gen random 3182 > $
gen random 3225 > $
gen random 3268 > $
gen random 3311 > $
gen random 3354 > $
gen random 3397 > $
gen random 3440 > $
gen random 3483 > $
gen random 3526 > $
gen random 3569 > $
gen random 3612 > $
gen random 3655 > $
gen random 3698 > $
gen random 3741 > $
gen random 3784 > $
gen random 3827 > $
gen random 3870 > $
gen random 3913 > $
gen random 3956 > $
gen random 3999 > $
gen random 4042 > $
gen random 4085 > $
gen random 4128 > $
gen random 4171 > $
gen random 4214 > $
gen random 4257 > $
gen random 4300 > $
gen random 4343 > $
gen random 4386 > $
gen random 4429 > $
gen random 4472 > $',
    '[{"input": "20 10 10\\n", "output": "20.000000\\n", "is_sample": true}, {"input": "45 4 10\\n", "output": "12.000000\\n", "is_sample": true}, {"input": "10 1 9.8\\n", "output": "1.428571\\n", "is_sample": true}]',
    '#include <iostream>
#include <iomanip>
#include <cmath>

using namespace std;

int main() {
    ios_base::sync_with_stdio(false);
    cin.tie(NULL);

    double h, v, g;
    if (cin >> h >> v >> g) {
        double t = sqrt(2.0 * h / g);
        double dist = v * t;
        cout << fixed << setprecision(6) << dist << "\\n";
    }

    return 0;
}
',
    NULL,
    NULL,
    NULL,
    'draft',
    NULL,
    NULL,
    NULL,
    @prova_owner_id,
    NOW(),
    NOW()
) ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    time_limit = VALUES(time_limit),
    memory_limit = VALUES(memory_limit),
    description = VALUES(description),
    input_description = VALUES(input_description),
    output_description = VALUES(output_description),
    notes = VALUES(notes),
    color_name = VALUES(color_name),
    color_hex = VALUES(color_hex),
    generator_source = VALUES(generator_source),
    generator_script = VALUES(generator_script),
    manual_tests = VALUES(manual_tests),
    solution_accept = VALUES(solution_accept),
    updated_at = NOW();

INSERT INTO polygon_exercises (
    contest_id, name, short_name, basename, time_limit, memory_limit, output_limit,
    description, input_description, output_description, notes,
    color_name, color_hex, generator_source, generator_script,
    manual_tests, solution_accept, solution_tle, solution_wrong,
    additional_solutions, status, calibration_results,
    published_problem_id, published_bank_code, created_by, created_at, updated_at
) VALUES (
    NULL,
    'O maior palíndromo do painel',
    'G05',
    'g05_o_maior_pal_ndromo_do_painel',
    1,
    256,
    1024,
    'Barbara Liskov, referência mundial em arquitetura e qualidade de software — com princípios de abstração tão influentes que moldaram o desenvolvimento moderno —, visitou a mostra tecnológica em Congonhas, perto do Santuário de Bom Jesus de Matosinhos.

Ao acompanhar um painel digital luminoso que projetava uma longa sequência de caracteres minúsculos, Barbara resolveu buscar padrões de simetria no fluxo de texto: trechos contíguos que permanecem idênticos quando lidos de frente para trás ou de trás para frente (palíndromos). Ela quer descobrir o maior palíndromo consecutivo e o seu comprimento. Se houver mais de um palíndromo empatado com o tamanho máximo, prevalece aquele que aparece primeiro na sequência.

Dada a sequência do painel, ajude Barbara a descobrir o maior palíndromo consecutivo e seu tamanho.',
    'Uma linha com a sequência **S**, formada por letras minúsculas, sem espaços.',
    'Na primeira linha, imprima o maior palíndromo consecutivo de **S** (em caso de empate, o que começa primeiro). Na segunda linha, imprima o tamanho dele.',
    '- `1 ≤ |S| ≤ 2·10^5`
- **S** contém apenas letras minúsculas.',
    'Safira',
    '#1D4ED8',
    '#include "testlib.h"
#include <iostream>
#include <string>
#include <algorithm>

using namespace std;

int main(int argc, char* argv[]) {
    registerGen(argc, argv, 1);
    string type = opt<string>(1);
    int len = opt<int>(2, 10);

    string s = "";
    if (type == "full_palindrome") {
        int half = len / 2;
        string h = "";
        for (int i = 0; i < half; ++i) h += (char)rnd.next(\'a\', \'z\');
        string rev = h;
        reverse(rev.begin(), rev.end());
        s = h + (len % 2 == 1 ? string(1, (char)rnd.next(\'a\', \'z\')) : "") + rev;
    } else if (type == "all_same") {
        s = string(len, (char)rnd.next(\'a\', \'z\'));
    } else if (type == "no_palindrome") {
        for (int i = 0; i < len; ++i) s += (char)(\'a\' + (i % 26));
    } else {
        for (int i = 0; i < len; ++i) s += (char)rnd.next(\'a\', \'z\');
    }

    cout << s << "\\n";
    return 0;
}
',
    'gen full_palindrome 1 > $
gen all_same 1 > $
gen no_palindrome 1 > $
gen full_palindrome 200000 > $
gen all_same 200000 > $
gen no_palindrome 200000 > $
gen random 200000 > $
gen full_palindrome 2 > $
gen all_same 2 > $
gen random 2 > $
gen full_palindrome 5 > $
gen all_same 5 > $
gen random 5 > $
gen full_palindrome 10 > $
gen all_same 10 > $
gen random 10 > $
gen full_palindrome 50 > $
gen all_same 50 > $
gen random 50 > $
gen full_palindrome 100 > $
gen all_same 100 > $
gen random 100 > $
gen full_palindrome 500 > $
gen all_same 500 > $
gen random 500 > $
gen full_palindrome 1000 > $
gen all_same 1000 > $
gen random 1000 > $
gen full_palindrome 5000 > $
gen all_same 5000 > $
gen random 5000 > $
gen full_palindrome 20000 > $
gen all_same 20000 > $
gen random 20000 > $
gen full_palindrome 50000 > $
gen all_same 50000 > $
gen random 50000 > $
gen full_palindrome 100000 > $
gen all_same 100000 > $
gen random 100000 > $
gen full_palindrome 200000 > $
gen all_same 200000 > $
gen random 200000 > $
gen random 85967 > $
gen random 87966 > $
gen random 89965 > $
gen random 91964 > $
gen random 93963 > $
gen random 95962 > $
gen random 97961 > $
gen random 99960 > $
gen random 101959 > $
gen random 103958 > $
gen random 105957 > $
gen random 107956 > $
gen random 109955 > $
gen random 111954 > $
gen random 113953 > $
gen random 115952 > $
gen random 117951 > $
gen random 119950 > $
gen random 121949 > $
gen random 123948 > $
gen random 125947 > $
gen random 127946 > $
gen random 129945 > $
gen random 131944 > $
gen random 133943 > $
gen random 135942 > $
gen random 137941 > $
gen random 139940 > $
gen random 141939 > $
gen random 143938 > $
gen random 145937 > $
gen random 147936 > $
gen random 149935 > $
gen random 151934 > $
gen random 153933 > $
gen random 155932 > $
gen random 157931 > $
gen random 159930 > $
gen random 161929 > $
gen random 163928 > $
gen random 165927 > $
gen random 167926 > $
gen random 169925 > $
gen random 171924 > $
gen random 173923 > $
gen random 175922 > $
gen random 177921 > $
gen random 179920 > $
gen random 181919 > $
gen random 183918 > $
gen random 185917 > $
gen random 187916 > $
gen random 189915 > $
gen random 191914 > $
gen random 193913 > $
gen random 195912 > $
gen random 197911 > $
gen random 199910 > $
gen random 1919 > $
gen random 3918 > $
gen random 5917 > $
gen random 7916 > $',
    '[{"input": "babad\\n", "output": "bab\\n3\\n", "is_sample": true}, {"input": "cbbd\\n", "output": "bb\\n2\\n", "is_sample": true}, {"input": "a\\n", "output": "a\\n1\\n", "is_sample": true}]',
    '#include <iostream>
#include <string>
#include <vector>
#include <algorithm>

using namespace std;

int main() {
    ios_base::sync_with_stdio(false);
    cin.tie(NULL);

    string s;
    if (!(cin >> s)) return 0;

    int n = s.size();
    if (n == 0) return 0;

    string t = "^";
    for (char c : s) {
        t += \'#\';
        t += c;
    }
    t += "#$";

    int m = t.size();
    vector<int> p(m, 0);
    int c = 0, r = 0;

    int max_len = 0;
    int best_start = 0;

    for (int i = 1; i < m - 1; ++i) {
        int i_mirror = 2 * c - i;
        if (r > i) {
            p[i] = min(r - i, p[i_mirror]);
        } else {
            p[i] = 0;
        }

        while (t[i + 1 + p[i]] == t[i - 1 - p[i]]) {
            p[i]++;
        }

        if (i + p[i] > r) {
            c = i;
            r = i + p[i];
        }

        int start_pos = (i - p[i]) / 2;
        if (p[i] > max_len) {
            max_len = p[i];
            best_start = start_pos;
        } else if (p[i] == max_len) {
            if (start_pos < best_start) {
                best_start = start_pos;
            }
        }
    }

    cout << s.substr(best_start, max_len) << "\\n";
    cout << max_len << "\\n";

    return 0;
}
',
    NULL,
    NULL,
    NULL,
    'draft',
    NULL,
    NULL,
    NULL,
    @prova_owner_id,
    NOW(),
    NOW()
) ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    time_limit = VALUES(time_limit),
    memory_limit = VALUES(memory_limit),
    description = VALUES(description),
    input_description = VALUES(input_description),
    output_description = VALUES(output_description),
    notes = VALUES(notes),
    color_name = VALUES(color_name),
    color_hex = VALUES(color_hex),
    generator_source = VALUES(generator_source),
    generator_script = VALUES(generator_script),
    manual_tests = VALUES(manual_tests),
    solution_accept = VALUES(solution_accept),
    updated_at = NOW();

INSERT INTO polygon_exercises (
    contest_id, name, short_name, basename, time_limit, memory_limit, output_limit,
    description, input_description, output_description, notes,
    color_name, color_hex, generator_source, generator_script,
    manual_tests, solution_accept, solution_tle, solution_wrong,
    additional_solutions, status, calibration_results,
    published_problem_id, published_bank_code, created_by, created_at, updated_at
) VALUES (
    NULL,
    'Caminho de maior soma',
    'G06',
    'g06_caminho_de_maior_soma',
    2,
    256,
    1024,
    'Edsger Dijkstra, lendário pioneiro dos algoritmos de menor caminho e célebre por sua aversão a atalhos improvisados (declarando guerra aberta ao comando `goto` em nome da disciplina na programação), visitou o trecho da Estrada Real entre Ouro Preto e Congonhas e resolveu disputar um jogo de trilha virtual criado para a Maratona.

O desafio acontece sobre um tabuleiro quadriculado de $N$ linhas e $M$ colunas, onde cada célula possui uma pontuação inteira (positiva ou negativa). Partindo da célula superior esquerda $(1, 1)$ com destino à célula inferior direita $(N, M)$, Dijkstra impôs a regra de caminhar a cada passo unicamente para a célula da direita ou para a célula de baixo. A pontuação final da caminhada é a soma de todas as células visitadas ao longo do trajeto, incluindo a inicial e a final.

Dada a grade, ajude Dijkstra descobrindo a maior pontuação possível ao final do caminho.',
    'A primeira linha contém dois inteiros **N** e **M**, que são, respectivamente, o número de linhas e o de colunas da grade. Em seguida, **N** linhas contêm **M** inteiros cada, os valores **A_ij**, que são a pontuação da célula na linha **i** e coluna **j**.',
    'Um inteiro: a maior soma possível entre os caminhos do canto superior esquerdo ao canto inferior direito.',
    '- `1 ≤ N, M ≤ 1000`
- `-10^6 ≤ A_ij ≤ 10^6`',
    'Rubi',
    '#BE123C',
    '#include "testlib.h"
#include <iostream>
#include <vector>

using namespace std;

int main(int argc, char* argv[]) {
    registerGen(argc, argv, 1);
    string type = opt<string>(1);
    int n = opt<int>(2, 3);
    int m = opt<int>(3, 3);

    cout << n << " " << m << "\\n";
    for (int i = 0; i < n; ++i) {
        for (int j = 0; j < m; ++j) {
            long long val = 0;
            if (type == "all_pos") {
                val = rnd.next(0LL, 1000000LL);
            } else if (type == "all_neg") {
                val = rnd.next(-1000000LL, 0LL);
            } else if (type == "zeros") {
                val = 0;
            } else {
                val = rnd.next(-1000000LL, 1000000LL);
            }
            cout << val << (j + 1 == m ? "" : " ");
        }
        cout << "\\n";
    }
    return 0;
}
',
    'gen all_pos 1 1 > $
gen all_neg 1 1 > $
gen zeros 1 1 > $
gen all_pos 1000 1000 > $
gen all_neg 1000 1000 > $
gen zeros 1000 1000 > $
gen random 1000 1000 > $
gen all_pos 2 2 > $
gen all_neg 2 2 > $
gen random 2 2 > $
gen all_pos 3 3 > $
gen all_neg 3 3 > $
gen random 3 3 > $
gen all_pos 5 5 > $
gen all_neg 5 5 > $
gen random 5 5 > $
gen all_pos 10 10 > $
gen all_neg 10 10 > $
gen random 10 10 > $
gen all_pos 20 20 > $
gen all_neg 20 20 > $
gen random 20 20 > $
gen all_pos 50 50 > $
gen all_neg 50 50 > $
gen random 50 50 > $
gen all_pos 100 100 > $
gen all_neg 100 100 > $
gen random 100 100 > $
gen all_pos 250 250 > $
gen all_neg 250 250 > $
gen random 250 250 > $
gen all_pos 500 500 > $
gen all_neg 500 500 > $
gen random 500 500 > $
gen all_pos 1000 1000 > $
gen all_neg 1000 1000 > $
gen random 1000 1000 > $
gen random 852 518 > $
gen random 875 559 > $
gen random 898 600 > $
gen random 921 641 > $
gen random 944 682 > $
gen random 967 723 > $
gen random 990 764 > $
gen random 13 805 > $
gen random 36 846 > $
gen random 59 887 > $
gen random 82 928 > $
gen random 105 969 > $
gen random 128 10 > $
gen random 151 51 > $
gen random 174 92 > $
gen random 197 133 > $
gen random 220 174 > $
gen random 243 215 > $
gen random 266 256 > $
gen random 289 297 > $
gen random 312 338 > $
gen random 335 379 > $
gen random 358 420 > $
gen random 381 461 > $
gen random 404 502 > $
gen random 427 543 > $
gen random 450 584 > $
gen random 473 625 > $
gen random 496 666 > $
gen random 519 707 > $
gen random 542 748 > $
gen random 565 789 > $
gen random 588 830 > $
gen random 611 871 > $
gen random 634 912 > $
gen random 657 953 > $
gen random 680 994 > $
gen random 703 35 > $
gen random 726 76 > $
gen random 749 117 > $
gen random 772 158 > $
gen random 795 199 > $
gen random 818 240 > $
gen random 841 281 > $
gen random 864 322 > $
gen random 887 363 > $
gen random 910 404 > $
gen random 933 445 > $
gen random 956 486 > $
gen random 979 527 > $
gen random 2 568 > $
gen random 25 609 > $
gen random 48 650 > $
gen random 71 691 > $
gen random 94 732 > $
gen random 117 773 > $
gen random 140 814 > $
gen random 163 855 > $
gen random 186 896 > $
gen random 209 937 > $
gen random 232 978 > $
gen random 255 19 > $
gen random 278 60 > $
gen random 301 101 > $
gen random 324 142 > $
gen random 347 183 > $
gen random 370 224 > $
gen random 393 265 > $',
    '[{"input": "3 3\\n5 1 2\\n1 9 1\\n2 1 8\\n", "output": "24\\n", "is_sample": true}, {"input": "2 3\\n1 2 10\\n4 1 1\\n", "output": "14\\n", "is_sample": true}, {"input": "1 4\\n-2 5 -1 3\\n", "output": "5\\n", "is_sample": true}]',
    '#include <iostream>
#include <vector>
#include <algorithm>

using namespace std;

int main() {
    ios_base::sync_with_stdio(false);
    cin.tie(NULL);

    int n, m;
    if (!(cin >> n >> m)) return 0;

    vector<vector<long long>> a(n, vector<long long>(m));
    for (int i = 0; i < n; ++i) {
        for (int j = 0; j < m; ++j) {
            cin >> a[i][j];
        }
    }

    vector<vector<long long>> dp(n, vector<long long>(m));
    dp[0][0] = a[0][0];

    for (int j = 1; j < m; ++j) {
        dp[0][j] = dp[0][j - 1] + a[0][j];
    }

    for (int i = 1; i < n; ++i) {
        dp[i][0] = dp[i - 1][0] + a[i][0];
    }

    for (int i = 1; i < n; ++i) {
        for (int j = 1; j < m; ++j) {
            dp[i][j] = a[i][j] + max(dp[i - 1][j], dp[i][j - 1]);
        }
    }

    cout << dp[n - 1][m - 1] << "\\n";

    return 0;
}
',
    NULL,
    NULL,
    NULL,
    'draft',
    NULL,
    NULL,
    NULL,
    @prova_owner_id,
    NOW(),
    NOW()
) ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    time_limit = VALUES(time_limit),
    memory_limit = VALUES(memory_limit),
    description = VALUES(description),
    input_description = VALUES(input_description),
    output_description = VALUES(output_description),
    notes = VALUES(notes),
    color_name = VALUES(color_name),
    color_hex = VALUES(color_hex),
    generator_source = VALUES(generator_source),
    generator_script = VALUES(generator_script),
    manual_tests = VALUES(manual_tests),
    solution_accept = VALUES(solution_accept),
    updated_at = NOW();

