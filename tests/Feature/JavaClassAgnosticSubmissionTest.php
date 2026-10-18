<?php

namespace Tests\Feature;

use App\Models\Contest;
use App\Models\Language;
use App\Models\Problem;
use App\Models\Run;
use App\Models\Site;
use App\Services\RunSubmissionService;
use Helium\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class JavaClassAgnosticSubmissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();
        Storage::fake('local');
    }

    private function setupContestEnvironment(string $extension = 'java21'): array
    {
        $site = Site::factory()->create();
        $contest = $site->contest;
        $contest->update([
            'is_active' => true,
            'start_time' => now()->subMinute(),
        ]);

        $problem = Problem::factory()->create(['contest_id' => $contest->id]);
        $language = Language::factory()->create([
            'contest_id' => $contest->id,
            'name' => 'Java (OpenJDK 21 LTS)',
            'extension' => $extension,
            'compile_command' => '/usr/lib/jvm/java-21-openjdk/bin/javac {source}',
            'run_command' => '/usr/lib/jvm/java-21-openjdk/bin/java -Xss8m -Xmx{memory}m {classname}',
            'is_active' => true,
        ]);
        $user = User::factory()->create([
            'site_id' => $site->id,
            'contest_id' => $contest->id,
        ]);

        return [$contest, $problem, $language, $user];
    }

    public function test_java_submission_with_public_class_main_generates_capitalized_main_java(): void
    {
        [, $problem, $language, $user] = $this->setupContestEnvironment();

        $sourceCode = <<<'JAVA'
/******************************************************************************
Online Java Compiler.
*******************************************************************************/
import java.util.Scanner;

public class Main
{
    public static void main(String[] args) {
        Scanner s = new Scanner(System.in);
        System.out.println("Hello");
    }
}
JAVA;

        $response = $this->actingAs($user)->post("/submit/{$problem->id}", [
            'language_id' => $language->id,
            'code_text' => $sourceCode,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('submissions'));

        $run = Run::query()->latest('id')->firstOrFail();
        $this->assertSame('Main.java', $run->filename);
    }

    public function test_java_submission_with_custom_public_class_generates_matching_filename(): void
    {
        [, $problem, $language, $user] = $this->setupContestEnvironment();

        $sourceCode = <<<'JAVA'
import java.util.Scanner;

public class SolucaoExercicio
{
    public static void main(String[] args) {
        System.out.println(42);
    }
}
JAVA;

        $response = $this->actingAs($user)->post("/submit/{$problem->id}", [
            'language_id' => $language->id,
            'code_text' => $sourceCode,
        ]);

        $response->assertSessionHasNoErrors();

        $run = Run::query()->latest('id')->firstOrFail();
        $this->assertSame('SolucaoExercicio.java', $run->filename);
    }

    public function test_java_submission_without_public_modifier_identifies_main_class(): void
    {
        [, $problem, $language, $user] = $this->setupContestEnvironment();

        $sourceCode = <<<'JAVA'
class Helper
{
    void doSomething() {}
}

class MeuPrograma
{
    public static void main(String[] args) {
        System.out.println("OK");
    }
}
JAVA;

        $response = $this->actingAs($user)->post("/submit/{$problem->id}", [
            'language_id' => $language->id,
            'code_text' => $sourceCode,
        ]);

        $response->assertSessionHasNoErrors();

        $run = Run::query()->latest('id')->firstOrFail();
        $this->assertSame('MeuPrograma.java', $run->filename);
    }

    public function test_java_submission_ignores_comments_and_strings_with_fake_class_declarations(): void
    {
        [, $problem, $language, $user] = $this->setupContestEnvironment();

        $sourceCode = <<<'JAVA'
/*
 * Exemplo de uso:
 * public class ClasseDeComentario {
 *     public static void main(String[] args) {}
 * }
 */
// public class FakeSingleLineClass { public static void main }

public class ClasseVerdadeira
{
    String teste = "public class ClasseEmString { public static void main }";

    public static void main(String[] args) {
        System.out.println("Real");
    }
}
JAVA;

        $response = $this->actingAs($user)->post("/submit/{$problem->id}", [
            'language_id' => $language->id,
            'code_text' => $sourceCode,
        ]);

        $response->assertSessionHasNoErrors();

        $run = Run::query()->latest('id')->firstOrFail();
        $this->assertSame('ClasseVerdadeira.java', $run->filename);
    }

    public function test_java_file_upload_with_lowercase_main_normalizes_to_capital_main(): void
    {
        [, $problem, $language, $user] = $this->setupContestEnvironment();

        $file = UploadedFile::fake()->createWithContent(
            'main.java',
            "public class Main { public static void main(String[] args) {} }"
        );

        $response = $this->actingAs($user)->post("/submit/{$problem->id}", [
            'language_id' => $language->id,
            'source_file' => $file,
        ]);

        $response->assertSessionHasNoErrors();

        $run = Run::query()->latest('id')->firstOrFail();
        $this->assertSame('Main.java', $run->filename);
    }

    public function test_non_java_languages_retain_standard_filenames(): void
    {
        $languagesToTest = [
            'c' => 'main.c',
            'cpp' => 'main.cpp',
            'py3' => 'main.py',
            'js_node24' => 'main.js',
        ];

        foreach ($languagesToTest as $ext => $expectedFilename) {
            $site = Site::factory()->create();
            $contest = $site->contest;
            $contest->update(['is_active' => true, 'start_time' => now()->subMinute()]);

            $problem = Problem::factory()->create(['contest_id' => $contest->id]);
            $language = Language::factory()->create([
                'contest_id' => $contest->id,
                'extension' => $ext,
                'is_active' => true,
            ]);
            $user = User::factory()->create([
                'site_id' => $site->id,
                'contest_id' => $contest->id,
            ]);

            $response = $this->actingAs($user)->post("/submit/{$problem->id}", [
                'language_id' => $language->id,
                'code_text' => '// code',
            ]);

            $response->assertSessionHasNoErrors();

            $run = Run::query()->latest('id')->firstOrFail();
            $this->assertSame($expectedFilename, $run->filename);
        }
    }

    public function test_java_class_detection_unit_directly(): void
    {
        $service = app(RunSubmissionService::class);

        // Public record
        $this->assertSame('MeuRecord', $service->detectJavaMainClass('public record MeuRecord(int x) {}'));

        // Public final class
        $this->assertSame('MinhaFinal', $service->detectJavaMainClass('public final class MinhaFinal {}'));

        // Sealed class
        $this->assertSame('MinhaSealed', $service->detectJavaMainClass('public sealed class MinhaSealed permits Outra {}'));

        // Empty code
        $this->assertNull($service->detectJavaMainClass(''));

        // Non-java code
        $this->assertNull($service->detectJavaMainClass('int main() { return 0; }'));
    }
}
