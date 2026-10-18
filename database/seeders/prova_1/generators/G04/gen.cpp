#include "testlib.h"
#include <iostream>
#include <iomanip>

using namespace std;

int main(int argc, char* argv[]) {
    registerGen(argc, argv, 1);
    string type = opt<string>(1);

    double h = 1.0, v = 1.0, g = 9.8;
    if (type == "min") {
        h = 0.01; v = 0.01; g = 0.01;
    } else if (type == "max") {
        h = 1000000.0; v = 10000.0; g = 10000.0;
    } else {
        h = rnd.next(0.01, 1000000.0);
        v = rnd.next(0.01, 10000.0);
        g = rnd.next(0.01, 10000.0);
    }

    cout << fixed << setprecision(6) << h << " " << v << " " << g << "\n";
    return 0;
}
