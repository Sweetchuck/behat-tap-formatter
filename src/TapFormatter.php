<?php

declare(strict_types = 1);

namespace Sweetchuck\BehatTapFormatter;

use Behat\Behat\EventDispatcher\Event\AfterOutlineTested;
use Behat\Behat\EventDispatcher\Event\AfterScenarioTested;
use Behat\Behat\EventDispatcher\Event\AfterStepTested;
use Behat\Behat\EventDispatcher\Event\BeforeOutlineTested;
use Behat\Behat\EventDispatcher\Event\BeforeScenarioTested;
use Behat\Behat\EventDispatcher\Event\ExampleTested;
use Behat\Behat\EventDispatcher\Event\OutlineTested;
use Behat\Behat\EventDispatcher\Event\ScenarioTested;
use Behat\Behat\EventDispatcher\Event\StepTested;
use Behat\Gherkin\Node\ScenarioLikeInterface;
use Behat\Testwork\EventDispatcher\Event\BeforeSuiteTested;
use Behat\Testwork\EventDispatcher\Event\SuiteTested;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Printer\OutputPrinter;
use Behat\Testwork\Tester\Result\ExceptionResult;
use Behat\Testwork\Tester\Result\TestResult;
use Behat\Testwork\Tester\Result\TestResults;

class TapFormatter implements Formatter
{
    /**
     * @phpstan-var behat-tap-formatter-parameters
     */
    protected array $parameters = [
        // Show the exception trace when the test fails.
        'show_trace' => true,
        // How many entries should be shown from the stack trace.
        'trace_depth' => 1,

        'outline_as_subtest' => false,

        // Allowed values:
        // - never: Do not show the steps as subtest test points.
        // - on_failure: Shows the steps as subtest test points only when there was a failure.
        // - always: Always shows the steps as test points.
        'show_executed_steps' => 'on_failure',

        // When there was a failure, show the remaining steps as skipped test points.
        // This only makes sense when "show_executed_steps" is set to "on_failure" or "always".
        'show_remaining_steps' => false,
    ];

    protected ?BeforeOutlineTested $beforeOutlineTestedEvent = null;

    /**
     * @var array<\Behat\Behat\EventDispatcher\Event\AfterStepTested>
     */
    protected array $afterStepEvents = [];

    protected int $scenarioNumber = 0;

    public function __construct(
        protected OutputPrinter $printer,
        protected TapWriterInterface $tapWriter,
    ) {
        if ($this->tapWriter instanceof TapWriter) {
            $this->tapWriter->setPrinter($this->printer);
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            SuiteTested::BEFORE => 'onBeforeSuiteTested',
            //SuiteTested::AFTER_SETUP => 'onAfterSuiteSetupTested',
            //SuiteTested::BEFORE_TEARDOWN => 'onAfterSuiteTeardownTested',
            SuiteTested::AFTER => 'onAfterSuiteTested',

            //FeatureTested::BEFORE => 'onBeforeFeatureTested',
            //FeatureTested::AFTER_SETUP => 'onAfterFeatureSetupTested',
            //FeatureTested::BEFORE_TEARDOWN => 'onAfterFeatureTeardownTested',
            //FeatureTested::AFTER => 'onAfterFeatureTested',

            //BackgroundTested::BEFORE => 'onBeforeBackgroundTested',
            //BackgroundTested::AFTER_SETUP => 'onAfterBackgroundSetupTested',
            //BackgroundTested::BEFORE_TEARDOWN => 'onBeforeBackgroundTeardownTested',
            //BackgroundTested::AFTER => 'onAfterBackgroundTested',

            OutlineTested::BEFORE => 'onBeforeOutlineTested',
            //OutlineTested::AFTER_SETUP => 'onAfterOutlineSetupTested',
            //OutlineTested::BEFORE_TEARDOWN => 'onBeforeOutlineTeardownTested',
            OutlineTested::AFTER => 'onAfterOutlineTested',

            ExampleTested::BEFORE => 'onBeforeExampleTested',
            //ExampleTested::AFTER_SETUP => 'onAfterExampleSetupTested',
            //ExampleTested::BEFORE_TEARDOWN => 'onBeforeExampleTeardownTested',
            ExampleTested::AFTER => 'onAfterExampleTested',

            ScenarioTested::BEFORE => 'onBeforeScenarioTested',
            //ScenarioTested::AFTER_SETUP => 'onAfterScenarioSetupTested',
            //ScenarioTested::BEFORE_TEARDOWN => 'onBeforeScenarioTeardownTested',
            ScenarioTested::AFTER => 'onAfterScenarioTested',

            //StepTested::BEFORE => 'onBeforeStepTested',
            //StepTested::AFTER_SETUP => 'onAfterStepSetupTested',
            //StepTested::BEFORE_TEARDOWN => 'onBeforeStepTeardownTested',
            StepTested::AFTER => 'onAfterStepTested',
        ];
    }

    // region Formatter
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'tap';
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(): string
    {
        return 'TAP (Test Anything Protocol) formatter';
    }

    /**
     * {@inheritdoc}
     *
     * @param mixed $value
     */
    public function setParameter($name, $value): static
    {
        // @phpstan-ignore-next-line
        $this->parameters = array_replace_recursive(
            $this->parameters,
            [$name => $value],
        );

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameter($name): mixed
    {
        return $this->parameters[$name] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function getOutputPrinter(): OutputPrinter
    {
        return $this->printer;
    }
    // endregion

    // region Event handlers
    public function onBeforeSuiteTested(BeforeSuiteTested $event): void
    {
        $this->scenarioNumber = 0;
        $this->beforeOutlineTestedEvent = null;
        $this->tapWriter->tapVersion();
        // We don't know how many scenarios will be tested.
        // Plan will be emitted at the end of the suite.
    }

    public function onAfterSuiteTested(SuiteTested $event): void
    {
        $this->tapWriter->tapPlan($this->scenarioNumber);
    }

    public function onBeforeOutlineTested(BeforeOutlineTested $event): void
    {
        $this->beforeOutlineTestedEvent = $event;
    }

    public function onAfterOutlineTested(AfterOutlineTested $event): void
    {
        $this->beforeOutlineTestedEvent = null;
    }

    public function onBeforeExampleTested(BeforeScenarioTested $event): void
    {
        $this->beforeTest($event->getScenario());
    }

    public function onAfterExampleTested(AfterScenarioTested $event): void
    {
        $this->afterTest($event);
    }

    public function onBeforeScenarioTested(BeforeScenarioTested $event): void
    {
        $this->beforeTest($event->getScenario());
    }

    public function onAfterScenarioTested(AfterScenarioTested $event): void
    {
        $this->afterTest($event);
    }

    public function onAfterStepTested(AfterStepTested $event): void
    {
        // @todo Add events to the list only when it's needed.
        // Check "show_steps" parameter.
        $this->afterStepEvents[] = $event;
        if ($this->parameters['show_executed_steps'] === 'always') {
            $parts = [
                'status' => $event->getTestResult()->isPassed(),
                'id' => count($this->afterStepEvents),
                // @todo DRY.
                'description' => sprintf(
                    '%s %s',
                    $event->getStep() ->getKeyword(),
                    $event->getStep() ->getText(),
                ),
            ];
            $this->tapWriter->tapTestPoint($parts);
        }
    }
    // endregion

    protected function beforeTest(ScenarioLikeInterface $scenario): void
    {
        $this->afterStepEvents = [];
        $this->scenarioNumber++;

        if ($this->parameters['show_executed_steps'] === 'always') {
            $this->tapWriter->tapComment('Subtest: Steps');
            $this->tapWriter->incrementDepth();
        }
    }

    protected function afterTest(AfterScenarioTested $event): void
    {
        $resultCode = $event->getTestResult()->getResultCode();
        if ($this->parameters['show_executed_steps'] === 'on_failure'
            && $resultCode === TestResult::FAILED
        ) {
            $this->tapWriter->tapComment('Subtest: Steps');
            $this->tapWriter->incrementDepth();
            foreach ($this->afterStepEvents as $index => $afterStepEvent) {
                $parts = [
                    'status' => $afterStepEvent->getTestResult()->isPassed(),
                    'id' => $index + 1,
                    'description' => sprintf(
                        '%s %s',
                        $afterStepEvent->getStep() ->getKeyword(),
                        $afterStepEvent->getStep() ->getText(),
                    ),
                ];
                $this->tapWriter->tapTestPoint($parts);
            }
            $this->tapWriter->tapPlan(count($this->afterStepEvents));
            $this->tapWriter->decrementDepth();
        }

        if ($this->parameters['show_executed_steps'] === 'always') {
            $this->tapWriter->tapPlan(count($this->afterStepEvents));
            $this->tapWriter->decrementDepth();
        }

        $testPoint = $this->afterTestEventToTapTestPoint($event);
        $this->tapWriter->tapTestPoint($testPoint);
    }

    /**
     * @phpstan-return behat-tap-formatter-tap-test-point
     */
    protected function afterTestEventToTapTestPoint(AfterScenarioTested $event): array
    {
        $resultCode = $event->getTestResult()->getResultCode();
        $status = $resultCode !== TestResult::FAILED;

        $testPoint = [
            'status' => $status,
            'id' => $this->scenarioNumber,
            'description' => sprintf(
                '%s: %s | %s',
                $event->getSuite()->getName(),
                $event->getFeature()->getTitle(),
                $this->getScenarioTitle($event),
            ),
            'directive' => [
                'id' => null,
                'reason' => null,
            ],
            'yamlBlock' => $this->getFailedParams($event),
        ];

        switch ($resultCode) {
            case TestResult::PASSED:
            case TestResult::FAILED:
                // Do nothing.
                break;

            case TestResult::SKIPPED:
                $testPoint['directive']['id'] = 'skip';
                break;

            case TestResult::PENDING:
                $testPoint['directive']['id'] = 'skip';
                $testPoint['directive']['reason'] = 'pending';
                break;

            case TestResult::UNDEFINED:
                $testPoint['directive']['id'] = 'skip';
                $testPoint['directive']['reason'] = 'undefined';
                break;

            case TestResults::NO_TESTS:
                // @todo Add the scenario address to the error message.
                $testPoint['directive']['id'] = 'skip';
                $testPoint['directive']['reason'] = 'invalid scenario address';
                break;

            default:
                $testPoint['directive']['id'] = 'skip';
                $testPoint['directive']['reason'] = "unknown result code: $resultCode";
                break;
        }

        return $testPoint;
    }

    protected function getScenarioTitle(AfterScenarioTested $event): string
    {
        $isOutline = $this->beforeOutlineTestedEvent !== null
            && $event->getScenario()->getNodeType() === 'Example';

        if (!$isOutline) {
            return $event->getScenario()->getTitle();
        }

        return $this->beforeOutlineTestedEvent->getOutline()->getTitle() . ' ' . $event->getScenario()->getTitle();
    }

    /**
     * @return null|array<string, mixed>
     */
    protected function getFailedParams(AfterScenarioTested|AfterOutlineTested $event): ?array
    {
        $lastStepEvent = end($this->afterStepEvents);
        if (!$lastStepEvent
            || $lastStepEvent->getTestResult()->isPassed()
        ) {
            return null;
        }

        $feature = $event->getFeature();
        $scenario = $event instanceof AfterScenarioTested
            ? $event->getScenario()
            : $event->getOutline();
        $step = $lastStepEvent->getStep();

        $params = [
            'feature' => [
                'title' => $feature->getTitle(),
                'file' => $feature->getFile(),
            ],
            'scenario' => [
                'title' => $scenario->getTitle(),
                'line' => $scenario->getLine(),
            ],
            'step' => [
                'text' => $step->getText(),
                'line' => $step->getLine(),
            ],
        ];

        $result = $lastStepEvent->getTestResult();
        if (!($result instanceof ExceptionResult)
            || !$result->hasException()
        ) {
            $params['message'] = sprintf(
                'Unknown error in %s',
                get_class($lastStepEvent),
            );

            return $params;
        }

        $exception = $result->getException();
        $params['message'] = $exception->getMessage();

        $showTrace = (bool) $this->getParameter('show_trace');
        $traceDepth = (int) $this->getParameter('trace_depth');
        if ($showTrace) {
            $params['trace'] = $traceDepth
                ? array_slice($exception->getTrace(), 0, $traceDepth)
                : $exception->getTrace();
        }

        return $params;
    }
}
