<?php

declare(strict_types = 1);

namespace Sweetchuck\BehatTapFormatter\Tests\Helper\Behat\Context;

use Behat\Behat\Context\Context;
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
     * @Given a dummy thing with :id and :result
     * @When I create a dummy thing with :id and :result
     * @Then I should have a dummy thing with :id and :result
     *
     * @throws \Throwable
     */
    public function doDummySimple(string $id, string $result): void
    {
        if ($result !== 'ok') {
            throw new \Exception(sprintf(
                'Fail. id: %s, result: %s',
                $id,
                $result,
            ));
        }
    }
}
