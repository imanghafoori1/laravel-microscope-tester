<?php

use App\Models\User;
use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\LaravelMicroscope\Features\CheckGates\SpyGate;

class CheckGatesTest extends TestCase
{
    public function test()
    {
        SpyGate::start();
        app(GateContract::class)->define('update', fn ($user) => true);
        app(GateContract::class)->define('update', 'f@f');
        app(GateContract::class)->define('update', User::class);
        app(GateContract::class)->define('update', User::class.'@s');
        $r = $this->artisan('check:gates')->run();

        $this->assertEquals(0, $r);
    }
}
