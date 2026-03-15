<?php

use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\LaravelMicroscope\ErrorReporters\ErrorPrinter;
use Imanghafoori\LaravelMicroscope\Features\CheckDD\CheckDD;
use Imanghafoori\LaravelMicroscope\Features\SearchReplace\CachedFiles;
use Imanghafoori\LaravelMicroscope\Foundations\Color;
use Imanghafoori\LaravelMicroscope\Foundations\Console;

class CheckStringyClassTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Console::recoredWrites();
        ErrorPrinter::$instance = null;
        ErrorPrinter::$terminalWidth = 10;

        CheckDD::$cache = false;
        Color::$color = false;
    }

    public function tearDown(): void
    {
        Console::reset();
        CheckDD::$cache = true;
        @unlink($this->tmpFileUnderTest());
        @unlink($this->getCacheFilePath());
        @unlink(app_path('Models/stringy.php'));
        parent::tearDown();
    }

    public function test_0()
    {
        copy(__DIR__.'/CheckStringyClassStubs/init.stub', $this->tmpFileUnderTest());

        Console::$forcedAnswer = false;
        $this->artisan('check:stringy_classes')->assertFailed()->run();

        $write = Console::$instance->writeln;
        array_pop($write);

        $ds = DIRECTORY_SEPARATOR;
        $this->assertEquals([
            PHP_EOL."3 |'App\Models\User';",
            "at app{$ds}stringy.php:3",
            PHP_EOL."5 |'\App\Models\User';",
            "at app{$ds}stringy.php:5",
            "   1 Class ''App\Models\User2'' does not exist:",
            "   'App\Models\User2'",
            "at app{$ds}stringy.php:4",
            '_______',
        ], $write);

        //
        $this->assertEquals([
            "Replace: 'App\\Models\\User' with ::class version of it?",
            "Replace: '\\App\\Models\\User' with ::class version of it?",
        ], Console::$askedConfirmations);

        // Ensure the file has not changed:
        $this->assertFileEquals(
            __DIR__.'/CheckStringyClassStubs/init.stub',
            $this->tmpFileUnderTest()
        );
    }

    public function test_1()
    {
        copy(__DIR__.'/CheckStringyClassStubs/init.stub', $this->tmpFileUnderTest());

        Console::enforceTrue();

        $this->artisan('check:stringy_classes')->assertFailed()->run();
        $write = Console::$instance->writeln;
        array_pop($write);

        $ds = DIRECTORY_SEPARATOR;

        $this->assertEquals([
            0 => PHP_EOL."3 |'App\Models\User';",
            1 => "at app{$ds}stringy.php:3",
            2 => "✔ Replaced with: \App\Models\User::class",
            3 => " _______",
            4 => PHP_EOL."5 |'\App\Models\User';",
            5 => "at app{$ds}stringy.php:5",
            6 => "✔ Replaced with: \App\Models\User::class",
            7 => " _______",
            8 => "   1 Class ''App\Models\User2'' does not exist:",
            9 => "   'App\Models\User2'",
            10 => "at app{$ds}stringy.php:4",
            11 => "_______",
        ], $write);

        $this->assertEquals([
            "Replace: 'App\\Models\\User' with ::class version of it?",
            "Replace: '\\App\\Models\\User' with ::class version of it?",
        ], Console::$askedConfirmations);

        $this->assertFileEquals(
            __DIR__.'/CheckStringyClassStubs/expect.stub',
            $this->tmpFileUnderTest()
        );
    }

    public function test_2()
    {
        $mainPath = app_path('Models/stringy.php');
        copy(__DIR__.'/CheckStringyClassStubs/init-2.stub', $mainPath);

        Console::enforceTrue();
        $r = $this->artisan('check:stringy_classes')->run();
        $write = Console::$instance->writeln;
        array_pop($write);

        $ds = DIRECTORY_SEPARATOR;

        $this->assertEquals([
            0 => PHP_EOL."5 |'App\Models\User';",
            1 => "at app{$ds}Models{$ds}stringy.php:5",
            2 => "✔ Replaced with: User::class",
            3 => " _______",
            4 => PHP_EOL."7 |'\App\Models\User';",
            5 => "at app{$ds}Models{$ds}stringy.php:7",
            6 => "✔ Replaced with: User::class",
            7 => " _______",
            8 => "   1 Class ''App\Models\User2'' does not exist:",
            9 => "   'App\Models\User2'",
            10 => "at app{$ds}Models{$ds}stringy.php:6",
            11 => "_______",
        ], $write);
        $this->assertEquals([
            "Replace: 'App\\Models\\User' with ::class version of it?",
            "Replace: '\\App\\Models\\User' with ::class version of it?",
        ], Console::$askedConfirmations);

        $this->assertFileEquals(
            __DIR__.'/CheckStringyClassStubs/expect-2.stub',
            $mainPath
        );

        $this->assertEquals(1, $r);
    }

    private function tmpFileUnderTest()
    {
        return app_path('stringy.php');
    }

    private function getCacheFilePath()
    {
        return CachedFiles::getFolderPath().'stringy_classes.php';
    }
}
