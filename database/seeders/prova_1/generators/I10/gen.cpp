#include "testlib.h"
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
