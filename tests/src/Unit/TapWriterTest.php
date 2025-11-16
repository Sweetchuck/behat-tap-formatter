<?php

declare(strict_types = 1);

namespace Sweetchuck\BehatTapFormatter\Tests\Unit;

use Behat\Testwork\Output\Printer\OutputPrinter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Sweetchuck\BehatTapFormatter\TapWriter;
use Sweetchuck\BehatTapFormatter\Tests\Helper\DummyOutputPrinter;

#[CoversClass(TapWriter::class)]
class TapWriterTest extends TestBase
{
    protected function createInstance(OutputPrinter $outputPrinter): TapWriter
    {
        return new TapWriter($outputPrinter);
    }

    #[Test]
    public function testBasic(): void
    {
        $outputPrinter = new DummyOutputPrinter();
        $tapWriter = $this->createInstance($outputPrinter);

        $tapWriter
            ->tapVersion()
            ->tapPlan(3)
            ->tapComment('My comment 01')
            ->tapTestPoint([
                'status' => true,
                'id' => 1,
                'description' => 'My test 01 with # hash mark',
                'directive' => [
                    'id' => 'TODO',
                    'reason' => 'My reason',
                ],
            ])
            ->startSubTest('My subtest 03')
            ->tapTestPoint([
                'status' => true,
                'id' => 1,
                'yamlBlock' => [
                    'key' => 'value',
                ]
            ])
            ->endSubTest(1)
            ->tapTestPoint([
                'comment' => 'My comment 02',
                'status' => false,
                'id' => 2,
                'description' => 'My test 02',
            ])
            ->tapBailOut([
                'comment' => 'My bail out',
                'description' => 'My bail out description'
            ]);

        $expected = <<< 'TEXT'
            TAP version 14
            1..3
            # My comment 01
            ok 1 - My test 01 with \# hash mark # TODO My reason
            # Subtest: My subtest 03
                ok 1
                  ---
                  key: value
                  ...
                1..1
            # My comment 02
            not ok 2 - My test 02
            # My bail out
            Bail out! My bail out description

            TEXT;

        static::assertSame($expected, $outputPrinter->outputs[0]->fetch());
    }
}
