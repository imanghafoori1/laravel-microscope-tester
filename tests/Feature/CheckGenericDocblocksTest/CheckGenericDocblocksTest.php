<?php

use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\LaravelMicroscope\Foundations\Color;

class CheckGenericDocblocksTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Color::$color = false;
        copy(__DIR__.'/CheckGenericDocblocksStubs/init.stub', $this->tmpFileUnderTest());
    }

    public function tearDown(): void
    {
        Color::$color = true;
        @unlink($this->tmpFileUnderTest());
        parent::tearDown();
    }

    public function test()
    {
        $r = $this->artisan('check:generic_docblocks')
            ->expectsConfirmation('Do you want to remove doc-blocks from: HelloController.php', 'yes')
            ->expectsOutputToContain('7 generic doc-blocks were found.')
            ->run();

        $this->assertEquals(
            file_get_contents(__DIR__.'/CheckGenericDocblocksStubs/expected.stub'),
            file_get_contents($this->tmpFileUnderTest())
        );
        $this->assertEquals(1, $r);
    }

    private function tmpFileUnderTest()
    {
        return app_path('HelloController.php');
    }
}
