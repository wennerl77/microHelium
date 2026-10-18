#include "testlib.h"
#include <iostream>

using namespace std;

int main(int argc, char* argv[]) {
    registerGen(argc, argv, 1);
    string type = opt<string>(1);

    long long n = 1, m = 1;
    if (type == "manual") {
        n = opt<long long>(2);
        m = opt<long long>(3);
    } else if (type == "equal") {
        n = m = opt<long long>(2, 100);
    } else if (type == "coprime") {
        n = 999999937LL; // prime
        m = 999999929LL; // prime
    } else if (type == "multiple") {
        m = rnd.next(1LL, 1000LL);
        long long k = rnd.next(1LL, 1000000LL);
        n = k * m;
    } else if (type == "max") {
        n = 1000000000LL;
        m = 1000000000LL;
    } else {
        n = rnd.next(1LL, 1000000000LL);
        m = rnd.next(1LL, 1000000000LL);
    }

    cout << n << " " << m << "\n";
    return 0;
}
