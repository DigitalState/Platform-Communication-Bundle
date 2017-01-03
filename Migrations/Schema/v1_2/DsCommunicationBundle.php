<?php

namespace Ds\Bundle\CommunicationBundle\Migrations\Schema\v1_2;

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
        $table->removeForeignKey('fk_ds_comm_message_user_id');
        $table->dropIndex('IDX_5F3E5701A76ED395');
        $table->dropColumn('user_id');

        $table->addColumn('recipient_entity_name', 'string', ['length' => 255]);
        $table->addColumn('recipient_entity_id', 'integer');

        $table->addIndex(['recipient_entity_name','recipient_entity_id'], 'IDX_5F3E5701A745698', []);
    }
}
