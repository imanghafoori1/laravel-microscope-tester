<?php

use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\ImportAnalyzer\ErrorReporters\ErrorPrinter;

class EnforceQueryTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        copy(__DIR__.'/EnforceQueryStub/enforce-query-init.stub', $this->mainPath());
    }

    public function tearDown(): void
    {
        ErrorPrinter::$instance = null;
        @unlink($this->mainPath());
        parent::tearDown();
    }

    public function test()
    {
        $r = $this->artisan('enforce:query')
            ->expectsQuestion('Do you want to replace Query.php with new version of it?', 'yes')
            ->run();

        $this->assertEquals(
            file_get_contents(__DIR__.'/EnforceQueryStub/enforce-query-final.stub'),
            file_get_contents($this->mainPath())
        );

        $this->assertEquals(0, $r);
    }

    private function mainPath(): string
    {
        return app_path('Query.php');
    }
}