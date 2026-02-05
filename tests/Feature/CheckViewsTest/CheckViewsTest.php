<?php

use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\LaravelMicroscope\Features\CheckView\Check\CheckView;
use Imanghafoori\LaravelMicroscope\Features\SearchReplace\CachedFiles;
use Imanghafoori\LaravelMicroscope\Foundations\Color;

class CheckViewsTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

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
        @rmdir(resource_path('views'));
        @rmdir(resource_path());
        CheckView::$cache = true;
        Color::$color = true;
        parent::tearDown();
    }

    public function test()
    {
        $ds = DIRECTORY_SEPARATOR;
        $r = $this->artisan('check:views')
            ->expectsOutputToContain('5 view references were checked to exist. (1 skipped)')
            ->expectsOutputToContain('at app'.$ds.'Views.php:11')
            ->expectsOutputToContain('at app'.$ds.'Views.php:12')
            ->expectsOutputToContain('at app'.$ds.'Views.php:13')
            ->expectsOutputToContain('The blade file is missing:')
            ->expectsOutputToContain('abc.blade.php does not exist')
            ->expectsOutputToContain('sdfv.blade.php does not exist')
            ->expectsOutputToContain('bar.blade.php does not exist')
            ->expectsOutputToContain('make.blade.php does not exist')
            ->expectsOutputToContain('route_view.blade.php does not exist')
            ->expectsOutputToContain('at resources'.$ds.'views'.$ds.'my-blade.blade.php:1')
            ->expectsOutputToContain('at resources'.$ds.'views'.$ds.'my-blade.blade.php:3')
            ->run();

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
        $this->assertEquals(1, $r);
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
