<?php

namespace Ds\Bundle\CommunicationBundle;

use Ds\Bundle\CommunicationBundle\DependencyInjection\Compiler\MessageContentBuilderPass;
use Ds\Bundle\CommunicationBundle\DependencyInjection\Compiler\MessageEventPass;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Ds\Bundle\CommunicationBundle\DependencyInjection\Compiler\ChannelPass;

/**
 * Class DsCommunicationBundle
 */
class DsCommunicationBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container
            ->addCompilerPass(new ChannelPass)
            ->addCompilerPass(new MessageContentBuilderPass())
            ->addCompilerPass(new MessageEventPass());
    }
}
