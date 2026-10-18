#include "testlib.h"
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
