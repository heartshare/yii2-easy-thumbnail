<?php

namespace sadovojav\image;

use Yii;
use yii\web\View;

/**
 * Class EasyThumbnail
 * @package sadovojav\image
 */
class EasyThumbnail extends \yii\base\Object
{
    /**
     * @var string
     */
    public $cachePath = '@webroot/assets/thumbnails';

    /**
     * @var string
     */
    public $basePath = '@webroot';

    /**
     * @var int
     */
    public $cacheExpire = 86400;

    /**
     * @var array
     */
    public $placeholder = [];

    /**
     * @var array
     */
    public $options = [];

    /**
     * @var array
     */
    private $default = [
        'placeholder' => [
            'type' => Thumbnail::PLACEHOLDER_TYPE_URL,
            'backgroundColor' => '#f5f5f5',
            'textColor' => '#cdcdcd',
            'text' => 'Ooops!'
        ],
        'options' => [
            'quality' => 75
        ]
    ];

    public function init()
    {
        $placeholder = array_merge($this->default['placeholder'], $this->placeholder);

        if ($placeholder['type'] == Thumbnail::PLACEHOLDER_TYPE_JS) {
            list(,$path) = \Yii::$app->assetManager->publish(__DIR__."/assets");

            Yii::$app->getView()->registerJsFile($path . '/js/holder.min.js', [
                'position' => View::POS_END
            ]);
        }

        Thumbnail::$cachePath = $this->cachePath;
        Thumbnail::$cacheExpire = YII_DEBUG ? 0 : $this->cacheExpire;
        Thumbnail::$basePath = $this->basePath;
        Thumbnail::$placeholder = $placeholder;
        Thumbnail::$options = array_merge($this->default['options'], $this->options);
    }
}
