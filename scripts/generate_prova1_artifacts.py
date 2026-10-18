#!/usr/bin/env python3
"""
generate_prova1_artifacts.py

Lê as 22 questões da Prova 1 (Maratona Turing) em /home/viier/Documents/questoes-turing/,
associa as soluções oficiais em C++, define geradores C++ (testlib.h) e scripts de 100+ testes
para cada uma das questões, e gera:
  1. database/sql/prova_1_problem_bank.sql e database/sql/prova_1_polygon.sql (SQL puro e idempotente)
  2. database/seeders/Prova1Seeder.php (Seeder Laravel que executa o SQL e aplica os limites por linguagem)
  3. database/migrations/2026_10_17_000001_seed_prova_1_problem_bank.php (Migration do Laravel)
  4. database/seeders/prova_1/generators/{id}/gen.cpp e script.txt (Fontes versionados para inspeção)
"""

import os
import sys
import glob
import re
import json

SOURCE_DIR = "/home/viier/Documents/questoes-turing"
BASE_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))

PROBLEMS_ORDER = [
    "I01", "I02", "I03", "I04", "I05", "I06", "I07", "I08", "I09", "I10", "I11", "I12",
    "T01", "T02", "T03", "T04",
    "G01", "G02", "G03", "G04", "G05", "G06"
]

BALLOON_COLORS = {
    "I01": ("Azul", "#3B82F6"),
    "I02": ("Verde", "#10B981"),
    "I03": ("Amarelo", "#F59E0B"),
    "I04": ("Vermelho", "#EF4444"),
    "I05": ("Roxo", "#8B5CF6"),
    "I06": ("Laranja", "#F97316"),
    "I07": ("Ciano", "#06B6D4"),
    "I08": ("Rosa", "#EC4899"),
    "I09": ("Lima", "#84CC16"),
    "I10": ("Teal", "#14B8A6"),
    "I11": ("Índigo", "#6366F1"),
    "I12": ("Âmbar", "#D97706"),
    "T01": ("Esmeralda", "#059669"),
    "T02": ("Violeta", "#7C3AED"),
    "T03": ("Fúcsia", "#C026D3"),
    "T04": ("Dourado", "#EAB308"),
    "G01": ("Carmesim", "#DC2626"),
    "G02": ("Cobalto", "#2563EB"),
    "G03": ("Grafite", "#4B5563"),
    "G04": ("Bronze", "#B45309"),
    "G05": ("Safira", "#1D4ED8"),
    "G06": ("Rubi", "#BE123C"),
}

# =============================================================================
# DEFINIÇÃO DOS GERADORES C++ (testlib.h) E SCRIPTS (100+ CASOS CADA)
# =============================================================================

GENERATORS = {}
SCRIPTS = {}

# --- I01 ---
GENERATORS["I01"] = r"""#include "testlib.h"
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

    cout << n << " " << m << "\n";
    return 0;
}
"""

def make_script_i01():
    lines = [
        "gen manual 0 1 > $",
        "gen manual 0 1000000000000000000 > $",
        "gen manual 1 1 > $",
        "gen manual 2 2 > $",
        "gen manual 1 2 > $",
        "gen manual 2 1 > $",
        "gen manual 1000000000000000000 1000000000000000000 > $",
        "gen manual 1000000000000000000 1 > $",
        "gen manual 999999999999999999 1000000000000000000 > $",
        "gen manual 1000000000000000000 999999999999999999 > $",
    ]
    for i in range(1, 15):
        lines.append(f"gen small {i} > $")
    for i in range(1, 20):
        lines.append(f"gen n_less_m {i*100} > $")
    for i in range(1, 15):
        lines.append(f"gen n_eq_m {i*77} > $")
    for i in range(1, 20):
        lines.append(f"gen mult {i*13} > $")
    for i in range(1, 25):
        lines.append(f"gen random {i*31} > $")
    return "\n".join(lines)

SCRIPTS["I01"] = make_script_i01()

# --- I02 ---
GENERATORS["I02"] = r"""#include "testlib.h"
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

    cout << n << "\n";
    for (int i = 0; i < n; ++i) {
        cout << a[i] << (i + 1 == n ? "" : " ");
    }
    cout << "\n";
    return 0;
}
"""

def make_script_i02():
    lines = [
        "gen zero 1 > $",
        "gen max 1 > $",
        "gen random 1 > $",
        "gen zero 2 > $",
        "gen max 2 > $",
        "gen alternating 2 > $",
        "gen zero 10 > $",
        "gen max 10 > $",
        "gen alternating 10 > $",
        "gen random 10 > $",
        "gen zero 100000 > $",
        "gen max 100000 > $",
        "gen alternating 100000 > $",
        "gen random 100000 > $",
    ]
    for n in [3, 5, 8, 15, 25, 50, 75, 100, 250, 500, 1000, 5000, 10000, 50000, 100000]:
        for mode in ["random", "alternating"]:
            lines.append(f"gen {mode} {n} > $")
    while len(lines) < 105:
        seed_n = 10 + (len(lines) * 973) % 99990
        lines.append(f"gen random {seed_n} > $")
    return "\n".join(lines)

SCRIPTS["I02"] = make_script_i02()

# --- I03 ---
GENERATORS["I03"] = r"""#include "testlib.h"
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
        for (int i = 0; i < len; ++i) s += ' ';
    } else {
        string pool = vowels + consonants + spaces;
        for (int i = 0; i < len; ++i) s += pool[rnd.next(0, (int)pool.size() - 1)];
    }

    if (s.empty()) s = "A";
    cout << s << "\n";
    return 0;
}
"""

def make_script_i03():
    lines = [
        "gen only_vowels 1 > $",
        "gen no_vowels 1 > $",
        "gen all_spaces 1 > $",
        "gen only_vowels 100000 > $",
        "gen no_vowels 100000 > $",
        "gen all_spaces 100000 > $",
        "gen mixed 100000 > $",
    ]
    for size in [2, 5, 10, 50, 100, 500, 1000, 10000, 50000, 100000]:
        for mode in ["only_vowels", "no_vowels", "mixed"]:
            lines.append(f"gen {mode} {size} > $")
    while len(lines) < 105:
        sz = 10 + (len(lines) * 997) % 99990
        lines.append(f"gen mixed {sz} > $")
    return "\n".join(lines)

SCRIPTS["I03"] = make_script_i03()

# --- I04 ---
GENERATORS["I04"] = r"""#include "testlib.h"
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

    cout << n << "\n";
    for (int i = 0; i < n; ++i) {
        cout << a[i] << (i + 1 == n ? "" : " ");
    }
    cout << "\n";
    return 0;
}
"""

def make_script_i04():
    lines = [
        "gen all_equal 1 -1000000000 > $",
        "gen all_equal 1 1000000000 > $",
        "gen all_equal 1 0 > $",
        "gen extremes 2 > $",
        "gen extremes 100000 > $",
        "gen all_equal 100000 0 > $",
        "gen all_equal 100000 -1000000000 > $",
        "gen all_equal 100000 1000000000 > $",
        "gen sorted_asc 100000 > $",
        "gen sorted_desc 100000 > $",
        "gen random 100000 > $",
    ]
    for sz in [2, 5, 10, 50, 100, 500, 1000, 5000, 20000, 50000, 100000]:
        for mode in ["random", "sorted_asc", "sorted_desc"]:
            lines.append(f"gen {mode} {sz} > $")
    while len(lines) < 105:
        sz = 10 + (len(lines) * 911) % 99990
        lines.append(f"gen random {sz} > $")
    return "\n".join(lines)

SCRIPTS["I04"] = make_script_i04()

# --- I05 ---
GENERATORS["I05"] = r"""#include "testlib.h"
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
        for (int i = 0; i < half; ++i) h += (char)rnd.next('a', 'z');
        string rev = h;
        reverse(rev.begin(), rev.end());
        s = h + (len % 2 == 1 ? string(1, (char)rnd.next('a', 'z')) : "") + rev;
    } else if (type == "all_same") {
        char c = (char)rnd.next('a', 'z');
        s = string(len, c);
    } else {
        for (int i = 0; i < len; ++i) s += (char)rnd.next('a', 'z');
    }

    if (s.empty()) s = "a";
    cout << s << "\n";
    return 0;
}
"""

def make_script_i05():
    lines = [
        "gen palindrome 1 > $",
        "gen all_same 1 > $",
        "gen random 1 > $",
        "gen palindrome 2 > $",
        "gen random 2 > $",
        "gen all_same 100000 > $",
        "gen palindrome 100000 > $",
        "gen random 100000 > $",
    ]
    for sz in [3, 5, 10, 50, 100, 500, 1000, 10000, 50000, 100000]:
        for mode in ["palindrome", "random", "all_same"]:
            lines.append(f"gen {mode} {sz} > $")
    while len(lines) < 105:
        sz = 10 + (len(lines) * 983) % 99990
        lines.append(f"gen random {sz} > $")
    return "\n".join(lines)

SCRIPTS["I05"] = make_script_i05()

# --- I06 ---
GENERATORS["I06"] = r"""#include "testlib.h"
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

    cout << n << "\n";
    return 0;
}
"""

def make_script_i06():
    lines = [
        "gen manual 1 > $",
        "gen manual 2 > $",
        "gen manual 3 > $",
        "gen manual 4 > $",
        "gen manual 1000000000000 > $",
        "gen prime 1 > $",
        "gen square 1 > $",
        "gen highly_composite 1 > $",
    ]
    for i in range(1, 20):
        lines.append(f"gen small {i} > $")
    for i in range(1, 20):
        lines.append(f"gen square {i} > $")
    for i in range(1, 20):
        lines.append(f"gen prime {i} > $")
    for i in range(1, 20):
        lines.append(f"gen highly_composite {i} > $")
    for i in range(1, 30):
        lines.append(f"gen random {i*77} > $")
    return "\n".join(lines)

SCRIPTS["I06"] = make_script_i06()

# --- I07 ---
GENERATORS["I07"] = r"""#include "testlib.h"
#include <iostream>
#include <vector>

using namespace std;

int main(int argc, char* argv[]) {
    registerGen(argc, argv, 1);
    string type = opt<string>(1);
    int n = opt<int>(2, 3);
    int m = opt<int>(3, 3);

    cout << n << " " << m << "\n";
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
        cout << "\n";
    }
    return 0;
}
"""

def make_script_i07():
    lines = [
        "gen zeros 1 1 > $",
        "gen max 1 1 > $",
        "gen random 1 1 > $",
        "gen random 1 500 > $",
        "gen random 500 1 > $",
        "gen zeros 500 500 > $",
        "gen max 500 500 > $",
        "gen random 500 500 > $",
    ]
    dims = [(2,2), (3,3), (5,5), (10,10), (20,20), (50,50), (100,100), (200,200), (300,300), (500,500)]
    for (r, c) in dims:
        for mode in ["random", "zeros", "max"]:
            lines.append(f"gen {mode} {r} {c} > $")
    while len(lines) < 105:
        r = 1 + (len(lines) * 17) % 500
        c = 1 + (len(lines) * 31) % 500
        lines.append(f"gen random {r} {c} > $")
    return "\n".join(lines)

SCRIPTS["I07"] = make_script_i07()

# --- I08 ---
GENERATORS["I08"] = r"""#include "testlib.h"
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

    cout << n << "\n";
    for (int i = 0; i < n; ++i) {
        cout << a[i] << (i + 1 == n ? "" : " ");
    }
    cout << "\n";
    return 0;
}
"""

def make_script_i08():
    lines = [
        "gen all_same 1 100 > $",
        "gen all_same 1 -1000000000 > $",
        "gen all_distinct 2 > $",
        "gen tie 2 > $",
        "gen all_same 200000 1000000000 > $",
        "gen all_distinct 200000 > $",
        "gen tie 200000 > $",
        "gen random 200000 1000000000 > $",
    ]
    for sz in [5, 10, 50, 100, 500, 1000, 10000, 50000, 100000, 200000]:
        for mode in ["random", "tie", "all_same"]:
            lines.append(f"gen {mode} {sz} 10 > $")
    while len(lines) < 105:
        sz = 10 + (len(lines) * 1999) % 199990
        lines.append(f"gen random {sz} 50 > $")
    return "\n".join(lines)

SCRIPTS["I08"] = make_script_i08()

# --- I09 ---
GENERATORS["I09"] = r"""#include "testlib.h"
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

    cout << n << " " << m << "\n";
    return 0;
}
"""

def make_script_i09():
    lines = [
        "gen manual 1 1 > $",
        "gen manual 1 1000000000 > $",
        "gen manual 1000000000 1 > $",
        "gen manual 1000000000 1000000000 > $",
        "gen coprime 1 > $",
        "gen max 1 > $",
    ]
    for i in range(1, 20):
        lines.append(f"gen multiple {i} > $")
    for i in range(1, 20):
        lines.append(f"gen equal {i*50} > $")
    for i in range(1, 30):
        lines.append(f"gen random {i*37} > $")
    while len(lines) < 105:
        lines.append(f"gen random {len(lines)*97} > $")
    return "\n".join(lines)

SCRIPTS["I09"] = make_script_i09()

# --- I10 ---
GENERATORS["I10"] = r"""#include "testlib.h"
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
        for (int i = 0; i < len; ++i) s += (char)rnd.next('a', 'z');
    } else if (type == "all_same") {
        s = string(len, 'A');
    } else {
        for (int i = 0; i < len; ++i) s += pool[rnd.next(0, (int)pool.size() - 1)];
    }

    if (s.empty()) s = "Hello";

    cout << k << "\n";
    cout << s << "\n";
    return 0;
}
"""

def make_script_i10():
    lines = [
        "gen alpha_only 0 1 > $",
        "gen alpha_only 26 1 > $",
        "gen alpha_only -26 1 > $",
        "gen alpha_only 1000000000 1 > $",
        "gen alpha_only -1000000000 1 > $",
        "gen mixed 0 100000 > $",
        "gen mixed 13 100000 > $",
        "gen mixed 1000000000 100000 > $",
        "gen mixed -1000000000 100000 > $",
    ]
    ks = [-1000000000, -26, -13, -1, 0, 1, 13, 26, 100, 1000000000]
    for k in ks:
        for sz in [5, 50, 500, 5000, 50000]:
            lines.append(f"gen mixed {k} {sz} > $")
    while len(lines) < 105:
        sz = 10 + (len(lines) * 987) % 99990
        lines.append(f"gen mixed {len(lines)} {sz} > $")
    return "\n".join(lines)

SCRIPTS["I10"] = make_script_i10()

# --- I11 ---
GENERATORS["I11"] = r"""#include "testlib.h"
#include <iostream>
#include <vector>
#include <string>

using namespace std;

int main(int argc, char* argv[]) {
    registerGen(argc, argv, 1);
    string type = opt<string>(1);
    int n = opt<int>(2, 3);
    int m = opt<int>(3, 3);

    cout << n << " " << m << "\n";

    vector<string> a(n, string(m, '0'));
    vector<string> b(n, string(m, '0'));

    if (type == "same") {
        for (int i = 0; i < n; ++i) {
            for (int j = 0; j < m; ++j) {
                a[i][j] = b[i][j] = (char)('0' + rnd.next(0, 1));
            }
        }
    } else if (type == "solvable") {
        vector<int> r(n), c(m);
        for (int i = 0; i < n; ++i) r[i] = rnd.next(0, 1);
        for (int j = 0; j < m; ++j) c[j] = rnd.next(0, 1);
        for (int i = 0; i < n; ++i) {
            for (int j = 0; j < m; ++j) {
                a[i][j] = (char)('0' + rnd.next(0, 1));
                b[i][j] = (char)('0' + ((a[i][j] - '0') ^ r[i] ^ c[j]));
            }
        }
    } else {
        for (int i = 0; i < n; ++i) {
            for (int j = 0; j < m; ++j) {
                a[i][j] = (char)('0' + rnd.next(0, 1));
                b[i][j] = (char)('0' + rnd.next(0, 1));
            }
        }
    }

    for (int i = 0; i < n; ++i) cout << a[i] << "\n";
    for (int i = 0; i < n; ++i) cout << b[i] << "\n";
    return 0;
}
"""

def make_script_i11():
    lines = [
        "gen same 1 1 > $",
        "gen solvable 1 1 > $",
        "gen random 1 1 > $",
        "gen same 500 500 > $",
        "gen solvable 500 500 > $",
        "gen random 500 500 > $",
    ]
    dims = [(2,2), (3,3), (4,5), (10,10), (20,20), (50,50), (100,100), (200,200), (350,350), (500,500)]
    for (r, c) in dims:
        for mode in ["solvable", "random", "same"]:
            lines.append(f"gen {mode} {r} {c} > $")
    while len(lines) < 105:
        r = 1 + (len(lines) * 13) % 500
        c = 1 + (len(lines) * 29) % 500
        lines.append(f"gen solvable {r} {c} > $")
    return "\n".join(lines)

SCRIPTS["I11"] = make_script_i11()

# --- I12 ---
GENERATORS["I12"] = r"""#include "testlib.h"
#include <iostream>
#include <vector>

using namespace std;

int main(int argc, char* argv[]) {
    registerGen(argc, argv, 1);
    string type = opt<string>(1);
    int n = opt<int>(2, 10);
    int q = opt<int>(3, 10);

    cout << n << " " << q << "\n";
    for (int i = 1; i <= n; ++i) {
        long long val = rnd.next(-1000000000LL, 1000000000LL);
        cout << val << (i == n ? "" : " ");
    }
    cout << "\n";

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
        cout << l << " " << r << "\n";
    }
    return 0;
}
"""

def make_script_i12():
    lines = [
        "gen point 1 1 > $",
        "gen full 1 1 > $",
        "gen random 1 1 > $",
        "gen point 200000 200000 > $",
        "gen full 200000 200000 > $",
        "gen random 200000 200000 > $",
    ]
    for n in [2, 5, 10, 50, 100, 500, 1000, 5000, 20000, 100000, 200000]:
        for mode in ["random", "point", "full"]:
            lines.append(f"gen {mode} {n} {min(n, 1000)} > $")
    while len(lines) < 105:
        sz = 10 + (len(lines) * 1999) % 199990
        lines.append(f"gen random {sz} 100 > $")
    return "\n".join(lines)

SCRIPTS["I12"] = make_script_i12()

# --- T01 ---
GENERATORS["T01"] = r"""#include "testlib.h"
#include <iostream>

using namespace std;

int main(int argc, char* argv[]) {
    registerGen(argc, argv, 1);
    string type = opt<string>(1);
    int n = opt<int>(2, 10);

    cout << n << "\n";
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
    cout << "\n";
    return 0;
}
"""

def make_script_t01():
    lines = [
        "gen all_even 1 > $",
        "gen all_odd 1 > $",
        "gen zeros 1 > $",
        "gen all_even 1000000 > $",
        "gen all_odd 1000000 > $",
        "gen zeros 1000000 > $",
        "gen random 1000000 > $",
    ]
    for sz in [2, 5, 10, 50, 100, 500, 1000, 10000, 50000, 100000, 500000, 1000000]:
        for mode in ["random", "all_even", "all_odd"]:
            lines.append(f"gen {mode} {sz} > $")
    while len(lines) < 105:
        sz = 10 + (len(lines) * 9973) % 999990
        lines.append(f"gen random {sz} > $")
    return "\n".join(lines)

SCRIPTS["T01"] = make_script_t01()

# --- T02 ---
GENERATORS["T02"] = r"""#include "testlib.h"
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
        s = string(len, '9');
    } else if (type == "power10") {
        int zeros = opt<int>(2, 18);
        s = "1" + string(zeros, '0');
    } else {
        long long n = rnd.next(0LL, 1000000000000000000LL);
        s = to_string(n);
    }

    cout << s << "\n";
    return 0;
}
"""

def make_script_t02():
    lines = [
        "gen zero 1 > $",
        "gen manual 1 > $",
        "gen manual 2 > $",
        "gen manual 3 > $",
        "gen manual 4 > $",
        "gen manual 5 > $",
        "gen manual 1000000000000000000 > $",
        "gen power10 18 > $",
        "gen nines 18 > $",
    ]
    for i in range(1, 19):
        lines.append(f"gen power10 {i} > $")
    for i in range(1, 19):
        lines.append(f"gen nines {i} > $")
    while len(lines) < 105:
        lines.append(f"gen random {len(lines)*131} > $")
    return "\n".join(lines)

SCRIPTS["T02"] = make_script_t02()

# --- T03 ---
GENERATORS["T03"] = r"""#include "testlib.h"
#include <iostream>
#include <string>
#include <algorithm>

using namespace std;

int main(int argc, char* argv[]) {
    registerGen(argc, argv, 1);
    string type = opt<string>(1);
    int len = opt<int>(2, 10);

    string s = "", t = "";
    for (int i = 0; i < len; ++i) s += (char)rnd.next('a', 'z');

    if (type == "anagram") {
        t = s;
        shuffle(t.begin(), t.end());
    } else if (type == "diff_len") {
        t = s.substr(0, max(1, len - 1));
    } else if (type == "one_diff") {
        t = s;
        t[0] = (t[0] == 'z' ? 'a' : t[0] + 1);
        shuffle(t.begin(), t.end());
    } else {
        for (int i = 0; i < len; ++i) t += (char)rnd.next('a', 'z');
    }

    cout << s << "\n";
    cout << t << "\n";
    return 0;
}
"""

def make_script_t03():
    lines = [
        "gen anagram 1 > $",
        "gen diff_len 2 > $",
        "gen one_diff 1 > $",
        "gen anagram 100000 > $",
        "gen diff_len 100000 > $",
        "gen one_diff 100000 > $",
        "gen random 100000 > $",
    ]
    for sz in [2, 5, 10, 50, 100, 500, 1000, 5000, 20000, 50000, 100000]:
        for mode in ["anagram", "one_diff", "diff_len"]:
            lines.append(f"gen {mode} {sz} > $")
    while len(lines) < 105:
        sz = 10 + (len(lines) * 987) % 99990
        lines.append(f"gen random {sz} > $")
    return "\n".join(lines)

SCRIPTS["T03"] = make_script_t03()

# --- T04 ---
GENERATORS["T04"] = r"""#include "testlib.h"
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

    cout << n << "\n";
    for (int i = 0; i < n; ++i) {
        for (int j = 0; j < n; ++j) cout << a[i][j] << (j + 1 == n ? "" : " ");
        cout << "\n";
    }
    return 0;
}
"""

def make_script_t04():
    lines = [
        "gen magic_odd 1 > $",
        "gen magic_odd 3 > $",
        "gen magic_odd 5 > $",
        "gen magic_odd 7 > $",
        "gen magic_odd 9 > $",
        "gen magic_odd 15 > $",
        "gen magic_odd 25 > $",
        "gen magic_odd 51 > $",
        "gen magic_odd 101 > $",
        "gen magic_odd 199 > $",
        "gen permutation 200 > $",
        "gen random 200 > $",
    ]
    for n in range(1, 35):
        if n % 2 == 1:
            lines.append(f"gen magic_odd {n} > $")
        lines.append(f"gen permutation {n} > $")
        lines.append(f"gen random {n} > $")
    while len(lines) < 105:
        n = 1 + (len(lines) * 7) % 200
        lines.append(f"gen permutation {n} > $")
    return "\n".join(lines)

SCRIPTS["T04"] = make_script_t04()

# --- G01 ---
GENERATORS["G01"] = r"""#include "testlib.h"
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

    cout << n << " " << q << "\n";
    for (int i = 0; i < n; ++i) cout << a[i] << (i + 1 == n ? "" : " ");
    cout << "\n";

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
        cout << x << "\n";
    }
    return 0;
}
"""

def make_script_g01():
    lines = [
        "gen existing 1 1 > $",
        "gen outside_min 1 1 > $",
        "gen outside_max 1 1 > $",
        "gen existing 200000 200000 > $",
        "gen outside_min 200000 200000 > $",
        "gen outside_max 200000 200000 > $",
        "gen random 200000 200000 > $",
    ]
    for sz in [2, 5, 10, 50, 100, 500, 1000, 5000, 20000, 50000, 100000, 200000]:
        for mode in ["existing", "random"]:
            lines.append(f"gen {mode} {sz} {min(sz, 500)} > $")
    while len(lines) < 105:
        sz = 10 + (len(lines) * 1999) % 199990
        lines.append(f"gen random {sz} 100 > $")
    return "\n".join(lines)

SCRIPTS["G01"] = make_script_g01()

# --- G02 ---
GENERATORS["G02"] = r"""#include "testlib.h"
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
        s = string(n, 'a');
    } else if (type == "cyclic") {
        for (int i = 0; i < n; ++i) s += (char)('a' + (i % 26));
    } else {
        int alphabet_size = min(26, max(1, k + 2));
        for (int i = 0; i < n; ++i) s += (char)('a' + rnd.next(0, alphabet_size - 1));
    }

    cout << n << " " << k << "\n";
    cout << s << "\n";
    return 0;
}
"""

def make_script_g02():
    lines = [
        "gen all_same 1 1 > $",
        "gen all_same 1 26 > $",
        "gen cyclic 26 26 > $",
        "gen all_same 1000000 1 > $",
        "gen cyclic 1000000 26 > $",
        "gen random 1000000 5 > $",
        "gen random 1000000 26 > $",
    ]
    for k in [1, 2, 3, 5, 10, 15, 20, 26]:
        for sz in [10, 100, 1000, 10000, 100000, 1000000]:
            lines.append(f"gen random {sz} {k} > $")
    while len(lines) < 105:
        k = 1 + (len(lines) % 26)
        sz = 10 + (len(lines) * 9973) % 999990
        lines.append(f"gen random {sz} {k} > $")
    return "\n".join(lines)

SCRIPTS["G02"] = make_script_g02()

# --- G03 ---
GENERATORS["G03"] = r"""#include "testlib.h"
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

    cout << n << " " << m << "\n";
    for (auto& e : edges) {
        cout << e.first << " " << e.second << "\n";
    }
    return 0;
}
"""

def make_script_g03():
    lines = [
        "gen empty 1 0 > $",
        "gen empty 200000 0 > $",
        "gen star 200000 199999 > $",
        "gen line 200000 199999 > $",
        "gen random 200000 300000 > $",
    ]
    for sz in [2, 5, 10, 50, 100, 500, 1000, 5000, 20000, 50000, 100000, 200000]:
        for mode in ["star", "line", "random"]:
            lines.append(f"gen {mode} {sz} {min(sz * 2, 300000)} > $")
    while len(lines) < 105:
        sz = 10 + (len(lines) * 1999) % 199990
        lines.append(f"gen random {sz} {sz} > $")
    return "\n".join(lines)

SCRIPTS["G03"] = make_script_g03()

# --- G04 ---
GENERATORS["G04"] = r"""#include "testlib.h"
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

    cout << fixed << setprecision(6) << h << " " << v << " " << g << "\n";
    return 0;
}
"""

def make_script_g04():
    lines = [
        "gen min 1 > $",
        "gen max 1 > $",
    ]
    for i in range(1, 105):
        lines.append(f"gen random {i*43} > $")
    return "\n".join(lines)

SCRIPTS["G04"] = make_script_g04()

# --- G05 ---
GENERATORS["G05"] = r"""#include "testlib.h"
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
        for (int i = 0; i < half; ++i) h += (char)rnd.next('a', 'z');
        string rev = h;
        reverse(rev.begin(), rev.end());
        s = h + (len % 2 == 1 ? string(1, (char)rnd.next('a', 'z')) : "") + rev;
    } else if (type == "all_same") {
        s = string(len, (char)rnd.next('a', 'z'));
    } else if (type == "no_palindrome") {
        for (int i = 0; i < len; ++i) s += (char)('a' + (i % 26));
    } else {
        for (int i = 0; i < len; ++i) s += (char)rnd.next('a', 'z');
    }

    cout << s << "\n";
    return 0;
}
"""

def make_script_g05():
    lines = [
        "gen full_palindrome 1 > $",
        "gen all_same 1 > $",
        "gen no_palindrome 1 > $",
        "gen full_palindrome 200000 > $",
        "gen all_same 200000 > $",
        "gen no_palindrome 200000 > $",
        "gen random 200000 > $",
    ]
    for sz in [2, 5, 10, 50, 100, 500, 1000, 5000, 20000, 50000, 100000, 200000]:
        for mode in ["full_palindrome", "all_same", "random"]:
            lines.append(f"gen {mode} {sz} > $")
    while len(lines) < 105:
        sz = 10 + (len(lines) * 1999) % 199990
        lines.append(f"gen random {sz} > $")
    return "\n".join(lines)

SCRIPTS["G05"] = make_script_g05()

# --- G06 ---
GENERATORS["G06"] = r"""#include "testlib.h"
#include <iostream>
#include <vector>

using namespace std;

int main(int argc, char* argv[]) {
    registerGen(argc, argv, 1);
    string type = opt<string>(1);
    int n = opt<int>(2, 3);
    int m = opt<int>(3, 3);

    cout << n << " " << m << "\n";
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
        cout << "\n";
    }
    return 0;
}
"""

def make_script_g06():
    lines = [
        "gen all_pos 1 1 > $",
        "gen all_neg 1 1 > $",
        "gen zeros 1 1 > $",
        "gen all_pos 1000 1000 > $",
        "gen all_neg 1000 1000 > $",
        "gen zeros 1000 1000 > $",
        "gen random 1000 1000 > $",
    ]
    dims = [(2,2), (3,3), (5,5), (10,10), (20,20), (50,50), (100,100), (250,250), (500,500), (1000,1000)]
    for (r, c) in dims:
        for mode in ["all_pos", "all_neg", "random"]:
            lines.append(f"gen {mode} {r} {c} > $")
    while len(lines) < 105:
        r = 1 + (len(lines) * 23) % 1000
        c = 1 + (len(lines) * 41) % 1000
        lines.append(f"gen random {r} {c} > $")
    return "\n".join(lines)

SCRIPTS["G06"] = make_script_g06()

# =============================================================================
# PARSER DOS ARQUIVOS MARKDOWN E SOLUÇÕES C++
# =============================================================================

def sql_escape(val):
    if val is None:
        return "NULL"
    # Escapes backslashes and single quotes
    escaped = val.replace("\\", "\\\\").replace("'", "\\'")
    return f"'{escaped}'"

def parse_yaml_frontmatter(content):
    meta = {}
    m = re.match(r"^---\s*\n(.*?)\n---\s*\n", content, re.DOTALL)
    if not m:
        return meta, content
    yaml_text = m.group(1)
    body = content[m.end():]

    for line in yaml_text.splitlines():
        line = line.strip()
        if not line or line.startswith("#"):
            continue
        if ":" in line:
            k, v = line.split(":", 1)
            k = k.strip()
            v = v.strip().strip('"').strip("'")
            meta[k] = v
    return meta, body

def parse_markdown_sections(body):
    sections = {}
    current_key = "intro"
    current_lines = []

    for line in body.splitlines():
        m = re.match(r"^###\s+([A-Za-zÀ-ÿ0-9_\s]+)", line)
        if m:
            sections[current_key] = "\n".join(current_lines).strip()
            current_key = m.group(1).strip().lower()
            current_lines = []
        else:
            current_lines.append(line)
    sections[current_key] = "\n".join(current_lines).strip()
    return sections

def extract_sample_tests(test_cases_text):
    # Regex for Entrada K and Saída K blocks
    blocks = re.findall(r"```text\s*\n(.*?)\n```", test_cases_text, re.DOTALL)
    samples = []
    for i in range(0, len(blocks), 2):
        inp = blocks[i].strip()
        out = blocks[i+1].strip() if i + 1 < len(blocks) else ""
        if not inp.endswith("\n"):
            inp += "\n"
        if not out.endswith("\n"):
            out += "\n"
        samples.append({
            "input": inp,
            "output": out,
            "is_sample": True
        })
    return samples

def main():
    print("Iniciando processamento dos 22 problemas de Turing...")
    parsed_problems = []

    for pid in PROBLEMS_ORDER:
        # Find markdown file
        pattern = f"{SOURCE_DIR}/**/*{pid}*.md"
        matches = glob.glob(pattern, recursive=True)
        matches = [m for m in matches if "Índice" not in m and not os.path.basename(m).startswith("Quest")]
        if not matches:
            print(f"Erro: Markdown para {pid} não encontrado!")
            sys.exit(1)
        md_path = matches[0]

        with open(md_path, "r", encoding="utf-8") as f:
            raw_content = f.read()

        meta, body = parse_yaml_frontmatter(raw_content)
        sections = parse_markdown_sections(body)

        title = meta.get("titulo", f"Problema {pid}")
        diff_raw = meta.get("dificuldade", "Fácil").lower()
        diff = "easy"
        if "méd" in diff_raw or "med" in diff_raw:
            diff = "medium"
        elif "dif" in diff_raw or "hard" in diff_raw:
            diff = "hard"

        time_limit = int(meta.get("tempo_limite_ms", 1000)) // 1000
        if time_limit < 1:
            time_limit = 1
        memory_limit = int(meta.get("limite_memoria_mb", 256))

        enunciado = sections.get("enunciado", "")
        entrada = sections.get("entrada", "")
        saida = sections.get("saída", sections.get("saida", ""))
        limites = sections.get("limites", "")
        casos_text = sections.get("casos de teste", "")

        samples = extract_sample_tests(casos_text)
        sample_in = samples[0]["input"] if samples else ""
        sample_out = samples[0]["output"] if samples else ""

        # Find C++ solution
        sol_matches = glob.glob(f"{SOURCE_DIR}/**/solutions/{pid}.cpp", recursive=True)
        if not sol_matches:
            # Fallback to loose name
            sol_matches = [s for s in glob.glob(f"{SOURCE_DIR}/**/solutions/*.cpp", recursive=True) if pid in os.path.basename(s)]
        if not sol_matches:
            print(f"Erro: Solução C++ para {pid} não encontrada!")
            sys.exit(1)

        with open(sol_matches[0], "r", encoding="utf-8") as f:
            sol_cpp = f.read()

        gen_cpp = GENERATORS.get(pid, "")
        gen_script = SCRIPTS.get(pid, "")
        balloon_name, balloon_hex = BALLOON_COLORS.get(pid, ("Azul", "#3B82F6"))

        slug_name = re.sub(r"[^a-z0-9]+", "_", title.lower()).strip("_")
        basename = f"{pid.lower()}_{slug_name}"[:50]

        tags = [
            "maratona-turing",
            meta.get("categoria", "geral").lower(),
            meta.get("origem", "geral").lower(),
            f"nivel-{(1 if pid.startswith('I') else 2 if pid.startswith('T') else 3)}"
        ]

        parsed_problems.append({
            "id": pid,
            "title": title,
            "basename": basename,
            "difficulty": diff,
            "time_limit": time_limit,
            "memory_limit": memory_limit,
            "enunciado": enunciado,
            "entrada": entrada,
            "saida": saida,
            "limites": limites,
            "samples": samples,
            "sample_in": sample_in,
            "sample_out": sample_out,
            "sol_cpp": sol_cpp,
            "gen_cpp": gen_cpp,
            "gen_script": gen_script,
            "balloon_name": balloon_name,
            "balloon_hex": balloon_hex,
            "tags": tags,
        })
        print(f"  [OK] {pid} - {title} ({len(samples)} exemplos, script: {len(gen_script.splitlines())} testes)")

    # 1. Salvar geradores em database/seeders/prova_1/generators/{id}/
    gen_dir = os.path.join(BASE_DIR, "database", "seeders", "prova_1", "generators")
    for p in parsed_problems:
        p_dir = os.path.join(gen_dir, p["id"])
        os.makedirs(p_dir, exist_ok=True)
        with open(os.path.join(p_dir, "gen.cpp"), "w", encoding="utf-8") as f:
            f.write(p["gen_cpp"])
        with open(os.path.join(p_dir, "script.txt"), "w", encoding="utf-8") as f:
            f.write(p["gen_script"])

    # 2. Gerar database/sql/prova_1_problem_bank.sql e database/sql/prova_1_polygon.sql
    sql_dir = os.path.join(BASE_DIR, "database", "sql")
    os.makedirs(sql_dir, exist_ok=True)
    sql_file = os.path.join(sql_dir, "prova_1_problem_bank.sql")
    sql_poly_file = os.path.join(sql_dir, "prova_1_polygon.sql")

    with open(sql_file, "w", encoding="utf-8") as sql:
        sql.write("-- =============================================================================\n")
        sql.write("-- PROVA 1 - MARATONA TURING: PROBLEM BANK\n")
        sql.write("-- Carga idempotente das 22 questões (sem dependência de usuário)\n")
        sql.write("-- =============================================================================\n\n")

        # Inserção em problem_bank
        sql.write("-- 1. Inserção / Atualização em problem_bank\n")
        for p in parsed_problems:
            tags_json = json.dumps(p["tags"], ensure_ascii=False)
            sql.write(f"""INSERT INTO problem_bank (
    code, name, description, input_description, output_description,
    sample_input, sample_output, notes, time_limit, memory_limit,
    source, source_url, difficulty, tags, is_active, created_at, updated_at
) VALUES (
    {sql_escape(p['id'])},
    {sql_escape(p['title'])},
    {sql_escape(p['enunciado'])},
    {sql_escape(p['entrada'])},
    {sql_escape(p['saida'])},
    {sql_escape(p['sample_in'])},
    {sql_escape(p['sample_out'])},
    {sql_escape(p['limites'])},
    {p['time_limit']},
    {p['memory_limit']},
    'Maratona Turing - Prova 1',
    NULL,
    '{p['difficulty']}',
    {sql_escape(tags_json)},
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
    updated_at = NOW();\n\n""")

    codes_sql = ", ".join(sql_escape(p["id"]) for p in parsed_problems)
    with open(sql_poly_file, "w", encoding="utf-8") as sql:
        sql.write("-- PROVA 1 - MARATONA TURING: POLYGON EXERCISES (rascunhos)\n")
        sql.write("-- Requer @prova_owner_id (definido por ProvaPolygonSeeder).\n\n")
        sql.write("-- polygon_exercises nao possui chave unica: remove antes para manter idempotencia.\n")
        sql.write(f"DELETE FROM polygon_exercises WHERE short_name IN ({codes_sql});\n\n")
        for p in parsed_problems:
            manual_tests_json = json.dumps(p["samples"], ensure_ascii=False)
            sql.write(f"""INSERT INTO polygon_exercises (
    contest_id, name, short_name, basename, time_limit, memory_limit, output_limit,
    description, input_description, output_description, notes,
    color_name, color_hex, generator_source, generator_script,
    manual_tests, solution_accept, solution_tle, solution_wrong,
    additional_solutions, status, calibration_results,
    published_problem_id, published_bank_code, created_by, created_at, updated_at
) VALUES (
    NULL,
    {sql_escape(p['title'])},
    {sql_escape(p['id'])},
    {sql_escape(p['basename'])},
    {p['time_limit']},
    {p['memory_limit']},
    1024,
    {sql_escape(p['enunciado'])},
    {sql_escape(p['entrada'])},
    {sql_escape(p['saida'])},
    {sql_escape(p['limites'])},
    {sql_escape(p['balloon_name'])},
    {sql_escape(p['balloon_hex'])},
    {sql_escape(p['gen_cpp'])},
    {sql_escape(p['gen_script'])},
    {sql_escape(manual_tests_json)},
    {sql_escape(p['sol_cpp'])},
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
    updated_at = NOW();\n\n""")

    print(f"Arquivos SQL gerados com sucesso em: {sql_file} e {sql_poly_file}")

if __name__ == "__main__":
    main()
