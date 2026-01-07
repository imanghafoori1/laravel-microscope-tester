<?php

use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\ImportAnalyzer\ErrorReporters\ErrorPrinter;

class EnforceHelpersTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        @unlink(app_path('Helper.php'));
        copy(__DIR__.'/EnforceHelpers/init.stub', app_path('Helper.php'));
    }

    public function tearDown(): void
    {
        ErrorPrinter::$instance = null;
        parent::tearDown();
        @unlink(app_path('Helper.php'));
    }

    public function test()
    {
        $r = $this->artisan('enforce:helper_functions')
            ->expectsQuestion('Do you want to replace Helper.php with new version of it?', true)
            ->run();

        $this->assertEquals(0, $r);

        $this->assertEquals(
            file_get_contents(__DIR__.'/EnforceHelpers/expected.stub'),
            file_get_contents(app_path('Helper.php'))
        );
    }
}
