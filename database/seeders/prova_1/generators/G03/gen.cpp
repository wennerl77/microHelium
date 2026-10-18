#include "testlib.h"
#include <iostream>
#include <vector>

using namespace std;

int main(int argc, char* argv[]) {
    registerGen(argc, argv, 1);
    string type = opt<string>(1);
    int n = opt<int>(2, 5);
    int m = opt<int>(3, 4);

    vector<pair<int, int>> edges;
    if (type == "empty") {
        m = 0;
    } else if (type == "star") {
        m = n - 1;
        for (int i = 2; i <= n; ++i) edges.push_back({1, i});
    } else if (type == "line") {
        m = n - 1;
        for (int i = 1; i < n; ++i) edges.push_back({i, i + 1});
    } else {
        for (int i = 0; i < m; ++i) {
            int u = rnd.next(1, n);
            int v = rnd.next(1, n);
            while (u == v && n > 1) v = rnd.next(1, n);
            edges.push_back({u, v});
        }
    }

    cout << n << " " << m << "\n";
    for (auto& e : edges) {
        cout << e.first << " " << e.second << "\n";
    }
    return 0;
}
