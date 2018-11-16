<?php

use yii\db\Migration;

class m181115_160618_add_comments_table_edi_provider extends Migration
{
    public function safeUp()
    {
        $this->execute('alter table `edi_provider` comment "Таблица сведений о провайдерах EDI";');
        $this->addCommentOnColumn('{{%edi_provider}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%edi_provider}}', 'name','Наименование провайдера');
        $this->addCommentOnColumn('{{%edi_provider}}', 'legal_name','Официальное наименование провайдера');
        $this->addCommentOnColumn('{{%edi_provider}}', 'web_site','Веб-сайт провайдера');
        $this->addCommentOnColumn('{{%edi_provider}}', 'provider_class','Класс провайдера');
        $this->addCommentOnColumn('{{%edi_provider}}', 'realization_class','Класс реализации');
    }

    public function safeDown()
    {
        $this->execute('alter table `edi_provider` comment "";');
        $this->dropCommentFromColumn('{{%edi_provider}}', 'id');
        $this->dropCommentFromColumn('{{%edi_provider}}', 'name');
        $this->dropCommentFromColumn('{{%edi_provider}}', 'legal_name');
        $this->dropCommentFromColumn('{{%edi_provider}}', 'web_site');
        $this->dropCommentFromColumn('{{%edi_provider}}', 'provider_class');
        $this->dropCommentFromColumn('{{%edi_provider}}', 'realization_class');
    }
}