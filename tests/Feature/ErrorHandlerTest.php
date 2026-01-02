<?php

use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Composer;
use Imanghafoori\LaravelMicroscope\ErrorReporters\ErrorPrinter;
use Imanghafoori\LaravelMicroscope\Foundations\Handlers\ErrorExceptionHandler;
use Imanghafoori\LaravelMicroscope\Foundations\Loop;

class ErrorHandlerTest extends TestCase
{
    public function test()
    {
        $obj = new class
        {
            public function dumpAutoloads()
            {
                $_SESSION['-dumpAutoloads-'] = 1;
            }
        };
        app()->bind(Composer::class, fn () => $obj);
        ErrorPrinter::singleton()->printer = new class
        {
            public function writeln()
            {
                //
            }
        };
        ErrorExceptionHandler::$exceptionOrigin = 'Feature|ErrorHandlerTest.php';
        $this->assertNull(ErrorExceptionHandler::handle(new RuntimeException('vendor')));
        $this->assertEquals(1, $_SESSION['-dumpAutoloads-']);
        unset($_SESSION['-dumpAutoloads-']);
    }

    public function test_loop()
    {
        $count = Loop::walkCount([1, 2, 3], fn ($int) => true);
        $this->assertEquals(3, $count);
        $count = Loop::walkCount([1, 2, 3], fn ($int) => false);
        $this->assertEquals(0, $count);

        $list = Loop::mapToList([1, 2, 3], fn ($int) => $int + 1);
        $this->assertEquals([2, 3, 4], $list);

        $list = Loop::mapIf([1, 2, 3], fn ($int) => $int > 1, fn ($int, $key) => [$key => $int + 1]);
        $this->assertEquals([1 => 3, 2 => 4], $list);
    }
}
