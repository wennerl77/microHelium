#include "testlib.h"
#include <iostream>
#include <string>

using namespace std;

int main(int argc, char* argv[]) {
    registerGen(argc, argv, 1);
    string type = opt<string>(1);

    long long n = 0, m = 1;

    if (type == "manual") {
        n = opt<long long>(2);
        m = opt<long long>(3);
    } else if (type == "zero") {
        n = 0;
        m = opt<long long>(2);
    } else if (type == "small") {
        n = rnd.next(0LL, 100LL);
        m = rnd.next(1LL, 100LL);
    } else if (type == "medium") {
        n = rnd.next(0LL, 1000000000LL);
        m = rnd.next(1LL, 1000000000LL);
    } else if (type == "max") {
        n = opt<long long>(2, 1000000000000000000LL);
        m = opt<long long>(3, 1000000000000000000LL);
    } else if (type == "n_less_m") {
        m = rnd.next(2LL, 1000000000000000000LL);
        n = rnd.next(0LL, m - 1);
    } else if (type == "n_eq_m") {
        n = m = rnd.next(1LL, 1000000000000000000LL);
    } else if (type == "mult") {
        m = rnd.next(1LL, 1000000000LL);
        long long k = rnd.next(0LL, 1000000000LL);
        n = k * m;
    } else {
        n = rnd.next(0LL, 1000000000000000000LL);
        m = rnd.next(1LL, 1000000000000000000LL);
    }

    cout << n << " " << m << "\n";
    return 0;
}
