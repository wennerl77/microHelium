#include "testlib.h"
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
