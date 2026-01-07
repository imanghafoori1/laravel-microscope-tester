<?php

use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\LaravelMicroscope\ErrorReporters\ErrorPrinter;

class CheckEndIfTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        @unlink($this->mainPath());
        copy(__DIR__.'/EndifStubs/endif-init.stub', $this->mainPath());
    }

    public function tearDown(): void
    {
        ErrorPrinter::$instance = null;
        @unlink($this->mainPath());
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
            file_get_contents($this->mainPath())
        );
    }

    private function mainPath(): string
    {
        return app_path('endIf.php');
    }
}