<?php

use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\LaravelMicroscope\Foundations\Reports\ComposerJsonReport;
use Imanghafoori\LaravelMicroscope\Foundations\Iterators\BladeFiles\CheckBladePaths;

class CheckViewsTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        @mkdir(resource_path());
        @mkdir(resource_path('views'));
        copy(__DIR__.'/CheckViewsStubs/init-1.stub', $this->mainPath());
        copy(__DIR__.'/CheckViewsStubs/my-blade.init.stub', $this->bladePath());
        CheckBladePaths::$scanned = [];
        ComposerJsonReport::$callback = null;
    }

    public function tearDown(): void
    {
        CheckBladePaths::$scanned = [];
        @unlink($this->mainPath());
        @unlink($this->bladePath());
        @rmdir(resource_path('views'));
        @rmdir(resource_path());
        parent::tearDown();
    }

    public function test()
    {
        $r = $this->artisan('check:views')
            ->expectsOutputToContain('5 view references were checked to exist. (1 skipped)')
            ->expectsOutputToContain('at app'.DIRECTORY_SEPARATOR.'Views.php:11')
            ->expectsOutputToContain('at app'.DIRECTORY_SEPARATOR.'Views.php:12')
            ->expectsOutputToContain('at app'.DIRECTORY_SEPARATOR.'Views.php:13')
            ->expectsOutputToContain('The blade file is missing:')
            ->expectsOutputToContain('abc.blade.php does not exist')
            ->expectsOutputToContain('sdfv.blade.php does not exist')
            ->expectsOutputToContain('bar.blade.php does not exist')
            ->expectsOutputToContain('make.blade.php does not exist')
            ->expectsOutputToContain('route_view.blade.php does not exist')
            ->expectsOutputToContain('at resources'.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'my-blade.blade.php:1')
            ->expectsOutputToContain('at resources'.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'my-blade.blade.php:3')
            ->run();

        $this->assertEquals(1, $r);
    }

    private function mainPath()
    {
        return app_path('Views.php');
    }

    private function bladePath()
    {
        return resource_path('views/my-blade.blade.php');
    }
}