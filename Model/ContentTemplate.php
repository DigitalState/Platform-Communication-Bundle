<?php

namespace Ds\Bundle\CommunicationBundle\Model;

use Ds\Bundle\CommunicationBundle\Entity\Communication;
use Oro\Bundle\EmailBundle\Model\EmailTemplateInterface;
use Oro\Bundle\SegmentBundle\Entity\Segment;
use Oro\Bundle\QueryDesignerBundle\Model\AbstractQueryDesigner;

class ContentTemplate implements EmailTemplateInterface
{

    protected $subject;

    protected $content;

    public function __construct($subject, $content)
    {
        $this->subject = $subject;
        $this->content = $content;
    }

    /**
     * Gets email template type
     *
     * @return string
     */
    public function getType()
    {
        return 'html';
    }

    /**
     * @return mixed
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param mixed $subject
     *
     * @return ContentTemplate
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param mixed $content
     *
     * @return ContentTemplate
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }


}
