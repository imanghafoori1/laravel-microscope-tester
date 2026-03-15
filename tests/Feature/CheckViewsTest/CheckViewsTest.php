<?php

use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\LaravelMicroscope\ErrorReporters\ErrorPrinter;
use Imanghafoori\LaravelMicroscope\Features\CheckView\Check\CheckView;
use Imanghafoori\LaravelMicroscope\Features\SearchReplace\CachedFiles;
use Imanghafoori\LaravelMicroscope\Foundations\Color;
use Imanghafoori\LaravelMicroscope\Foundations\Console;

class CheckViewsTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Console::recoredWrites();
        ErrorPrinter::$instance = null;
        ErrorPrinter::$terminalWidth = 10;
        CheckView::$cache = false;
        Color::$color = false;
        @mkdir(resource_path('views'), 0777, true);
        copy(__DIR__.'/CheckViewsStubs/init-1.stub', $this->tmpFileUnderTest());
        copy(__DIR__.'/CheckViewsStubs/my-blade.init.stub', $this->bladePath());
        @unlink($this->getCacheFilePath());
    }

    public function tearDown(): void
    {
        @unlink($this->tmpFileUnderTest());
        @unlink($this->bladePath());
        @unlink($this->getCacheFilePath());
        @rmdir(resource_path('views'));
        @rmdir(resource_path());

        CheckView::$cache = true;
        Console::reset();
        ErrorPrinter::$instance = null;

        parent::tearDown();
    }

    public function test()
    {
        $ds = DIRECTORY_SEPARATOR;
        $this->artisan('check:views')->assertFailed()->run();
        $write = Console::$instance->writeln;
        array_pop($write);

        $this->assertEquals([
            '   1 The blade file is missing:',
            '   sdfv.blade.php does not exist',
            "at resources{$ds}views{$ds}my-blade.blade.php:1",
            '_______',
            '   2 The blade file is missing:',
            '   bar.blade.php does not exist',
            "at resources{$ds}views{$ds}my-blade.blade.php:3",
            '_______',
            '   3 The blade file is missing:',
            '   make.blade.php does not exist',
            "at app{$ds}Views.php:11",
            '_______',
            '   4 The blade file is missing:',
            '   route_view.blade.php does not exist',
            "at app{$ds}Views.php:12",
            '_______',
            '   5 The blade file is missing:',
            '   abc.blade.php does not exist',
            "at app{$ds}Views.php:13",
            '_______',
        ], $write);

        $cache = $this->getCacheFilePath();
        $this->assertFileExists($cache);
        $data = require $cache;

        $this->assertTrue(in_array([
            [
                [11, 'make'],
                [12, 'route_view'],
                [13, 'abc'],
            ],
            [10],
        ], $data));
        @unlink($cache);
    }

    private function tmpFileUnderTest()
    {
        return app_path('Views.php');
    }

    private function bladePath()
    {
        return resource_path('views/my-blade.blade.php');
    }

    private function getCacheFilePath(): string
    {
        return CachedFiles::getFolderPath().'check_views_call.php';
    }
}
