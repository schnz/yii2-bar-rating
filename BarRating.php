<?php

namespace coksnuss\widgets\barrating;

use yii\widgets\InputWidget;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\JsExpression;
use yii\web\View;

class BarRating extends InputWidget
{
    /**
     * The name of the jQuery plugin to use for this widget.
     */
    const PLUGIN_NAME = 'barrating';

    /**
     * Available styles
     */
    const STYLE_VERTICAL_BAR   = 'vbar';
    const STYLE_HORIZONTAL_BAR = 'hbar';
    const STYLE_LINE           = 'line';
    const STYLE_BOX            = 'box';
    const STYLE_BOX_ALT        = 'box-alt';
    const STYLE_STAR           = 'star';

    /**
     * @var string The CSS style to apply to this widget. Use one of the constants defined in this class.
     * Set this to null if you want to apply your own style.
     */
    public $style = 'vbar';
    /**
     * @var integer The key value pairs of the rating plugin.
     */
    public $items = [1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5];
    /**
     * @var string Pass an option value to specify initial rating. If null, the plugin will try to set the
     * initial rating by finding an option with a `selected` attribute.
     */
    public $initialRating;
    /**
     * @var boolean If set to true, rating values will be displayed on the bars. Defaults to false.
     */
    public $showValues;
    /**
     * @var boolean If set to true, user selected rating will be displayed next to the widget.
     * Defaults to true
     */
    public $showSelectedRating;
    /**
     * @var boolean If set to true, the ratings will be reversed. Defaults to false.
     */
    public $reverse;
    /**
     * @var array the JQuery plugin options for the input mask plugin.
     * @see https://github.com/antennaio/jquery-bar-rating
     */
    public $clientOptions = [];
    /**
     * @var array
     */
    public $options = ['prompt' => ''];
    /**
     * @var array The options to apply to the wrapper div tag
     */
    public $wrapperOptions = [];
    /**
     * @var array A list of default options to be applied for a specific style. Keys represent the style
     * names and the key-value pairs define the default options to be applied.
     */
    public $defaults = [
        'line'    => ['showValues' => true, 'showSelectedRating' => false],
        'box'     => ['showValues' => true, 'showSelectedRating' => false],
        'box-alt' => ['showValues' => true, 'showSelectedRating' => false],
        'star'    => ['showSelectedRating' => false],
    ];

    /**
     * @var string the hashed variable to store the clientOptions
     */
    protected $_hashVar;


    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (isset($this->style)) {
            if (isset($this->defaults[$this->style])) {
                foreach ($this->defaults[$this->style] as $prop => $val) {
                    if (!isset($this->$prop)) {
                        $this->$prop = $val;
                    }
                }
            }

            Html::addCssClass($this->wrapperOptions, 'rating-style-' . $this->style);
        }
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        echo Html::beginTag('div', $this->wrapperOptions);
        if ($this->hasModel()) {
            echo Html::activeDropDownList($this->model, $this->attribute, $this->items, $this->options);
        } else {
            echo Html::dropDownList($this->name, $this->value, $this->items, $this->options);
        }
        echo Html::endTag('div', $this->wrapperOptions);

        $this->registerClientScript();
    }

    /**
     * Generates a hashed variable to store the plugin `clientOptions`. Helps in reusing the variable for similar
     * options passed for other widgets on the same page. The following special data attributes will also be
     * setup for the input widget, that can be accessed through javascript:
     *
     * - 'data-plugin-options' will store the hashed variable storing the plugin options.
     * - 'data-plugin-name' the name of the plugin
     *
     * @param View $view the view instance
     * @author [Thiago Talma](https://github.com/thiagotalma)
     */
    protected function hashClientOptions($view)
    {
        $encOptions = empty($this->clientOptions) ? '{}' : Json::encode($this->clientOptions);
        $this->_hashVar = self::PLUGIN_NAME . '_' . hash('crc32', $encOptions);
        $this->options['data-plugin-name'] = self::PLUGIN_NAME;
        $this->options['data-plugin-options'] = $this->_hashVar;
        $view->registerJs("var {$this->_hashVar} = {$encOptions};\n", View::POS_HEAD);
    }

    /**
     * Initializes client options
     */
    protected function initClientOptions()
    {
        $options = $this->clientOptions;
        foreach ($options as $key => $value) {
            if (in_array($key, ['onSelect', 'onClear', 'onDestroy']) && !$value instanceof JsExpression
            ) {
                $options[$key] = new JsExpression($value);
            }
        }
        $this->clientOptions = $options;
    }

    /**
     * Registers the needed client script and options.
     */
    public function registerClientScript()
    {
        $js = '';
        $view = $this->getView();
        $this->initClientOptions();
        if (isset($this->initialRating)) {
            $this->clientOptions['initialRating'] = intval($this->initialRating);
        }
        if (isset($this->showValues)) {
            $this->clientOptions['showValues'] = (bool) $this->showValues;
        }
        if (isset($this->showSelectedRating)) {
            $this->clientOptions['showSelectedRating'] = (bool) $this->showSelectedRating;
        }
        if (isset($this->reverse)) {
            $this->clientOptions['reverse'] = (bool) $this->reverse;
        }
        $this->hashClientOptions($view);

        $id = $this->options['id'];
        $js .= '$("#' . $id . '").' . self::PLUGIN_NAME . "(" . $this->_hashVar . ");\n";
        BarRatingAsset::register($view);
        $view->registerJs($js);
    }
}
