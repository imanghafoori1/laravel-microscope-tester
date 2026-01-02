<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Imanghafoori\ImportAnalyzer\ErrorReporters\ErrorPrinter;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Foundation\Testing\TestCase;

class CheckImportCommandTest extends TestCase
{
    protected $testDirectory;

    protected $stubPath;

    protected static $composerJson;

    protected function setUp(): void
    {
        parent::setUp();
        ErrorPrinter::$instance = null;
        @mkdir(base_path('dev-classes'));
        copy(
            __DIR__.'/CheckImportsStubs/imports.stub',
            base_path('dev-classes/Imports.php')
        );
    }

    protected function tearDown(): void
    {
        ErrorPrinter::$instance = null;

        unlink(base_path('dev-classes/Imports.php'));
        rmdir(base_path('dev-classes'));
        parent::tearDown();
    }

    #[Test]
    public function it_fixes_namespaces_in_php_files()
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
            ->expectsOutputToContain('references were checked, 8 errors found.')
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
    /*
    #[Test]
    public function no_file_found()
    {
        $this->artisan('check:imports', ['--folder' => 'rhello'])
            ->expectsOutputToContain('No imports were found!')
            ->run();
    }
*/

    #[Test]
    public function wrong_only()
    {
        $this->artisan('check:imports --wrong')
            ->expectsOutputToContain('\Wrong')
            ->run();

        $this->artisan('check:imports --extra')
            ->expectsOutputToContain('App4')
            ->run();
    }
}
