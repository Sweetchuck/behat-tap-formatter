<?php

declare(strict_types = 1);

namespace Sweetchuck\BehatTapFormatter;

use Behat\Testwork\Output\ServiceContainer\OutputExtension;
use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @todo Support for "output_path" configuration like "pretty" formatter.
 */
class TapFormatterExtension implements Extension
{

    /**
     * {@inheritdoc}
     */
    public function getConfigKey(): string
    {
        return 'tap';
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(ExtensionManager $extensionManager): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function configure(ArrayNodeDefinition $builder): void
    {
        $childrenBuilder = $builder
            ->addDefaultsIfNotSet()
            ->children();

        $childrenBuilder
            ->booleanNode('show_trace')
            ->info('If TRUE then the exception trace is added into the YamlBlock when a test fails.')
            ->defaultTrue()
            ->end();
        $childrenBuilder
            ->scalarNode('trace_depth')
            ->info(<<<'TEXT'
                Number of entries from the call stack to show. 0 to show all.
                Used only when "show_trace" is TRUE.
                TEXT
            )
            ->defaultValue(3)
            ->end();
        $childrenBuilder
            ->scalarNode('show_executed_steps')
            ->info(<<<'TEXT'
                Show steps as subtests.
                Allowed values:
                - never: Do not show the steps as subtest test points.
                - on_failure: Shows the steps as subtest test points only when there was a failure.
                - always: Always shows the steps as test points.
                TEXT
            )
            ->defaultValue('on_failure')
            ->end();
        $childrenBuilder
            ->booleanNode('show_remaining_steps')
            ->info(<<<'TEXT'
                When there was a failure, show the remaining steps as skipped test points.
                This only makes sense when "show_executed_steps" is set to "on_failure" or "always".
                TEXT
            )
            ->defaultFalse()
            ->end();
    }

    /**
     * {@inheritdoc}
     */
    public function load(ContainerBuilder $container, array $config): void
    {
        $outputDefinition = new Reference('cli.output');
        $outputPrinterDefinition = new Definition(
            ConsoleOutput::class,
            [
                $outputDefinition,
            ],
        );

        $tapWriterDefinition = new Definition(
            TapWriter::class,
            [
                $outputPrinterDefinition,
            ],
        );
        $container->setDefinition(TapWriterInterface::class, $tapWriterDefinition);

        $tapFormatterDefinition = new Definition(
            TapFormatter::class,
            [
                $outputPrinterDefinition,
                $tapWriterDefinition,
            ],
        );
        $tapFormatterDefinition->addTag(
            OutputExtension::FORMATTER_TAG,
            [
                'priority' => 90,
            ],
        );
        $container->setDefinition(
            OutputExtension::FORMATTER_TAG . '.' . $this->getConfigKey(),
            $tapFormatterDefinition,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
    }
}
