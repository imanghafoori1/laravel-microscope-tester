<?php

use Illuminate\Foundation\Testing\TestCase;

class CheckExtractBladeTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        @mkdir(resource_path());
        @mkdir(resource_path('ns'));
        @mkdir($this->views());
        copy(__DIR__.'/ExtractBladeStubs/init.stub', $this->tmpFileUnderTest());
    }

    public function tearDown(): void
    {
        @unlink(resource_path('ns/myPartials/body.blade.php'));
        @unlink(resource_path('views/myPartials/head.blade.php'));
        @unlink(resource_path('views/hello.blade.php'));
        @rmdir(resource_path('views/myPartials'));
        @rmdir($this->views());
        @rmdir(resource_path('ns/myPartials'));
        @rmdir(resource_path('ns'));
        @rmdir(resource_path());
        @unlink($this->tmpFileUnderTest());
        parent::tearDown();
    }

    public function test()
    {
        View::addNamespace('ns', resource_path('ns'));
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
            file_get_contents(resource_path('ns/myPartials/body.blade.php'))
        );

        $this->assertEquals(0, $r);
    }

    private function tmpFileUnderTest()
    {
        return resource_path('views/hello.blade.php');
    }

    private function views(): string
    {
        return resource_path('views');
    }
}
