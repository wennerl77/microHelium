<?php

namespace Database\Factories;

use App\Models\Contest;
use App\Models\PolygonExercise;
use App\Models\Problem;
use Helium\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PolygonExercise>
 */
class PolygonExerciseFactory extends Factory
{
    protected $model = PolygonExercise::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->words(3, true);

        return [
            'contest_id' => null,
            'name' => ucfirst($name),
            'short_name' => 'A',
            'basename' => Str::slug($name),
            'time_limit' => 1,
            'memory_limit' => 256,
            'output_limit' => 1024,
            'description' => "# {$name}\n\nEnunciado do problema de teste.",
            'input_description' => 'A primeira linha contém dois inteiros N e M.',
            'output_description' => 'Imprima o resultado correspondente.',
            'notes' => '1 <= N, M <= 10^5',
            'color_name' => 'Azul',
            'color_hex' => '#3B82F6',
            'generator_source' => <<<'CPP'
#include "testlib.h"
#include <iostream>

using namespace std;

int main(int argc, char* argv[]) {
    registerGen(argc, argv, 1);
    long long n = atoll(argv[1]);
    long long m = atoll(argv[2]);
    cout << rnd.next(1LL, n) << " " << rnd.next(1LL, m) << endl;
    return 0;
}
CPP,
            'generator_script' => "gen 5 10 > $\ngen 10 50 > $\n",
            'manual_tests' => [
                [
                    'number' => 1,
                    'input' => "4 3\n",
                    'output' => "PAR\n1\n",
                    'is_sample' => true,
                ],
            ],
            'solution_accept' => <<<'CPP'
#include <iostream>

using namespace std;

int main() {
    long long n, m;
    if (cin >> n >> m) {
        cout << (n % 2 == 0 ? "PAR" : "IMPAR") << endl;
        cout << (n % m) << endl;
    }
    return 0;
}
CPP,
            'solution_tle' => null,
            'solution_wrong' => null,
            'additional_solutions' => null,
            'status' => PolygonExercise::STATUS_DRAFT,
            'calibration_results' => null,
            'published_problem_id' => null,
            'published_bank_code' => null,
            'created_by' => User::factory(),
        ];
    }

    public function forContest(Contest|int $contest): static
    {
        return $this->state(fn () => [
            'contest_id' => $contest instanceof Contest ? $contest->id : $contest,
        ]);
    }

    public function generated(): static
    {
        return $this->state(fn () => [
            'status' => PolygonExercise::STATUS_GENERATED,
        ]);
    }

    public function calibrated(): static
    {
        return $this->state(fn () => [
            'status' => PolygonExercise::STATUS_CALIBRATED,
            'calibration_results' => [
                'summary' => [
                    'total_tests' => 2,
                    'accept_passed' => true,
                    'max_time_seconds' => 0.02,
                    'max_memory_mb' => 12.5,
                ],
            ],
        ]);
    }

    public function published(?Problem $problem = null, ?string $bankCode = null): static
    {
        return $this->state(fn () => [
            'status' => PolygonExercise::STATUS_PUBLISHED,
            'published_problem_id' => $problem?->id,
            'published_bank_code' => $bankCode ?? 'POLY-001',
        ]);
    }
}
