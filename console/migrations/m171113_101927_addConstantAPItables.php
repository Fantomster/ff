<?php

use yii\db\Migration;

/**
 * Class m171113_101927_addConstantAPItables
 */
class m171113_101927_addConstantAPItables extends Migration
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

        $this->createTable(
            '{{%rk_dicconst}}',
            [
                'id'=> $this->primaryKey(11),
                'denom'=> $this->string(255)->null()->defaultValue(null),
                'def_value'=> $this->string(255)->null()->defaultValue(null),
                'comment'=> $this->string(255)->null()->defaultValue(null),
            ],$tableOptions
        );
        $this->createTable(
            '{{%rk_pconst}}',
            [
                'id'=> $this->primaryKey(11),
                'const_id'=> $this->integer(11)->null()->defaultValue(null),
                'org'=> $this->integer(11)->null()->defaultValue(null),
                'value'=> $this->string(255)->null()->defaultValue(null),
                'created_at'=> $this->datetime()->null()->defaultValue(null),
                'updated_at'=> $this->datetime()->null()->defaultValue(null),
            ],$tableOptions
        );

        $this->addForeignKey('{{%fk_rk_const}}', '{{%rk_pconst}}', 'const_id', '{{%rk_dicconst}}', 'id');

        $this->insert('{{%rk_dicconst}}',[ 'denom' => 'taxVat', 'def_value' =>'1800', 'comment' => 'Ставка НДС по умолчанию при выгрузке накладных']);
        $this->insert('{{%rk_dicconst}}',[ 'denom' => 'defStore', 'def_value' =>'0', 'comment' => 'Склад, на который по умолчанию выгружаются приходы']);
        $this->insert('{{%rk_dicconst}}',[ 'denom' => 'useAutoExport', 'def_value' =>'0', 'comment' => 'Использование автоматической выгрузки накладных']);
        $this->insert('{{%rk_dicconst}}',[ 'denom' => 'useAutoNumber', 'def_value' =>'0', 'comment' => 'Использование автоматической нумерации накладных']);
        $this->insert('{{%rk_dicconst}}',[ 'denom' => 'moveZipAfter', 'def_value' =>'0', 'comment' => 'Через сколько дней отправлять накладную в архив']);
        $this->insert('{{%rk_dicconst}}',[ 'denom' => 'doBackSync', 'def_value' =>'0', 'comment' => 'Выполнять автоматическую обратную синхронизацию Заказов']);
        $this->insert('{{%rk_dicconst}}',[ 'denom' => 'useAcceptedDocs', 'def_value' =>'0', 'comment' => 'Выгружать накладные в SH с последующим проведением']);
        $this->insert('{{%rk_dicconst}}',[ 'denom' => 'useAgentGroup', 'def_value' =>'0', 'comment' => 'Использовать отдельную группу при выгрузке контрагентов']);



    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKey('{{%fk_rk_const}}', '{{%rk_pconst}}');
        $this->dropTable('{{%rk_dicconst}}');
        $this->dropTable('{{%rk_pconst}}');
    }


}
