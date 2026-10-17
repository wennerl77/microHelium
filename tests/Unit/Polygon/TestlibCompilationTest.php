<?php

namespace Tests\Unit\Polygon;

use Tests\TestCase;

class TestlibCompilationTest extends TestCase
{
    private string $tempDir;
    private string $includePath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tempDir = sys_get_temp_dir() . '/polygon_testlib_' . uniqid();
        @mkdir($this->tempDir, 0755, true);
        $this->includePath = base_path('resources/judge-runtime/include');
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->tempDir);
        parent::tearDown();
    }

    public function test_testlib_header_exists_and_is_not_empty(): void
    {
        $testlibPath = $this->includePath . '/testlib.h';

        $this->assertFileExists($testlibPath, 'O arquivo testlib.h deve existir em resources/judge-runtime/include/');
        $this->assertGreaterThan(10000, filesize($testlibPath), 'O arquivo testlib.h não deve estar vazio.');

        $content = file_get_contents($testlibPath);
        $this->assertStringContainsString('registerGen', $content, 'O arquivo testlib.h deve conter registerGen.');
        $this->assertStringContainsString('rnd.next', $content, 'O arquivo testlib.h deve conter rnd.next.');
    }

    public function test_can_compile_cpp_generator_with_testlib(): void
    {
        $this->ensureGppAvailable();

        $sourceFile = $this->tempDir . '/gen.cpp';
        $binaryFile = $this->tempDir . '/gen';

        file_put_contents($sourceFile, $this->getSampleGeneratorSource());

        $cmd = sprintf(
            'g++ -O2 -std=c++20 %s -I %s %s -o %s 2>&1',
            $this->getStaticFlag(),
            escapeshellarg($this->includePath),
            escapeshellarg($sourceFile),
            escapeshellarg($binaryFile)
        );

        exec($cmd, $output, $returnCode);

        $this->assertSame(0, $returnCode, 'A compilação do gen.cpp com testlib.h falhou: ' . implode("\n", $output));
        $this->assertFileExists($binaryFile, 'O executável gen compilado deve existir.');
        $this->assertTrue(is_executable($binaryFile), 'O arquivo gen deve ter permissão de execução.');
    }

    public function test_generator_executes_and_outputs_within_bounds(): void
    {
        $this->ensureGppAvailable();

        $sourceFile = $this->tempDir . '/gen.cpp';
        $binaryFile = $this->tempDir . '/gen';

        file_put_contents($sourceFile, $this->getSampleGeneratorSource());

        $cmd = sprintf(
            'g++ -O2 -std=c++20 %s -I %s %s -o %s 2>&1',
            $this->getStaticFlag(),
            escapeshellarg($this->includePath),
            escapeshellarg($sourceFile),
            escapeshellarg($binaryFile)
        );

        exec($cmd, $compileOutput, $compileReturn);
        $this->assertSame(0, $compileReturn, 'Falha ao compilar: ' . implode("\n", $compileOutput));

        // Executa com limites: limite_n = 5, limite_m = 10
        $execCmd = escapeshellarg($binaryFile) . ' 5 10 2>&1';
        exec($execCmd, $runOutput, $runReturn);

        $this->assertSame(0, $runReturn, 'A execução de ./gen 5 10 falhou: ' . implode("\n", $runOutput));
        $this->assertNotEmpty($runOutput, 'A saída do gerador não deve estar vazia.');

        $outputLine = trim(implode(' ', $runOutput));
        $parts = preg_split('/\s+/', $outputLine);

        $this->assertCount(2, $parts, 'O gerador deve emitir 2 números: n e m.');
        $n = (int) $parts[0];
        $m = (int) $parts[1];

        $this->assertGreaterThanOrEqual(0, $n, "n ($n) deve ser >= 0.");
        $this->assertLessThanOrEqual(5, $n, "n ($n) deve ser <= 5.");

        $this->assertGreaterThanOrEqual(1, $m, "m ($m) deve ser >= 1.");
        $this->assertLessThanOrEqual(10, $m, "m ($m) deve ser <= 10.");
    }

    public function test_generator_produces_deterministic_output(): void
    {
        $this->ensureGppAvailable();

        $sourceFile = $this->tempDir . '/gen.cpp';
        $binaryFile = $this->tempDir . '/gen';

        file_put_contents($sourceFile, $this->getSampleGeneratorSource());

        $cmd = sprintf(
            'g++ -O2 -std=c++20 %s -I %s %s -o %s 2>&1',
            $this->getStaticFlag(),
            escapeshellarg($this->includePath),
            escapeshellarg($sourceFile),
            escapeshellarg($binaryFile)
        );

        exec($cmd, $compileOutput, $compileReturn);
        $this->assertSame(0, $compileReturn, 'Falha ao compilar: ' . implode("\n", $compileOutput));

        // Executa 2 vezes com os mesmos argumentos para testar determinismo do testlib
        exec(escapeshellarg($binaryFile) . ' 100 500', $run1, $code1);
        exec(escapeshellarg($binaryFile) . ' 100 500', $run2, $code2);

        $this->assertSame(0, $code1);
        $this->assertSame(0, $code2);
        $this->assertSame($run1, $run2, 'O gerador com testlib.h registerGen deve ser estritamente determinístico.');
    }

    public function test_cleans_up_temporary_files(): void
    {
        $tempPath = $this->tempDir;
        $this->assertDirectoryExists($tempPath);

        // Dispara o tearDown manualmente para checar a limpeza
        $this->removeDirectory($tempPath);
        $this->assertDirectoryDoesNotExist($tempPath);
    }

    private function getSampleGeneratorSource(): string
    {
        return <<<'CPP'
#include "testlib.h"
#include <iostream>

using namespace std;

int main(int argc, char* argv[]) {
    registerGen(argc, argv, 1);

    long long limite_n = atoll(argv[1]);
    long long limite_m = atoll(argv[2]);

    long long n = rnd.next(0LL, limite_n);
    long long m = rnd.next(1LL, limite_m); 

    cout << n << " " << m << endl;
    return 0;
}
CPP;
    }

    private function ensureGppAvailable(): void
    {
        exec('which g++ 2>/dev/null', $output, $code);
        if ($code !== 0) {
            $this->markTestSkipped('g++ não está instalado no ambiente de teste.');
        }
    }

    private function getStaticFlag(): string
    {
        static $staticSupported = null;

        if ($staticSupported === null) {
            exec('echo "int main(){}" | g++ -x c++ - -static -o /dev/null 2>/dev/null', $out, $code);
            $staticSupported = ($code === 0);
        }

        return $staticSupported ? '-static' : '';
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = scandir($dir);
        if ($files === false) {
            return;
        }

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                @unlink($path);
            }
        }

        @rmdir($dir);
    }
}
