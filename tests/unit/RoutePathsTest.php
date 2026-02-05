<?php

namespace Tests\SpyClasses;

use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\LaravelMicroscope\SpyClasses\RoutePaths;
use Imanghafoori\LaravelMicroscope\Foundations\FileReaders\BasePath;
use PHPUnit\Framework\Attributes\Test;

class RoutePathsTest extends TestCase
{
    private $originalBasePath;
    private $originalPaths;
    private $originalProviders;
    private $originalAdditionalFiles;

    protected function setUp(): void
    {
        parent::setUp();

        // Store original static properties
        $this->originalBasePath = BasePath::$path;
        $this->originalPaths = RoutePaths::$paths;
        $this->originalProviders = RoutePaths::$providers;
        $this->originalAdditionalFiles = RoutePaths::$additionalFiles;

        // Reset static properties before each test
        RoutePaths::$paths = [];
        RoutePaths::$providers = [];
        RoutePaths::$additionalFiles = [];
    }

    protected function tearDown(): void
    {
        // Restore original static properties
        BasePath::$path = $this->originalBasePath;
        RoutePaths::$paths = $this->originalPaths;
        RoutePaths::$providers = $this->originalProviders;
        RoutePaths::$additionalFiles = $this->originalAdditionalFiles;
        @unlink(app_path('RouteServiceProvider.php'));
        parent::tearDown();
    }

    #[Test] public function it_returns_direct_paths()
    {
        copy(__DIR__.'/SampleRouteServiceProvider.stub', app_path('RouteServiceProvider.php'));
        $ds = DIRECTORY_SEPARATOR;
        // Arrange
        RoutePaths::$paths = [
            "{$ds}var{$ds}www{$ds}project{$ds}routes{$ds}web.php",
            "{$ds}var{$ds}www{$ds}project{$ds}routes{$ds}api.php",
        ];

        RoutePaths::$additionalFiles = [
            "{$ds}var{$ds}www{$ds}project{$ds}routes{$ds}web.php",
            "{$ds}var{$ds}www{$ds}project{$ds}routes{$ds}api.php",
        ];
        // Arrange
        $providerClass = 'App\RouteServiceProvider';
        RoutePaths::$providers = [$providerClass];

        $result = iterator_to_array(RoutePaths::get());


        $this->assertEquals([
            "{$ds}var{$ds}www{$ds}project{$ds}routes{$ds}web.php",
            "{$ds}var{$ds}www{$ds}project{$ds}routes{$ds}api.php",
            base_path("routes{$ds}web.php"),
        ], $result);
    }
}
