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
        $rules[] = [['full_name'], 'required'];
        $rules[] = [['full_name'], 'filter', 'filter'=>'\yii\helpers\HtmlPurifier::process'];
        return $rules;
    }
}
