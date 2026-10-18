#include "testlib.h"
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
