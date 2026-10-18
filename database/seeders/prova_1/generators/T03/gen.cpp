#include "testlib.h"
#include <iostream>
#include <string>
#include <algorithm>

using namespace std;

int main(int argc, char* argv[]) {
    registerGen(argc, argv, 1);
    string type = opt<string>(1);
    int len = opt<int>(2, 10);

    string s = "", t = "";
    for (int i = 0; i < len; ++i) s += (char)rnd.next('a', 'z');

    if (type == "anagram") {
        t = s;
        shuffle(t.begin(), t.end());
    } else if (type == "diff_len") {
        t = s.substr(0, max(1, len - 1));
    } else if (type == "one_diff") {
        t = s;
        t[0] = (t[0] == 'z' ? 'a' : t[0] + 1);
        shuffle(t.begin(), t.end());
    } else {
        for (int i = 0; i < len; ++i) t += (char)rnd.next('a', 'z');
    }

    cout << s << "\n";
    cout << t << "\n";
    return 0;
}
