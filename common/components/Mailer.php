<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\components;

use common\models\notifications\EmailBlacklist;

/**
 * Description of Mailer
 *
 * @author elbabuino
 */
class Mailer extends \yashop\ses\Mailer {
    public function beforeSend($message) {
        parent::beforeSend($message);
        //check blacklist
        if (EmailBlacklist::find()->where(['email' => $message->getTo()])) {
            \Yii::error('blacklisted! ' . $message->getTo());
            return false;
        }
        \Yii::error('wtf');
        return true;
    }
}
