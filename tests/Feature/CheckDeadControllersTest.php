<?php

use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\LaravelMicroscope\Foundations\Reports\ComposerJsonReport;

class CheckDeadControllersTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        ComposerJsonReport::$callback = null;
        @unlink($this->mainPath());
        copy(__DIR__.'/CheckDeadControllersTest/init.stub', $this->mainPath());
    }

    public function tearDown(): void
    {
        @unlink($this->mainPath());
        parent::tearDown();
    }

    public function test()
    {
        $ds = DIRECTORY_SEPARATOR;
        $r = $this
            ->artisan('check:dead_controllers')
            ->expectsOutputToContain('App\MyDeadController@myAction1')
            ->expectsOutputToContain('No route is defined for controller action:')
            ->expectsOutputToContain('App\MyDeadController@myAction2')
            ->expectsOutputToContain('at app'.$ds.'MyDeadController.php:14')
            ->doesntExpectOutputToContain('App\MyDeadController@myAction3')
            ->run();

        $this->assertEquals(1, $r);
    }

    private function mainPath()
    {
        return app_path('MyDeadController.php');
    }
}