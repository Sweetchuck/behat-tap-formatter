<?php

declare(strict_types = 1);

namespace Sweetchuck\BehatTapFormatter;

use Behat\Testwork\Output\Printer\OutputPrinter;
use Symfony\Component\Yaml\Yaml;

/**
 * @see https://testanything.org/tap-version-14-specification.html
 *
 * @todo Move TAP writer/parser/reader to a separate package.
 */
class TapWriter implements TapWriterInterface
{
    public function __construct(
        protected ?OutputPrinter $printer = null,
    ) {
    }

    public function getPrinter(): ?OutputPrinter
    {
        return $this->printer;
    }

    public function setPrinter(OutputPrinter $printer): static
    {
        $this->printer = $printer;

        return $this;
    }

    // region depth
    protected string $linePrefix = '';

    /**
     * @var int<0, max>
     */
    protected int $depth = 0;

    /**
     * @return int<0, max>
     */
    public function getDepth(): int
    {
        return $this->depth;
    }

    public function incrementDepth(): static
    {
        $this->depth++;
        $this->updateLinePrefix();

        return $this;
    }

    public function decrementDepth(): static
    {
        if ($this->depth === 0) {
            throw new \LogicException('Cannot decrement depth below zero.');
        }

        $this->depth--;
        $this->updateLinePrefix();

        return $this;
    }

    protected function updateLinePrefix(): static
    {
        $this->linePrefix = str_repeat('    ', $this->depth);

        return $this;
    }
    // endregion

    // region TAP
    public function tapVersion(): static
    {
        if ($this->depth !== 0) {
            throw new \LogicException('Cannot write TAP version outside of the root test suite.');
        }

        $this->printer->writeln('TAP version 14');

        return $this;
    }

    /**
     * @see https://testanything.org/tap-version-14-specification.html#plan
     */
    public function tapPlan(int $amount): static
    {
        $this->printer->writeln(sprintf(
            '%s1..%d',
            $this->linePrefix,
            $amount,
        ));

        return $this;
    }

    public function tapComment(?string $lines): static
    {
        if ($lines === null) {
            return $this;
        }

        $printer = $this->printer;
        $lines = explode("\n", $lines);
        foreach ($lines as $line) {
            $printer->writeln($this->linePrefix . '#' . ($line === '' ? '' : ' ' . $line));
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function tapYamlBlock(?array $data): static
    {
        if ($data === null) {
            return $this;
        }

        $lines = explode("\n", $this->dataToTapYamlBlock($data));
        foreach ($lines as $line) {
            $this->printer->writeln($this->linePrefix . $line);
        }

        return $this;
    }

    /**
     * @phpstan-param behat-tap-formatter-tap-test-point $parts
     */
    public function tapTestPoint(array $parts): static
    {
        $pattern = $this->linePrefix . '%s';
        $args = [
            $parts['status'] ? 'ok' : 'not ok',
        ];

        if (!empty($parts['id'])) {
            $pattern .= ' %d';
            $args[] = $parts['id'];
        }

        if (!empty($parts['description'])) {
            $pattern .= ' - %s';
            $args[] = addcslashes($parts['description'], '#');
        }

        if (!empty($parts['directive']['id'])) {
            $pattern .= ' #%s';
            $args[] = strtoupper($parts['directive']['id']);

            if (!empty($parts['directive']['reason'])) {
                $pattern .= ' %s';
                $args[] = $parts['directive']['reason'];
            }
        }

        $this->tapComment($parts['comment'] ?? null);
        $this->printer->writeln(vsprintf($pattern, $args));
        $this->tapYamlBlock($parts['yamlBlock'] ?? null);

        return $this;
    }

    public function tapBailOut(array $parts): static
    {
        $this->tapComment($parts['comment'] ?? null);
        $mainText = 'Bail out!';
        if (!empty($parts['description'])) {
            $mainText .= ' ' . $parts['description'];
        }
        $this->printer->writeln($mainText);

        return $this;
    }
    // endregion

    /**
     * @todo SubTest depth support.
     *
     * @param array<string, mixed> $params
     */
    protected function dataToTapYamlBlock(array $params): string
    {
        $block = Yaml::dump(
            $params,
            99,
            2,
            Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK,
        );
        $block = str_replace("\n", "\n  ", $block);

        return "  ---\n  {$block}...";
    }
}
