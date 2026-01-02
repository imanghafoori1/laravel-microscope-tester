<?php

use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\LaravelMicroscope\Foundations\Iterators\BladeFiles\CheckBladePaths;

class CheckExtractBladeTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        CheckBladePaths::$scanned = [];
        @mkdir(resource_path());
        @mkdir($this->views());
        copy(__DIR__.'/ExtractBladeStubs/init.stub', $this->mainPath());
    }

    public function tearDown(): void
    {
        @unlink(resource_path('views/myPartials/body.blade.php'));
        @unlink(resource_path('views/myPartials/head.blade.php'));
        @unlink(resource_path('views/hello.blade.php'));
        @rmdir(resource_path('views/myPartials'));
        @rmdir($this->views());
        @rmdir(resource_path());
        @unlink($this->mainPath());
        parent::tearDown();
    }


    public function test()
    {
        $r = $this->artisan('check:extract_blades')
            ->expectsQuestion('Do you have committed everything in git?', true)
            ->run();

        $this->assertEquals(
            file_get_contents(__DIR__.'/ExtractBladeStubs/expected.stub'),
            file_get_contents(resource_path('views/hello.blade.php'))
        );
        $this->assertEquals(
            file_get_contents(__DIR__.'/ExtractBladeStubs/head.stub'),
            file_get_contents(resource_path('views/myPartials/head.blade.php'))
        );
        $this->assertEquals(
            file_get_contents(__DIR__.'/ExtractBladeStubs/body.stub'),
            file_get_contents(resource_path('views/myPartials/body.blade.php'))
        );

        $this->assertEquals(0, $r);
    }

    private function mainPath()
    {
        return resource_path('views/hello.blade.php');
    }

    private function views(): string
    {
        return resource_path('views');
    }
}