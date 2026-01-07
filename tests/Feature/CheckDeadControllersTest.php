<?php

use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\ImportAnalyzer\ErrorReporters\ErrorPrinter;

class CheckDeadControllersTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        copy(__DIR__.'/CheckDeadControllersTest/init.stub', $this->mainPath());
        copy(__DIR__.'/CheckDeadControllersTest/abstractCtrl.stub', app_path('AbstractCtrl.php'));
        copy(__DIR__.'/CheckDeadControllersTest/invokable.stub', app_path('InvokableCtrl.php'));
    }

    public function tearDown(): void
    {
        @unlink($this->mainPath());
        @unlink(app_path('AbstractCtrl.php'));
        @unlink(app_path('InvokableCtrl.php'));
        ErrorPrinter::$instance = null;
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
            ->expectsOutputToContain('App\InvokableCtrl@__invoke')
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