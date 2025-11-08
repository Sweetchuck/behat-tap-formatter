<?php

declare(strict_types = 1);

namespace Sweetchuck\BehatTapFormatter\Tests\Helper\Behat\Context;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Hook\BeforeSuite;

class Dummy implements Context
{
    #[BeforeSuite]
    public static function onBeforeSuite(): void
    {
        $cwd = getcwd();
        $dir = "$cwd/.cache/behat/rerun";
        if (!is_dir($dir)) {
            mkdir($dir, 0777 - umask(), true);
        }
    }

    /**
     * @Given there is a thing with ID :id Result :result
     * @When I create a thing with ID :id Result :result
     * @Then I see two things with ID :id Result :result
     *
     * @throws \Throwable
     */
    public function doDummySimple(string $id, string $result): void
    {
        if ($result === 'fail') {
            throw new \Exception(sprintf(
                'Fail. id: %s, result: %s',
                $id,
                $result,
            ));
        }
    }

    /**
     * @Given There is a table with ID :id Result :result and table:
     * @When I create a table with ID :id Result :result and table:
     * @Then I see two tables with ID :id Result :result and table:
     *
     * @throws \Throwable
     */
    public function doDummyTable(string $id, string $result, TableNode $tableNode): void
    {
        if ($result === 'fail') {
            throw new \Exception(sprintf(
                'Fail. id: %s, result: %s',
                $id,
                $result,
            ));
        }
    }

    /**
     * @Given There is a string with ID :id Result :result and string:
     * @When I create a string with ID :id Result :result and string:
     * @Then I see two strings with ID :id Result :result and string:
     *
     * @throws \Throwable
     */
    public function doDummyString(string $id, string $result, PyStringNode $string): void
    {
        if ($result === 'fail') {
            throw new \Exception(sprintf(
                'Fail. id: %s, result: %s',
                $id,
                $result,
            ));
        }
    }
}
