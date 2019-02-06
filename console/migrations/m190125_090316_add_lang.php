<?php

use yii\db\Migration;

class m190125_090316_add_lang extends Migration
{
    public $translations = [
        'api.common.models.id' => 'Идентификатор записи в таблице',
        'api.common.models.country.uuid' => 'Идентификатор государства',
        'api.common.models.vats' => 'Величины налога',
        'api.common.models.created.at' => 'Дата и время создания записи в таблице',
        'api.common.models.updated.at' => 'Дата и время последнего изменения записи в таблице',
        'api.common.models.created.by.id' => 'Идентификатор пользователя, создавшего запись',
        'api.common.models.updated.by.id' => 'Идентификатор пользователя, последним изменившим запись',
        'organization.access_denied' => 'Доступ к организации запрещен.',
        'organization.access_denied' => 'Доступ к организации запрещен.',
    ];

    public function safeUp()
    {
        \console\helpers\BatchTranslations::insertCategory('ru', 'app', $this->translations);
    }

    public function safeDown()
    {
        \console\helpers\BatchTranslations::deleteCategory('ru', 'app', $this->translations);
    }
}
