<?php

namespace api\common\models\iiko\search;

use api\common\models\iiko\iikoService;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Organization;
use common\helpers\DBNameHelper;

class iikoServiceSearch extends iikoService
{

    public $fd_range;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at', 'is_deleted', 'user_id', 'org', 'fd', 'td', 'status_id', 'is_deleted', 'code', 'name', 'address', 'phone', 'fd_range'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = iikoService::find();
        $dbName = DBNameHelper::getMainName();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);
        $org_table_name = Organization::tableName();
        $query->leftJoin($dbName . '.' . $org_table_name, $org_table_name . '.id = org');

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere(['status_id' => $this->status_id])
            ->andFilterWhere(['like', $org_table_name . '.name', $this->org]);

        if (!empty($this->fd)) {
            list($day, $month, $year) = explode('.', $this->fd);
            $fd_normal = $year . '-' . $month . '-' . $day . ' 00:00:00';
            $query->andFilterWhere(['>=', 'fd', $fd_normal]);
        }

        if (!empty($this->td)) {
            list($day, $month, $year) = explode('.', $this->td);
            $td_normal = $year . '-' . $month . '-' . $day . ' 23:59:59';
            $query->andFilterWhere(['<=', 'td', $td_normal]);
        }

        return $dataProvider;
    }
}
