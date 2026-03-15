<?php

use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\File;
use Imanghafoori\LaravelMicroscope\ErrorReporters\ErrorPrinter;
use Imanghafoori\LaravelMicroscope\Features\CheckExtraImports\Checks\CheckForExtraImports;
use Imanghafoori\LaravelMicroscope\Features\CheckExtraImports\Handlers\ExtraImportsHandler;
use Imanghafoori\LaravelMicroscope\Features\CheckImports\Checks\CheckClassReferencesAreValid;
use Imanghafoori\LaravelMicroscope\Features\CheckImports\Handlers\FixWrongClassRefs;
use Imanghafoori\LaravelMicroscope\Features\SearchReplace\CachedFiles;
use Imanghafoori\LaravelMicroscope\Foundations\ClassListProvider;
use Imanghafoori\LaravelMicroscope\Foundations\Color;
use Imanghafoori\LaravelMicroscope\Foundations\Console;
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

        Color::$color = false;
        Console::recoredWrites();
        ErrorPrinter::$instance = null;
        ErrorPrinter::$terminalWidth = 10;

        CheckForExtraImports::$importsCount = 0;
        ExtraImportsHandler::$count = 0;
        ImportsAnalyzer::$checkedRefCount = 0;
        ClassListProvider::$allNamespaces = [];
        @mkdir(base_path('dev-classes'));
        copy(__DIR__.'/CheckImportsStubs/imports.stub', base_path('dev-classes/Imports.php'));
    }

    protected function tearDown(): void
    {
        CheckForExtraImports::$importsCount = 0;
        ExtraImportsHandler::$count = 0;
        ImportsAnalyzer::$checkedRefCount = 0;
        ClassListProvider::$allNamespaces = [];
        CheckClassReferencesAreValid::$wrongClassRefsHandler = FixWrongClassRefs::class;

        @unlink(base_path('dev-classes/Imports.php'));
        @unlink(CachedFiles::getFolderPath().'check_extra_imports.php');
        @unlink(CachedFiles::getFolderPath().'check_imports.php');
        @rmdir(base_path('dev-classes'));

        parent::tearDown();
    }

    public function test_no_fix()
    {
        $ds = DIRECTORY_SEPARATOR;

        $this->artisan('check:imports --nofix')->run();

        $write = (Console::$instance)->writeln;
        array_pop($write);

        $this->assertEquals([
            "   1 Extra Import: User",
            "   4| use App\Models\User; // extra",
            "at dev-classes{$ds}Imports.php:4",
            "_______",
            "   2 Unused & wrong import:",
            "   6| use App2;",
            "at dev-classes{$ds}Imports.php:6",
            "_______",
            "   3 Unused & wrong import:",
            "   7| use App4;",
            "at dev-classes{$ds}Imports.php:7",
            "_______",
            "   4 Class Reference App\User does not exist:",
            "   9| class T extends Mooser",
            "at dev-classes{$ds}Imports.php:9",
            "_______",
            "   5 Class Reference App3 does not exist:",
            "   14| \t\tApp3::class;",
            "at dev-classes{$ds}Imports.php:14",
            "_______",
            "   6 Class Reference \Wrong does not exist:",
            "   15| \t\t\Wrong::class;",
            "at dev-classes{$ds}Imports.php:15",
            "_______",
            "   7 Class User2 does not exist:",
            "   'App\Models\User2@hello'",
            "at dev-classes{$ds}Imports.php:16",
            "_______",
            "   8 Method 'hello' does not exist:",
            "   App\Models\User@hello",
            "at dev-classes{$ds}Imports.php:17",
            "_______",
        ], $write);
    }

    public function test_folder_option_nothing_found()
    {
        $this->artisan('check:imports --folder=nothing')->expectsOutput('Checking imports and class references...')->expectsOutputToContain('No imports were found! with filter: "nothing"')->run();
    }

    #[Test]
    public function test_2()
    {
        $ds = DIRECTORY_SEPARATOR;
        // Create test files with incorrect/missing namespaces
        // Run the artisan command on our test directory
        $this->artisan('check:imports')->assertFailed()->run();

        $write = (Console::$instance)->writeln;
        array_pop($write);
        $this->assertEquals([
            "   1 Extra Import: User",
            "   4| use App\Models\User; // extra",
            "at dev-classes{$ds}Imports.php:4",
            "_______",
            "   2 Unused & wrong import:",
            "   6| use App2;",
            "at dev-classes{$ds}Imports.php:6",
            "_______",
            "   3 Unused & wrong import:",
            "   7| use App4;",
            "at dev-classes{$ds}Imports.php:7",
            "_______",
            "   4 Class User does not exist:",
            "   9| class T extends Mooser",
            "at dev-classes{$ds}Imports.php:9",
            "_______",
            "   5 Inline class ref 'App3' does not exist:",
            "   14| \t\tApp3::class;",
            "at dev-classes{$ds}Imports.php:14",
            "_______",
            "   6 Inline class ref 'Wrong' does not exist:",
            "   15| \t\t\Wrong::class;",
            "at dev-classes{$ds}Imports.php:15",
            "_______",
            "   7 Class User2 does not exist:",
            "   'App\Models\User2@hello'",
            "at dev-classes{$ds}Imports.php:16",
            "_______",
            "   8 Method 'hello' does not exist:",
            "   App\Models\User@hello",
            "at dev-classes{$ds}Imports.php:17",
            "_______",
        ], $write);
    }

    public function test_class_at_method_fix()
    {
        $mainPath = app_path('Models/stringy.php');
        copy(__DIR__.'/CheckImportsStubs/init-3.stub', $mainPath);

        $r = $this->artisan('check:imports')->run();
        $actual = file_get_contents($mainPath);
        @unlink($mainPath);

        $this->assertEquals(file_get_contents(__DIR__.'/CheckImportsStubs/expect-3.stub'), $actual);

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
