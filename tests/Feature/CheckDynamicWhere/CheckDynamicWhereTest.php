<?php

use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\LaravelMicroscope\Foundations\Color;
use Imanghafoori\LaravelMicroscope\Foundations\Console;

class CheckDynamicWhereTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Color::$color = false;
        Console::recoredWrites();
    }

    public function tearDown(): void
    {
        Console::reset();
        @unlink($this->tmpFileUnderTest());
        @unlink(app_path('B.php'));
        @unlink(app_path('C.php'));
        @unlink(app_path('Model2.php'));
        parent::tearDown();
    }

    public function test()
    {
        copy(__DIR__.'/DynamicWhereStubs/dynamic-where-init.stub', $this->tmpFileUnderTest());
        copy(__DIR__.'/DynamicWhereStubs/Model2.stub', app_path('Model2.php'));

        Console::enforceTrue();
        $this->artisan('check:dynamic_wheres')->assertFailed()->run();

        $this->assertEquals([
            'Do you want to replace A.php with new version of it?',
            'Do you want to replace A.php with new version of it?',
            'Do you want to replace A.php with new version of it?',
        ], Console::$askedConfirmations);

        $this->assertFileEquals(
    __DIR__.'/DynamicWhereStubs/dynamic-where-final.stub',
            $this->tmpFileUnderTest()
        );
    }

    public function test_2()
    {
        copy(__DIR__.'/DynamicWhereStubs/dynamic-where-init-2.stub', app_path('B.php'));

        Console::enforceTrue();
        $r = $this->artisan('check:dynamic_wheres')->run();

        $this->assertEquals([
            'Do you want to replace B.php with new version of it?',
            'Do you want to replace B.php with new version of it?',
        ], Console::$askedConfirmations);

        $this->assertFileEquals(
            __DIR__.'/DynamicWhereStubs/dynamic-where-final-2.stub',
            app_path('B.php')
        );

        $this->assertEquals(1, $r);
    }

    public function test_3()
    {
        copy(__DIR__.'/DynamicWhereStubs/dynamic-where-init-3.stub', app_path('C.php'));
        Console::enforceTrue();
        $hasError = $this->artisan('check:dynamic_wheres')->run();

        $this->assertEquals([], Console::$askedConfirmations);
        $this->assertEquals(0, $hasError);
    }

    private function tmpFileUnderTest()
    {
        return app_path('A.php');
    }
}