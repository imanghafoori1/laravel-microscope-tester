<?php

use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\LaravelMicroscope\Foundations\Color;
use Imanghafoori\LaravelMicroscope\Foundations\Console;

class CheckExtraSemiColonsTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Console::enforceTrue();
        Console::recoredWrites();
        Color::$color = false;
    }

    public function tearDown(): void
    {
        Console::reset();
        @unlink($this->tmpFileUnderTest());

        parent::tearDown();
    }

    public function test()
    {
        copy(__DIR__.'/CheckExtraSemiColonStubs/init.stub', $this->tmpFileUnderTest());

        $r = $this->artisan('check:extra_semi_colons')
            ->assertFailed()
            ->run();
        $this->assertEquals([
            'Do you want to replace extra_semi_colons.php with new version of it?',
            'Do you want to replace extra_semi_colons.php with new version of it?',
            'Do you want to replace extra_semi_colons.php with new version of it?',
            'Do you want to replace extra_semi_colons.php with new version of it?',
        ], Console::$askedConfirmations);

        $this->assertFileEquals(
            __DIR__.'/CheckExtraSemiColonStubs/expected.stub',
            $this->tmpFileUnderTest()
        );

        $cachePath = storage_path('framework/cache/microscope/extra_semi_colons-v1.php');
        $this->assertFileExists($cachePath);
        $content = require $cachePath;
        $this->assertIsArray($content);
        $this->assertTrue(in_array('helpers.php', $content));
        $this->assertTrue(in_array('a.php', $content));
        $this->assertTrue(in_array('web.php', $content));
        $this->assertTrue(in_array('UserFactory.php', $content));
        $this->assertTrue(in_array('DatabaseSeeder.php', $content));

        @unlink($cachePath);
    }

    private function tmpFileUnderTest()
    {
        return app_path('extra_semi_colons.php');
    }
}
