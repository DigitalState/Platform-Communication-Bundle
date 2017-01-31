<?php

namespace Ds\Bundle\CommunicationBundle\Migrations\Schema\v1_4;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * Class DsCommunicationBundle
 */
class DsCommunicationBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('ds_comm_message');

        $table->changeColumn('title', ['length' => 255, 'notnull' => false]);
        $table->addIndex(['message_uid'] , 'IDX_701A74ADD04A420');
    }
}
