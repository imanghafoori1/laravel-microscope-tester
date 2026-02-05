<?php

use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\LaravelMicroscope\ErrorReporters\ErrorPrinter;

class CheckFqcnCommandTest extends TestCase
{
    public function tearDown(): void
    {
        ErrorPrinter::$ignored = [];
        unlink($this->tmpFileUnderTest());
        @unlink(app_path('Fqcn2.php'));
        parent::tearDown();
    }

    public function test()
    {
        copy(__DIR__.'/CheckFqcn/initial.stub', $this->tmpFileUnderTest());
        $ds = DIRECTORY_SEPARATOR;
        $this->artisan('check:extra_fqcn --fix')
            ->expectsOutputToContain('FQCN is already imported with an alias: G')
            ->expectsOutputToContain('FQCN is already on the same namespace. (fixed)')
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

        $this->assertEquals(
            file_get_contents(__DIR__.'/CheckFqcn/expected.stub'),
            file_get_contents($this->tmpFileUnderTest())
        );
    }

    public function test_no_fix()
    {
        copy(__DIR__.'/CheckFqcn/initial.stub', $this->tmpFileUnderTest());
        copy(__DIR__.'/CheckFqcn/ignored-initial.stub', app_path('Fqcn2.php'));
        ErrorPrinter::$ignored = ['*Fqcn2.php'];
        $ds = DIRECTORY_SEPARATOR;
        $this->artisan('check:extra_fqcn')
            ->expectsOutputToContain('FQCN is already imported with an alias: G')
            ->expectsOutputToContain('FQCN is already on the same namespace.')
            ->expectsOutputToContain('FQCN is already imported at line: 5')
            ->expectsOutputToContain("at app{$ds}Fqcn.php:13")
            ->expectsOutputToContain("at app{$ds}Fqcn.php:14")
            ->expectsOutputToContain("at app{$ds}Fqcn.php:15")
            ->expectsOutputToContain("at app{$ds}Fqcn.php:18")
            ->expectsOutputToContain("at app{$ds}Fqcn.php:19")
            ->doesntExpectOutputToContain("at app{$ds}Fqcn2.php:9")
            ->doesntExpectOutputToContain("at app{$ds}Fqcn2.php:10")
            ->expectsOutputToContain('\C\E')
            ->expectsOutputToContain('\He\R\T\U2')
            ->expectsOutputToContain('\He\R\T\Hh can be replaced with: G')
            ->run();

        $this->assertEquals(
            file_get_contents(__DIR__.'/CheckFqcn/initial.stub'),
            file_get_contents($this->tmpFileUnderTest())
        );
    }

    private function tmpFileUnderTest(): string
    {
        return app_path('Fqcn.php');
    }
}
