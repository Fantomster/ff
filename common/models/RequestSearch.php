<?php

namespace common\models;

use yii\data\ActiveDataProvider;

/**
 * OrderSearch represents the model behind the search form about `common\models\Order`.
 */
class RequestSearch extends Request
{

    public $searchString;
    public $date_from;
    public $date_to;
    public $product;
    public $comment;
    public $clientName;
    public $categoryName;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['amount'], 'integer'],
            [['created_at', 'date_from', 'date_to', 'searchString', 'product', 'comment', 'clientName', 'categoryName'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return "{{%request}}";
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search($params, $franchisee_id, $prev30 = false)
    {
        $requestTable = Request::tableName();
        $query = Request::find()->leftJoin('franchisee_associate', "franchisee_associate.organization_id = request.rest_org_id")->leftJoin('organization', "organization.id = request.rest_org_id")->leftJoin('mp_category', "mp_category.id = request.category");
        if ($franchisee_id) {
            $query->where(['franchisee_associate.franchisee_id' => $franchisee_id]);
        }
        $this->load($params);

        $from = \DateTime::createFromFormat('d.m.Y H:i:s', $this->date_from . " 00:00:00");
        if ($from) {
            $t1_f = $from->format('Y-m-d');
        }
        $to = \DateTime::createFromFormat('d.m.Y H:i:s', $this->date_to . " 00:00:00");
        if ($to) {
            $to->add(new \DateInterval('P1D'));
            $t2_f = $to->format('Y-m-d');
        }

        // grid filtering conditions
        $query->andFilterWhere(['or',
            ['like', 'product', $this->searchString],
            ['like', 'comment', $this->searchString],
            ['like', 'organization.name', $this->searchString],
            ['like', 'mp_category.name', $this->searchString],
        ]);

        $query->andFilterWhere([
            'created_at' => $this->created_at,
        ]);

        if ($prev30) {
            $query->andWhere("$requestTable.created_at between CURDATE() - INTERVAL 30 DAY and CURDATE() + INTERVAL 1 DAY ");
        } else {
            $query->andFilterWhere(['>=', "$requestTable.created_at", $t1_f]);
            $query->andFilterWhere(['<=', "$requestTable.created_at", $t2_f]);
        }

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
