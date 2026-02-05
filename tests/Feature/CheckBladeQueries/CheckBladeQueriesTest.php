<?php

use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\LaravelMicroscope\Foundations\Color;

class CheckBladeQueriesTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        @mkdir(resource_path());
        @mkdir(resource_path('views'));
        copy(__DIR__.'/CheckBladeQueriesStubs/init.stub', $this->tmpFileUnderTest());
        Color::$color = false;
    }

    public function tearDown(): void
    {
        Color::$color = true;
        @unlink($this->tmpFileUnderTest());
        @rmdir(resource_path('views'));
        @rmdir(resource_path());
        parent::tearDown();
    }

    public function test()
    {
        $ds = DIRECTORY_SEPARATOR;
        $r = $this->artisan('check:blade_queries')
            // --------------------------------========-------------------------------- //
            ->expectsOutputToContain('   1 Query in blade file: ')->expectsOutputToContain('   \App\Models\User  <=== DB query in blade file')->expectsOutputToContain("at resources{$ds}views{$ds}blade_queries.blade.php:4")
            // --------------------------------========-------------------------------- //
            ->expectsOutput('   2 Query in blade file: ')->expectsOutput('   \DB  <=== DB query in blade file')->expectsOutput("at resources{$ds}views{$ds}blade_queries.blade.php:5")
            // --------------------------------========-------------------------------- //
            ->expectsOutput('   3 Query in blade file: ')->expectsOutput('   DB  <=== DB query in blade file')->expectsOutput("at resources{$ds}views{$ds}blade_queries.blade.php:6")
            // --------------------------------========-------------------------------- //
            ->run();

        $this->assertEquals(1, $r);
    }

    private function tmpFileUnderTest()
    {
        return resource_path('views/blade_queries.blade.php');
    }
}
