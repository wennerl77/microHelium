#include "testlib.h"
#include <iostream>
#include <string>
#include <algorithm>

using namespace std;

int main(int argc, char* argv[]) {
    registerGen(argc, argv, 1);
    string type = opt<string>(1);
    int len = opt<int>(2, 10);

    string s = "";
    if (type == "palindrome") {
        int half = len / 2;
        string h = "";
        for (int i = 0; i < half; ++i) h += (char)rnd.next('a', 'z');
        string rev = h;
        reverse(rev.begin(), rev.end());
        s = h + (len % 2 == 1 ? string(1, (char)rnd.next('a', 'z')) : "") + rev;
    } else if (type == "all_same") {
        char c = (char)rnd.next('a', 'z');
        s = string(len, c);
    } else {
        for (int i = 0; i < len; ++i) s += (char)rnd.next('a', 'z');
    }

    if (s.empty()) s = "a";
    cout << s << "\n";
    return 0;
}
