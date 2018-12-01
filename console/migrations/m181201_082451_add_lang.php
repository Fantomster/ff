<?php

use yii\db\Migration;

/**
 * Class m181201_082451_add_lang
 */
class m181201_082451_add_lang extends Migration
{
    public $translations = [
        'user.employee.update.access_denied' => 'Вы не можете редактировать этого пользователя',
        'user.employee.delete.access_denied' => 'Вы не можете удалить этого пользователя',
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        \console\helpers\BatchTranslations::insertCategory('ru', 'api_web', $this->translations);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        \console\helpers\BatchTranslations::deleteCategory('ru', 'api_web', $this->translations);
    }
}
