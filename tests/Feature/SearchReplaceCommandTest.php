<?php

use Illuminate\Foundation\Testing\TestCase;

class SearchReplaceCommandTest extends TestCase
{
    public function tearDown(): void
    {
        @unlink($this->tmpFileUnderTest());
        parent::tearDown();
    }

    public function test_0()
    {
        $r = $this->artisan('search_replace')->run();

        $this->assertEquals(0, $r);

        $this->assertFileExists($this->tmpFileUnderTest());
        @unlink($this->tmpFileUnderTest());
        copy(__DIR__.'/SearchReplaceCommandStub/init.stub', $this->tmpFileUnderTest());
        $this->artisan('search_replace')->run();
    }

    public function test_1()
    {
        $this->artisan('search_replace')->assertOk()->run();
        $this->assertFileExists($this->tmpFileUnderTest());
        copy(__DIR__.'/SearchReplaceCommandStub/init_no_replace.stub', $this->tmpFileUnderTest());
        $ds = DIRECTORY_SEPARATOR;
        $this->artisan('search_replace')
            ->assertFailed()
            ->expectsOutputToContain('Matched Code: protected function casts(): array')
            ->expectsOutputToContain('at app'.$ds.'Models'.$ds.'User.php:41')
            ->run();
    }

    private function tmpFileUnderTest()
    {
        return base_path('search_replace.php');
    }
}
