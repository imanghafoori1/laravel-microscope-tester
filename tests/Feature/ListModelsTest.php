<?php

use Illuminate\Foundation\Testing\TestCase;

class ListModelsTest extends TestCase
{
    public function test()
    {
        $r = $this->artisan('list:models')->run();

        $this->assertEquals(0, $r);
    }
}