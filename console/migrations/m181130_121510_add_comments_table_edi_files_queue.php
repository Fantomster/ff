<?php

use yii\db\Migration;

class m181130_121510_add_comments_table_edi_files_queue extends Migration
{
    public function safeUp()
    {
        $this->execute('alter table `edi_files_queue` comment "Таблица сведений о полученных документах от системы EDI";');
        $this->addCommentOnColumn('{{%edi_files_queue}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%edi_files_queue}}', 'name','Название файла xml либо идентификатор файла');
        $this->addCommentOnColumn('{{%edi_files_queue}}', 'organization_id','Идентификатор организации, от которой получен документ');
        $this->addCommentOnColumn('{{%edi_files_queue}}', 'status','Статус обработки документа (1 - новый, 2 - обрабатывается, 3 - ошибка, 4 - обработан)');
        $this->addCommentOnColumn('{{%edi_files_queue}}', 'error_text','Текст ошибки при получении файла или обработке документа');
        $this->addCommentOnColumn('{{%edi_files_queue}}', 'created_at','Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%edi_files_queue}}', 'updated_at','Дата и время последнего изменения записи в таблице');
        $this->addCommentOnColumn('{{%edi_files_queue}}', 'json_data','Данные в формате JSON от Leradata');
    }

    public function safeDown()
    {
        $this->execute('alter table `edi_files_queue` comment "";');
        $this->dropCommentFromColumn('{{%edi_files_queue}}', 'id');
        $this->dropCommentFromColumn('{{%edi_files_queue}}', 'name');
        $this->dropCommentFromColumn('{{%edi_files_queue}}', 'organization_id');
        $this->dropCommentFromColumn('{{%edi_files_queue}}', 'status');
        $this->dropCommentFromColumn('{{%edi_files_queue}}', 'error_text');
        $this->dropCommentFromColumn('{{%edi_files_queue}}', 'created_at');
        $this->dropCommentFromColumn('{{%edi_files_queue}}', 'updated_at');
        $this->dropCommentFromColumn('{{%edi_files_queue}}', 'json_data');
    }
}
