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

    /**
     * @var int admin role
     */
    const ROLE_ADMIN = 1;
    /**
     * @var int Restaurant manager role
     */
    const ROLE_RESTAURANT_MANAGER = 3;
    /**
     * @var int Restaurant employee role
     */
    const ROLE_RESTAURANT_EMPLOYEE = 4;
    /**
     * @var int Supplier manager role
     */
    const ROLE_SUPPLIER_MANAGER = 5;
    /**
     * @var int Supplier employee role
     */
    const ROLE_SUPPLIER_EMPLOYEE = 6;
    /**
     * @var int f-keeper manager role
     */
    const ROLE_FKEEPER_MANAGER = 7;
    /**
     * @var int f-keeper observer role
     */
    const ROLE_FKEEPER_OBSERVER = 8;
    /**
     * @var int franchisee owner role
     */
    const ROLE_FRANCHISEE_OWNER = 9;
    /**
     * @var int franchisee operator role
     */
    const ROLE_FRANCHISEE_OPERATOR = 10;
    /**
     * @var int franchisee accountant role
     */
    const ROLE_FRANCHISEE_ACCOUNTANT = 11;
    /**
     * @var int franchisee agent role
     */
    const ROLE_FRANCHISEE_AGENT = 12;
    /**
     * @var int franchisee leader role
     */
    const ROLE_FRANCHISEE_LEADER = 13;
    /**
     * @var int franchisee manager role
     */
    const ROLE_FRANCHISEE_MANAGER = 14;

    
    public static function getManagerRole($organization_type) {
        $role = static::find()->where('can_manage=1 AND organization_type = :orgType', [
            ':orgType' => $organization_type
        ])->one();
        return isset($role) ? $role->id : static::ROLE_USER;
    }
    
    public static function getAdminRoles() {
        return [self::ROLE_ADMIN, self::ROLE_FKEEPER_MANAGER, self::ROLE_FKEEPER_OBSERVER];
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
                $dropdown[$model->id] = \Yii::t('app', $model->name);
            }
        }
        return $dropdown;
    }


    public static function getRoleName(int $roleId): string
    {
        $role = static::findOne(['id'=>$roleId]);
        return $role->name ?? '';
    }


    public static function getExceptionArray(): array
    {
        return [self::ROLE_ADMIN, self::ROLE_FKEEPER_OBSERVER];
    }


    public static function getFranchiseeEditorRoles(): array
    {
        return [self::ROLE_FRANCHISEE_OWNER, self::ROLE_FRANCHISEE_OPERATOR, self::ROLE_FRANCHISEE_LEADER, self::ROLE_FRANCHISEE_MANAGER];
    }


    public function getRelationOrganizationType(int $userID, int $organizationID): int
    {
        $rel = RelationUserOrganization::findOne(['user_id'=>$userID, 'organization_id'=>$organizationID]);
        $roleID = $rel->role_id;
        if($roleID==self::ROLE_RESTAURANT_MANAGER || $roleID==self::ROLE_RESTAURANT_EMPLOYEE){
            return Organization::TYPE_RESTAURANT;
        }else{
            return Organization::TYPE_SUPPLIER;
        }
    }
}
