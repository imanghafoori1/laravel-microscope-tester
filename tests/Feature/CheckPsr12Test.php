<?php

use Illuminate\Foundation\Testing\TestCase;

class CheckPsr12Test extends TestCase
{
    public function tearDown(): void
    {
        @unlink($this->tmpFileUnderTest());
        parent::tearDown();
    }

    public function test()
    {
        copy(__DIR__.'/CheckPsr12Stub/init.stub', $this->tmpFileUnderTest());

        $r = $this->artisan('check:psr12')
            ->expectsQuestion('Do you have committed everything in git?', 'yes')
            ->run();

        $this->assertEquals(0, $r);
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
