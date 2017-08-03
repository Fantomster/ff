<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use Aws\Sns\MessageValidator\Message;
use Aws\Sns\MessageValidator\MessageValidator;
use Aws\Sns\MessageValidator\Exception\SnsMessageValidatorException;

/**
 * Description of SnsEndpointController
 *
 * @author elbabuino
 */
class SnsEndpointController extends \yii\rest\Controller {

    public function actionBounce() {
        //
        $message = Message::fromRawPostData();
        $validator = new MessageValidator();

        Yii::error("yay!");
        Yii::error($message->get('Message'));
        
        try {
            $validator->validate($message);
        } catch (SnsException $ex) {
            Yii::error($ex->getMessage());
            throw new HttpException(404 ,'Нет здесь ничего такого, проходите, гражданин');
        }
        // Check the type of the message and handle the subscription.
        if ($message->get('Type') === 'SubscriptionConfirmation') {
            // Confirm the subscription by sending a GET request to the SubscribeURL
            Yii::error($message->get('SubscribeURL'));
            file_get_contents($message->get('SubscribeURL'));
        }
        
        if ($message->get('Type') === 'Notification' && $message->data['Message']['notificationType'] === 'Bounce') {
            Yii::error('bounce!');
        }
    }

    public function actionComplaint() {
        //
        $message = Message::fromRawPostData();
        $validator = new MessageValidator();

        try {
            $validator->validate($message);
        } catch (SnsException $ex) {
            Yii::error($ex->getMessage());
            throw new HttpException(404 ,'Нет здесь ничего такого, проходите, гражданин');
        }
        // Check the type of the message and handle the subscription.
        if ($message->get('Type') === 'SubscriptionConfirmation') {
            // Confirm the subscription by sending a GET request to the SubscribeURL
            Yii::error($message->get('SubscribeURL'));
            file_get_contents($message->get('SubscribeURL'));
        }
        
        if ($message->get('Type') === 'Notification' && $message->data['Message']['notificationType'] === 'Complaint') {
            //process complaint
        }
    }

}
