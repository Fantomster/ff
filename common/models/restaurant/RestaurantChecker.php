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
	public static function checkEmail($email)
    {           
            $currentUser = User::findIdentity(Yii::$app->user->id);
            
		if(User::find()->select('email')->where(['email' => $email])->exists())
		{
		$sql = "SELECT "
                        . "`user`.`id` as user_id, "
                        . "`profile`.`full_name` as user_full_name, "
                        . "`user`.`organization_id` as organization_id, "
                        . "`organization`.`type_id` as organization_type_id, "
                        . "`organization`.`name` as organization_name, "
                        . "`user`.`status` as user_status, "
                        . "`user`.`email` as user_email "
                . " FROM {{%user}} "
                . " LEFT JOIN {{%organization}} on `user`.`organization_id` = `organization`.`id` "
                . " LEFT JOIN {{%profile}} on `user`.`id` = `profile`.`user_id`"
                . " WHERE `user`.`email` = :email";
                $vendor_info = \Yii::$app->db->createCommand($sql);
                $vendor_info->bindParam(":email",$email,\PDO::PARAM_STR);
                $vendor_info->queryOne();
                $vendor_info = $vendor_info->queryOne();
                $userProfileFullName = $vendor_info['user_full_name'];
                $userProfileStatus = $vendor_info['user_status']; //есть менеджеры или нет (user.status > 0)
                
                $userProfileOrgId = $vendor_info['organization_id'];
                $userOrgTypeId = $vendor_info['organization_type_id'];
                $userOrgName = $vendor_info['organization_name'];
			if($userOrgTypeId==2)
			{
				if(RelationSuppRest::find()->where(['rest_org_id' => $currentUser->organization_id,'supp_org_id'=>$userProfileOrgId])->exists())
				{
				$userRelationSuppRest = RelationSuppRest::find()->select('invite')->where(['rest_org_id' => $currentUser->organization_id,'supp_org_id'=>$userProfileOrgId])->one();
					if($userRelationSuppRest['invite']==RelationSuppRest::INVITE_ON)
					{
	
					//есть связь с поставщиком invite_on
					$result = ['success'=>true,'eventType'=>1,'message'=>'Данный поставщик уже имеется в вашем списке контактов!',
					'fio' => $userProfileFullName,
					'organization' => $userOrgName]; 
	
					return $result;
	
					}else{
	
					//поставщику было отправлено приглашение, но поставщик еще не добавил этот ресторан
					$result = ['success'=>true,'eventType'=>2,'message'=>'Вы уже отправили приглашение этому поставщику, ожидается подтверждение от поставщика',
					'fio' => $userProfileFullName,
					'organization' => $userOrgName]; 
						
					return $result;
	
					} 
				}else{
                                        $managersIsActive = User::find()->where(['organization_id' => $userProfileOrgId, 'status' =>1])->count();
					if($managersIsActive==0){
					//поставщик не авторизован
					//добавляем к базовому каталогу поставщика каталог ресторана и создаем связь    
					$result = ['success'=>true,'eventType'=>3,'message'=>'Поставщик еще не авторизован / добавляем каталог',
					'fio' => $userProfileFullName,
					'organization' => $userOrgName,'org_id'=>$userProfileOrgId];
			
					return $result;

					}else{
					//поставщик авторизован
					$result = [
                                            'success'=>true,
                                            'eventType'=>6,
                                            'message'=>'Поставщик уже зарегистрирован в системе, Вы можете его добавить нажав кнопку <strong>Пригласить</strong>',
                                            'fio' => $userProfileFullName,
                                            'organization' => $userOrgName,
                                            'org_id'=>$userProfileOrgId
                                                   ];
			
					return $result;

					}  
				} 
			}else{
			//найден email ресторана
			$result = ['success'=>true,'eventType'=>4,'message'=>'Данный email не может быть использован']; 
			return $result;
	
			}
		}else{
			//нет в базе такого email
			$result = ['success'=>true,'eventType'=>5,'message'=>'Нет совпадений по Email'];
			return $result;
			exit;  
			  
		}	
	}
	public static function getBaseCatalog($id_org){
		$idorg = Catalog::find()->select('id')->where(['supp_org_id' => $id_org,'type'=>Catalog::BASE_CATALOG])->one();
		return $idorg;
	}
}		
