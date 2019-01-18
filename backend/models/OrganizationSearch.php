<?php

namespace backend\models;

use common\models\licenses\LicenseOrganization;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Organization;
use yii\data\ArrayDataProvider;
use yii\data\SqlDataProvider;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * OrganizationSearch represents the model behind the search form about common\models\Organization.
 */
class OrganizationSearch extends Organization
{
    private $dbApiName;
    private $dbName;
    private $tenDaysAfter;
    private $tenDaysAgo;
    private $maxQuery;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'type_id', 'step', 'blacklisted'], 'integer'],
            [['name', 'city', 'address', 'zip_code', 'phone', 'email', 'website', 'created_at', 'updated_at', 'white_list', 'partnership', 'locality'], 'safe'],
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
     * @return ActiveDataProvider
     */
    public function search($params, bool $isForLicenses = false)
    {
        $this->load($params);
        if ($isForLicenses) {
            $this->dbApiName = \common\helpers\DBNameHelper::getApiName();
            $this->dbName = \common\helpers\DBNameHelper::getMainName();
            $this->tenDaysAfter = new Expression('NOW() + INTERVAL 10 DAY');
            $this->tenDaysAgo = new Expression('NOW() - INTERVAL 1 DAY');

            $maxSelect = (new \yii\db\Query())
                ->select('MAX(td)')
                ->from("$this->dbApiName.license_organization AS lo")
                ->where("lo.org_id = $this->dbName.organization.id");
            $maxSelectSQL = $maxSelect->createCommand()->getRawSql();

            $this->maxQuery = "$this->dbApiName.license_organization.td = ($maxSelectSQL)";
            $query1 = $this->getSQLQuery(1);

            $query2 = $this->getSQLQuery(2);

            $query3 = $this->getSQLQuery(3);

            $query4 = Organization::find()->with('licenseOrganization')->andFilterWhere(['like', 'name', $this->name])->andFilterWhere(['organization.id' => $this->id]);

            $query1->union($query2)->union($query3)->union($query4);
            $sql = $query1->createCommand()->getRawSql();

            $dataProvider = new SqlDataProvider([
                'sql'        => $sql,
                'key'        => 'id',
                'pagination' => ['pageSize' => 20],
                'sort'       => [
                    'attributes' => [
                        'id'   => [
                            'asc'     => ['id' => SORT_ASC], // от А до Я
                            'desc'    => ['id' => SORT_DESC], // от Я до А
                            'default' => SORT_DESC
                        ]
                    ],
                ],
            ]);
        } else {
            $query = Organization::find();
// add conditions that should always apply here

            $dataProvider = new ActiveDataProvider([
                'query'      => $query,
                'sort'       => [
                    'attributes' => [
                        'id',
                        'name',
                    ]
                ],
                'pagination' => ['pageSize' => 20]
            ]);

            if (!$this->validate()) {
// uncomment the following line if you do not want to return any records when validation fails
// $query->where('0=1');
                return $dataProvider;
            }

// grid filtering conditions
            $query->andFilterWhere([
                'id'          => $this->id,
                'type_id'     => $this->type_id,
                'created_at'  => $this->created_at,
                'updated_at'  => $this->updated_at,
                'step'        => $this->step,
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
        }

        return $dataProvider;
    }

    private function getDbName($db)
    {
        $db = Yii::$app->get($db);
        $dbNameArr = explode(';dbname=', $db->dsn);

        return $dbNameArr[1];
    }

    private function getSQLQuery($queue)
    {
        $query = Organization::find()
            ->innerJoin("$this->dbApiName.license_organization", "$this->dbApiName.license_organization.org_id=$this->dbName.organization.id");
        if ($queue == 1 || $queue == 2) {
            $query = $query->where($this->maxQuery);
        }
        if ($queue == 1) {
            $query = $query->andWhere(['between', 'td', $this->tenDaysAgo, $this->tenDaysAfter]);
        }
        if ($queue == 2) {
            $query = $query->andWhere(['<=', 'td', $this->tenDaysAfter]);
        }
        if ($queue == 3) {
            $query = $query->andWhere(['>', 'td', $this->tenDaysAfter]);
        }

        $query = $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['organization.id' => $this->id]);
        return $query;
    }
}
