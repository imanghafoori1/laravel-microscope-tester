<?php

use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\LaravelMicroscope\Features\CheckUnusedBladeVars\UnusedVarsInstaller;
use Imanghafoori\LaravelMicroscope\Features\RouteOverride\RouteDefinitionPrinter;

class CheckUnusedBladeVarsTest extends TestCase
{
    public function tearDown(): void
    {
        @unlink(storage_path('logs/test.log'));
        @unlink($this->bladeFile());
        @rmdir(resource_path('views'));
        @rmdir(resource_path());
        parent::tearDown();
    }
    public function test()
    {
        $logfile = storage_path('logs/test.log');

        // make a sample blade file:
        @mkdir(resource_path('views'), 0777, true);
        file_put_contents($this->bladeFile(), '{{ $viewsData1 }}');

        config()->set('microscope.log_unused_view_vars', true);
        config()->set('logging.default', 'single');
        config()->set('logging.channels.single.path', $logfile);

        // make the log file empty
        file_put_contents($logfile, '');
        UnusedVarsInstaller::spyView();
        view('unused', [
            'viewsData1' => 'a', // <= is used
            'viewsData2' => 'b', // <= not used
            'viewsData3' => 'c', // <= not used
        ])->render();

        // simulate termination:
        (UnusedVarsInstaller::install())();

        $log = file_get_contents($logfile);

        $this->assertIsInt(strpos($log, '$viewsData2'));
        $this->assertIsInt(strpos($log, '$viewsData3'));
        $this->assertIsInt(strpos($log, 'Laravel Microscope - The view file "unused"'));
    }

    private function bladeFile(): string
    {
        return resource_path('views/unused.blade.php');
    }
}
