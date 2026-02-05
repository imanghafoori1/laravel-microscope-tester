<?php

use Illuminate\Foundation\Testing\TestCase;

class CheckFacadesTest extends TestCase
{
    public function tearDown(): void
    {
        @unlink(app_path('SampleFacade.php'));
        @unlink(app_path('MySampleRoot.php'));
        @unlink(app_path('NoRootFacade.php'));
        parent::tearDown();
    }

    public function test_1()
    {
        copy(__DIR__.'/CheckFacadeDocblocksStubs/SampleFacade.stub', app_path('SampleFacade.php'));
        copy(__DIR__.'/CheckFacadeDocblocksStubs/MySampleRoot.stub', app_path('MySampleRoot.php'));

        $r = $this->artisan('check:facades')->run();

        $this->assertEquals(
            file_get_contents(__DIR__.'/CheckFacadeDocblocksStubs/SampleFacade-result.stub'),
            file_get_contents(app_path('SampleFacade.php'))
        );

        $this->assertEquals(1, $r);
    }

    public function test_2()
    {
        copy(__DIR__.'/CheckFacadeDocblocksStubs/NoRootFacade.stub', app_path('NoRootFacade.php'));

        $r = $this->artisan('check:facades')->run();

        $this->assertEquals(
            file_get_contents(__DIR__.'/CheckFacadeDocblocksStubs/NoRootFacade.stub'),
            file_get_contents(app_path('NoRootFacade.php'))
        );

        $this->assertEquals(1, $r);
    }
}
