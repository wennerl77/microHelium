#include "testlib.h"
#include <iostream>
#include <string>

using namespace std;

int main(int argc, char* argv[]) {
    registerGen(argc, argv, 1);
    string type = opt<string>(1);

    long long n = 1;
    if (type == "manual") {
        n = opt<long long>(2);
    } else if (type == "small") {
        n = rnd.next(1LL, 100LL);
    } else if (type == "prime") {
        // Known big prime
        long long primes[] = {2, 3, 5, 7, 11, 13, 999999999989LL, 100000000003LL, 1000000007LL, 99999999997LL};
        n = primes[rnd.next(0, 9)];
    } else if (type == "square") {
        long long r = rnd.next(1LL, 1000000LL);
        n = r * r;
    } else if (type == "highly_composite") {
        // High number of divisors
        long long hc[] = {720720, 1441440, 2162160, 3603600, 7207200, 9979200, 1000000000000LL, 735134400000LL};
        n = hc[rnd.next(0, 7)];
    } else if (type == "max") {
        n = 1000000000000LL;
    } else {
        n = rnd.next(1LL, 1000000000000LL);
    }

    cout << n << "\n";
    return 0;
}
