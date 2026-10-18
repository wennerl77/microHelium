#include "testlib.h"
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
