<?php

use yii\db\Migration;
use common\models\SourceMessage;
use common\models\Message;

/**
 * Class m180227_093606_add_translate_messages
 */
class m180227_093606_add_translate_messages extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->batchInsert('{{%source_message}}', ['category', 'message'], [
            ['message','common.models.rel_already_exists']
        ]);

        $row_id = SourceMessage::findOne(['category'=>'message', 'message' => 'common.models.rel_already_exists']) -> id;

        if(Message::findOne(['id' => $row_id]) == null)
            $this->batchInsert('{{%message}}', ['id', 'language', 'translation'], [
                [$row_id,'en', 'Restaurant with this email address is already working with you. Please check your client list!'],
                [$row_id,'es', 'El restaurante con esta dirección de correo electrónico ya está trabajando con usted. Por favor revisa tu lista de clientes!'],
                [$row_id,'ru', 'Ресторан с таким email уже сотрудничает с вами. Проверьте список ваших клиентов!']
            ]);
        else {
            $this->update('{{%message}}', [
                'translation' => 'Restaurant with this email address is already working with you. Please check your client list!'],
                "id=$row_id and language = 'en')"
            );
            $this->update('{{%message}}', [
                'translation' => 'El restaurante con esta dirección de correo electrónico ya está trabajando con usted. Por favor revisa tu lista de clientes!'],
                "id=$row_id and language = 'es')"
            );
            $this->update('{{%message}}', [
                'translation' => 'Ресторан с таким email уже сотрудничает с вами. Проверьте список ваших клиентов!'],
                "id=$row_id and language = 'ru'"
            );
        }

        /*************************************************************************************/
        $this->batchInsert('{{%source_message}}', ['category', 'message'], [
            ['message','frontend.views.client.suppliers.supplier_apply']
        ]);

        $row_id = SourceMessage::findOne(['category'=>'message', 'message' => 'frontend.views.client.suppliers.supplier_apply']) -> id;

        if(Message::findOne(['id' => $row_id]) == null)
            $this->batchInsert('{{%message}}', ['id', 'language', 'translation'], [
                [$row_id,'en', 'Cooperate!'],
                [$row_id,'es', 'Cooperar!'],
                [$row_id,'ru', 'Сотрудничать!']
            ]);
        else {
            $this->update('{{%message}}', [
                'translation' => 'Cooperate!'],
                "id=$row_id and language = 'en'"
            );
            $this->update('{{%message}}', [
                'translation' => 'Cooperar!'],
                "id=$row_id and language = 'es'"
            );
            $this->update('{{%message}}', [
                'translation' => 'Сотрудничать!'],
                "id=$row_id and language = 'ru'"
            );
        }
        /************************************************************/
        $this->batchInsert('{{%source_message}}', ['category', 'message'], [
            ['app','common.mail.accept_active_vendor_invite.we']
        ]);

        $row_id = SourceMessage::findOne(['category'=>'app', 'message' => 'common.mail.accept_active_vendor_invite.we']) -> id;

        if(Message::findOne(['id' => $row_id]) == null)
            $this->batchInsert('{{%message}}', ['id', 'language', 'translation'], [
                [$row_id,'en', '{vendor}, has already made its work easier with MixCart {link} and is glad to invite you to work together on mix-cart.com.'],
                [$row_id,'es', '{vendor}, has already made its work easier with MixCart {link} and is glad to invite you to work together on mix-cart.com.'],
                [$row_id,'ru', '{vendor}, уже сделал свою работу легче с помощью {link} и приглашает вас работать вместе с ним.']
            ]);
        else {
            $this->update('{{%message}}', [
                'translation' => '{vendor}, has already made its work easier with MixCart {link} and is glad to invite you to work together on mix-cart.com.'],
                "id=$row_id and language = 'en'"
            );
            $this->update('{{%message}}', [
                'translation' => '{vendor}, has already made its work easier with MixCart {link} and is glad to invite you to work together on mix-cart.com.'],
                "id=$row_id and language = 'es'"
            );
            $this->update('{{%message}}', [
                'translation' => '{vendor}, уже сделал свою работу легче с помощью {link} и приглашает вас работать вместе с ним.'],
                "id=$row_id and language = 'ru'"
            );
        }
        /************************************************************/
        $this->batchInsert('{{%source_message}}', ['category', 'message'], [
            ['app','common.mail.accept_active_vendor_invite.i_invite']
        ]);

        $row_id = SourceMessage::findOne(['category'=>'app', 'message' => 'common.mail.accept_active_vendor_invite.i_invite']) -> id;

        if(Message::findOne(['id' => $row_id]) == null)
            $this->batchInsert('{{%message}}', ['id', 'language', 'translation'], [
                [$row_id,'en', 'Make your purchases easier and faster with MixCart: price comparison, actual price lists, ordering in 2 clicks and much more.'],
                [$row_id,'es', 'Make your purchases easier and faster with MixCart: price comparison, actual price lists, ordering in 2 clicks and much more.'],
                [$row_id,'ru', 'Сервис автоматизации закупок для ресторанов MixCart позволит вам делать закупки легко и быстро, попробуйте!']
            ]);
        else {
            $this->update('{{%message}}', [
                'translation' => 'Make your purchases easier and faster with MixCart: price comparison, actual price lists, ordering in 2 clicks and much more.'],
                "id=$row_id and language = 'en'"
            );
            $this->update('{{%message}}', [
                'translation' => 'Make your purchases easier and faster with MixCart: price comparison, actual price lists, ordering in 2 clicks and much more..'],
                "id=$row_id and language = 'es'"
            );
            $this->update('{{%message}}', [
                'translation' => 'Сервис автоматизации закупок для ресторанов MixCart позволит вам делать закупки легко и быстро, попробуйте!'],
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
