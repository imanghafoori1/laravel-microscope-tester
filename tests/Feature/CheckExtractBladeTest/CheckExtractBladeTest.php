<?php

use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\LaravelMicroscope\Foundations\Color;
use Imanghafoori\LaravelMicroscope\Foundations\Console;

class CheckExtractBladeTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Color::$color = false;
        Console::recoredWrites();
        @mkdir(resource_path());
        @mkdir(resource_path('ns'));
        @mkdir($this->views());
        copy(__DIR__.'/ExtractBladeStubs/init.stub', $this->tmpFileUnderTest());
    }

    public function tearDown(): void
    {
        Console::reset();
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
        Console::enforceTrue();

        View::addNamespace('ns', resource_path('ns'));
        $this->artisan('check:extract_blades')->assertOk()->run();

        $this->assertFileEquals(
            __DIR__.'/ExtractBladeStubs/expected.stub',
            resource_path('views/hello.blade.php')
        );
        $this->assertFileEquals(
            __DIR__.'/ExtractBladeStubs/head.stub',
            resource_path('views/myPartials/head.blade.php')
        );
        $this->assertFileEquals(
            __DIR__.'/ExtractBladeStubs/body.stub',
            resource_path('ns/myPartials/body.blade.php')
        );

        $this->assertEquals([
            'Do you have committed everything in git?'
        ], Console::$askedConfirmations);
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
