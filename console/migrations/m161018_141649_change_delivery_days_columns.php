<?php

use yii\db\Migration;

class m161018_141649_change_delivery_days_columns extends Migration
{
    public function safeUp()
    {
        $this->renameColumn('{{%delivery}}', 'delivery_mon', 'mon');
        $this->renameColumn('{{%delivery}}', 'delivery_tue', 'tue');
        $this->renameColumn('{{%delivery}}', 'delivery_wed', 'wed');
        $this->renameColumn('{{%delivery}}', 'delivery_thu', 'thu');
        $this->renameColumn('{{%delivery}}', 'delivery_fri', 'fri');
        $this->renameColumn('{{%delivery}}', 'delivery_sat', 'sat');
        $this->renameColumn('{{%delivery}}', 'delivery_sun', 'sun');
    }

    public function safeDown()
    {
        $this->renameColumn('{{%delivery}}', 'mon', 'delivery_mon');
        $this->renameColumn('{{%delivery}}', 'tue', 'delivery_tue');
        $this->renameColumn('{{%delivery}}', 'wed', 'delivery_wed');
        $this->renameColumn('{{%delivery}}', 'thu', 'delivery_thu');
        $this->renameColumn('{{%delivery}}', 'fri', 'delivery_fri');
        $this->renameColumn('{{%delivery}}', 'sat', 'delivery_sat');
        $this->renameColumn('{{%delivery}}', 'sun', 'delivery_sun');
    }
}
