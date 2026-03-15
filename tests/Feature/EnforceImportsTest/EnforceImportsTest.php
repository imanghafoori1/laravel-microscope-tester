<?php

use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\LaravelMicroscope\ErrorReporters\ErrorPrinter;
use Imanghafoori\LaravelMicroscope\Features\SearchReplace\CachedFiles;
use Imanghafoori\LaravelMicroscope\Foundations\Color;
use Imanghafoori\LaravelMicroscope\Foundations\Console;

class EnforceImportsTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Color::$color = false;
        Console::recoredWrites();
        ErrorPrinter::$instance = null;
        ErrorPrinter::$terminalWidth = 10;

        copy(__DIR__.'/EnforceImportsStubs/init.stub', $this->tmpFileUnderTest());
    }

    public function tearDown(): void
    {
        Console::reset();

        @unlink(CachedFiles::getFolderPath().'EnforceImports.php');
        @unlink(CachedFiles::getFolderPath().'extra_fqcn.php');
        @unlink($this->tmpFileUnderTest());

        parent::tearDown();
    }

    public function test_1()
    {
        $this->artisan('enforce:imports')->assertFailed()->run();

        $write = Console::getInstance()->writeln;
        array_pop($write);

        $ds = DIRECTORY_SEPARATOR;
        $this->assertEquals([
            '   1 FQCN is already imported at line: 5',
            '   \C\E',
            "at app{$ds}EnforceImports.php:12",
            '_______',
            '   2 FQCN is already on the same namespace.',
            '   \He\R\T\U2',
            "at app{$ds}EnforceImports.php:15",
            '_______',
            '   3 FQCN is already on the same namespace.',
            '   \He\R\T\Hh',
            "at app{$ds}EnforceImports.php:19",
            '_______',
            '   4 FQCN got imported at the top',
            '   \App\R\T\U3',
            "at app{$ds}EnforceImports.php:16",
            '_______',
            '   5 FQCN got imported at the top',
            '   \App\R\T\U4',
            "at app{$ds}EnforceImports.php:18",
            '_______',
        ], $write);

        $this->assertEquals(
            $this->getContents(__DIR__.'/EnforceImportsStubs/expected.stub'),
            $this->getContents($this->tmpFileUnderTest())
        );
    }

    public function test_2()
    {
        $this->artisan('enforce:imports --class=U3,U5')->assertFailed()->run();

        $write = Console::getInstance()->writeln;
        array_pop($write);

        $ds = DIRECTORY_SEPARATOR;
        $this->assertEquals([
            '   1 FQCN is already imported at line: 5',
            '   \C\E',
            "at app{$ds}EnforceImports.php:12",
            '_______',
            '   2 FQCN is already on the same namespace.',
            '   \He\R\T\U2',
            "at app{$ds}EnforceImports.php:15",
            '_______',
            '   3 FQCN is already on the same namespace.',
            '   \He\R\T\Hh',
            "at app{$ds}EnforceImports.php:19",
            '_______',
            '   4 FQCN got imported at the top',
            '   \App\R\T\U3',
            "at app{$ds}EnforceImports.php:16",
            '_______',
        ], $write);

        $this->assertEquals(
            $this->getContents(__DIR__.'/EnforceImportsStubs/expected-2.stub'),
            $this->getContents($this->tmpFileUnderTest())
        );
    }

    private function getContents(string $path)
    {
        return str_replace("\r\n", "\n", file_get_contents($path));
    }

    private function tmpFileUnderTest()
    {
        return app_path('EnforceImports.php');
    }
}
