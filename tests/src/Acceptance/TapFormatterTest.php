<?php

declare(strict_types = 1);

namespace Sweetchuck\BehatTapFormatter\Tests\Acceptance;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class TapFormatterTest extends TestBase
{
    /**
     * @return array<string, mixed>
     */
    public static function casesTapFormatter(): array
    {
        $fs = new Filesystem();
        $projectDir = static::getFixturesDir('project-01');

        // show_trace: false
        // trace_depth: 1
        // examples_as_subtest: false
        // show_executed_steps: 'never'
        // show_remaining_steps: false
        $showExecutedSteps = [
            'ses-n' => ['show_executed_steps' => 'never'],
            'ses-f' => ['show_executed_steps' => 'on_failure'],
            'ses-a' => ['show_executed_steps' => 'always'],
        ];
        $showRemainingSteps = [
            'srs-f' => ['show_remaining_steps' => false],
            'srs-t' => ['show_remaining_steps' => true],
        ];

        $cases = [];

        $scenarios = [
            //'simple-bg0-3' => 'features/scenario-simple-bg0.feature:3',
            'simple-bg0-8' => 'features/scenario-simple-bg0.feature:8',
            //'simple-bg0-13' => 'features/scenario-simple-bg0.feature:13',
            //'simple-bg0-18' => 'features/scenario-simple-bg0.feature:18',
        ];
        $defaultTapParameters = [
            'show_trace' => false,
            'trace_depth' => 1,
            'examples_as_subtest' => false,
        ];
        foreach ($scenarios as $scenarioName => $scenario) {
            foreach ($showExecutedSteps as $sesName => $sesParams) {
                foreach ($showRemainingSteps as $srsName => $srsParams) {
                    $caseName = "$scenarioName.$sesName.$srsName";
                    $cases[$caseName] = [
                        'expected' => $fs->readFile("$projectDir/cases/$caseName.expected.txt"),
                        'behatParams' => [
                            'formatters' => [
                                'tap' => $sesParams + $srsParams + $defaultTapParameters,
                            ],
                        ],
                        'cliArgs' => [$scenario],
                    ];
                }
            }
        }

        return $cases;
    }

    /**
     * @param string $expected
     * @param array<string, mixed> $behatParams
     * @param array<string> $cliArgs
     */
    #[Test]
    #[DataProvider('casesTapFormatter')]
    public function testTapFormatter(string $expected, array $behatParams, array $cliArgs): void
    {
        $envVars = null;
        if ($behatParams) {
            $envVars = [
                'BEHAT_PARAMS' => json_encode($behatParams),
            ];
        }

        $command = [
            '../../../vendor/bin/behat',
            ...$cliArgs,
        ];
        $process = new Process(
            $command,
            static::getFixturesDir('project-01'),
            $envVars,
        );
        $process->run();
        static::assertSame($expected, $process->getOutput());
    }
}
