<?php

declare(strict_types = 1);

namespace Sweetchuck\BehatTapFormatter\Tests\Helper;

use Behat\Behat\Definition\Definition;
use Behat\Behat\Definition\SearchResult;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Tester\Result\DefinedStepResult;
use Behat\Behat\Tester\Result\StepResult;
use Behat\Testwork\Call\CallResult;
use Behat\Testwork\Tester\Result\ExceptionResult;

class DummyExecutedStepResult implements StepResult, DefinedStepResult, ExceptionResult
{
    public function __construct(
        protected ?SearchResult $searchResult = null,
        protected ?CallResult $callResult = null,
    ) {
    }

    public function getSearchResult(): ?SearchResult
    {
        return $this->searchResult;
    }

    public function setSearchResult(SearchResult $searchResult): static
    {
        $this->searchResult = $searchResult;

        return $this;
    }

    public function getCallResult(): ?CallResult
    {
        return $this->callResult;
    }

    public function setCallResult(CallResult $callResult): static
    {
        $this->callResult = $callResult;

        return $this;
    }

    public function getStepDefinition(): ?Definition
    {
        return $this->searchResult->getMatchedDefinition();
    }

    public function hasException(): bool
    {
        return null !== $this->getException();
    }

    public function getException(): ?\Exception
    {
        return $this->callResult->getException();
    }

    public function getResultCode(): int
    {
        if ($this->callResult->hasException() && $this->callResult->getException() instanceof PendingException) {
            return self::PENDING;
        }

        if ($this->callResult->hasException()) {
            return self::FAILED;
        }

        return self::PASSED;
    }

    public function isPassed(): bool
    {
        return self::PASSED == $this->getResultCode();
    }
}
