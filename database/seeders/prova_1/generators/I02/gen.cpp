#include "testlib.h"
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
