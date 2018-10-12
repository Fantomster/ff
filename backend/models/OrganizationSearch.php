<?php

namespace backend\models;

use common\models\licenses\LicenseOrganization;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Organization;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * OrganizationSearch represents the model behind the search form about `common\models\Organization`.
 */
class OrganizationSearch extends Organization {

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['id', 'type_id', 'step', 'blacklisted'], 'integer'],
            [['name', 'city', 'address', 'zip_code', 'phone', 'email', 'website', 'created_at', 'updated_at', 'white_list', 'partnership', 'locality'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios() {
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
    public function search($params, bool $isForLicenses = false) {
        if($isForLicenses){
            $dbApiName = $this->getDbName('db_api');
            $dbName = $this->getDbName('db');
            $now = new Expression('NOW()');
            $tenDaysAgo = new Expression('NOW() - INTERVAL 10 DAY');
            $query1 = Organization::find()->innerJoin($dbApiName.'.license_organization', "`$dbApiName`.license_organization.org_id=`$dbName`.organization.id")->andWhere(['between', 'td', $tenDaysAgo, $now])->orderBy('td', 'desc')->groupBy('td');
            $query2 = Organization::find()->innerJoin($dbApiName.'.license_organization', "`$dbApiName`.license_organization.org_id=`$dbName`.organization.id")->andWhere(['<', 'td', $now])->orderBy('td', 'desc')->groupBy('td');
            $allLicenseOrganizations = ArrayHelper::getColumn(LicenseOrganization::find()->where(['not', ['org_id' => null]])->groupBy('org_id')->all(), 'org_id');
            //$query3 = Organization::find()->where(['not in', 'id', $allLicenseOrganizations]);
            $query = $query1->union($query2);
        }else{
            $query = Organization::find();
        }


        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]],
            'pagination' => ['pageSize' => 20]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'type_id' => $this->type_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'step' => $this->step,
            'blacklisted' => $this->blacklisted,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
                ->andFilterWhere(['like', 'city', $this->city])
                ->andFilterWhere(['like', 'locality', $this->locality])
                ->andFilterWhere(['like', 'address', $this->address])
                ->andFilterWhere(['like', 'zip_code', $this->zip_code])
                ->andFilterWhere(['like', 'phone', $this->phone])
                ->andFilterWhere(['like', 'email', $this->email])
                ->andFilterWhere(['like', 'website', $this->website])
                ->andFilterWhere(['white_list' => $this->white_list])
                ->andFilterWhere(['partnership' => $this->partnership]);

        return $dataProvider;
    }


    private function getDbName($db){
        $db = Yii::$app->get($db);
        $dbNameArr = explode(';dbname=', $db->dsn);
        $dbName = $dbNameArr[1];
        return $dbName;
    }
}
