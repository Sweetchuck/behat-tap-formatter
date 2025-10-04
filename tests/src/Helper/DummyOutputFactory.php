<?php

declare(strict_types = 1);

namespace Sweetchuck\BehatTapFormatter\Tests\Helper;

use Behat\Testwork\Output\Printer\Factory\OutputFactory;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

class DummyOutputFactory extends OutputFactory
{
    /**
     * @var array<\Symfony\Component\Console\Output\BufferedOutput>
     */
    public array $outputs = [];

    /**
     * {@inheritdoc}
     */
    public function createOutput(): OutputInterface
    {
        $output = new BufferedOutput();
        $this->outputs[] = $output;

        return $output;
    }
}
