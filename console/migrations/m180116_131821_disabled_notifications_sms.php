<?php

use yii\db\Migration;

/**
 * Class m180116_131821_disabled_notifications_sms
 */
class m180116_131821_disabled_notifications_sms extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $sql = <<<sql
    #Темповая таблица для уведомлений
    DROP TEMPORARY TABLE IF EXISTS sms_notification_id_type;
    CREATE TEMPORARY TABLE sms_notification_id_type (id int PRIMARY KEY, type_id int)
    SELECT sms_alias.id, org.type_id FROM sms_notification as sms_alias
      INNER JOIN user user ON sms_alias.user_id = user.id
      LEFT JOIN organization org ON user.organization_id = org.id
    WHERE org.type_id is not null;
    
    #Обновляем рестораны
    UPDATE sms_notification SET
      order_created = 0, order_done = 0
    WHERE id IN ( select id from sms_notification_id_type WHERE type_id = 1);
    #Обновляем поставщиков
    UPDATE sms_notification SET
      order_processing = 0, order_done = 0, request_accept = 0
    WHERE id IN ( select id from sms_notification_id_type WHERE type_id = 2);
    #Удаляем таблицу
    DROP TEMPORARY TABLE IF EXISTS sms_notification_id_type;
sql;

        return \Yii::$app->db->createCommand($sql)->execute();

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180116_131821_disabled_notifications_sms cannot be reverted.\n";
        return false;
    }
}
