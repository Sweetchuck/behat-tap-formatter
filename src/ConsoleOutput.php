<?php

declare(strict_types = 1);

namespace Sweetchuck\BehatTapFormatter;

use Behat\Testwork\Output\Printer\OutputPrinter;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleOutput implements OutputPrinter
{
    public function __construct(
        protected OutputInterface $output,
    ) {
    }

    protected ?string $outputPath = null;

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
     * @var array<string, mixed>
     */
    protected array $styles = [];

    /**
     * {@inheritdoc}
     *
     * @return array<string, mixed>
     */
    public function getOutputStyles(): array
    {
        return $this->styles;
    }

    /**
     * {@inheritdoc}
     *
     * @param array<string, mixed> $styles
     */
    public function setOutputStyles(array $styles): static
    {
        $this->styles = $styles;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isOutputDecorated(): bool
    {
        return $this->output->isDecorated();
    }

    /**
     * {@inheritdoc}
     */
    public function setOutputDecorated($decorated): static
    {
        $this->output->setDecorated($decorated);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getOutputVerbosity(): int
    {
        return $this->output->getVerbosity();
    }

    /**
     * {@inheritdoc}
     *
     * @phpstan-param \Symfony\Component\Console\Output\OutputInterface::VERBOSITY_* $level
     */
    public function setOutputVerbosity($level): static
    {
        $this->output->setVerbosity($level);

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @phpstan-param string|array<string> $messages
     */
    public function write($messages): static
    {
        $this->output->write($messages);

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @phpstan-param string|array<string> $messages
     */
    public function writeln($messages = ''): static
    {
        $this->output->writeln($messages);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function flush(): static
    {
        return $this;
    }
}
