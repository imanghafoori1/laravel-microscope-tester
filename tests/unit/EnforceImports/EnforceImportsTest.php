<?php

namespace Imanghafoori\LaravelMicroscope\Tests\Unit;

use Imanghafoori\LaravelMicroscope\Features\EnforceImports\EnforceImports;
use Imanghafoori\LaravelMicroscope\Foundations\PhpFileDescriptor;
use PHPUnit\Framework\TestCase;

class EnforceImportsTest extends TestCase
{
    public function setUp(): void
    {
        copy(__DIR__.'/imports-initial.stub', __DIR__.'/imports.temp');
    }

    public function tearDown(): void
    {
        unlink(__DIR__.'/imports.temp');
    }

    public function testFixFile()
    {
        EnforceImports::setOptions(false, 'U3', function ($err) {
        });
        $result = EnforceImports::performCheck(
            PhpFileDescriptor::make(__DIR__.DIRECTORY_SEPARATOR.'imports.temp')
        );

        $actual = file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'imports.temp');
        $expected = file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'imports-expected.stub');
        $this->assertEquals($expected, $actual);
        $this->assertEquals(true, $result);
    }
}
