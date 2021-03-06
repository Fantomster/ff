<?php
use yii\db\Schema;
use yii\db\Migration;

class m161207_082832_mp_ed extends Migration
{
    public function safeUp() {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%mp_ed}}', [
            'id' => Schema::TYPE_PK,
            'name' => Schema::TYPE_STRING . ' NOT NULL'
            ], $tableOptions);
        $this->batchInsert('{{%mp_ed}}', ['name'], [
            ['бутылка'],
            ['баллон'],
            ['г'],
            ['кг'],
            ['контейнер'],
            ['коробка'],
            ['л'],
            ['м'],
            ['мешок'],
            ['набор'],
            ['пакет'],
            ['упаковка'],
            ['штука'],
            ['ящик'],
            ['см']
        ]);
    }
    public function safeDown() {
        $this->dropTable('{{%mp_ed}}');
    }
}
