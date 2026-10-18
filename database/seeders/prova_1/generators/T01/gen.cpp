#include "testlib.h"
#include <iostream>

using namespace std;

int main(int argc, char* argv[]) {
    registerGen(argc, argv, 1);
    string type = opt<string>(1);
    int n = opt<int>(2, 10);

    cout << n << "\n";
    for (int i = 0; i < n; ++i) {
        long long val = 0;
        if (type == "all_even") {
            val = rnd.next(-500000000LL, 500000000LL) * 2;
        } else if (type == "all_odd") {
            val = rnd.next(-500000000LL, 500000000LL) * 2 + 1;
        } else if (type == "zeros") {
            val = 0;
        } else {
            val = rnd.next(-1000000000LL, 1000000000LL);
        }
        cout << val << (i + 1 == n ? "" : " ");
    }
    cout << "\n";
    return 0;
}
