<?php

namespace common\assets;

use Yii;
use yii\web\AssetBundle;

class GoogleApiAsset extends AssetBundle
{
    /**
     * Sets options for the google map
     * @var array
     */
    public $options = [];
    /**
     * @inheritdoc
     */
    public function init()
    {
	// BACKWARD COMPATIBILITY
	// To configure please, add `googleMapsApiKey` parameter to your application configuration
	// file with the value of your API key. To get yours, please visit https://code.google.com/apis/console/.
	$key = @Yii::$app->params['googleMapsApiKey'];
	// To configure please, add `googleMapsLibraries` parameter to your application configuration
	$libraries = @Yii::$app->params['googleMapsLibraries'];
	// To configure please, add `googleMapsLanguage` parameter to your application configuration
	$language = @Yii::$app->params['googleMapsLanguage'];
	$this->options = array_merge($this->options, array_filter([
            'key' => $key,
            'libraries' => $libraries,
            'language' => $language
	]));
	// BACKWARD COMPATIBILITY
        $this->js[] = 'https://maps.googleapis.com/maps/api/js?'. http_build_query($this->options);
    }
} 