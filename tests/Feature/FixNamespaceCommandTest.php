<?php

use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Composer;
use Illuminate\Support\Facades\File;
use Imanghafoori\LaravelMicroscope\Features\Psr4\Console\NamespaceFixer\NamespaceFixerMessages;
use Imanghafoori\LaravelMicroscope\Features\Psr4\Console\Psr4Errors;
use Imanghafoori\LaravelMicroscope\Foundations\Color;
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
        Color::$color = true;
        // Clean up after tests
        $this->cleanUpTestDirectory();
        file_put_contents(base_path('composer.json'), self::$composerJson);

        parent::tearDown();
    }

    #[Test]
    public function it_detects_wrong_namespaces_in_php_files()
    {
        // Create test files with incorrect/missing namespaces
        $this->createTestFiles();
        $this->mock(Composer::class)->shouldReceive('dumpAutoloads')->once();
        NamespaceFixerMessages::$pause = 20;
        Psr4Errors::$pause = 70;

        $this->artisan('check:psr4 --nofix')
            ->expectsOutputToContain("The file name and the class name are different.")
            ->expectsOutputToContain('Namespace of class "Wrong\Namespace\TestClassWithWrongNamespace" should be:')
            ->expectsOutputToContain('Models')
            ->expectsOutputToContain('Namespace of class "\TestClassWithoutNamespace" should be:')
            ->run();
    }

    #[Test]
    public function it_fixes_namespaces_in_php_files()
    {
        // Create test files with incorrect/missing namespaces
        $this->createTestFiles();
        $this->mock(Composer::class)->shouldReceive('dumpAutoloads')->once();
        NamespaceFixerMessages::$pause = 0;
        Psr4Errors::$pause = 0;
        // Run the artisan command on our test directory
        $this->artisan('check:psr4')
            ->expectsConfirmation("Do you want to change it to: Models", 'yes')
            ->expectsConfirmation("Do you want to update reference to the old namespace?", 'yes')
            ->expectsConfirmation("Do you want to change it to: App\TestNamespaceFixer\SubDir", 'yes')
            ->expectsConfirmation("Do you want to change it to: App\TestNamespaceFixer", 'yes')
            ->expectsConfirmation("Do you want to change it to: App\TestNamespaceFixer", 'yes')
            ->expectsOutputToContain('Namespace Not Found for class: TestClassWithoutNamespace')
            ->expectsOutputToContain('Namespace Not Found for class: TestClassDeclareWithoutNamespace')
            ->expectsOutputToContain('Namespace of class "TestClassWithoutNamespace" fixed to:')
            ->expectsOutputToContain(str_replace('\\', DIRECTORY_SEPARATOR,'at app\TestNamespaceFixer\TestClassDeclareWithoutNamespace.php:3'))
            ->expectsOutputToContain(str_replace('\\', DIRECTORY_SEPARATOR,'at app\TestNamespaceFixer\TestClassWithWrongNamespace.php:3'))
            ->expectsOutputToContain(str_replace('\\', DIRECTORY_SEPARATOR,'at app\TestNamespaceFixer\SubDir\TestClassWithoutNamespace.php:3'))
            ->expectsOutputToContain('Incorrect namespace: \'Wrong\Namespace\'')
            ->expectsOutputToContain('The file name and the class name are different.')
            ->run();
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
