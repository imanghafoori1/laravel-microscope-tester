<?php

namespace Imanghafoori\LaravelMicroscope\Tests\Unit\EnforceImports;

use Imanghafoori\LaravelMicroscope\Features\EnforceImports\EnforceImportsCheck;
use Imanghafoori\LaravelMicroscope\Foundations\PhpFileDescriptor;
use PHPUnit\Framework\Attributes\Test;
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

    #[Test]
    public function testFixFile()
    {
        EnforceImportsCheck::setOptions(false, 'U3');
        $result = EnforceImportsCheck::performCheck(
            PhpFileDescriptor::make(__DIR__.DIRECTORY_SEPARATOR.'imports.temp')
        );

        $actual = file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'imports.temp');
        $expected = file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'imports-expected.stub');
        $this->assertEquals($expected, $actual);
        $this->assertEquals(true, $result);
    }
}
