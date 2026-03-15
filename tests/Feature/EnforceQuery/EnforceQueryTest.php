<?php

use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\LaravelMicroscope\ErrorReporters\ErrorPrinter;
use Imanghafoori\LaravelMicroscope\Features\EnforceImports\EnforceImportsCheck;
use Imanghafoori\LaravelMicroscope\Foundations\Color;
use Imanghafoori\LaravelMicroscope\Foundations\Console;

class EnforceQueryTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Console::recoredWrites();
        Color::$color = EnforceImportsCheck::$cache = false;
        copy(__DIR__.'/EnforceQueryStub/enforce-query-init.stub', $this->tmpFileUnderTest());
    }

    public function tearDown(): void
    {
        ErrorPrinter::$instance = null;
        Color::$color = EnforceImportsCheck::$cache = true;
        @unlink($this->tmpFileUnderTest());
        Console::reset();

        parent::tearDown();
    }

    public function test()
    {
        Console::enforceTrue();
        $this->artisan('enforce:query')->assertFailed()->run();

        $this->assertEquals([
            'Do you want to replace Query.php with new version of it?',
            'Do you want to replace Query.php with new version of it?',
        ], Console::$askedConfirmations);

        $this->assertFileEquals(
            __DIR__.'/EnforceQueryStub/enforce-query-final.stub',
            $this->tmpFileUnderTest()
        );
    }

    private function tmpFileUnderTest(): string
    {
        return app_path('Query.php');
    }
}