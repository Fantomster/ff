<?php

namespace api_web\classes;

use Yii;
use common\models\PromoAction;
use yii\web\BadRequestHttpException;

/**
 * Class PromoWebApi
 *
 * @package api_web\classes
 */
class PromoWebApi extends \api_web\components\WebApi
{

    /**
     * Отправка сообщения
     *
     * @param $post
     * @return array
     * @throws BadRequestHttpException|\Exception
     */
    public function send($post)
    {
        $this->validateRequest($post, ['lead_email', 'action_id']);

        $model = PromoAction::find()->where(['id' => $post['action_id']])->one();

        if (empty($model)) {
            throw new BadRequestHttpException('promo_action_not_found');
        }

        if (isset($post['promo_code'])) {
            if ($post['promo_code'] != $model->code) {
                throw new BadRequestHttpException('promo_action_code_error');
            }
        }

        $result = true;
        $mailer = Yii::$app->mailer;
        try {
            $mailer->compose()
                ->setTo($post['lead_email'])
                ->setSubject($model->title)
                ->setHtmlBody($model->getCompleteMessage($post))
                ->send();
        } catch (\Exception $e) {
            \Yii::error($e->getMessage());
            $result = false;
        }

return ['result' => $result];
}
}
