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
    if (type == "full_palindrome") {
        int half = len / 2;
        string h = "";
        for (int i = 0; i < half; ++i) h += (char)rnd.next('a', 'z');
        string rev = h;
        reverse(rev.begin(), rev.end());
        s = h + (len % 2 == 1 ? string(1, (char)rnd.next('a', 'z')) : "") + rev;
    } else if (type == "all_same") {
        s = string(len, (char)rnd.next('a', 'z'));
    } else if (type == "no_palindrome") {
        for (int i = 0; i < len; ++i) s += (char)('a' + (i % 26));
    } else {
        for (int i = 0; i < len; ++i) s += (char)rnd.next('a', 'z');
    }

    cout << s << "\n";
    return 0;
}
