#include "testlib.h"
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
