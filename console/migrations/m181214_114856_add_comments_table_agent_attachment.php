<?php

use yii\db\Migration;

class m181214_114856_add_comments_table_agent_attachment extends Migration
{
    public function safeUp()
    {
        $this->execute('alter table `agent_attachment` comment "Таблица сведений о приложенных файлах к заявкам организаций на присоединение к франшизе";');
        $this->addCommentOnColumn('{{%agent_attachment}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%agent_attachment}}', 'agent_request_id','Идентификатор заявки организации на присоединение к франшизе');
        $this->addCommentOnColumn('{{%agent_attachment}}', 'attachment','Название приложенного файла');
    }

    public function safeDown()
    {
        $this->execute('alter table `agent_attachment` comment "";');
        $this->dropCommentFromColumn('{{%agent_attachment}}', 'id');
        $this->dropCommentFromColumn('{{%agent_attachment}}', 'agent_request_id');
        $this->dropCommentFromColumn('{{%agent_attachment}}', 'attachment');
    }
}
