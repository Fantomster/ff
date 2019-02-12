<?php
/**
 * Created by PhpStorm.
 * User: Konstantin Silukov
 * Date: 2018-12-03
 * Time: 11:26
 */

namespace api_web\classes;

use api_web\components\Registry;
use api_web\components\WebApi;
use api_web\helpers\WebApiHelper;
use common\models\AllService;
use common\models\AllServiceOperation;
use common\models\Journal;
use common\models\Role;
use yii\data\ActiveDataProvider;
use yii\data\Pagination;

/**
 * Class JournalWebApi
 *
 * @package api_web\classes
 */
class JournalWebApi extends WebApi
{
    private $arAvailableFields = [
        'response',
        'created_at',
        'service_id',
        'type',
        'user_id',
    ];

    /**
     * @param $request
     * @return array
     * @throws \yii\base\InvalidArgumentException
     */
    public function list($request)
    {
        $page = $request['pagination']['page'] ?? 1;
        $pageSize = $request['pagination']['page_size'] ?? 12;
        $sort = $request['sort'] ?? null;

        $query = Journal::find();
        if (isset($request['search'])) {
            $search = $request['search'];
            if (isset($search['date']) && !empty($search['date'])) {
                $date = $search['date'];
                if (isset($date['start']) && !empty($date['start'])) {
                    $query->andWhere('created_at >= :date_from',
                        [':date_from' => date('Y-m-d H:i:s', strtotime($date['start'] . ' 00:00:00'))]);
                }
                if (isset($date['end']) && !empty($date['end'])) {
                    $query->andWhere('created_at <= :date_to',
                        [':date_to' => date('Y-m-d H:i:s', strtotime($date['end'] . ' 23:59:59'))]);
                }
            }
            if (isset($search['service_id']) && !empty($search['service_id'])) {
                $query->andWhere([Journal::tableName().'.service_id' => $search['service_id']]);
            }
            if (isset($search['type']) && !empty($search['type'])) {
                $query->andWhere(['type' => $search['type']]);
            }
        }

        if (isset($request['search']['user_id']) && !empty($request['search']['user_id'])) {
            $query->andWhere(['user_id' => $request['search']['user_id']]);
        } elseif (in_array($this->user->role_id, [Role::ROLE_RESTAURANT_MANAGER, Role::ROLE_RESTAURANT_EMPLOYEE])) {
            $query->andWhere(['organization_id' => $this->user->organization_id]);
        } else {
            $query->andWhere(['user_id' => $this->user->id]);
        }

        if ($sort && in_array(ltrim($sort, '-'), $this->arAvailableFields)) {
            $sortDirection = 'ASC';
            if (strpos('-', $sort) !== false) {
                $sortDirection = 'DESC';
            }
            $query->orderBy($sort . ' ' . $sortDirection);
        } else {
            $query->orderBy('id DESC');
        }

        $tableName = Journal::tableName();
        $query->select([$tableName.".id", $tableName.".service_id",
            Journal::tableName().".operation_code", $tableName.".user_id", $tableName.".organization_id",
            "IF ($tableName.service_id = ".Registry::MERC_SERVICE_ID.", ".AllServiceOperation::tableName().".comment, $tableName.response) as response",
            $tableName.".log_guide", $tableName.".type", $tableName.".created_at"]);
        $query->joinWith('operation');

        $dataProvider = new ActiveDataProvider([
            'query' => $query
        ]);

        $pagination = new Pagination();
        $pagination->setPage($page - 1);
        $pagination->setPageSize($pageSize);
        $dataProvider->setPagination($pagination);

        $result = [];
        /** @var Journal $model */
        foreach ($dataProvider->models as $model) {
            $model->created_at = WebApiHelper::asDatetime($model->created_at);
            $result[] = $model;
        }

        $return = [
            'result'     => $result,
            'pagination' => [
                'page'       => ($dataProvider->pagination->page + 1),
                'page_size'  => $dataProvider->pagination->pageSize,
                'total_page' => ceil($dataProvider->totalCount / $pageSize)
            ]
        ];

        return $return;
    }

}
