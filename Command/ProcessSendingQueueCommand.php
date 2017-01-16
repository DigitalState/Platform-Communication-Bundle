<?php

namespace Ds\Bundle\CommunicationBundle\Command;


use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Ds\Bundle\CommunicationBundle\Entity\Message;
use Ds\Bundle\CommunicationBundle\Manager\CommunicationManager;
use Ds\Bundle\CommunicationBundle\Manager\MessageManager;
use Ds\Bundle\TransportBundle\Entity\WebHookData;
use Ds\Bundle\TransportBundle\Model\AbstractMessageEvent;
use Oro\Bundle\CronBundle\Command\CronCommandInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Oro\Component\Log\OutputLogger;
use Symfony\Component\Stopwatch\Stopwatch;


class ProcessSendingQueueCommand extends ContainerAwareCommand implements CronCommandInterface
{

    const STATUS_SUCCESS = 0;

    const COMMAND_NAME = 'oro:cron:ds:communication:sending-queue:process';

    const LIMIT_PER_BATCH = 1000;


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
            ->setDescription('Process the sending queue');
    }

    /**
     * @var CommunicationManager
     */
    protected $commManager;

    /**
     * @var MessageManager
     */
    protected $messageManager;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var EntityManager
     */
    protected $em;

    private function processMessage(Message $message)
    {

        $stopwatch = new Stopwatch();

        $stopwatch->start('compileMessage');
        $this->output->write(sprintf('Processing message %d for %s#%d ... compiling ...', $message->getId(), $message->getRecipientEntityName(), $message->getRecipientEntityId()));
        $message = $this->commManager->compileMessage($message);

        $message->setDeliveryStatus(\Ds\Bundle\TransportBundle\Model\Message::STATUS_PROCESSING);
        $this->em->persist($message);
        $this->em->flush();


        $this->output->write(sprintf(' compiled (%dms)... ', $stopwatch->stop('compileMessage')->getDuration()));

        $stopwatch->start('send');

        $message = $this->messageManager->send($message);
        $this->output->write(sprintf(' %s (%dms)... ', $message->getDeliveryStatus(), $stopwatch->stop('send')->getDuration()));

        $this->em->persist($message);
        $this->em->flush();

        $this->output->writeln(' saved. ');


    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = new OutputLogger($output);
        if ($this->getContainer()->get('oro_cron.job_manager')->getRunningJobsCount(self::COMMAND_NAME) > 1)
        {
            $logger->warning('Parsing job already running. Terminating current job.');

            return self::STATUS_SUCCESS;
        }

        $this->output         = $output;
        $this->commManager    = $this->getContainer()->get('ds.communication.manager.communication');
        $this->messageManager = $this->getContainer()->get('ds.communication.manager.message');
        $this->em             = $this->getContainer()->get('doctrine.orm.entity_manager');
        $qb                   = $this->em->getRepository('DsCommunicationBundle:Message')->createQueryBuilder('m');

        $iterableResult = $qb
            ->select('m.id')
            ->where('m.deliveryStatus = :deliveryStatus')
            ->setParameter(':deliveryStatus', \Ds\Bundle\TransportBundle\Model\Message::STATUS_QUEUED)
            ->getQuery()
            ->setMaxResults(self::LIMIT_PER_BATCH)
            ->iterate();

        $output->writeln('Processing sending queue...');

        foreach ($iterableResult as $i => $row)
        {
            $message_id = $row[$i]['id'];

            /** @var Message $message */
            $message = $this->em
                ->getRepository('DsCommunicationBundle:Message')
                ->findOneBy([
                                'id'             => $message_id,
                                'deliveryStatus' => \Ds\Bundle\TransportBundle\Model\Message::STATUS_QUEUED,
                            ]);

            if ($message)
            {
                $this->processMessage($message);

                // detach from Doctrine, so that it can be Garbage-Collected immediately
                $this->em->detach($message);
            }
        }

        $this->em->flush();

        $logger->info('Completed');

        return 0;
    }
}
