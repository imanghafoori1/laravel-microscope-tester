<?php

use App\MyController;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Route;
use Imanghafoori\LaravelMicroscope\Foundations\Color;

class CheckActionCommentTest extends TestCase
{
    public function setUp(): void
    {
        Color::$color = false;
        parent::setUp();
    }

    public function tearDown(): void
    {
        Color::$color = true;
        @unlink($this->tmpFileUnderTest());
        parent::tearDown();
    }

    public function test_adds_missing_comment()
    {
        copy(__DIR__.'/CheckActionCommentStubs/sample-controller-2.stub', $this->tmpFileUnderTest());
        Route::get('/url-route', [MyController::class, 'myAction'])
            ->middleware('web')
            ->name('name_route');

        $this->artisan('check:action_comments')
            ->expectsQuestion('Add route definition into the: App\MyController', true)
            ->expectsOutputToContain('Commentify Route Actions...')
            ->run();

        $this->assertEquals(
            file_get_contents(__DIR__.'/CheckActionCommentStubs/expected.stub'),
            file_get_contents($this->tmpFileUnderTest())
        );
    }

    public function test_updates_existing_comment()
    {
        copy(__DIR__.'/CheckActionCommentStubs/sample-controller.stub', $this->tmpFileUnderTest());
        Route::get('/url-route', [MyController::class, 'myAction'])
            ->middleware('web')
            ->name('name_route');

        $this->artisan('check:action_comments')
            ->expectsQuestion('Add route definition into the: App\MyController', true)
            ->expectsOutputToContain('Commentify Route Actions...')
            ->run();

        $this->assertEquals(
            file_get_contents(__DIR__.'/CheckActionCommentStubs/expected.stub'),
            file_get_contents($this->tmpFileUnderTest())
        );
    }

    private function tmpFileUnderTest(): string
    {
        return app_path('MyController.php');
    }
}
