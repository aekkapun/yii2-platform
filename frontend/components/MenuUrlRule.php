<?php
/**
 * @link https://github.com/gromver/yii2-cmf.git#readme
 * @copyright Copyright (c) Gayazov Roman, 2014
 * @license https://github.com/gromver/yii2-grom/blob/master/LICENSE
 * @package yii2-cmf
 * @version 1.0.0
 */

namespace gromver\platform\frontend\components;


use yii\base\InvalidConfigException;
use yii\base\Object;

/**
 * Class MenuUrlRule
 * @package yii2-cmf
 * @author Gayazov Roman <gromver5@gmail.com>
 */
class MenuUrlRule extends Object {
    /**
     * @var string
     */
    public $handler;
    /**
     * @var string
     */
    public $router;

    public function init()
    {
        if (!isset($this->handler) || !is_string($this->handler)) {
            throw new InvalidConfigException(__CLASS__ . '::handler must be set.');
        }

        if (!isset($this->router) || !is_string($this->router)) {
            throw new InvalidConfigException(__CLASS__ . '::router must be set.');
        }
    }

    /**
    * /**
     * @param $requestInfo MenuRequest
     * @param $menuManager MenuManager
     * @return array|false
     */
    public function process($requestInfo, $menuManager)
    {
        return false;
    }
} 