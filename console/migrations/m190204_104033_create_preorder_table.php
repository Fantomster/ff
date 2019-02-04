<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%preorder}}`.
 */
class m190204_104033_create_preorder_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = 'ENGINE=InnoDB';

        $this->createTable('{{%preorder}}', [
            'id'              => $this->primaryKey(11)->comment('Идентификатор записи в таблице'),
            'organization_id' => $this->integer(11)->comment('id организации, которая сделала предзаказ'),
            'user_id'         => $this->integer(11)->comment('id пользователя, который создал предзаказ'),
            'is_active'       => $this->tinyInteger(2)->defaultValue(1)->comment('активен ли данный предзаказ'),
            'created_at'      => $this->timestamp()->null()->defaultValue(null)->comment('Дата и время создания записи в таблице'),
            'updated_at'      => $this->timestamp()->null()->defaultValue(null)->comment('Дата и время последнего изменения записи в таблице'),
        ], $tableOptions);

        $this->execute('alter table `preorder` comment "Таблица сведений о предзаказах организаций";');

        // add foreign key for table `organization`
        $this->addForeignKey(
            'fk-preorder_organization-organization_id',
            'preorder',
            'organization_id',
            'organization',
            'id',
            'CASCADE'
        );

        // add foreign key for table `user`
        $this->addForeignKey(
            'fk-preorder_user-user_id',
            'preorder',
            'user_id',
            'user',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops ForeignKey for column `organization_id`
        $this->dropForeignKey(
            'fk-preorder_organization-organization_id',
            'preorder'
        );

        // drops ForeignKey for column `user_id`
        $this->dropForeignKey(
            'fk-preorder_user-user_id',
            'preorder'
        );

        $this->dropTable('{{%preorder}}');
    }
}
