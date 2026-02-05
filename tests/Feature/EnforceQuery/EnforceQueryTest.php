<?php

use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\LaravelMicroscope\Features\EnforceImports\EnforceImports;
use Imanghafoori\LaravelMicroscope\Foundations\Color;

class EnforceQueryTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Color::$color = EnforceImports::$cache = false;
        copy(__DIR__.'/EnforceQueryStub/enforce-query-init.stub', $this->tmpFileUnderTest());
    }

    public function tearDown(): void
    {
        Color::$color = EnforceImports::$cache = true;
        @unlink($this->tmpFileUnderTest());
        parent::tearDown();
    }

    public function test()
    {
        $r = $this->artisan('enforce:query')
            ->expectsQuestion('Do you want to replace Query.php with new version of it?', 'yes')
            ->run();

        $this->assertEquals(
            file_get_contents(__DIR__.'/EnforceQueryStub/enforce-query-final.stub'),
            file_get_contents($this->tmpFileUnderTest())
        );

        $this->assertEquals(1, $r);
    }

    private function tmpFileUnderTest(): string
    {
        return app_path('Query.php');
    }
}