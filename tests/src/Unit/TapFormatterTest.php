<?php

declare(strict_types = 1);

namespace Sweetchuck\BehatTapFormatter\Tests\Unit;

use Behat\Behat\Definition\Call\DefinitionCall;
use Behat\Behat\Definition\Call\Then;
use Behat\Behat\Definition\SearchResult;
use Behat\Behat\EventDispatcher\Event\AfterScenarioTested;
use Behat\Behat\EventDispatcher\Event\AfterStepTested;
use Behat\Behat\EventDispatcher\Event\BeforeScenarioTested;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\ScenarioNode;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Call\CallResult;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\EventDispatcher\Event\BeforeSuiteTested;
use Behat\Testwork\Output\Printer\Factory\OutputFactory;
use Behat\Testwork\Output\Printer\OutputPrinter;
use Behat\Testwork\Output\Printer\StreamOutputPrinter;
use Behat\Testwork\Specification\SpecificationIterator;
use Behat\Testwork\Suite\Suite;
use Behat\Testwork\Tester\Result\IntegerTestResult;
use Behat\Testwork\Tester\Result\TestResult;
use Behat\Testwork\Tester\Setup\SuccessfulTeardown;
use Sweetchuck\BehatTapFormatter\TapFormatter;
use Sweetchuck\BehatTapFormatter\TapWriter;
use Sweetchuck\BehatTapFormatter\Tests\Helper\DummyExecutedStepResult;
use Sweetchuck\BehatTapFormatter\Tests\Helper\DummyOutputFactory;

class TapFormatterTest extends TestBase
{
    protected function createOutputFactory(): DummyOutputFactory
    {
        return new DummyOutputFactory();
    }

    protected function createOutputPrinter(OutputFactory $outputFactory): OutputPrinter
    {
        return new StreamOutputPrinter($outputFactory);
    }

    public function testTapOutput(): void
    {
        $outputFactory = $this->createOutputFactory();
        $printer = $this->createOutputPrinter($outputFactory);
        $tapWriter = new TapWriter($printer);
        $formatter = new TapFormatter($printer, $tapWriter);
        $formatterParameters = [
            'show_trace' => false,
            'show_steps' => 'never',
        ];
        foreach ($formatterParameters as $key => $value) {
            $formatter->setParameter($key, $value);
        }

        $suite01 = $this->createMock(Suite::class);
        $suite01->method('getName')->willReturn('dummy-suite-01');
        $environment01 = $this->createMock(Environment::class);
        $environment01
            ->method('getSuite')
            ->willReturn($suite01);

        // Suite start.
        $specificationIterator = $this->createMock(SpecificationIterator::class);
        $eventSuiteTested = new BeforeSuiteTested($environment01, $specificationIterator);
        $formatter->onBeforeSuiteTested($eventSuiteTested);

        $feature01 = $this->createMock(FeatureNode::class);
        $feature01->method('getTitle')->willReturn('dummy-feature-01');

        $events =  $this->getScenarioEvents(
            $environment01,
            $feature01,
            [
                'scenario' => [
                    'title' => 'dummy-scenario-01',
                ],
            ],
        );
        $this->triggerScenarioEvents($formatter, $events);

        $events =  $this->getScenarioEvents(
            $environment01,
            $feature01,
            [
                'scenario' => [
                    'title' => 'dummy-scenario-02',
                ],
                'test' => [
                    'result' => TestResult::FAILED,
                ],
            ],
        );
        $this->triggerScenarioEvents($formatter, $events);

        $events =  $this->getScenarioEvents(
            $environment01,
            $feature01,
            [
                'scenario' => [
                    'title' => 'dummy-scenario-03',
                ],
                'test' => [
                    'result' => TestResult::FAILED,
                    'exception' => [
                        'class' => \Exception::class,
                        'message' => 'I fail',
                        'code' => 42,
                    ],
                ],
            ],
        );
        $this->triggerScenarioEvents($formatter, $events);

        // Suite end.
        $formatter->onAfterSuiteTested($eventSuiteTested);

        $output = $outputFactory->outputs[0];
        $expected = <<<'TEXT'
            TAP version 14
            ok 1 - dummy-suite-01: dummy-feature-01 | dummy-scenario-01
            not ok 2 - dummy-suite-01: dummy-feature-01 | dummy-scenario-02
            not ok 3 - dummy-suite-01: dummy-feature-01 | dummy-scenario-03
              ---
              feature:
                title: dummy-feature-01
                file: null
              scenario:
                title: dummy-scenario-03
                line: null
              step:
                text: 'I fail'
                line: 42
              message: 'I fail'
              ...
            1..3

            TEXT;
        $this->assertSame($expected, $output->fetch());
    }

    /**
     * @phpstan-param array<string, mixed> $info
     *
     * @phpstan-return scenario-events
     */
    protected function getScenarioEvents(
        Environment $environment,
        FeatureNode $feature,
        array $info,
    ): array {
        $info = array_replace_recursive(
            [
                'test' => [
                    'result' => TestResult::PASSED,
                ],
            ],
            $info,
        );

        $scenario = $this->createMock(ScenarioNode::class);
        $scenario->method('getTitle')->willReturn($info['scenario']['title']);

        $testResult = new IntegerTestResult($info['test']['result']);
        $tearDown = new SuccessfulTeardown();
        $events = [
            'before' => new BeforeScenarioTested($environment, $feature, $scenario),
            'after' => new AfterScenarioTested(
                $environment,
                $feature,
                $scenario,
                $testResult,
                $tearDown,
            ),
        ];

        if ($info['test']['result'] === TestResult::PASSED) {
            return $events;
        }

        $stepFailed01 = new StepNode(
            'Then',
            'I fail',
            [],
            42,
        );
        $searchResult = new SearchResult();
        $definition = new Then('I fail', static fn() => throw new \Exception('I fail'));
        $call = new DefinitionCall(
            $environment,
            $feature,
            $stepFailed01,
            $definition,
            [],
        );

        $exception = null;
        if (isset($info['test']['exception']['class'])) {
            $exception = $this->createException($info['test']['exception']);
        }

        $callResult = new CallResult($call, 1, $exception);
        $stepResultFailed01 = new DummyExecutedStepResult($searchResult, $callResult);
        $tearDown = new SuccessfulTeardown();
        $events['step'] = new AfterStepTested(
            $environment,
            $feature,
            $stepFailed01,
            $stepResultFailed01,
            $tearDown,
        );

        return $events;
    }

    /**
     * @phpstan-param scenario-events $events
     */
    protected function triggerScenarioEvents(TapFormatter $formatter, array $events): static
    {
        $formatter->onBeforeScenarioTested($events['before']);
        if (isset($events['step'])) {
            $formatter->onAfterStepTested($events['step']);
        }
        $formatter->onAfterScenarioTested($events['after']);

        return $this;
    }

    protected function createException(array $info): \Exception
    {
        return new \Exception(
            $info['message'] ?? '',
            $info['code'] ?? 0,
        );
    }
}
