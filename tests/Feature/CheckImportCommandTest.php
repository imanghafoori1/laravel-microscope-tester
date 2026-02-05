<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\File;
use Imanghafoori\LaravelMicroscope\Features\CheckImports\Checks\CheckClassReferencesAreValid;
use Imanghafoori\LaravelMicroscope\Features\CheckImports\Handlers\FixWrongClassRefs;
use Imanghafoori\TokenAnalyzer\ImportsAnalyzer;
use PHPUnit\Framework\Attributes\Test;

class CheckImportCommandTest extends TestCase
{
    protected $testDirectory;

    protected $stubPath;

    protected static $composerJson;

    protected function setUp(): void
    {
        parent::setUp();
        @mkdir(base_path('dev-classes'));
        copy(
            __DIR__.'/CheckImportsStubs/imports.stub',
            base_path('dev-classes/Imports.php')
        );
    }

    protected function tearDown(): void
    {
        ImportsAnalyzer::$checkedRefCount = 0;
        CheckClassReferencesAreValid::$wrongClassRefsHandler = FixWrongClassRefs::class;
        @unlink(base_path('dev-classes/Imports.php'));
        @rmdir(base_path('dev-classes'));
        parent::tearDown();
    }
    public function test_no_fix()
    {
        $ds = DIRECTORY_SEPARATOR;

        $this->artisan('check:imports --nofix')
            ->expectsOutput('Checking imports and class references...')
            ->expectsOutputToContain("at dev-classes{$ds}Imports.php:7")
            ->expectsOutputToContain("at dev-classes{$ds}Imports.php:14")
            ->expectsOutputToContain("at dev-classes{$ds}Imports.php:15")
            ->run();
    }

    public function test_folder()
    {
        $this->artisan('check:imports --folder=nothing')
            ->expectsOutput('Checking imports and class references...')
            ->expectsOutputToContain('No imports were found! with filter: "nothing"')
            ->run();
    }

    #[Test]
    public function test_2()
    {
        $ds = DIRECTORY_SEPARATOR;
        // Create test files with incorrect/missing namespaces
        // Run the artisan command on our test directory
        $status = $this->artisan('check:imports')
            ->expectsOutput('Checking imports and class references...')
            ->expectsOutput('Imports were checked under:')
            ->expectsOutputToContain('./composer.json')
            ->expectsOutputToContain(' ⬛️ Overall:')
            ->expectsOutputToContain('➖  PSR-4')
            ->expectsOutputToContain('./app/')
            ->expectsOutputToContain('./database/factories/')
            ->expectsOutputToContain('./database/seeders/')
            ->expectsOutputToContain('./dev-classes/')
            ->expectsOutputToContain('   ➖  App\\:')
            ->expectsOutputToContain('    ➖  Database\\Factories\\:')
            ->expectsOutputToContain('    ➖  Database\\Seeders\\:')
            ->expectsOutputToContain('    ➖  Dev\\:')
            ->expectsOutputToContain('    ➖  routes/web.php')
            ->expectsOutputToContain('config/ (10 files)')
            ->expectsOutputToContain('database/migrations/ (2 files)')
            ->expectsOutputToContain('29 references were checked, 5 errors found.')
            ->expectsOutputToContain('🔸 2 wrong imports found.')
            ->expectsOutputToContain('🔸 3 wrong class references found.')
            //
            ->expectsOutputToContain("at dev-classes{$ds}Imports.php:6")
            ->expectsOutputToContain("at dev-classes{$ds}Imports.php:7")
            ->expectsOutputToContain("at dev-classes{$ds}Imports.php:9")
            ->expectsOutputToContain("at dev-classes{$ds}Imports.php:14")
            ->expectsOutputToContain("at dev-classes{$ds}Imports.php:15")
            ->expectsOutputToContain("at dev-classes{$ds}Imports.php:16")
            ->expectsOutputToContain("at dev-classes{$ds}Imports.php:17")
            //
            ->expectsOutputToContain("App\Models\User@hello")
            ->expectsOutputToContain("'App\\Models\\User2@hello'")
            ->expectsOutputToContain('App\User')
            ->expectsOutputToContain('App3')
            ->expectsOutputToContain('use App2;')
            ->expectsOutputToContain('use App4;')
            ->expectsOutputToContain('\Wrong')
            //
            ->expectsOutputToContain('1 Class does not exist:')
            ->expectsOutputToContain('2 Inline class Ref does not exist:')
            ->expectsOutputToContain('3 Inline class Ref does not exist:')
            ->expectsOutputToContain('4 Unused & wrong import:')
            ->expectsOutputToContain('5 Unused & wrong import:')
            ->expectsOutputToContain('6 Class does not exist:')
            ->expectsOutputToContain('7 Method does not exist:')
            ->run();

        $this->assertEquals(1, $status);
    }

    public function test_3()
    {
        $mainPath = app_path('Models/stringy.php');
        copy(__DIR__.'/CheckStringyClassStubs/init-3.stub', $mainPath);

        $r = $this->artisan('check:imports')->run();
        $actual = file_get_contents($mainPath);
        @unlink($mainPath);

        $this->assertEquals(
            file_get_contents(__DIR__.'/CheckStringyClassStubs/expect-3.stub'),
            $actual
        );

        $this->assertEquals(1, $r);
    }

    protected function createTestFiles(): void
    {
        mkdir($this->testDirectory.'/SubDir');
        // File 1: No namespace
        $content1 = file_get_contents(__DIR__.'/Psr4Tests/initial/TestClassWithoutNamespace.stub');
        File::put($this->testDirectory.'/SubDir/TestClassWithoutNamespace.php', $content1);

        copy(__DIR__.'/Psr4Tests/initial/OldRef.stub', app_path('Models/Ref.php'));
    }
}
