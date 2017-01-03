<?php

namespace Ds\Bundle\CommunicationBundle\Migrations\Schema\v1_3;

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
        $table->addColumn('delivery_status', 'string', ['length' => 255]);

    }
}
