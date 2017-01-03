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
        $table->addColumn('message_uid', 'string', ['length' => 255]);
        $table->addIndex(['message_uid']);


        $table->addColumn('profile_id', 'integer');
        $table->addIndex(['profile_id'] , 'IDX_2BAAEC5EE108AD1A2E');
        $table->addForeignKeyConstraint(
            $schema->getTable('ds_transport_profile'),
            ['profile_id'],
            ['id']
        );


        $table->addColumn('content_id', 'integer');
        $table->addIndex(['content_id'] , 'IDX_F30267687FAB08218122');
        $table->addForeignKeyConstraint(
            $schema->getTable('ds_comm_content'),
            ['content_id'],
            ['id']
        );


    }
}
