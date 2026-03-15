<?php

use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\File;
use Imanghafoori\LaravelMicroscope\ErrorReporters\ErrorPrinter;
use Imanghafoori\LaravelMicroscope\Features\SearchReplace\CachedFiles;
use Imanghafoori\LaravelMicroscope\Foundations\Color;
use Imanghafoori\LaravelMicroscope\Foundations\Console;
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
        Console::recoredWrites();
        ErrorPrinter::$instance = null;
        ErrorPrinter::$terminalWidth = 10;

        @unlink($this->getCacheFilePath());
        @mkdir(base_path('dev-classes'));
        copy(
            __DIR__.'/CheckExtraImportsStubs/imports.stub',
            base_path('dev-classes/Imports.php')
        );
    }

    protected function tearDown(): void
    {
        Console::reset();
        ErrorPrinter::$instance = null;

        @unlink($this->getCacheFilePath());
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
        $status = $this->artisan('check:extra_imports')->run();

        $write = (Console::$instance)->writeln;
        array_pop($write);

        $msg = (Console::$instance)->msg;
        $this->assertEquals('15 imports were checked.', $msg[42]);
        $this->assertEquals(' 🔸 5 unused imports found.', $msg[44]);

        $this->assertEquals([
            "   1 Extra Import: User",
            "   4| use App\Models\User;",
            "at dev-classes{$ds}Imports.php:4",
            "_______",
            "   2 Extra Import: App2",
            "   6| use App2; // extra",
            "at dev-classes{$ds}Imports.php:6",
            "_______",
            "   3 Extra Import: App4",
            "   7| use App4;",
            "at dev-classes{$ds}Imports.php:7",
            "_______",
            "   4 Extra Import: A",
            "   8| use App\Http\{A,B};",
            "at dev-classes{$ds}Imports.php:8",
            "_______",
            "   5 Extra Import: B",
            "   8| use App\Http\{A,B};",
            "at dev-classes{$ds}Imports.php:8",
            "_______",
        ], $write);

        $this->assertEquals(1, $status);
        $this->assertFileExists($this->getCacheFilePath());
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

    private function getCacheFilePath()
    {
        return CachedFiles::getFolderPath().'check_extra_imports.php';
    }
}
