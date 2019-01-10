<?php

use yii\db\Migration;

class m181226_103259_add_comments_table_rk_wserror extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `rk_wserror` comment "Таблица сведений об ошибках при работе с системой R-Keeper";');
        $this->addCommentOnColumn('{{%rk_wserror}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%rk_wserror}}', 'code', 'Код ошибки при работе с системой R-Keeper');
        $this->addCommentOnColumn('{{%rk_wserror}}', 'egroup', 'Буквенное обозначение группы ошибок при работе с системой R-Keeper');
        $this->addCommentOnColumn('{{%rk_wserror}}', 'en_text', 'Описание ошибки на английском языке');
        $this->addCommentOnColumn('{{%rk_wserror}}', 'denom', 'Описание ошибки на русском языке');
        $this->addCommentOnColumn('{{%rk_wserror}}', 'comment', 'Комментарий к ошибке при работе с системой R-Keeper');
    }

    public function safeDown()
    {
        $this->execute('alter table `rk_wserror` comment "";');
        $this->dropCommentFromColumn('{{%rk_wserror}}', 'id');
        $this->dropCommentFromColumn('{{%rk_wserror}}', 'code');
        $this->dropCommentFromColumn('{{%rk_wserror}}', 'egroup');
        $this->dropCommentFromColumn('{{%rk_wserror}}', 'en_text');
        $this->dropCommentFromColumn('{{%rk_wserror}}', 'denom');
        $this->dropCommentFromColumn('{{%rk_wserror}}', 'comment');
    }
}
