<?php

namespace Ds\Bundle\CommunicationBundle\Migrations\Schema\v1_1;

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
        $table = $schema->getTable('ds_comm');
        $table->addColumn('entityName', 'string', ['length' => 255]);

        $table = $schema->getTable('ds_comm_template');
        $table->addColumn('entityName', 'string', ['length' => 255]);
    }
}
