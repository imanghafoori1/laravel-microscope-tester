<?php

use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Composer;
use Illuminate\Support\Facades\File;
use Imanghafoori\LaravelMicroscope\ErrorReporters\ErrorPrinter;
use Imanghafoori\LaravelMicroscope\Features\Psr4\Console\NamespaceFixer\NamespaceFixerMessages;
use Imanghafoori\LaravelMicroscope\Foundations\Color;
use Imanghafoori\LaravelMicroscope\Foundations\Console;
use PHPUnit\Framework\Attributes\Test;

class FixNamespaceCommandTest extends TestCase
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

        $composerJson = self::$composerJson = file_get_contents(base_path('composer.json'));
        $newComposer = str_replace('"App\\\\": "app/",', '"App\\\\": "app/", "Models\\\\": "app/Models",', $composerJson);
        file_put_contents(base_path('composer.json'), $newComposer);

        // Create test directory structure
        $this->testDirectory = base_path('app/TestNamespaceFixer');
        $this->stubPath = base_path('tests/Stubs');

        // Clean up any previous test directory
        $this->cleanUpTestDirectory();

        // Create fresh test directory
        File::makeDirectory($this->testDirectory, 0755, true);

        // Create stub directory if it doesn't exist
        if (! File::exists($this->stubPath)) {
            File::makeDirectory($this->stubPath, 0755, true);
        }
    }

    protected function tearDown(): void
    {
        // Clean up after tests
        $this->cleanUpTestDirectory();
        file_put_contents(base_path('composer.json'), self::$composerJson);
        Console::reset();

        parent::tearDown();
    }

    #[Test]
    public function it_detects_wrong_namespaces_in_php_files()
    {
        // Create test files with incorrect/missing namespaces
        $this->createTestFiles();
        $this->mock(Composer::class)->shouldReceive('dumpAutoloads')->once();
        NamespaceFixerMessages::$pause = 20;
        Console::$pause = 70;

        $this->artisan('check:psr4 --nofix')->run();

        $write = (Console::$instance)->writeln;
        array_pop($write);
        $ds = DIRECTORY_SEPARATOR;
        $br = PHP_EOL;
        $this->assertEquals([
            0 => "   1 The file name and the class name are different.",
            1 => "   Class name: G$br   File name:  BadFileName.php",
            2 => "at app{$ds}BadFileName.php:1",
            3 => "_______",
            4 => '   2  Namespace of class "Wrong\Namespace\TestClassWithWrongNamespace" should be:',
            5 => "   Models",
            6 => "at app{$ds}Models{$ds}TestClassWithWrongNamespaceDeclare.php:4",
            7 => "_______",
            8 => '   3  Namespace of class "\TestClassWithoutNamespace" should be:',
            9 => "   App\TestNamespaceFixer\SubDir",
            10 => "at app{$ds}TestNamespaceFixer{$ds}SubDir{$ds}TestClassWithoutNamespace.php:4",
            11 => "_______",
            12 => '   4  Namespace of class "\TestClassDeclareWithoutNamespace" should be:',
            13 => "   App\TestNamespaceFixer",
            14 => "at app{$ds}TestNamespaceFixer{$ds}TestClassDeclareWithoutNamespace.php:4",
            15 => "_______",
            16 => "   5  Namespace of class \"Wrong\Namespace\TestClassWithWrongNamespace\" should be:",
            17 => "   App\TestNamespaceFixer",
            18 => "at app{$ds}TestNamespaceFixer{$ds}TestClassWithWrongNamespace.php:4",
        ], $write);
    }

    #[Test]
    public function it_fixes_namespaces_in_php_files()
    {
        // Create test files with incorrect/missing namespaces
        $this->createTestFiles();
        $this->mock(Composer::class)->shouldReceive('dumpAutoloads')->once();
        NamespaceFixerMessages::$pause = 0;
        Console::$pause = 0;

        Console::enforceTrue();

        // Run the artisan command on our test directory
        $this->artisan('check:psr4')->run();

        $ds = DIRECTORY_SEPARATOR;
        $write = (Console::$instance)->writeln;
        array_pop($write);

        $this->assertEquals([
            "Incorrect namespace: 'Wrong\Namespace'",
            "at app{$ds}Models{$ds}TestClassWithWrongNamespaceDeclare.php:3",
            "Namespace updated to: Models",
            "Searching for old references...",
            "at ".ltrim(base_path("app{$ds}Models{$ds}Ref.php"), '/\\').':3',
            "\Wrong\Namespace\TestClassWithWrongNamespace::class;\n",
            "Namespace Not Found for class: TestClassWithoutNamespace",
            "at app{$ds}TestNamespaceFixer{$ds}SubDir{$ds}TestClassWithoutNamespace.php:3",
            "Namespace updated to: App\TestNamespaceFixer\SubDir",
            "Searching for old references...",
            "Namespace Not Found for class: TestClassDeclareWithoutNamespace",
            "at app{$ds}TestNamespaceFixer{$ds}TestClassDeclareWithoutNamespace.php:3",
            "Namespace updated to: App\TestNamespaceFixer",
            "Searching for old references...",
            "Incorrect namespace: 'Wrong\Namespace'",
            "at app{$ds}TestNamespaceFixer{$ds}TestClassWithWrongNamespace.php:3",
            "Namespace updated to: App\TestNamespaceFixer",
            "Searching for old references...",
            "   1 The file name and the class name are different.",
            "   Class name: G".PHP_EOL."   File name:  BadFileName.php",
            "at app{$ds}BadFileName.php:1",
            "_______",
            "   2 Namespace replacement:",
            "   ",
            "at app{$ds}Models{$ds}Ref.php:3",
            "_______",
            '   3  Namespace of class "TestClassWithWrongNamespace" fixed to:',
            "   Models",
            "at app{$ds}Models{$ds}TestClassWithWrongNamespaceDeclare.php:4",
            "_______",
            '   4  Namespace of class "TestClassWithoutNamespace" fixed to:',
            "   App\TestNamespaceFixer\SubDir",
            "at app{$ds}TestNamespaceFixer{$ds}SubDir{$ds}TestClassWithoutNamespace.php:4",
            "_______",
            '   5  Namespace of class "TestClassDeclareWithoutNamespace" fixed to:',
            "   App\TestNamespaceFixer",
            "at app{$ds}TestNamespaceFixer{$ds}TestClassDeclareWithoutNamespace.php:4",
            "_______",
            '   6  Namespace of class "TestClassWithWrongNamespace" fixed to:',
            "   App\TestNamespaceFixer",
            "at app{$ds}TestNamespaceFixer{$ds}TestClassWithWrongNamespace.php:4",
        ], $write);
        $expected = [
            'Do you want to change it to: Models',
            'Do you want to update reference to the old namespace?',
            'Do you want to change it to: App\TestNamespaceFixer\SubDir',
            'Do you want to change it to: App\TestNamespaceFixer',
            'Do you want to change it to: App\TestNamespaceFixer',
        ];
        $this->assertEquals($expected, Console::$askedConfirmations);
        // Check output contains expected messages
        $this->assertEquals(
            $this->getFileContent(__DIR__.'/Psr4Tests/expected_results/TestClassWithoutNamespace.stub'),
            $this->getFileContent($this->testDirectory.'/SubDir/TestClassWithoutNamespace.php')
        );
        $this->assertEquals(
            $this->getFileContent(__DIR__.'/Psr4Tests/expected_results/TestClassWithWrongNamespace.stub'),
            $this->getFileContent($this->testDirectory.'/TestClassWithWrongNamespace.php')
        );
        $this->assertEquals(
            $this->getFileContent(__DIR__.'/Psr4Tests/expected_results/TestClassDeclareWithoutNamespace.stub'),
            $this->getFileContent($this->testDirectory.'/TestClassDeclareWithoutNamespace.php')
        );
        $this->assertEquals(
            $this->getFileContent(__DIR__.'/Psr4Tests/expected_results/TestClassWithWrongNamespaceDeclare.stub'),
            $this->getFileContent(app_path('Models/TestClassWithWrongNamespaceDeclare.php'))
        );
        $this->assertEquals(
            $this->getFileContent(__DIR__.'/Psr4Tests/expected_results/Ref.stub'),
            $this->getFileContent(app_path('Models/Ref.php'))
        );
    }

    protected function createTestFiles(): void
    {
        mkdir($this->testDirectory.'/SubDir');
        // File 1: No namespace
        $content1 = file_get_contents(__DIR__.'/Psr4Tests/initial/TestClassWithoutNamespace.stub');
        File::put($this->testDirectory . '/SubDir/TestClassWithoutNamespace.php', $content1);

        // File 2: Wrong namespace
        $content2 = file_get_contents(__DIR__.'/Psr4Tests/initial/TestClassWithWrongNamespace.stub');
        File::put($this->testDirectory . '/TestClassWithWrongNamespace.php', $content2);

        // File 3: Correct namespace (should not be modified)
        $content3 = file_get_contents(__DIR__.'/Psr4Tests/initial/TestClassWithCorrectNamespace.stub');
        File::put($this->testDirectory . '/TestClassWithCorrectNamespace.php', $content3);

        // File 4: no namespace with declare at the top (should be modified)
        $content4 = file_get_contents(__DIR__.'/Psr4Tests/initial/TestClassDeclareWithoutNamespace.stub');
        File::put($this->testDirectory . '/TestClassDeclareWithoutNamespace.php', $content4);

        // File 5: wrong namespace with declare at the top (should be modified)
        $content5 = file_get_contents(__DIR__.'/Psr4Tests/initial/TestClassWithWrongNamespaceDeclare.stub');
        File::put(app_path('Models/TestClassWithWrongNamespaceDeclare.php'), $content5);

        $content6 = file_get_contents(__DIR__.'/Psr4Tests/initial/BadFileName.stub');
        File::put(app_path('BadFileName.php'), $content6);

        copy(__DIR__.'/Psr4Tests/initial/OldRef.stub', app_path('Models/Ref.php'));
    }

    private function cleanUpTestDirectory(): void
    {
        if (File::exists($this->testDirectory)) {
            File::deleteDirectory($this->testDirectory);
        }

        File::delete(app_path('Models/Ref.php'));
        File::delete(app_path('BadFileName.php'));
        File::delete(app_path('Models/TestClassWithWrongNamespaceDeclare.php'));
    }

    private function getFileContent(string $add)
    {
        return str_replace("\r\n", "\n", file_get_contents($add));
    }
}
