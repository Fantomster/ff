<?php

namespace api_web\components\definitions\egais\actWriteOn;

use api_web\components\ValidateRequest;

/**
 * @SWG\Definition(type="object")
 */
class ProductProducer extends ValidateRequest
{
    /**
     * @SWG\Property(@SWG\Xml(name="client_reg_id"), example="010000000467")
     * @var string
     */
    public $client_reg_id;

    /**
     * @SWG\Property(@SWG\Xml(name="inn"), example="5038002790")
     * @var string
     */
    public $inn;

    /**
     * @SWG\Property(@SWG\Xml(name="kpp"), example="503801001")
     * @var string
     */
    public $kpp;

    /**
     * @SWG\Property(@SWG\Xml(name="full_name"), example="Акционерное общество 'Ликеро-водочный завод 'Топаз'")
     * @var string
     */
    public $full_name;

    /**
     * @SWG\Property(@SWG\Xml(name="short_name"), example="АО 'ЛВЗ 'Топаз'")
     * @var string
     */
    public $short_name;

    /**
     * @SWG\Property(property="address", ref="ProducerAddress")
     * @var object
     */
    public $address;

    public function rules()
    {
        return [
          [['client_reg_id', 'inn', 'kpp', 'full_name', 'short_name', 'address'], 'required'],
          [['client_reg_id', 'inn', 'kpp', 'full_name', 'short_name'], 'string'],
          ['address', 'isInstanceOf', 'params' => ['class' => ProducerAddress::class]],
        ];
    }
}