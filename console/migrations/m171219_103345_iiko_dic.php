<?php

use yii\db\Migration;

/**
 * Class m171219_103345_iiko_dic
 */
class m171219_103345_iiko_dic extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $tableOptions = 'ENGINE=InnoDB';

        $this->createTable(
            '{{%iiko_dic}}',
            [
                'id'=> $this->primaryKey(11),
                'org_id'=> $this->integer(11)->null()->defaultValue(null),
                'dictype_id'=> $this->integer(11)->null()->defaultValue(null),
                'dicstatus_id'=> $this->integer(11)->null()->defaultValue(null),
                'created_at'=> $this->datetime()->null()->defaultValue(new \yii\db\Expression('NOW()')),
                'updated_at'=> $this->datetime()->null()->defaultValue(null),
                'obj_count'=> $this->integer(11)->null()->defaultValue(null),
                'obj_mapcount'=> $this->integer(11)->null()->defaultValue(null),
                'comment'=> $this->string(255)->null()->defaultValue(null),
            ],$tableOptions
        );

        $this->createTable(
            '{{%iiko_dicstatus}}',
            [
                'id'=> $this->primaryKey(11),
                'denom'=> $this->string(255)->null()->defaultValue(null),
                'comment'=> $this->string(255)->null()->defaultValue(null),
            ],$tableOptions
        );

        $this->createTable(
            '{{%iiko_dictype}}',
            [
                'id'=> $this->primaryKey(11),
                'denom'=> $this->string(255)->null()->defaultValue(null),
                'created_at'=> $this->datetime()->null()->defaultValue(new \yii\db\Expression('NOW()')),
                'comment'=> $this->string(255)->null()->defaultValue(null),
                'contr'=> $this->string(128)->null()->defaultValue(null),
            ],$tableOptions
        );

        $this->batchInsert('{{%iiko_dictype}}', ['denom', 'comment', 'contr'], [
            ['Контрагенты', 'Синхронизатор контрагентов', 'sync\agent'],
            ['Склады', 'Синхронизатор складов', 'sync\store'],
            ['Категории', 'Синхронизатор категорий', 'sync\category'],
            ['Товары', 'Синхронизатор товаров', 'sync\goods'],
        ]);

        $this->batchInsert('{{%iiko_dicstatus}}', ['denom'], [
            ['Синхронизирован'],
            ['Ошибка при синхронизации'],
            ['Синхронизация не проводилась'],
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('{{%iiko_dic}}');
        $this->dropTable('{{%iiko_dicstatus}}');
        $this->dropTable('{{%iiko_dictype}}');
    }
}
