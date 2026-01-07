<?php

use App\MyController;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Route;
use Imanghafoori\ImportAnalyzer\ErrorReporters\ErrorPrinter;
use Imanghafoori\LaravelMicroscope\Foundations\Reports\ComposerJsonReport;

class CheckActionCommentTest extends TestCase
{
    public function tearDown(): void
    {
        ErrorPrinter::$instance = null;
        ComposerJsonReport::$callback = null;
        @unlink($this->mainPath());
        parent::tearDown();
    }

    public function test()
    {
        copy(__DIR__.'/CheckActionCommentStubs/init.stub', $this->mainPath());
        Route::get('/url-route', [MyController::class, 'myAction'])
            ->middleware('web')
            ->name('name_route');

        $this->artisan('check:action_comments')
            ->expectsQuestion('Add route definition into the: <fg=yellow>App\MyController</>', true)
            ->expectsOutputToContain('Commentify Route Actions...')
            ->run();
        $this->assertEquals(
            file_get_contents(__DIR__.'/CheckActionCommentStubs/expected.stub'),
            file_get_contents($this->mainPath())
        );
    }

    public function test_2()
    {
        copy(__DIR__.'/CheckActionCommentStubs/init-2.stub', $this->mainPath());
        Route::get('/url-route', [MyController::class, 'myAction'])
            ->middleware('web')
            ->name('name_route');

        $this->artisan('check:action_comments')
            ->expectsQuestion('Add route definition into the: <fg=yellow>App\MyController</>', true)
            ->expectsOutputToContain('Commentify Route Actions...')
            ->run();

        $this->assertEquals(
            file_get_contents(__DIR__.'/CheckActionCommentStubs/expected.stub'),
            file_get_contents($this->mainPath())
        );
    }

    private function mainPath(): string
    {
        return app_path('MyController.php');
    }
}