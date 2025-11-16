<?php

declare(strict_types = 1);

namespace Sweetchuck\BehatTapFormatter\Tests\Helper;

use Behat\Testwork\Output\Printer\OutputPrinter;
use Symfony\Component\Console\Output\BufferedOutput;

class DummyOutputPrinter implements OutputPrinter
{

    /**
     * @var array<\Symfony\Component\Console\Output\BufferedOutput>
     */
    public array $outputs = [];

    /**
     * @param string $outputPath
     * @param array<string, mixed> $outputStyles
     */
    public function __construct(
        protected string $outputPath = '',
        protected array $outputStyles = [],
    ) {
        $this->outputs[] = new BufferedOutput();
    }

    /**
     * {@inheritdoc}
     */
    public function getOutputPath(): ?string
    {
        return $this->outputPath;
    }

    /**
     * {@inheritdoc}
     */
    public function setOutputPath($path): static
    {
        $this->outputPath = $path;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @return array<string, mixed>
     */
    public function getOutputStyles(): array
    {
        return $this->outputStyles;
    }

    /**
     * {@inheritdoc}
     *
     * @param array<string, mixed> $styles
     */
    public function setOutputStyles(array $styles): static
    {
        $this->outputStyles = $styles;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function isOutputDecorated(): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function setOutputDecorated($decorated): static
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getOutputVerbosity(): int
    {
        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function setOutputVerbosity($level): static
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @param string|array<string> $messages
     */
    public function write($messages): static
    {
        $this->outputs[0]->write($messages);

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @param string|array<string> $messages
     */
    public function writeln($messages = ''): static
    {
        $this->outputs[0]->writeln($messages);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function flush(): static
    {
        array_unshift($this->outputs, new BufferedOutput());

        return $this;
    }
}
