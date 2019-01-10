<?php

namespace common\models;

use yii\data\ActiveDataProvider;

/**
 * OrderSearch represents the model behind the search form about `common\models\Order`.
 */
class RequestCallbackSearch extends Request
{

    public $searchString;
    public $price;
    public $comment;
    public $vendorName;
    public $date_from;
    public $date_to;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['searchString', 'price', 'comment', 'vendorName'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return "{{%request_callback}}";
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = RequestCallback::find()->leftJoin('organization', "organization.id = request_callback.supp_org_id")
            ->where(['request_callback.request_id' => $params['id']]);
        $this->load($params);

        // grid filtering conditions
        $query->andFilterWhere(['or',
            ['like', 'price', $this->searchString],
            ['like', 'comment', $this->searchString],
            ['like', 'organization.name', $this->searchString],
        ]);

        $query->andFilterWhere([
            'created_at' => $this->created_at,
        ]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'  => ['defaultOrder' => ['id' => SORT_DESC]]
        ]);

        if (!$this->validate()) {
            return $dataProvider;
        }
        return $dataProvider;
    }

}
