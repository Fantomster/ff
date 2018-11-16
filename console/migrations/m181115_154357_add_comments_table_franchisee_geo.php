<?php

use yii\db\Migration;

class m181115_154357_add_comments_table_franchisee_geo extends Migration
{
    public function safeUp()
    {
        $this->execute('alter table `franchisee_geo` comment "Таблица сведений о географической привязке франчайзи";');
        $this->addCommentOnColumn('{{%franchisee_geo}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%franchisee_geo}}', 'franchisee_id','Идентификатор франчайзи');
        $this->addCommentOnColumn('{{%franchisee_geo}}', 'country','Наименование государства, в котором находится франчайзи');
        $this->addCommentOnColumn('{{%franchisee_geo}}', 'locality','Наименование населённого пункта, в котором находится франчайзи');
        $this->addCommentOnColumn('{{%franchisee_geo}}', 'exception','Показатель исключения населённого пункта из показа и поиска (0 - не исключать, 1 - исключать)');
        $this->addCommentOnColumn('{{%franchisee_geo}}', 'created_at','Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%franchisee_geo}}', 'updated_at','Дата и время последнего изменения записи в таблице');
        $this->addCommentOnColumn('{{%franchisee_geo}}', 'administrative_area_level_1','Наименование региона 1 уровня государства, в котором находится франчайзи');
        $this->addCommentOnColumn('{{%franchisee_geo}}', 'status','Показатель статуса активности (не используется)');
    }

    public function safeDown()
    {
        $this->execute('alter table `franchisee_geo` comment "";');
        $this->dropCommentFromColumn('{{%franchisee_geo}}', 'id');
        $this->dropCommentFromColumn('{{%franchisee_geo}}', 'franchisee_id');
        $this->dropCommentFromColumn('{{%franchisee_geo}}', 'country');
        $this->dropCommentFromColumn('{{%franchisee_geo}}', 'locality');
        $this->dropCommentFromColumn('{{%franchisee_geo}}', 'exception');
        $this->dropCommentFromColumn('{{%franchisee_geo}}', 'created_at');
        $this->dropCommentFromColumn('{{%franchisee_geo}}', 'updated_at');
        $this->dropCommentFromColumn('{{%franchisee_geo}}', 'administrative_area_level_1');
        $this->dropCommentFromColumn('{{%franchisee_geo}}', 'status');
    }
}