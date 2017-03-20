<?php

use yii\db\Migration;

class m170317_123218_update_category extends Migration
{
    public function safeUp()
    {
        $this->batchInsert('{{%mp_category}}', ['name','parent'], [
            ['Мороженое', NULL],
            ['Сорбет',212],
            ['Фруктовый лёд',212],
            ['Мелорин',212],
        ]);
    }
}
