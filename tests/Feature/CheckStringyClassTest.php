<?php

use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\LaravelMicroscope\Features\CheckDD\CheckDD;
use Imanghafoori\LaravelMicroscope\Foundations\Color;

class CheckStringyClassTest extends TestCase
{
    public function setUp(): void
    {
        CheckDD::$cache = false;
        Color::$color = false;
        parent::setUp();
    }

    public function tearDown(): void
    {
        CheckDD::$cache = true;
        Color::$color = true;
        @unlink($this->tmpFileUnderTest());
        @unlink(app_path('Models/stringy.php'));
        parent::tearDown();
    }

    public function test_0()
    {
        copy(__DIR__.'/CheckStringyClassStubs/init.stub', $this->tmpFileUnderTest());

        $r = $this->artisan('check:stringy_classes')
            ->expectsConfirmation("Replace: 'App\\Models\\User' with ::class version of it?", 'no')
            ->expectsConfirmation("Replace: '\\App\\Models\\User' with ::class version of it?", 'no')
            ->run();

        $this->assertEquals(
            file_get_contents(__DIR__.'/CheckStringyClassStubs/init.stub'),
            file_get_contents($this->tmpFileUnderTest())
        );

        $this->assertEquals(1, $r);
    }

    public function test_1()
    {
        copy(__DIR__.'/CheckStringyClassStubs/init.stub', $this->tmpFileUnderTest());

        $r = $this->artisan('check:stringy_classes')
            ->expectsConfirmation("Replace: 'App\\Models\\User' with ::class version of it?", 'yes')
            ->expectsConfirmation("Replace: '\\App\\Models\\User' with ::class version of it?", 'yes')
            ->run();

        $this->assertEquals(
            file_get_contents(__DIR__.'/CheckStringyClassStubs/expect.stub'),
            file_get_contents($this->tmpFileUnderTest())
        );

        $this->assertEquals(1, $r);
    }

    public function test_2()
    {
        $mainPath = app_path('Models/stringy.php');
        copy(__DIR__.'/CheckStringyClassStubs/init-2.stub', $mainPath);

        $ds = DIRECTORY_SEPARATOR;
        $r = $this->artisan('check:stringy_classes')
            ->expectsConfirmation("Replace: 'App\\Models\\User' with ::class version of it?", 'yes')
            ->expectsConfirmation("Replace: '\\App\\Models\\User' with ::class version of it?", 'yes')
            ->expectsOutputToContain("✔ Replaced with: User::class")
            //
            ->expectsOutputToContain('1 Class does not exist:')
            ->expectsOutputToContain("'App\\Models\\User2'")
            ->expectsOutputToContain("at app{$ds}Models{$ds}stringy.php:6")
            ->expectsOutputToContain("at app{$ds}Models{$ds}stringy.php:7")
            //
            //->expectsOutputToContain('2 Class does not exist:')
            //->expectsOutputToContain("at app{$ds}Models{$ds}stringy.php:8")
            //
            //->expectsOutputToContain('3 Class does not exist:')
            //->expectsOutputToContain('\'App\Models\User2@hello\'')
            //->expectsOutputToContain("at app{$ds}Models{$ds}stringy.php:9")
            //
            //->expectsOutputToContain('App\\Models\\User2')
            //
            //->expectsOutputToContain('4 Method does not exist:')
            //->expectsOutputToContain('App\\Models\\User@hello')
            //->expectsOutputToContain("at app{$ds}Models{$ds}stringy.php:10")
            ->run();

        $this->assertEquals(
            file_get_contents(__DIR__.'/CheckStringyClassStubs/expect-2.stub'),
            file_get_contents($mainPath)
        );

        $this->assertEquals(1, $r);
    }

    private function tmpFileUnderTest()
    {
        return app_path('stringy.php');
    }
}
