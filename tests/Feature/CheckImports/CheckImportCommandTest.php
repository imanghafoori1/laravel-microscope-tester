<?php

use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\File;
use Imanghafoori\LaravelMicroscope\Features\CheckExtraImports\Checks\CheckForExtraImports;
use Imanghafoori\LaravelMicroscope\Features\CheckExtraImports\Handlers\ExtraImportsHandler;
use Imanghafoori\LaravelMicroscope\Features\CheckImports\Checks\CheckClassReferencesAreValid;
use Imanghafoori\LaravelMicroscope\Features\CheckImports\Handlers\FixWrongClassRefs;
use Imanghafoori\LaravelMicroscope\Foundations\ClassListProvider;
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
        CheckForExtraImports::$importsCount = 0;
        ExtraImportsHandler::$count = 0;
        ImportsAnalyzer::$checkedRefCount = 0;
        ClassListProvider::$allNamespaces = [];
        @mkdir(base_path('dev-classes'));
        copy(
            __DIR__.'/CheckImportsStubs/imports.stub',
            base_path('dev-classes/Imports.php')
        );
    }

    protected function tearDown(): void
    {
        CheckForExtraImports::$importsCount = 0;
        ExtraImportsHandler::$count = 0;
        ImportsAnalyzer::$checkedRefCount = 0;
        ClassListProvider::$allNamespaces = [];
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

    public function test_folder_option_nothing_found()
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
            ->expectsOutputToContain('    ➖  App\\:')
            ->expectsOutputToContain('    ➖  Database\\Factories\\:')
            ->expectsOutputToContain('    ➖  Database\\Seeders\\:')
            ->expectsOutputToContain('    ➖  Dev\\:')
            ->expectsOutputToContain('    ➖  routes/web.php')
            ->expectsOutputToContain('config/ (10 files)')
            ->expectsOutputToContain('database/migrations/ (2 files)')
            ->expectsOutputToContain('19 references were checked, 7 errors found.')
            ->expectsOutput(' 🔸 2 wrong imports found.')
            ->expectsOutput(' 🔸 3 wrong class references found.')
            ->expectsOutput(' 🔸 3 extra imports found.')
            //
            // ---------------------------------------------------------
            ->expectsOutput('   1 Extra Import: User')
            ->expectsOutput('   4| use App\Models\User; // extra')
            ->expectsOutput("at dev-classes{$ds}Imports.php:4")
            // ---------------------------------------------------------
            ->expectsOutput("   2 Unused & wrong import:")
            ->expectsOutput("   6| use App2;")
            ->expectsOutput("at dev-classes{$ds}Imports.php:6")
            // ---------------------------------------------------------
            ->expectsOutput("   3 Unused & wrong import:")
            ->expectsOutput('   7| use App4;')
            ->expectsOutput("at dev-classes{$ds}Imports.php:7")
            // ---------------------------------------------------------
            ->expectsOutput('   4 Class User does not exist:')
            ->expectsOutputToContain("   9| class T extends Mooser")
            ->expectsOutput("at dev-classes{$ds}Imports.php:9")
            // ---------------------------------------------------------
            ->expectsOutput("   5 Inline class ref 'App3' does not exist:")
            ->expectsOutput("   14| 		App3::class;")
            ->expectsOutputToContain("at dev-classes{$ds}Imports.php:14")
            // ---------------------------------------------------------
            ->expectsOutput("   6 Inline class ref 'Wrong' does not exist:")
            ->expectsOutputToContain("\Wrong::class;")
            ->expectsOutput("at dev-classes{$ds}Imports.php:15")
            // ---------------------------------------------------------
            ->expectsOutput("   7 Class User2 does not exist:")
            ->expectsOutput("   'App\Models\User2@hello'")
            ->expectsOutput("at dev-classes{$ds}Imports.php:16")
            // ---------------------------------------------------------
            ->expectsOutput("   8 Method 'hello' does not exist:")
            ->expectsOutput("   App\Models\User@hello")
            ->expectsOutput("at dev-classes{$ds}Imports.php:17")
            //
            ->run();

        $this->assertEquals(1, $status);
    }

    public function test_class_at_method_fix()
    {
        $mainPath = app_path('Models/stringy.php');
        copy(__DIR__.'/CheckImportsStubs/init-3.stub', $mainPath);

        $r = $this->artisan('check:imports')->run();
        $actual = file_get_contents($mainPath);
        @unlink($mainPath);

        $this->assertEquals(
            file_get_contents(__DIR__.'/CheckImportsStubs/expect-3.stub'),
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
