<?php

use yii\db\Migration;

/**
 * Class m180420_075819_email_translate
 */
class m180420_075819_email_translate extends Migration
{
    public $ru = [
        ['common.mail.layouts.unsubscribe', 'Отписаться'],
        ['common.mail.layouts.from_this_mailing', 'от этой рассылки'],
        ['common.mail.confirm_email.full_text', 'Благодарим Вас за регистрацию в MixCart! Перед началом работы необходимо подтвердить этот адрес электронной почты. Для этого пройдите по ссылке:'],
        ['common.mail.confirm_email.hello', 'Здравствуйте!'],
        ['common.mail.welcome.hello', 'Здравствуйте!'],
        ['common.mail.welcome.full_text', 'Меня зовут Ильдар Хасанов, я являюсь сооснователем сервиса MixCart. Я искренне рад видеть Вас в числе наших клиентов!<br><br>Технологии уже давно стали драйвером развития бизнеса во всех сферах. С MixCart рестораны и поставщики управляют закупками и делают это быстрее, удобнее, и с большей обоюдной выгодой для бизнеса.<br><br>Да, я знаю, сначала внедрение новых инструментов кажется сложным, но, поверьте, все намного проще, чем может показаться, и результат того стоит!'],
        ['common.mail.welcome.start', 'Начнем'],
        ['common.mail.welcome.read_instruction', 'Ознакомьтесь, пожалуйста, с инструкцией по работе с MixCart:'],
        ['common.mail.welcome.instruction_restaurant', 'для ресторанов'],
        ['common.mail.welcome.instruction_supplier', 'для поставщиков'],
        ['common.mail.welcome.head_good', 'Здорово,'],
        ['common.mail.welcome.head_good_1', 'Что вы с нами'],
        ['common.mail.weekend.subject', 'Вы с нами уже неделю!'],
        ['common.mail.weekend.thank_you', 'Спасибо,'],
        ['common.mail.weekend.that_you_are_with_us', 'Что вы с нами!'],
        ['common.mail.weekend.what_date', 'Вы знаете, что сегодня за дата?'],
        ['common.mail.weekend.dialog_message_1', '- Что?'],
        ['common.mail.weekend.dialog_message_2', '- Не говорите, что вы забыли!'],
        ['common.mail.weekend.together_time', 'Мы вместе уже 604800 секунд! Это ровно одна неделя!'],
        ['common.mail.weekend.together_time_2', 'Неважно, если вы забыли - мы все равно вас ценим!'],
        ['common.mail.weekend.team', 'Команда'],
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
}
