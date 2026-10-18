#include "testlib.h"
#include <iostream>
#include <vector>

using namespace std;

int main(int argc, char* argv[]) {
    registerGen(argc, argv, 1);
    string type = opt<string>(1);
    int n = opt<int>(2, 3);
    int m = opt<int>(3, 3);

    cout << n << " " << m << "\n";
    for (int i = 0; i < n; ++i) {
        for (int j = 0; j < m; ++j) {
            long long val = 0;
            if (type == "all_pos") {
                val = rnd.next(0LL, 1000000LL);
            } else if (type == "all_neg") {
                val = rnd.next(-1000000LL, 0LL);
            } else if (type == "zeros") {
                val = 0;
            } else {
                val = rnd.next(-1000000LL, 1000000LL);
            }
            cout << val << (j + 1 == m ? "" : " ");
        }
        cout << "\n";
    }
    return 0;
}
