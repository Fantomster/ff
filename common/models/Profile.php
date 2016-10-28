<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\models;

/**
 * Profile model
 *
 * @inheritdoc
 *
 */
class Profile extends \amnah\yii2\user\models\Profile {
    /**
     * @inheritdoc
     */
    public function rules() {
        $rules = parent::rules();
        $rules[] = [['full_name'], 'required', 'on' => 'register', 'message' => 'Пожалуйста, напишите как к вам обращаться'];
        $rules[] = [['full_name'], 'required'];
        $rules[] = [['full_name'], 'filter', 'filter'=>'\yii\helpers\HtmlPurifier::process'];
        
//        //переопределим сообщения валидации быдланским способом
//        $pos = array_search(['email', 'required'], $rules);
//        $rules[$pos]['message'] = 'Пожалуйста, напишите ваш адрес электронной почты';
        
        return $rules;
    }
    public function attributeLabels()
    {
        return [
            'full_name' => 'ФИО',
        ];
    }
}
