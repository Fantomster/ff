<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use Aws\Sns\MessageValidator\Message;
use Aws\Sns\MessageValidator\MessageValidator;
use Aws\Sns\MessageValidator\Exception\SnsMessageValidatorException;
use yii\helpers\Json;
use common\models\notifications\EmailBlacklist;
use common\models\notifications\EmailFails;

/**
 * Description of SnsEndpointController
 *
 * @author elbabuino
 */
class SnsEndpointController extends \yii\rest\Controller {

    public function actionBounce() {
        $message = Message::fromRawPostData();
        $validator = new MessageValidator();

        try {
            $validator->validate($message);
        } catch (SnsException $ex) {
            Yii::error($ex->getMessage());
            throw new HttpException(404, 'Нет здесь ничего такого, проходите, гражданин');
        }
        // Check the type of the message and handle the subscription.
        if ($message->get('Type') === 'SubscriptionConfirmation') {
            // Confirm the subscription by sending a GET request to the SubscribeURL
            file_get_contents($message->get('SubscribeURL'));
        }

        $data = Json::decode($message->get('Message'), true);
        if (($message->get('Type') === 'Notification') && ($data['notificationType'] === 'Bounce')) {
            $bouncedRecipients = $data["bounce"]["bouncedRecipients"];
            foreach ($bouncedRecipients as $recipient) {
                if (!EmailBlacklist::find()->where(['email' => $recipient['emailAddress']])->exists()) {
                    $newBlacklisted = new EmailBlacklist();
                    $newBlacklisted->email = $recipient['emailAddress'];
                    $newBlacklisted->save();
                }
                $newFail = new EmailFails();
                $newFail->type = EmailFails::TYPE_BOUNCE;
                $newFail->email = $recipient['emailAddress'];
                $newFail->body = $message->get('Message');
                $newFail->save();
            }
        }
    }

    public function actionComplaint() {
        $message = Message::fromRawPostData();
        $validator = new MessageValidator();

        try {
            $validator->validate($message);
        } catch (SnsException $ex) {
            Yii::error($ex->getMessage());
            throw new HttpException(404, 'Нет здесь ничего такого, проходите, гражданин');
        }
        // Check the type of the message and handle the subscription.
        if ($message->get('Type') === 'SubscriptionConfirmation') {
            // Confirm the subscription by sending a GET request to the SubscribeURL
            Yii::error($message->get('SubscribeURL'));
            file_get_contents($message->get('SubscribeURL'));
        }

        $data = Json::decode($message->get('Message'), true);
        if (($message->get('Type') === 'Notification') && ($data['notificationType'] === 'Complaint')) {
            $complainedRecipients = $data["bounce"]["complainedRecipients"];
            foreach ($complainedRecipients as $recipient) {
                if (!EmailBlacklist::find()->where(['email' => $recipient['emailAddress']])->exists()) {
                    $newBlacklisted = new EmailBlacklist();
                    $newBlacklisted->email = $recipient['emailAddress'];
                    $newBlacklisted->save();
                }
                $newFail = new EmailFails();
                $newFail->type = EmailFails::TYPE_COMPLAINT;
                $newFail->email = $recipient['emailAddress'];
                $newFail->body = $message->get('Message');
                $newFail->save();
            }
        }
    }

}
