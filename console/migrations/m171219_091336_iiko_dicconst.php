<?php

use yii\db\Migration;

/**
 * Class m171219_091336_iiko_dicconst
 */
class m171219_091336_iiko_dicconst extends Migration
{
    public $tableName = '{{%iiko_dicconst}}';
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
        $sql = "
            CREATE TABLE IF NOT EXISTS {$this->tableName}
            (
              id        INT AUTO_INCREMENT PRIMARY KEY,
              denom     VARCHAR(255) NULL,
              def_value VARCHAR(255) NULL,
              comment   VARCHAR(255) NULL,
              type      INT          NULL,
              is_active INT(2)       NULL
            );
        ";
        $this->execute($sql);

        $this->insert($this->tableName, [
            'denom' => 'URL',
            'def_value' => 'http://192.168.100.100:8080/resto/api',
            'comment' => 'Ссылка для подключения к вашему iiko Office (http://ваш_домен.ru:8080/resto/api)',
            'type' => 2,
            'is_active' => 1
        ]);

        $this->insert($this->tableName, [
            'denom' => 'auth_login',
            'def_value' => 'login',
            'comment' => 'Логин пользователя для подключения',
            'type' => 2,
            'is_active' => 1
        ]);

        $this->insert($this->tableName, [
            'denom' => 'auth_password',
            'def_value' => 'password',
            'comment' => 'Пароль для подключения',
            'type' => 3,
            'is_active' => 1
        ]);

        $this->insert($this->tableName, [
            'denom' => 'taxVat',
            'def_value' => '0',
            'comment' => 'Ставка ндс',
            'type' => 1,
            'is_active' => 1
        ]);

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable($this->tableName);
    }
}
