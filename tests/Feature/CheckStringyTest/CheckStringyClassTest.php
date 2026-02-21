<?php

use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\LaravelMicroscope\Features\CheckDD\CheckDD;
use Imanghafoori\LaravelMicroscope\Features\SearchReplace\CachedFiles;
use Imanghafoori\LaravelMicroscope\Foundations\Color;
use Imanghafoori\LaravelMicroscope\Foundations\Console;

class CheckStringyClassTest extends TestCase
{
    public function setUp(): void
    {
        Console::$instance = new class
        {
            public $text = [];

            public $writeln = [];

            public function writeln($write)
            {
                $this->writeln[] = $write;
            }

            public function text($text)
            {
                $this->text[] = $text;
            }
        };

        CheckDD::$cache = false;
        Color::$color = false;
        parent::setUp();
    }

    public function tearDown(): void
    {
        Console::reset();
        CheckDD::$cache = true;
        Color::$color = true;
        @unlink($this->tmpFileUnderTest());
        @unlink($this->getCacheFilePath());
        @unlink(app_path('Models/stringy.php'));
        parent::tearDown();
    }

    public function test_0()
    {
        copy(__DIR__.'/CheckStringyClassStubs/init.stub', $this->tmpFileUnderTest());

        Console::$forcedAnswer = false;
        $r = $this->artisan('check:stringy_classes')->run();
        $text = Console::$instance->text;
        $write = Console::$instance->writeln;
        $this->assertEquals([], $write);
        $this->assertStringContainsString("3 |'App\Models\User';", $text[0]);
        $this->assertStringContainsString("stringy.php:3", $text[1]);
        $this->assertStringContainsString("5 |'\App\Models\User';", $text[2]);
        $this->assertStringContainsString("stringy.php:5", $text[3]);
        $this->assertEquals([
            "Replace: 'App\\Models\\User' with ::class version of it?",
            "Replace: '\\App\\Models\\User' with ::class version of it?",
        ], Console::$askedConfirmations);
        $this->assertEquals(
            file_get_contents(__DIR__.'/CheckStringyClassStubs/init.stub'),
            file_get_contents($this->tmpFileUnderTest())
        );

        $this->assertEquals(1, $r);
    }

    public function test_1()
    {
        copy(__DIR__.'/CheckStringyClassStubs/init.stub', $this->tmpFileUnderTest());

        Console::enforceTrue();

        $r = $this->artisan('check:stringy_classes')->run();
        $write = Console::$instance->writeln;
        $this->assertEquals("✔ Replaced with: \App\Models\User::class", $write[0]);
        $this->assertStringContainsString('______________', $write[1]);
        $this->assertEquals("✔ Replaced with: \App\Models\User::class", $write[2]);
        $this->assertStringContainsString('______________', $write[3]);
        $this->assertEquals([
            "Replace: 'App\\Models\\User' with ::class version of it?",
            "Replace: '\\App\\Models\\User' with ::class version of it?",
        ], Console::$askedConfirmations);

        $this->assertEquals(
            file_get_contents(__DIR__.'/CheckStringyClassStubs/expect.stub'),
            file_get_contents($this->tmpFileUnderTest())
        );

        $this->assertEquals(1, $r);
    }

    public function test_2()
    {
        $mainPath = app_path('Models/stringy.php');
        copy(__DIR__.'/CheckStringyClassStubs/init-2.stub', $mainPath);

        Console::enforceTrue();
        $r = $this->artisan('check:stringy_classes')->run();
        $write = Console::$instance->writeln;
        $this->assertEquals("✔ Replaced with: User::class", $write[0]);
        $this->assertStringContainsString('______________', $write[1]);
        $this->assertEquals("✔ Replaced with: User::class", $write[2]);
        $this->assertStringContainsString('______________', $write[3]);

        $this->assertEquals([
            "Replace: 'App\\Models\\User' with ::class version of it?",
            "Replace: '\\App\\Models\\User' with ::class version of it?",
        ], Console::$askedConfirmations);

        $this->assertEquals(
            file_get_contents(__DIR__.'/CheckStringyClassStubs/expect-2.stub'),
            file_get_contents($mainPath)
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
