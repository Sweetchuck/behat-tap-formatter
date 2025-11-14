<?php

declare(strict_types = 1);

namespace Sweetchuck\BehatTapFormatter;

/**
 * @see https://testanything.org/tap-version-14-specification.html
 *
 * @todo Move TAP writer/parser/reader to a separate package.
 */
interface TapWriterInterface
{
    public function getDepth(): int;

    public function incrementDepth(): static;

    public function decrementDepth(): static;

    public function startSubTest(?string $description): static;

    public function endSubTest(int $amount): static;

    public function tapVersion(): static;

    /**
     * The "1..$amount" line.
     *
     * @see https://testanything.org/tap-version-14-specification.html#plan
     */
    public function tapPlan(int $amount): static;

    public function tapComment(?string $lines): static;

    /**
     * @param null|array<string, mixed> $data
     */
    public function tapYamlBlock(?array $data): static;

    /**
     * @phpstan-param behat-tap-formatter-tap-test-point $parts
     */
    public function tapTestPoint(array $parts): static;
}
