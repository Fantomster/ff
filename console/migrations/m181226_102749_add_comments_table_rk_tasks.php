<?php

use yii\db\Migration;

class m181226_102749_add_comments_table_rk_tasks extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `rk_tasks` comment "Таблица сведений о логах действий с системой R-Keeper";');
        $this->addCommentOnColumn('{{%rk_tasks}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%rk_tasks}}', 'fid', 'Непонятный идентификатор (не используется)');
        $this->addCommentOnColumn('{{%rk_tasks}}', 'acc', 'Идентификатор организации');
        $this->addCommentOnColumn('{{%rk_tasks}}', 'tasktype_id', 'Идентификатор типа действия');
        $this->addCommentOnColumn('{{%rk_tasks}}', 'guid', 'Уникальный идентификатор действия в системе R-Keeper');
        $this->addCommentOnColumn('{{%rk_tasks}}', 'intstatus_id', 'Показатель статуса действия (1 - отправлено, 2 - ошибка, 3 - успешно получен xml, 4 - успешно получен dic, 5 - успешно завершено)');
        $this->addCommentOnColumn('{{%rk_tasks}}', 'wsstatus_id', 'Показатель статуса действия на сервере');
        $this->addCommentOnColumn('{{%rk_tasks}}', 'wsclientstatus_id', 'Показатель статуса действия на клиенте');
        $this->addCommentOnColumn('{{%rk_tasks}}', 'fd', 'Дата и время начала действия');
        $this->addCommentOnColumn('{{%rk_tasks}}', 'td', 'Дата и время окончания действия');
        $this->addCommentOnColumn('{{%rk_tasks}}', 'created_at', 'Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%rk_tasks}}', 'updated_at', 'Дата и время последнего изменения записи в таблице');
        $this->addCommentOnColumn('{{%rk_tasks}}', 'callback_at', 'Дата и время получения ответа от сервера R-Keeper на запрос');
        $this->addCommentOnColumn('{{%rk_tasks}}', 'isactive', 'Показатель статуса активности действия (0 - не активно, 1 - активно)');
        $this->addCommentOnColumn('{{%rk_tasks}}', 'retry', 'Показатель повторного запроса');
        $this->addCommentOnColumn('{{%rk_tasks}}', 'fcode', 'Код действия, полученный в ответе от сервера R-Keeper');
        $this->addCommentOnColumn('{{%rk_tasks}}', 'version', 'Версия программного обеспечения сервера');
        $this->addCommentOnColumn('{{%rk_tasks}}', 'callback_xml', 'Дата и время начала получения ответа от сервера');
        $this->addCommentOnColumn('{{%rk_tasks}}', 'callback_end', 'Дата и время окончания получения ответа от сервера');
        $this->addCommentOnColumn('{{%rk_tasks}}', 'rcount', 'Всего пакетов в ответе');
        $this->addCommentOnColumn('{{%rk_tasks}}', 'total_parts', 'Всего успешно получено и обработано ответов');
        $this->addCommentOnColumn('{{%rk_tasks}}', 'current_part', 'Текущий пакет ответа');
        $this->addCommentOnColumn('{{%rk_tasks}}', 'req_uid', 'Уникальный идентификатор ответа от сервера R-Keeper');
    }

    public function safeDown()
    {
        $this->execute('alter table `rk_tasks` comment "";');
        $this->dropCommentFromColumn('{{%rk_tasks}}', 'id');
        $this->dropCommentFromColumn('{{%rk_tasks}}', 'fid');
        $this->dropCommentFromColumn('{{%rk_tasks}}', 'acc');
        $this->dropCommentFromColumn('{{%rk_tasks}}', 'tasktype_id');
        $this->dropCommentFromColumn('{{%rk_tasks}}', 'guid');
        $this->dropCommentFromColumn('{{%rk_tasks}}', 'intstatus_id');
        $this->dropCommentFromColumn('{{%rk_tasks}}', 'wsstatus_id');
        $this->dropCommentFromColumn('{{%rk_tasks}}', 'wsclientstatus_id');
        $this->dropCommentFromColumn('{{%rk_tasks}}', 'fd');
        $this->dropCommentFromColumn('{{%rk_tasks}}', 'td');
        $this->dropCommentFromColumn('{{%rk_tasks}}', 'created_at');
        $this->dropCommentFromColumn('{{%rk_tasks}}', 'updated_at');
        $this->dropCommentFromColumn('{{%rk_tasks}}', 'callback_at');
        $this->dropCommentFromColumn('{{%rk_tasks}}', 'isactive');
        $this->dropCommentFromColumn('{{%rk_tasks}}', 'retry');
        $this->dropCommentFromColumn('{{%rk_tasks}}', 'fcode');
        $this->dropCommentFromColumn('{{%rk_tasks}}', 'version');
        $this->dropCommentFromColumn('{{%rk_tasks}}', 'callback_xml');
        $this->dropCommentFromColumn('{{%rk_tasks}}', 'callback_end');
        $this->dropCommentFromColumn('{{%rk_tasks}}', 'rcount');
        $this->dropCommentFromColumn('{{%rk_tasks}}', 'total_parts');
        $this->dropCommentFromColumn('{{%rk_tasks}}', 'current_part');
        $this->dropCommentFromColumn('{{%rk_tasks}}', 'req_uid');
    }
}
