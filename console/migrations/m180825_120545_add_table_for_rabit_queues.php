<?php

use yii\db\Migration;
use yii\db\Schema;

/**
 * Class m180825_120545_add_table_for_rabit_queues
 */
class m180825_120545_add_table_for_rabit_queues extends Migration
{
	/**
	 * @inheritdoc
	 */
	public function init()
	{
		$this->db = 'db_api';
		parent::init();
	}

	public function safeUp()
	{
		$tableOptions = null;
		if ($this->db->driverName === 'mysql') {
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
		}
		$this->createTable('{{%rabbit_queues}}', [
			'id' => Schema::TYPE_PK,
			'consumer_class_name' => Schema::TYPE_STRING . ' not null',
			'organization_id' => Schema::TYPE_INTEGER . ' default null',
		], $tableOptions);
	}

	/**
	 * @inheritdoc
	 */
	public function safeDown()
	{
		$this->dropTable('{{%rabbit_queues}}');
	}
}
