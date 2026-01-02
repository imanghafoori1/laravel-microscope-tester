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

    //#[Test]
    //public function test_1()
    //{
    //    ClassAtMethodHandler::$fix = true;
    //    ClassAtMethodHandler::handle($file, $atSignTokens);
    //}

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
            ->expectsOutputToContain('➖  PSR-4')
            ->expectsOutputToContain('./app/')
            ->expectsOutputToContain('./database/factories/')
            ->expectsOutputToContain('./database/seeders/')
            ->expectsOutputToContain('./dev-classes/')
            ->expectsOutputToContain('App\\:')
            ->expectsOutputToContain('Database\\Factories\\:')
            ->expectsOutputToContain('Database\\Seeders\\:')
            ->expectsOutputToContain('Dev\\:')
            ->expectsOutputToContain('routes/web.php')
            ->expectsOutputToContain('Unused & wrong import:')
            ->expectsOutputToContain('Inline class Ref does not exist:')
            ->expectsOutputToContain("at dev-classes{$ds}Imports.php:6")
            ->expectsOutputToContain('config/ (10 files)')
            ->expectsOutputToContain('database/migrations/ (2 files)')
            ->expectsOutputToContain('references were checked, ')
            ->expectsOutputToContain("at dev-classes{$ds}Imports.php:7")
            ->expectsOutputToContain("at dev-classes{$ds}Imports.php:14")
            ->expectsOutputToContain("at dev-classes{$ds}Imports.php:15")
            ->expectsOutputToContain('App\User')
            ->expectsOutputToContain('App3')
            ->expectsOutputToContain('\Wrong')
            ->expectsOutputToContain('use App2;')
            ->run();

        $this->assertEquals(1, $status);
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
