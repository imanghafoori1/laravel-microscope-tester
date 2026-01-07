<?php

use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\LaravelMicroscope\ErrorReporters\ErrorPrinter;

class CheckGenericDocblocksTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        copy(__DIR__.'/CheckGenericDocblocksStubs/init.stub', $this->mainPath());
    }

    public function tearDown(): void
    {
        ErrorPrinter::$instance = null;
        @unlink($this->mainPath());
        parent::tearDown();
    }

    public function test()
    {
        $r = $this->artisan('check:generic_docblocks')
            ->expectsConfirmation('Do you want to remove doc-blocks from: <fg=yellow>HelloController.php</>', 'yes')
            ->run();

        $this->assertEquals(
            file_get_contents(__DIR__.'/CheckGenericDocblocksStubs/expected.stub'),
            file_get_contents($this->mainPath())
        );
        $this->assertEquals(1, $r);
    }

    private function mainPath()
    {
        return app_path('HelloController.php');
    }
}