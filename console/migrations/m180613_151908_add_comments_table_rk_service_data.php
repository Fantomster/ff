<?php

use yii\db\Migration;

class m180613_151908_add_comments_table_rk_service_data extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `rk_service_data` comment "Таблица сведений об услугах Mixcart интеграции c UCS в системе R-keeper";');
        $this->addCommentOnColumn('{{%rk_service_data}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%rk_service_data}}', 'service_id', 'Идентификатор лицензии UCS в системе R-keeper');
        $this->addCommentOnColumn('{{%rk_service_data}}', 'org', 'Идентификатор организации, использующей услугу Mixcart интеграции с UCS в системе R-keeper');
        $this->addCommentOnColumn('{{%rk_service_data}}', 'fd', 'Дата и время начала действия услуги Mixcart интеграции с UCS в системе R-keeper');
        $this->addCommentOnColumn('{{%rk_service_data}}', 'td', 'Дата и время окончания действия услуги Mixcart интеграции с UCS в системе R-keeper');
        $this->addCommentOnColumn('{{%rk_service_data}}', 'status_id', 'Показатель статуса услуги Mixcart (1 - активна, 0 - не активна)');
    }

    public function safeDown()
    {
        $this->execute('alter table `rk_service_data` comment "";');
        $this->dropCommentFromColumn('{{%rk_service_data}}', 'id');
        $this->dropCommentFromColumn('{{%rk_service_data}}', 'service_id');
        $this->dropCommentFromColumn('{{%rk_service_data}}', 'org');
        $this->dropCommentFromColumn('{{%rk_service_data}}', 'fd');
        $this->dropCommentFromColumn('{{%rk_service_data}}', 'td');
        $this->dropCommentFromColumn('{{%rk_service_data}}', 'status_id');
    }

}
