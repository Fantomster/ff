<?php

namespace api_web\components\definitions;

/**
 * @SWG\Definition(type="object", @SWG\Xml(name="IntegrationProvider"))
 */
class IntegrationProvider
{
    /**
     * @SWG\Property(@SWG\Xml(name="service"), example="iiko")
     * @var string
     */
    public $service;

    /**
     * @SWG\Property(@SWG\Xml(name="image"), example="http://local/iiko.jpg")
     * @var string
     */
    public $image;

    /**
     * @SWG\Property(@SWG\Xml(name="not_formed"), example=4)
     * @var integer
     */
    public $not_formed;

    /**
     * @SWG\Property(@SWG\Xml(name="awaiting"), example=4)
     * @var integer
     */
    public $awaiting;

    /**
     * @SWG\Property(@SWG\Xml(name="license"), type="object", example={
        "iiko": {
            "status": "Активна",
            "from": "01.08.2017",
            "to": "31.01.2018",
            "number": "111222333"
        },
        "mixcart": {
            "status": "Активна",
            "from": "01.08.2017",
            "to": "31.01.2018",
            "number": "111222333"
        }
     })
     */
    public $license;

}