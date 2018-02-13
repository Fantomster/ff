<?php

use yii\db\Migration;

/**
 * Class m180205_135105_add_sh_integr_service_data_table
 */
class m180205_135105_add_sh_integr_service_data_table extends Migration
{

    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%rk_service_data}}', [
            'id'=>$this->primaryKey(),
            'service_id'=> $this->integer(),
            'org' => $this->integer(),
            'fd' => $this->dateTime(),
            'td' => $this->datetime(),
        ], $tableOptions);

        $this->addForeignKey('FK_service_data', 'rk_service_data', 'service_id', 'rk_service', 'id', 'CASCADE', 'CASCADE');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
       $this->dropTable('{{%rk_service_data}');

    }
}
