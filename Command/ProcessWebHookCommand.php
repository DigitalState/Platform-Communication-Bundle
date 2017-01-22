<?php

namespace Ds\Bundle\CommunicationBundle\Command;


use Ds\Bundle\TransportBundle\Entity\WebHookData;
use Ds\Bundle\TransportBundle\Model\AbstractMessageEvent;
use Oro\Bundle\CronBundle\Command\CronCommandInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Oro\Component\Log\OutputLogger;


class ProcessWebHookCommand extends ContainerAwareCommand implements CronCommandInterface
{
    const STATUS_SUCCESS = 0;

    const COMMAND_NAME = 'oro:cron:ds:communication:webhook:process';

    const LIMIT_PER_BATCH = 10000;

    /**
     * {@inheritdoc}
     */
    public function getDefaultDefinition()
    {
        return '* * * * *';
    }

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this
            ->setName(static::COMMAND_NAME)
            ->setDescription('Process received WebHook events');
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = new OutputLogger($output);

        if ($this->getContainer()->get('oro_cron.job_manager')->getRunningJobsCount(self::COMMAND_NAME) > 1) {
            $logger->warning('Parsing job already running. Terminating current job.');

            return self::STATUS_SUCCESS;
        }

        $transportManager = $this->getContainer()
            ->get('ds.transport.manager.webhook')
            ->setLogger($logger);

        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $messageEventServices = $this->getContainer()->get('ds.communication.collection.message_event_handler');

        // @todo use itterable results
        $results = $em->getRepository('DsTransportBundle:WebHookData')
            ->findBy([
                         'processed' => false,
                     ], null, self::LIMIT_PER_BATCH);

        /** @var WebHookData $webHookData */
        foreach ($results as $webHookData)
        {
            $profile = $webHookData->getProfile();

            $hookHandler = $transportManager->getHandler($profile);

            try
            {
                $message_uid = $hookHandler->getMessageUID($webHookData);

                $logger->info(sprintf("Processing transport '%s' - '%s' #%d: UID=%s   ", $profile->getTransport()->getTitle(), $profile->getTitle() , $webHookData->getId() , $message_uid));
                /** @var AbstractMessageEvent $event */
                $event = $hookHandler->createEvent($webHookData);

                if ($event)
                {
                    $webHookData->setProcessed(true);

                    /** @var \Ds\Bundle\CommunicationBundle\Entity\Message $message */
                    $message = $em
                        ->getRepository('DsCommunicationBundle:Message')
                        ->findOneBy([
                                        'message_uid' => $message_uid,
                                        'profile'     => $profile->getId(),
                                    ]);

                    if ($message)
                    {
                        $event->setMessage($message);
                    }

                    $messageEventServices->dispatch($event);
                }
            }
            catch(\Exception $exception)
            {
                // @todo
                $logger->error(sprintf("Error in webhook data %d :%s", $webHookData->getId() , $exception->getMessage()));
            }

            $em->persist($webHookData);
        }

        $em->flush();

        $logger->info('Completed');

        return 0;
    }
    /**
     * Checks if the command active (i.e. properly configured etc).
     *
     * @return bool
     */
    public function isActive()
    {
        return true;
    }
}
