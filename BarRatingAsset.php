<?php

namespace coksnuss\widgets\barrating;

use yii\web\AssetBundle;

class BarRatingAsset extends AssetBundle
{
    public $sourcePath = '@coksnuss/widgets/barrating/assets';
    public $css = [
        'css/rating-style.css'
    ];
    public $depends = [
        'coksnuss\widgets\barrating\BarRatingPluginAsset'
    ];
}
