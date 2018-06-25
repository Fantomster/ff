<?php

use yii\db\Migration;

class m180625_083032_insert_table_jobs extends Migration
{
    public function safeUp()
    {
        $this->insert('{{%jobs}}', [
            'name_job' => 'Директор',
            'organization_type_id' => '1',
        ]);
        $this->insert('{{%jobs}}', [
            'name_job' => 'Управляющий',
            'organization_type_id' => '1',
        ]);
        $this->insert('{{%jobs}}', [
            'name_job' => 'Бухгалтер',
            'organization_type_id' => '1',
        ]);
        $this->insert('{{%jobs}}', [
            'name_job' => 'Менеджер по закупкам',
            'organization_type_id' => '1',
        ]);
        $this->insert('{{%jobs}}', [
            'name_job' => 'Повар',
            'organization_type_id' => '1',
        ]);
        $this->insert('{{%jobs}}', [
            'name_job' => 'Бармен',
            'organization_type_id' => '1',
        ]);
        $this->insert('{{%jobs}}', [
            'name_job' => 'Директор',
            'organization_type_id' => '2',
        ]);
        $this->insert('{{%jobs}}', [
            'name_job' => 'Управляющий',
            'organization_type_id' => '2',
        ]);
        $this->insert('{{%jobs}}', [
            'name_job' => 'Бухгалтер',
            'organization_type_id' => '2',
        ]);
        $this->insert('{{%jobs}}', [
            'name_job' => 'Менеджер',
            'organization_type_id' => '2',
        ]);
        $this->insert('{{%jobs}}', [
            'name_job' => 'Торговый представитель',
            'organization_type_id' => '2',
        ]);
    }

    public function safeDown()
    {
        $this->execute('truncate table `jobs`;');
    }

}
