<?php

use yii\db\Migration;

/**
 * Class m180424_141100_yet_another_email_translation
 */
class m180424_141100_yet_another_email_translation extends Migration
{
    public $ru = [
        ['common.mail.demo.link_text', 'блоге'],
        ['common.mail.demo.ps', 'P.S. Мы постоянно добавляем новые возможности, так что следите за новостями в'],
        ['common.mail.demo.manager_phone', '8-499-404-10-18'],
        ['common.mail.demo.manager_title', 'Менеджер по развитию'],
        ['common.mail.demo.manager_name', 'Ольга Захряпина'],
        ['common.mail.demo.farewell', 'Отличного дня,'],
        ['common.mail.demo.paragraph3', 'Сообщите, в какое время с Вами можно связаться и обговорить детали.'],
        ['common.mail.demo.paragraph2', 'Я предлагаю провести бесплатную демонстрацию решения на вашей территории - займет 10-15 минут и рассказать, как MixCart будет полезен в Вашем конкретном случае.'],
        ['common.mail.demo.paragraph1', 'Я уверена, что наш способ управлять закупками — самый удобный и простой. Но не верьте словам, лучше попробуйте сами или посмотрите!'],
        ['common.mail.demo.greetings', 'Здравствуйте!'],
        ['common.mail.demonstration.subject', 'Как управлять закупками с MixCart'],
        ['common.mail.manager_message.link_text', 'блоге'],
        ['common.mail.manager_message.ps', 'P.S. Мы постоянно добавляем новые возможности, так что следите за новостями в'],
        ['common.mail.manager_message.manager_phone', '8-499-404-10-18'],
        ['common.mail.manager_message.manager_title', 'Менеджер по развитию'],
        ['common.mail.manager_message.manager_name', 'Ольга Захряпина'],
        ['common.mail.manager_message.farewell', 'Отличного дня,'],
        ['common.mail.manager_message.paragraph2', 'Мне было бы очень интересно пообщаться с Вами и обсудить методы, которыми Вы пользуетесь в управлении закупками, возникающие сложности. Я могу рассказать, как MixCart будет полезен в Вашем конкретном случае.'],
        ['common.mail.manager_message.li4', 'Только для поставщиков: стать нашим партнером и размещать продукты на MixMarket'],
        ['common.mail.manager_message.li3', 'Быстро находить продукты и сравнивать цены'],
        ['common.mail.manager_message.li2', 'Интегрировать закупки с документооборотом (iiko, R-keeper, 1С)'],
        ['common.mail.manager_message.li1', 'Вести все заказы и рабочие процессы по закупкам в одной системе и в мобильном приложении'],
        ['common.mail.manager_message.two_words', 'В двух словах — что можно делать в MixCart:'],
        ['common.mail.manager_message.paragraph1', 'Меня зовут Оля, я менеджер по развитию в MixCart. Недавно Вы зарегистрировались на нашей платформе, и я хочу помочь Вам разобраться во всех её возможностях.'],
        ['common.mail.manager_message.greetings', 'Здравствуйте!'],
        ['common.mail.manager_message.subject', 'Ольга от MixCart'],
        ['frontend.views.settings.info_mail', 'Информационные рассылки по email'],
    ];
    
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        foreach ($this->ru as $row) {
            $key = trim($row[0]);
            $value = trim($row[1]);

            $source_message = \common\models\SourceMessage::findOne(['message' => $key, 'category' => 'app']);

            if (empty($source_message)) {
                $source_message = new \common\models\SourceMessage();
                $source_message->message = $key;
                $source_message->category = 'app';
                $source_message->save();
            }

            $message = \common\models\Message::findOne(['id' => $source_message->id, 'language' => 'ru']);
            if (empty($message)) {
                $message = new \common\models\Message();
            }

            $message->id = $source_message->id;
            $message->language = 'ru';
            $message->translation = $value;
            $message->save();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        foreach ($this->ru as $row) {
            $key = trim($row[0]);
            $source_message = \common\models\SourceMessage::findOne(['message' => $key, 'category' => 'app']);
            if (!empty($source_message)) {
                $message = \common\models\Message::findOne(['id' => $source_message->id, 'language' => 'ru']);
                if (!empty($message)) {
                    $message->delete();
                }
                $source_message->delete();
            }
        }
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180424_141100_yet_another_email_translation cannot be reverted.\n";

        return false;
    }
    */
}
