<?php

use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\LaravelMicroscope\ErrorReporters\ErrorPrinter;
use Imanghafoori\LaravelMicroscope\Features\SearchReplace\CachedFiles;
use Imanghafoori\LaravelMicroscope\Foundations\Color;
use Imanghafoori\LaravelMicroscope\Foundations\Console;

class CheckFqcnCommandTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Color::$color = false;
        Console::recoredWrites();
        ErrorPrinter::$terminalWidth = 10;
    }

    public function tearDown(): void
    {
        Console::reset();
        ErrorPrinter::$instance = null;
        ErrorPrinter::$ignored = [];

        @unlink($this->getCacheFilePath());
        @unlink($this->tmpFileUnderTest());
        @unlink(app_path('Fqcn2.php'));

        parent::tearDown();
    }

    public function test()
    {
        copy(__DIR__.'/CheckFqcn/initial.stub', $this->tmpFileUnderTest());
        $ds = DIRECTORY_SEPARATOR;
        $this->artisan('check:extra_fqcn --fix')->assertFailed()->run();

        $write = (Console::$instance)->writeln;
        unset($write[20]);

        $this->assertEquals([
            '   1 FQCN is already imported at line: 5',
            '   \C\E',
            "at app{$ds}Fqcn.php:13",
            '_______',
            '   2 FQCN is already imported at line: 5',
            '   \C\E',
            "at app{$ds}Fqcn.php:14",
            '_______',
            '   3 FQCN is already imported at line: 5',
            '   \C\E',
            "at app{$ds}Fqcn.php:15",
            '_______',
            '   4 FQCN is already on the same namespace. (fixed)',
            '   \He\R\T\U2',
            "at app{$ds}Fqcn.php:18",
            '_______',
            '   5 FQCN is already imported with an alias: G',
            '   \He\R\T\Hh can be replaced with: G',
            "at app{$ds}Fqcn.php:19",
            '_______',
        ], $write);

        $this->assertFileEquals(
            __DIR__.'/CheckFqcn/expected.stub',
            $this->tmpFileUnderTest()
        );

        $this->assertFileExists($this->getCacheFilePath());
    }

    public function test_no_fix()
    {
        copy(__DIR__.'/CheckFqcn/initial.stub', $this->tmpFileUnderTest());
        copy(__DIR__.'/CheckFqcn/ignored-initial.stub', app_path('Fqcn2.php'));
        ErrorPrinter::$ignored = ['*Fqcn2.php'];
        $ds = DIRECTORY_SEPARATOR;
        $this->artisan('check:extra_fqcn')->assertFailed()->run();
        $write = (Console::$instance)->writeln;
        unset($write[20]);

        $this->assertEquals($write, [
            '   1 FQCN is already imported at line: 5',
            '   \C\E',
            "at app{$ds}Fqcn.php:13",
            '_______',
            '   2 FQCN is already imported at line: 5',
            '   \C\E',
            "at app{$ds}Fqcn.php:14",
            '_______',
            '   3 FQCN is already imported at line: 5',
            '   \C\E',
            "at app{$ds}Fqcn.php:15",
            '_______',
            '   4 FQCN is already on the same namespace.',
            '   \He\R\T\U2',
            "at app{$ds}Fqcn.php:18",
            '_______',
            '   5 FQCN is already imported with an alias: G',
            '   \He\R\T\Hh can be replaced with: G',
            "at app{$ds}Fqcn.php:19",
            '_______',
        ]);

        $this->assertFileEquals(
            __DIR__.'/CheckFqcn/initial.stub',
            $this->tmpFileUnderTest()
        );
    }

    private function tmpFileUnderTest(): string
    {
        return app_path('Fqcn.php');
    }

    private function getCacheFilePath()
    {
        return CachedFiles::getFolderPath().'extra_fqcn.php';
    }
}
