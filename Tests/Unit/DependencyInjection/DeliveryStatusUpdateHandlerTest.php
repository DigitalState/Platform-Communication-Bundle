<?php

namespace Ds\Bundle\CommunicationBundle\Tests\Unit\DependencyInjection;

use Ds\Bundle\CommunicationBundle\Entity\Message;
use Ds\Bundle\CommunicationBundle\Provider\DeliveryStatusUpdateHandler;
use Ds\Bundle\TransportBundle\Model\MessageEvent;
use PHPUnit_Framework_TestCase;
use Ds\Bundle\CommunicationBundle\DependencyInjection\Configuration;

/**
 * Class DeliveryStatusUpdateHandlerTest
 */
class DeliveryStatusUpdateHandlerTest extends PHPUnit_Framework_TestCase
{


    protected function getEmMock()
    {
        $emMock = $this->getMock('\Doctrine\ORM\EntityManager',
                                 array ( 'getClassMetadata', 'persist', 'flush' ), array (), '', false);
        $emMock->expects($this->any())
            ->method('getClassMetadata')
            ->will($this->returnValue((object)array ( 'name' => 'aClass' )));
        $emMock->expects($this->any())
            ->method('persist')
            ->will($this->returnValue(null));
        $emMock->expects($this->any())
            ->method('flush')
            ->will($this->returnValue(null));

        return $emMock;  // it tooks 13 lines to achieve mock!
    }


    /**
     * Test config tree builder
     * @dataProvider statusProvider
     */
    public function testGetConfigTreeBuilder($old, $new, $expected)
    {
        $dsuh = new DeliveryStatusUpdateHandler($this->getEmMock());

        $message = new Message();
        $message->setDeliveryStatus($old);
        $event = new MessageEvent($new);
        $event->setMessage($message);

        $dsuh->handle($event);

        $this->assertEquals($expected, $event->getMessage()->getDeliveryStatus());
    }


    function statusProvider()
    {
        //        const STATUS_UNKNOWN = 'unknown';
        //        const STATUS_QUEUED = 'queued';
        //        const STATUS_SENDING = 'sending';
        //        const STATUS_SENT = 'sent';
        //        const STATUS_CANCELLED = 'cancelled';
        //        const STATUS_OPEN = 'open';
        //        const STATUS_FAILED = 'failed';

        return [
            [ 'queued', 'sent', 'sent' ],
            [ 'queued', 'failed', 'failed' ],


            [ 'sending', 'sent', 'sent' ],
            [ 'sending', 'failed', 'failed' ],
            [ 'sending', 'cancelled', 'cancelled' ],
            [ 'sending', 'sent', 'sent' ],
            [ 'sending', 'open', 'open' ],

            [ 'failed', 'sent', 'failed' ],
            [ 'failed', 'open', 'failed' ],
            [ 'failed', 'cancelled', 'failed' ],
            [ 'failed', 'sending', 'failed' ],
            [ 'failed', 'queued', 'failed' ],

            [ 'sent', 'queued', 'sent' ],
            [ 'sent', 'sending', 'sent' ],
            [ 'sent', 'failed', 'failed' ],
            [ 'sent', 'queued', 'sent' ],


        ];
    }
}
