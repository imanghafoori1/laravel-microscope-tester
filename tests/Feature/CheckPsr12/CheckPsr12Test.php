<?php

use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\LaravelMicroscope\Foundations\Console;

class CheckPsr12Test extends TestCase
{
    public function tearDown(): void
    {
        @unlink($this->tmpFileUnderTest());
        Console::reset();
        parent::tearDown();
    }

    public function test()
    {
        copy(__DIR__.'/CheckPsr12Stub/init.stub', $this->tmpFileUnderTest());

        Console::enforceTrue();
        $r = $this->artisan('check:psr12')->run();

        $this->assertEquals(0, $r);
        $this->assertEquals([
            'Do you have committed everything in git?',
        ], Console::$askedConfirmations);

        $this->assertEquals(
            str_replace("\r\n", "\n",  file_get_contents(__DIR__.'/CheckPsr12Stub/expected.stub')),
            str_replace("\r\n", "\n",  file_get_contents($this->tmpFileUnderTest()))
        );
    }

    private function tmpFileUnderTest()
    {
        return app_path('Psr12.php');
    }
}
