<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\models;

/**
 * User model
 *
 * @inheritdoc
 *
 * @property integer $organization_id
 * 
 * @property Organization $organization
 */
class User extends \amnah\yii2\user\models\User {

    /**
     * Set organization id
     * @param int $orgId
     * @return static
     */
    public function setOrganization($orgId)
    {
        $this->organization_id = $orgId;
        return $this;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrganization()
    {
        $organization = $this->module->model("Organization");
        return $this->hasOne($organization::className(), ['id' => 'organization_id']);
    }    
}
