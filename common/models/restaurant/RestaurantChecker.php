<?php
namespace frontend\models\restaurant;

use Yii;
use common\models\User;
use common\models\Catalog;
use common\models\Organization;
use common\models\Profile;
use common\models\RelationSuppRest;

class RestaurantChecker
{
	public static function checkEmail($email)
    {
		if(User::find()->select('email')->where(['email' => $email])->exists())
		{
		$rest_org_id = User::getOrganizationUser(Yii::$app->user->id);    
		$userProfile = User::find()->select('id,organization_id,status,email')->where(['email' => $email])->one();
		$userProfileFullName = Profile::find()->select('full_name')->where(['user_id' => $userProfile['id']])->one();
		$userProfileFullName =$userProfileFullName['full_name'];
		$userProfileOrgId = $userProfile['organization_id']; //организация
		$userProfileStatus = $userProfile['status']; //статус
		$userOrg = Organization::find()->select('type_id,name')->where(['id' => $userProfileOrgId])->one();
		$userOrgName = $userOrg['name'];
		$userOrgTypeId = $userOrg['type_id']; //тип организации 1 или 2
			if($userOrgTypeId==2)
			{
				if(RelationSuppRest::find()->where(['rest_org_id' => $rest_org_id,'sup_org_id'=>$userProfileOrgId])->exists())
				{
				$userRelationSuppRest = RelationSuppRest::find()->select('status')->where(['rest_org_id' => $rest_org_id,'sup_org_id'=>$userProfileOrgId])->one();
					if($userRelationSuppRest['status']==1)
					{
	
					//есть связь с поставщиком
					$result = ['success'=>true,'eventType'=>1,'message'=>'Поставщик уже есть в списке контактов!',
					'fio' => $userProfileFullName,
					'organization' => $userOrgName]; 
	
					return $result;
	
					}else{
	
					//поставщику было отправлено приглашение, но поставщик еще не добавил этот ресторан
					$result = ['success'=>true,'eventType'=>2,'message'=>'Вы уже отправили приглашение этому поставщику, ожидается отклик поставщика!',
					'fio' => $userProfileFullName,
					'organization' => $userOrgName]; 
						
					return $result;
	
					} 
				}else{
					if($userProfileStatus==0){
					//поставщик не авторизован
					//добавляем к базовому каталогу поставщика каталог ресторана и создаем связь    
					$result = ['success'=>true,'eventType'=>3,'message'=>'Поставщик еще не авторизован / добавляем каталог',
					'fio' => $userProfileFullName,
					'organization' => $userOrgName,'org_id'=>$userProfileOrgId];
			
					return $result;

					}else{
					//поставщик авторизован
					$result = ['success'=>true,'eventType'=>6,'message'=>'Поставщик авторизован, предлагаем invite',
					'fio' => $userProfileFullName,
					'organization' => $userOrgName];
			
					return $result;

					}  
				} 
			}else{
			//найден email ресторана
			$result = ['success'=>true,'eventType'=>4,'message'=>'err: Данный email не может быть использован!']; 
			return $result;
	
			}
		}else{
			//нет в базе такого email
			$result = ['success'=>true,'eventType'=>5,'message'=>'Нет совпадений по Email!'];
			return $result;
			exit;  
			  
		}	
	}
	public static function getBaseCatalog($id_org){
		$idorg=Catalog::find()->select('id')->where(['org_supp_id' => $id_org,'type'=>1])->one();
		return $idorg;
	}
}		