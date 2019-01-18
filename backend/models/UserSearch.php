<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\User;
use common\models\Role;
use common\models\Profile;
use common\models\Organization;
use common\models\Job;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

/**
 * UserSearch represents the model behind the search form about `common\models\User`.
 */
class UserSearch extends User
{

    public $role;
    public $full_name;
    public $phone;
    public $org_name;
    public $org_type_id;
    public $sms_allow;
    public $gender;
    public $email_allow;
    public $email;
    public $job;
    public $job_name;
    public $subscribe;
    public $sms_subscribe;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return "{{%user}}";
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'status', 'organization_id', 'sms_allow', 'gender', 'email_allow', 'job', 'subscribe', 'sms_subscribe'], 'integer'],
            [['email', 'full_name', 'phone', 'role', 'logged_in_ip', 'logged_in_at', 'created_ip', 'created_at', 'updated_at', 'org_name', 'org_type_id', 'job_name', 'language'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params, $role_id = null)
    {
        $query = User::find();

        $userTable = User::tableName();
        $profileTable = Profile::tableName();
        $roleTable = Role::tableName();
        $organizationTable = Organization::tableName();
        $jobTable = Job::tableName();

        $query = User::find();
        $query->joinWith(['role', 'profile', 'organization']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]],
            'pagination' => [
                'pageSize' => 20
            ]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $dataProvider->sort->attributes['role'] = [
            'asc' => ["$roleTable.name" => SORT_ASC],
            'desc' => ["$roleTable.name" => SORT_DESC],
        ];
        $dataProvider->sort->attributes['full_name'] = [
            'asc' => ["$profileTable.full_name" => SORT_ASC],
            'desc' => ["$profileTable.full_name" => SORT_DESC],
        ];
        $dataProvider->sort->attributes['phone'] = [
            'asc' => ["$profileTable.phone" => SORT_ASC],
            'desc' => ["$profileTable.phone" => SORT_DESC],
        ];
        $dataProvider->sort->attributes['org_name'] = [
            'asc' => ["$organizationTable.name" => SORT_ASC],
            'desc' => ["$organizationTable.name" => SORT_DESC],
        ];
        $dataProvider->sort->attributes['org_type_id'] = [
            'asc' => ["$organizationTable.type_id" => SORT_ASC],
            'desc' => ["$organizationTable.type_id" => SORT_DESC],
        ];
        $dataProvider->sort->attributes['gender'] = [
            'asc' => ["$profileTable.gender" => SORT_ASC],
            'desc' => ["$profileTable.gender" => SORT_DESC],
        ];
        $dataProvider->sort->attributes['job'] = [
            'asc' => ["$jobTable.name_job" => SORT_ASC],
            'desc' => ["$jobTable.name_job" => SORT_DESC],
        ];
        $dataProvider->sort->attributes['sms_subscribe'] = [
            'asc' => ["$userTable.sms_subscribe" => SORT_ASC],
            'desc' => ["$userTable.sms_subscribe" => SORT_DESC],
        ];
        $dataProvider->sort->attributes['subscribe'] = [
            'asc' => ["$userTable.subscribe" => SORT_ASC],
            'desc' => ["$userTable.subscribe" => SORT_DESC],
        ];
        $dataProvider->sort->attributes['language'] = [
            'asc' => ["$userTable.language" => SORT_ASC],
            'desc' => ["$userTable.language" => SORT_DESC],
        ];

        // grid filtering conditions
        $query->andFilterWhere([
            $userTable . '.id' => $this->id,
            'status' => $this->status,
            'logged_in_at' => $this->logged_in_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'organization_id' => $this->organization_id,
            'role_id' => $role_id,
            'language' => $this->language
        ]);

        $query->andFilterWhere(['like', 'user.email', $this->email])
            ->andFilterWhere(['like', 'logged_in_ip', $this->logged_in_ip])
            ->andFilterWhere(['like', 'created_ip', $this->created_ip])
            ->andFilterWhere(['like', "$roleTable.name", $this->role])
            ->andFilterWhere(['like', "$organizationTable.name", $this->org_name])
            ->andFilterWhere(['like', "$organizationTable.type_id", $this->org_type_id])
            ->andFilterWhere(['like', "$profileTable.full_name", $this->full_name])
            ->andFilterWhere(['like', "$userTable.subscribe", $this->subscribe])
            ->andFilterWhere(['like', "$userTable.sms_subscribe", $this->sms_subscribe])
            ->andFilterWhere(['like', "$profileTable.phone", $this->phone])
            ->andFilterWhere(['like', "$profileTable.gender", $this->gender])
            ->andFilterWhere(['like', "$profileTable.job_id", $this->job])
            ->andFilterWhere(['like', "$jobTable.name_job", $this->job_name]);

        return $dataProvider;
    }

    /**
     * Возвращает пользователей по их статусу
     *
     * @return array
     */
    public static function getListToStatus()
    {

        $models[] = ['id' => '0', 'name_allow' => 'Не активен'];
        $models[] = ['id' => '1', 'name_allow' => 'Активен'];
        $models[] = ['id' => '2', 'name_allow' => 'Ожидается подтверждение E-mail'];

        return
            ArrayHelper::map($models, 'id', 'name_allow');
        // );
    }

    /**
     * Возвращает пользователей по их языку
     *
     * @return array
     */
    public static function getListToLanguage()
    {

        $sql = 'SELECT DISTINCT u.language FROM user u ORDER BY u.language';
        $models0 = \Yii::$app->db->createCommand($sql)->queryAll();
        $models = array();
        foreach ($models0 as $m) {
            $models[] = ['id' => $m['language'], 'name_allow' => $m['language']];
        }

        /*$models = User::find()
        ->select(['language'])
        ->asArray()
        ->all();
        $models = ActiveQuery::removeDuplicateModels($models);*/

        return
            ArrayHelper::map($models, 'id', 'name_allow');
    }

}
