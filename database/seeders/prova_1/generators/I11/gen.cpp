#include "testlib.h"
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
