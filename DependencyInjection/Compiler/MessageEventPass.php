<?php

namespace Ds\Bundle\CommunicationBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class ChannelPass
 */
class MessageEventPass implements CompilerPassInterface
{
    /**
     * Process
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('ds.communication.collection.message_event_handler')) {
            return;
        }

        $definition = $container->findDefinition('ds.communication.collection.message_event_handler');
        $services = $container->findTaggedServiceIds('ds.communication.message_event.handler');

        foreach ($services as $serviceId => $service)
        {
            $definition->addMethodCall('add', [ new Reference($serviceId)]);
        }
    }
}
