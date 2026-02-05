<?php

use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\LaravelMicroscope\Features\CheckEndIf\CheckEndIfSyntax;
use Imanghafoori\LaravelMicroscope\Foundations\Color;

class CheckEndIfTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Color::$color = CheckEndIfSyntax::$cache = false;
        copy(__DIR__.'/EndifStubs/endif-init.stub', $this->tmpFileUnderTest());
    }

    public function tearDown(): void
    {
        Color::$color = CheckEndIfSyntax::$cache = true;
        @unlink($this->tmpFileUnderTest());
        parent::tearDown();
    }

    public function test()
    {
        $r = $this->artisan('check:endif')
            ->expectsQuestion('Do you have committed everything in git?', true)
            ->expectsQuestion('Replacing endif in: app'.DIRECTORY_SEPARATOR.'endIf.php', 'yes')
            ->run();

        $this->assertEquals(0, $r);
        $this->assertEquals(
            file_get_contents(__DIR__.'/EndifStubs/endif-expected.stub'),
            file_get_contents($this->tmpFileUnderTest())
        );
    }

    private function tmpFileUnderTest(): string
    {
        return app_path('endIf.php');
    }
}
