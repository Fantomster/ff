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
    
    public static function getEmployeeRole($organization_type) {
        $role = static::find()->where('can_manage=0 AND organization_type = :orgType', [
            ':orgType' => $organization_type
        ])->one();
        return isset($role) ? $role->id : static::ROLE_USER;
    }
    
    /**
     * Get list of roles for creating dropdowns
     * @return array
     */
    public static function dropdown($orgType = null)
    {
        // get all records from database and generate
        static $dropdown;
        if ($dropdown === null) {
            if (isset($orgType)) {
                $models = static::findAll(['organization_type' => $orgType]);
            } else {
                $models = static::find()->all();
            }
            foreach ($models as $model) {
                $dropdown[$model->id] = $model->name;
            }
        }
        return $dropdown;
    }
    
}
