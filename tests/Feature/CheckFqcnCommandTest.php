<?php

use Illuminate\Foundation\Testing\TestCase;

class CheckFqcnCommandTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        @unlink(app_path('Fqcn.php'));
        copy(__DIR__.'/CheckFqcn/initial.stub', app_path('Fqcn.php'));
    }

    public function tearDown(): void
    {
        $path = app_path('Fqcn.php');
        parent::tearDown();
        unlink($path);
    }

    public function test()
    {
        $ds = DIRECTORY_SEPARATOR;
        $this->artisan('check:fqcn --fix')
            ->expectsOutputToContain('FQCN is already imported with an alias: G')
            ->expectsOutputToContain('FQCN is already on the same namespace.')
            ->expectsOutputToContain('FQCN is already imported at line: 5')
            ->expectsOutputToContain("at app{$ds}Fqcn.php:13")
            ->expectsOutputToContain("at app{$ds}Fqcn.php:14")
            ->expectsOutputToContain("at app{$ds}Fqcn.php:15")
            ->expectsOutputToContain("at app{$ds}Fqcn.php:18")
            ->expectsOutputToContain("at app{$ds}Fqcn.php:19")
            ->expectsOutputToContain('\C\E')
            ->expectsOutputToContain('\He\R\T\U2')
            ->expectsOutputToContain('\He\R\T\Hh can be replaced with: G')
            ->run();

        $expected = file_get_contents(__DIR__.'/CheckFqcn/expected.stub');
        $this->assertEquals($expected, file_get_contents(app_path('Fqcn.php')));
    }
}