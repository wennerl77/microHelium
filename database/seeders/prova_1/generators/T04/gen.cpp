#include "testlib.h"
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
