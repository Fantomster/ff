<?php

namespace common\components;

use common\models\SourceMessage;
use common\models\Message;
use yii\i18n\MissingTranslationEvent;

/**
 * Description of Messages
 *
 */
class TranslationEventHandler {

    /**
    public static function handleMissingTranslation(MissingTranslationEvent $event)
    {
        if (!SourceMessage::findOne(['message' => $event->message])) {
            $sourceMessage = new SourceMessage();
            $sourceMessage->category = $event->category;
            $sourceMessage->message = $event->message;
            $sourceMessage->save();
            $id = $sourceMessage->id;
            foreach (SourceMessage::LANGUAGES as $language) {
                $message = new Message();
                $message->id = $id;
                $message->language = $language;
                $message->save();
            }
        }
    }
     */
}
