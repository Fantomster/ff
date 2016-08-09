<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\models;

/**
 * Role model
 *
 * @inheritdoc
 *
 * @property integer $can_manage
 * @property integer $organization_type
 * 
 * @property OrganizationType $organizationType
 */
class Role extends \amnah\yii2\user\models\Role {

    public static function getManagerRole($organization_type) {
        $role = static::find()->where('can_manage=1 AND organization_type = :orgType', [
            ':orgType' => $organization_type
        ])->one();
        return isset($role) ? $role->id : static::ROLE_USER;
    }
}
