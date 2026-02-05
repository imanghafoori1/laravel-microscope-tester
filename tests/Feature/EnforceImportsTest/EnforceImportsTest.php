<?php

use Illuminate\Foundation\Testing\TestCase;

class EnforceImportsTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        copy(__DIR__.'/EnforceImportsStubs/init.stub', $this->tmpFileUnderTest());
    }

    public function tearDown(): void
    {
        @unlink($this->tmpFileUnderTest());
        parent::tearDown();
    }

    public function test_1()
    {
        $r = $this->artisan('enforce:imports')->run();
        $this->assertEquals(
            str_replace("\r\n", "\n", file_get_contents(__DIR__.'/EnforceImportsStubs/expected.stub')),
            str_replace("\r\n", "\n", file_get_contents($this->tmpFileUnderTest()))
        );

        $this->assertEquals(1, $r);
    }

    public function test_2()
    {
        $r = $this->artisan('enforce:imports --class=U3,U5')->run();
        $this->assertEquals(
            str_replace("\r\n", "\n", file_get_contents(__DIR__.'/EnforceImportsStubs/expected-2.stub')),
            str_replace("\r\n", "\n", file_get_contents($this->tmpFileUnderTest()))
        );

        $this->assertEquals(1, $r);
    }

    private function tmpFileUnderTest()
    {
        return app_path('EnforceImports.php');
    }
}
