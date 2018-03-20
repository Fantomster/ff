<?php

use yii\db\Migration;
use common\models\SourceMessage;
use common\models\Message;

/**
 * Class m180226_141650_add_translate_number_row
 */
class m180226_141650_add_translate_number_row extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->batchInsert('{{%source_message}}', ['category', 'message'], [
            ['message','frontend.views.order.grid_row_number']
        ]);

        $row_id = SourceMessage::findOne(['category'=>'message', 'message' => 'frontend.views.order.grid_row_number']) -> id;

        if(Message::findOne(['id' => $row_id]) == null)
        $this->batchInsert('{{%message}}', ['id', 'language', 'translation'], [
            [$row_id,'en', '#'],
            [$row_id,'es', '#'],
            [$row_id,'ru', '№ п/п']
        ]);
        else {
            $this->update('{{%message}}', [
                'translation' => '#'],
                "id=$row_id and language in ('es', 'en')"
            );
            $this->update('{{%message}}', [
                'translation' => '№ п/п'],
                "id=$row_id and language = 'ru'"
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return true;
    }
}
