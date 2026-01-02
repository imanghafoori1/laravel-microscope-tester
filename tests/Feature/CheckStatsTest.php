<?php

use Illuminate\Foundation\Testing\TestCase;

class CheckStatsTest extends TestCase
{
    public function tearDown(): void
    {
        parent::tearDown();
    }

    public function test()
    {
        $r = $this->artisan('check:stats')
            ->expectsOutputToContain('1 Models found.')
            ->expectsOutputToContain('1 Factories found.')
            ->expectsOutputToContain('1 Seeders found.')
            ->expectsOutputToContain('| 3 class | 0 trait | 0 interface | 0 enum |')
            ->run();

        $this->assertEquals(0, $r);
    }
}
