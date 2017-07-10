<?php
namespace common\models\restaurant;

use Yii;
use common\models\User;
use common\models\Catalog;
use common\models\Organization;
use common\models\Profile;
use common\models\RelationSuppRest;

class RestaurantChecker
{
    const RELATION_INVITED = 1; //есть связь с поставщиком invite_on
    const RELATION_INVITE_IN_PROGRESS = 2; //поставщику было отправлено приглашение, но поставщик еще не добавил этот ресторан
    const NO_AUTH_ADD_RELATION_AND_CATALOG = 3; //поставщик не авторизован // добавляем к базовому каталогу поставщика каталог ресторана и создаем связь
    const THIS_IS_RESTAURANT = 4; //email ресторана
    const NEW_VENDOR = 5; //нет в базе такого email
    const AUTH_SEND_INVITE = 6; //поставщик авторизован invite
    
    public static function checkEmail($email)
    {           
            $currentUser = User::findIdentity(Yii::$app->user->id);
            
		if(User::find()->select('email')->where(['email' => $email])->exists())
		{
                    $vendor = User::find()->where(['email' => $email])->one();
                    $userProfileFullName = $vendor->profile->full_name;
                    $userProfilePhone = $vendor->profile->phone;
                    $userOrgId = $vendor->organization_id;
                    $userOrgTypeId = $vendor->organization->type_id;
                    $userOrgName = $vendor->organization->name;
			if($userOrgTypeId==Organization::TYPE_SUPPLIER)
			{
				if(RelationSuppRest::find()->where(['rest_org_id' => $currentUser->organization_id,'supp_org_id'=>$userOrgId,'deleted'=>false])->exists())
				{
				$userRelationSuppRest = RelationSuppRest::find()
                                        ->where(['rest_org_id' => $currentUser->organization_id,'supp_org_id'=>$userOrgId,'deleted'=>false])
                                        ->one();
					if($userRelationSuppRest->invite==RelationSuppRest::INVITE_ON)
					{
	
					//есть связь с поставщиком invite_on
					$result = ['success'=>true,'eventType'=>self::RELATION_INVITED,'message'=>'Данный поставщик уже имеется в вашем списке контактов!',
					'fio' => $userProfileFullName,
                                        'phone' => $userProfilePhone,
					'organization' => $userOrgName]; 
	
					return $result;
	
					}else{
	
					//поставщику было отправлено приглашение, но поставщик еще не добавил этот ресторан
					$result = ['success'=>true,'eventType'=>self::RELATION_INVITE_IN_PROGRESS,'message'=>'Вы уже отправили приглашение этому поставщику, ожидается подтверждение от поставщика',
					'fio' => $userProfileFullName,
                                        'phone' => $userProfilePhone,
					'organization' => $userOrgName]; 
						
					return $result;
	
					} 
				}else{
                                        $managersIsActive = User::find()->where('organization_id =' . $userOrgId . ' and status >1')->count();
					if($managersIsActive==0){
					//поставщик не авторизован
					//добавляем к базовому каталогу поставщика каталог ресторана и создаем связь    
					$result = ['success'=>true,'eventType'=>self::NO_AUTH_ADD_RELATION_AND_CATALOG,'message'=>'Поставщик еще не авторизован / добавляем каталог',
					'fio' => $userProfileFullName,
                                        'phone' => $userProfilePhone,
					'organization' => $userOrgName,
                                        'org_id'=>$userOrgId];
			
					return $result;

					}else{
					//поставщик авторизован
					$result = [
                                            'success'=>true,
                                            'eventType'=>self::AUTH_SEND_INVITE,
                                            'message'=>'Поставщик уже зарегистрирован в системе, Вы можете его добавить нажав кнопку <strong>Пригласить</strong>',
                                            'fio' => $userProfileFullName,
                                            'phone' => $userProfilePhone,
                                            'organization' => $userOrgName,
                                            'org_id'=>$userOrgId
                                                   ];
			
					return $result;

					}  
				} 
			}else{
			//найден email ресторана
			$result = ['success'=>true,'eventType'=>self::THIS_IS_RESTAURANT,'message'=>'Данный email не может быть использован']; 
			return $result;
	
			}
		}else{
			//нет в базе такого email
			$result = ['success'=>true,'eventType'=>self::NEW_VENDOR,'message'=>'Нет совпадений по Email'];
			return $result;
			exit;  
			  
		}	
	}
	public static function getBaseCatalog($id_org){
		return Catalog::find()->select('id')->where(['supp_org_id' => $id_org,'type'=>Catalog::BASE_CATALOG])->one();
		 
	}
}		
