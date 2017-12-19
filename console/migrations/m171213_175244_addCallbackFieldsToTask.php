<?php

use yii\db\Migration;

/**
 * Class m171213_175244_addCallbackFieldsToTask
 */
class m171213_175244_addCallbackFieldsToTask extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->addColumn('{{%rk_tasks}}','callback_xml', $this->dateTime());
        $this->addColumn('{{%rk_tasks}}','callback_end', $this->datetime());
        $this->addColumn('{{%rk_tasks}}','rcount', $this->integer(11));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%rk_tasks}}','callback_end');
        $this->dropColumn('{{%rk_tasks}}','callback_xml');
        $this->dropColumn('{{%rk_tasks}}','rcount');

    }


}
