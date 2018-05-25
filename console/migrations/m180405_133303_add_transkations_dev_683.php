<?php

use yii\db\Migration;
use common\models\SourceMessage;
use common\models\Message;

/**
 * Class m180405_133303_add_transkations_dev_683
 */
class m180405_133303_add_transkations_dev_683 extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->batchInsert('{{%source_message}}', ['category', 'message'], [
            ['message','frontend.views.order.add_to_order']
        ]);

        $row_id = SourceMessage::findOne(['category'=>'message', 'message' => 'common.models.rel_already_exists']) -> id;

        if(Message::findOne(['id' => $row_id]) == null)
            $this->batchInsert('{{%message}}', ['id', 'language', 'translation'], [
                [$row_id,'en', 'None'],
                [$row_id,'es', 'None'],
                [$row_id,'md', 'None'],
                [$row_id,'ru', 'Добавить в заказ']
            ]);
        else {
            $this->update('{{%message}}', [
                'translation' => 'None'],
                "id=$row_id and language = 'en'"
            );
            $this->update('{{%message}}', [
                'translation' => 'None'],
                "id=$row_id and language = 'es'"
            );
            $this->update('{{%message}}', [
                'translation' => 'None'],
                "id=$row_id and language = 'md'"
            );
            $this->update('{{%message}}', [
                'translation' => 'Добавить в заказ'],
                "id=$row_id and language = 'ru'"
            );
        }

        /*************************************************************************************/
        $this->batchInsert('{{%source_message}}', ['category', 'message'], [
            ['message','frontend.controllers.order.add_position']
        ]);

        $row_id = SourceMessage::findOne(['category'=>'message', 'message' => 'frontend.views.client.suppliers.supplier_apply']) -> id;

        if(Message::findOne(['id' => $row_id]) == null)
            $this->batchInsert('{{%message}}', ['id', 'language', 'translation'], [
                [$row_id,'en', 'None'],
                [$row_id,'es', 'None'],
                [$row_id,'md', 'None'],
                [$row_id,'ru', '<br\/>добавил {prod} {quantity} по цене {productPrice} {currencySymbol}/{ed}']
            ]);
        else {
            $this->update('{{%message}}', [
                'translation' => 'None'],
                "id=$row_id and language = 'en'"
            );
            $this->update('{{%message}}', [
                'translation' => 'None'],
                "id=$row_id and language = 'es'"
            );
            $this->update('{{%message}}', [
                'translation' => 'None'],
                "id=$row_id and language = 'md'"
            );
            $this->update('{{%message}}', [
                'translation' => '<br\/>добавил {prod} {quantity} по цене {productPrice} {currencySymbol}/{ed} '],
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
