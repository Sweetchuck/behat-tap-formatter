<?php

declare(strict_types = 1);

namespace Sweetchuck\BehatTapFormatter\Tests\Acceptance;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;

class TapFormatterTest extends TestBase
{
    /**
     * @return array<string, mixed>
     */
    public static function casesTapFormatter(): array
    {
        $fs = new Filesystem();
        $projectDir = static::getFixturesDir('project-01');
        $caseFiles = (new Finder())
            ->in("$projectDir/cases/")
            ->files()
            ->name('*.params.yml');
        $cases = [];
        foreach ($caseFiles as $caseFile) {
            $name = Path::getFilenameWithoutExtension(
                $caseFile->getRelativePathname(),
                '.params.yml',
            );
            $case = Yaml::parseFile($caseFile->getRealPath());
            if (isset($case['name'])) {
                $name = $case['name'];
                unset($case['name']);
            }

            if (!isset($case['expected'])) {
                $filePath = preg_replace(
                    '@\.params\.yml$@',
                    '.expected.text',
                    $caseFile->getPathname(),
                );
                $case['expected'] = $fs->readFile($filePath);
            }

            $case += [
                'behatParams' => [],
                'cliArgs' => [],
            ];

            $cases[$name] = $case;
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
            'tests/fixtures/project-01',
            $envVars,
        );
        $process->run();
        static::assertSame($expected, $process->getOutput());
    }
}
