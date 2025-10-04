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
            ->info('Show stack trace for failed tests.')
            ->defaultTrue()
            ->end();
        $childrenBuilder
            ->scalarNode('trace_depth')
            ->info('Number of entries from the call stack to show. 0 to show all.')
            ->defaultValue(3)
            ->end();
        $childrenBuilder
            ->scalarNode('show_steps')
            ->info('Show steps as subtests. Allowed values: never, always, on_failure.')
            ->defaultValue('on_failure')
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
