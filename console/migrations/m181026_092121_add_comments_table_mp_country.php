<?php

use yii\db\Migration;

class m181026_092121_add_comments_table_mp_country extends Migration
{
    public function safeUp()
    {
        $this->execute('alter table `mp_country` comment "Таблица сведений о государствах для Маркет Плейс";');
        $this->addCommentOnColumn('{{%mp_country}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%mp_country}}', 'name','Краткое наименование государства на русском языке');
        $this->addCommentOnColumn('{{%mp_country}}', 'full_name','Полное наименование государства на русском языке');
        $this->addCommentOnColumn('{{%mp_country}}', 'en_name','Краткое наименование государства на английском языке');
        $this->addCommentOnColumn('{{%mp_country}}', 'alpha2','Двухбуквенное обозначение государства');
        $this->addCommentOnColumn('{{%mp_country}}', 'alpha3','Трёхбуквенное обозначение государства');
        $this->addCommentOnColumn('{{%mp_country}}', 'location','Регион, где расположено данное государство');
    }

    public function safeDown()
    {
        $this->execute('alter table `mp_country` comment "";');
        $this->dropCommentFromColumn('{{%mp_country}}', 'id');
        $this->dropCommentFromColumn('{{%mp_country}}', 'name');
        $this->dropCommentFromColumn('{{%mp_country}}', 'full_name');
        $this->dropCommentFromColumn('{{%mp_country}}', 'en_name');
        $this->dropCommentFromColumn('{{%mp_country}}', 'alpha2');
        $this->dropCommentFromColumn('{{%mp_country}}', 'alpha3');
        $this->dropCommentFromColumn('{{%mp_country}}', 'location');
    }
}
