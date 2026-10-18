#include "testlib.h"
#include <iostream>
#include <string>

using namespace std;

int main(int argc, char* argv[]) {
    registerGen(argc, argv, 1);
    string type = opt<string>(1);

    string s = "";
    if (type == "manual") {
        s = opt<string>(2);
    } else if (type == "zero") {
        s = "0";
    } else if (type == "nines") {
        int len = opt<int>(2, 18);
        s = string(len, '9');
    } else if (type == "power10") {
        int zeros = opt<int>(2, 18);
        s = "1" + string(zeros, '0');
    } else {
        long long n = rnd.next(0LL, 1000000000000000000LL);
        s = to_string(n);
    }

    cout << s << "\n";
    return 0;
}
