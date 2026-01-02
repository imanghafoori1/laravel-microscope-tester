<?php

use App\MyController;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Route;
use Imanghafoori\LaravelMicroscope\Foundations\Reports\ComposerJsonReport;

class CheckActionCommentTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        copy(__DIR__.'/CheckActionCommentStubs/init.stub', $this->mainPath());
    }

    public function tearDown(): void
    {
        ComposerJsonReport::$callback = null;
        @unlink($this->mainPath());
        parent::tearDown();
    }

    public function test()
    {
        Route::get('/url-route', [MyController::class, 'myAction'])->name('name_route');

        $this->artisan('check:action_comments')
            ->expectsQuestion('Add route definition into the: <fg=yellow>App\MyController</>', true)
            ->expectsOutputToContain('Commentify Route Actions...')
            ->run();
    }

    private function mainPath(): string
    {
        return app_path('MyController.php');
    }
}