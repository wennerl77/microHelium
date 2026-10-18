#include "testlib.h"
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
