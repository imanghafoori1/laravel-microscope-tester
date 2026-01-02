<?php

use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\File;
use Imanghafoori\LaravelMicroscope\Foundations\Color;
use PHPUnit\Framework\Attributes\Test;

class CheckExtraImportCommandTest extends TestCase
{
    protected $testDirectory;

    protected $stubPath;

    protected static $composerJson;

    protected function setUp(): void
    {
        parent::setUp();
        Color::$color = false;
        @mkdir(base_path('dev-classes'));
        copy(
            __DIR__.'/CheckExtraImportsStubs/imports.stub',
            base_path('dev-classes/Imports.php')
        );
    }

    protected function tearDown(): void
    {
        Color::$color = true;
        @unlink(base_path('dev-classes/Imports.php'));
        @rmdir(base_path('dev-classes'));
        parent::tearDown();
    }

    #[Test]
    public function it_finds_extra_imports()
    {
        $ds = DIRECTORY_SEPARATOR;
        // Create test files with incorrect/missing namespaces
        // Run the artisan command on our test directory
        $status = $this->artisan('check:extra_imports')
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
            ->expectsOutputToContain('30 references were checked, 5 errors found.')
            //
            ->expectsOutputToContain('   ➖  config/ (10 files)')
            ->expectsOutputToContain('   ➖  database/migrations/ (2 files)')
            ->expectsOutputToContain('class_map/ (1 file)')
            ->expectsOutputToContain('Autoloaded files')
            //
            ->expectsOutputToContain('_____________')
            ->expectsOutputToContain('1 Extra Import:')
            ->expectsOutputToContain('App\Models\User')
            ->expectsOutputToContain("at dev-classes{$ds}Imports.php:4")
            //
            ->expectsOutputToContain('2 Extra Import:')
            ->expectsOutputToContain('App2')
            ->expectsOutputToContain("at dev-classes{$ds}Imports.php:6")
            //
            ->expectsOutputToContain('3 Extra Import:')
            ->expectsOutputToContain('App4')
            ->expectsOutputToContain("at dev-classes{$ds}Imports.php:7")
            //
            ->expectsOutputToContain('4 Extra Import:')
            ->expectsOutputToContain('App\Http\A')
            ->expectsOutputToContain("at dev-classes{$ds}Imports.php:8")
            //
            ->expectsOutputToContain('5 Extra Import:')
            ->expectsOutputToContain('App\Http\B')
            //
            ->run();

        $this->assertEquals(1, $status);
    }

    #[Test]
    public function no_file_found()
    {
        $status = $this->artisan('check:extra_imports --folder=ewfee')
            ->expectsOutput('Checking imports and class references...')
            ->expectsOutputToContain('No imports were found! with filter: "ewfee"')
            ->run();

        $this->assertEquals(0, $status);
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
