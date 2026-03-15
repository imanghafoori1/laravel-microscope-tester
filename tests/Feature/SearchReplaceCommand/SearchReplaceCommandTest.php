<?php

use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\LaravelMicroscope\ErrorReporters\ErrorPrinter;
use Imanghafoori\LaravelMicroscope\ErrorReporters\Printer;
use Imanghafoori\LaravelMicroscope\Features\SearchReplace\FullNamespaceIs;
use Imanghafoori\LaravelMicroscope\Features\SearchReplace\PatternRefactorings;
use Imanghafoori\LaravelMicroscope\Foundations\Color;
use Imanghafoori\LaravelMicroscope\Foundations\Console;
use Imanghafoori\SearchReplace\Filters;

class SearchReplaceCommandTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Console::recoredWrites();
        ErrorPrinter::$instance = null;
        Color::$color = false;
    }

    public function tearDown(): void
    {
        @unlink($this->tmpFileUnderTest());
        @unlink(app_path('sample.php'));
        PatternRefactorings::$patternFound = false;
        Console::reset();
        ErrorPrinter::$instance = null;
        parent::tearDown();
    }

    public function test_0()
    {
        $this->artisan('search_replace')->assertOk()->run();

        $this->assertFileExists($this->tmpFileUnderTest());
        @unlink($this->tmpFileUnderTest());
        copy(__DIR__.'/SearchReplaceCommandStub/init.stub', $this->tmpFileUnderTest());
        $this->artisan('search_replace --nofix')->run();

        file_put_contents($this->tmpFileUnderTest(), '<?php return [];');
        $this->artisan('search_replace')->run();
    }

    public function test_1()
    {
        $this->artisan('search_replace')->assertOk()->run();
        $this->assertFileExists($this->tmpFileUnderTest());
        copy(__DIR__.'/SearchReplaceCommandStub/init_no_replace.stub', $this->tmpFileUnderTest());

        Color::$color = false;
        Console::recoredWrites();
        ErrorPrinter::singleton()->printer = new Printer(Console::$instance);
        $this->artisan('search_replace --tag=tag1')
            ->assertFailed()
            ->run();

        $ds = DIRECTORY_SEPARATOR;

        $write = (Console::$instance)->writeln;

        $this->assertEquals($write[0], '   1 Pattern Matched: ');
        $this->assertEquals($write[1], '   Matched Code: protected function casts(): array');
        $this->assertEquals($write[2], 'at app'.$ds.'Models'.$ds.'User.php:24');
    }

    public function test_2()
    {
        Console::enforceTrue();
        Filters::$filters['full_namespace_is'] = FullNamespaceIs::class;
        copy(__DIR__.'/SearchReplaceCommandStub/search-pattern-init-1.stub', $this->tmpFileUnderTest());
        copy(__DIR__.'/SearchReplaceCommandStub/sample.stub', app_path('sample.php'));
        $this->artisan('search_replace --name=auth_id')->assertFailed()->run();
        Console::recoredWrites();

        $this->assertEquals([], (Console::$instance)->writeln);
        $this->assertEquals([
            'Do you want to replace sample.php with new version of it?'
        ], Console::$askedConfirmations);

        $this->assertFileEquals(
            __DIR__.'/SearchReplaceCommandStub/result.stub',
            app_path('sample.php')
        );
    }

    private function tmpFileUnderTest()
    {
        return base_path('search_replace.php');
    }
}
