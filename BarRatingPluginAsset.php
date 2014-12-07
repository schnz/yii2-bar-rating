<?php

namespace coksnuss\widgets\barrating;

use yii\web\AssetBundle;

class BarRatingPluginAsset extends AssetBundle
{
    public $sourcePath = '@bower/jquery-bar-rating';
    public $js = [
        'jquery.barrating.js'
    ];
    public $depends = [
        'yii\web\JqueryAsset'
    ];
}
