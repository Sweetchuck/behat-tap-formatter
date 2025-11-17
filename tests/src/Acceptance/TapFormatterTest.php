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
        $cases = [];
        $cases += static::casesWithPrefix('simple-bg0-3', ['features/simple-bg0.feature:3']);
        $cases += static::casesWithPrefix('simple-bg0-8', ['features/simple-bg0.feature:8']);
        $cases += static::casesWithPrefix('simple-bg0-13', ['features/simple-bg0.feature:13']);
        $cases += static::casesWithPrefix('simple-bg0-18', ['features/simple-bg0.feature:18']);
        $cases += static::casesWithPrefix('simple-bg0', ['features/simple-bg0.feature']);
        $cases += static::casesWithPrefix('simple-bg1-fail', ['features/simple-bg1-fail.feature']);
        $cases += static::casesWithPrefix('simple-bg1-ok', ['features/simple-bg1-ok.feature']);
        $cases += static::casesWithPrefix('step-arguments-bg0', ['features/step-arguments-bg0.feature']);
        $cases += static::casesWithPrefix('outline-bg0', ['features/outline-bg0.feature']);

        return $cases;
    }

    /**
     * @param array<string> $cliArgs
     *
     * @return array<string, array<string, mixed>>
     */
    protected static function casesWithPrefix(string $caseNamePrefix, array $cliArgs): array
    {
        $defaultTapParameters = [
            'show_trace' => false,
            'trace_depth' => 1,
        ];

        $fs = new Filesystem();
        $projectDir = static::getFixturesDir('project-01');

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
        foreach ($showExecutedSteps as $sesName => $sesParams) {
            foreach ($showRemainingSteps as $srsName => $srsParams) {
                $caseName = "$caseNamePrefix.$sesName.$srsName";
                $cases[$caseName] = [
                    'expected' => $fs->readFile("$projectDir/cases/$caseName.expected.txt"),
                    'behatParams' => [
                        'formatters' => [
                            'tap' => $sesParams + $srsParams + $defaultTapParameters,
                        ],
                    ],
                    'cliArgs' => $cliArgs,
                ];
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
