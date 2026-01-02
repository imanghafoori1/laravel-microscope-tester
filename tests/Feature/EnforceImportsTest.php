<?php

use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\LaravelMicroscope\Foundations\Reports\ComposerJsonReport;

class EnforceImportsTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        ComposerJsonReport::$callback = null;
        copy(__DIR__.'/EnforceImportsStubs/init.stub', $this->mainPath());
    }

    public function tearDown(): void
    {
        @unlink($this->mainPath());
        parent::tearDown();
    }

    public function test()
    {
        $r = $this->artisan('enforce:imports')->run();
        $this->assertEquals(
            file_get_contents(__DIR__.'/EnforceImportsStubs/expected.stub'),
            file_get_contents($this->mainPath())
        );

        $this->assertEquals(1, $r);
    }

    private function mainPath()
    {
        return app_path('EnforceImports.php');
    }
}