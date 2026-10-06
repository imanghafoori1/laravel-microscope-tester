<?php

use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\LaravelMicroscope\ErrorReporters\ErrorPrinter;
use Imanghafoori\LaravelMicroscope\Foundations\Color;
use Imanghafoori\LaravelMicroscope\Foundations\Console;
use Imanghafoori\LaravelMicroscope\SpyClasses\RoutePaths;

class CheckDeadControllersTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Color::$color = false;
        Console::recoredWrites();
        ErrorPrinter::$instance = null;
        ErrorPrinter::$terminalWidth = 10;

        copy(__DIR__.'/CheckDeadControllersTest/init.stub', $this->tmpFileUnderTest());
        copy(__DIR__.'/CheckDeadControllersTest/abstractCtrl.stub', app_path('AbstractCtrl.php'));
        copy(__DIR__.'/CheckDeadControllersTest/invokable.stub', app_path('InvokableCtrl.php'));
    }

    public function tearDown(): void
    {
        Console::reset();
        ErrorPrinter::$instance = null;
        RoutePaths::$providers = [];
        RoutePaths::$additionalFiles = [];

        @unlink($this->tmpFileUnderTest());
        @unlink(app_path('AbstractCtrl.php'));
        @unlink(app_path('InvokableCtrl.php'));

        parent::tearDown();
    }

    public function test()
    {
        $ds = DIRECTORY_SEPARATOR;
        $r = $this->artisan('check:dead_controllers')->run();

        $write = (Console::$instance)->writeln;
        unset($write[12]);

         $this->assertTrue(in_array("   1 No route is defined for controller action:", $write));
         $this->assertTrue(in_array("   App\InvokableCtrl@__invoke", $write));
         $this->assertTrue(in_array("at app{$ds}InvokableCtrl.php:9", $write));
         $this->assertTrue(in_array("_______", $write));
         $this->assertTrue(in_array("   2 No route is defined for controller action:", $write));
         $this->assertTrue(in_array("   App\MyDeadController@myAction1", $write));
         $this->assertTrue(in_array("at app{$ds}MyDeadController.php:14", $write));
         $this->assertTrue(in_array("_______", $write));
         $this->assertTrue(in_array("   3 No route is defined for controller action:", $write));
         $this->assertTrue(in_array("   App\MyDeadController@myAction2", $write));
         $this->assertTrue(in_array("at app{$ds}MyDeadController.php:19", $write));

        $this->assertEquals(1, $r);
    }

    private function tmpFileUnderTest()
    {
        return app_path('MyDeadController.php');
    }
}