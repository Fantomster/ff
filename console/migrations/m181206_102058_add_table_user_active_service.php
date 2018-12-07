<?php

use yii\db\Migration;

/**
 * Class m181206_102058_add_table_user_active_service
 */
class m181206_102058_add_table_user_active_service extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $table = 'user_active_service';
        $this->createTable($table, [
            'user_id'         => $this->integer()->notNull()->comment('Пользователь'),
            'organization_id' => $this->integer()->notNull()->comment('Организация'),
            'service_id'      => $this->integer()->notNull()->comment('Выбраный сервис в организации')
        ]);

        $this->createIndex('idx_user_id_' . $table, $table, 'user_id');
        $this->createIndex('idx_organization_id_' . $table, $table, 'organization_id');
        $this->createIndex('idx_user_organization_' . $table, $table, ['user_id', 'organization_id'], true);
        $this->createIndex('idx_service_id_' . $table, $table, 'service_id');
        $this->addForeignKey('fk_user_' . $table, $table, 'user_id', \common\models\User::tableName(), 'id');
        $this->addForeignKey('fk_organization_' . $table, $table, 'organization_id', \common\models\Organization::tableName(), 'id');

        $models = \Yii::$app->db->createCommand('
              SELECT 
                     u.id, u.organization_id, u.integration_service_id 
              FROM user u
              INNER JOIN organization o ON o.id = organization_id
              WHERE integration_service_id > 0
              ')->queryAll();
        if (!empty($models)) {
            $batch = [];
            foreach ($models as $model) {
                $batch[] = [
                    'user_id'         => $model['id'],
                    'organization_id' => $model['organization_id'],
                    'service_id'      => $model['integration_service_id']
                ];
            }
            \Yii::$app->db->createCommand()
                ->batchInsert('user_active_service', ['user_id', 'organization_id', 'service_id'], $batch)
                ->execute();
        }

        $this->dropColumn(\common\models\User::tableName(), 'integration_service_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn(\common\models\User::tableName(), 'integration_service_id', $this->integer()->null());
        $rows = \Yii::$app->db->createCommand('SELECT * FROM user_active_service')->queryAll();
        if (!empty($rows)) {
            foreach ($rows as $row) {
                $model = \common\models\User::findOne(['id' => $row['user_id'], 'organization_id' => $row['organization_id']]);
                if ($model) {
                    \Yii::$app->db->createCommand()->update(\common\models\User::tableName(), [
                        'integration_service_id' => $row['service_id']
                    ], [
                        'id' => $row['user_id']
                    ])->execute();
                }
            }
        }
        $this->dropTable('user_active_service');
    }
}
