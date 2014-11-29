<?php
/**
 * @link https://github.com/gromver/yii2-cmf.git#readme
 * @copyright Copyright (c) Gayazov Roman, 2014
 * @license https://github.com/gromver/yii2-grom/blob/master/LICENSE
 * @package yii2-cmf
 * @version 1.0.0
 */

namespace gromver\platform\frontend\widgets;

use gromver\platform\common\widgets\Widget;
use gromver\models\ObjectModel;
use yii\base\InvalidParamException;

/**
 * Class UserProfile
 * @package yii2-cmf
 * @author Gayazov Roman <gromver5@gmail.com>
 */
class UserProfile extends Widget {
    /**
     * @ignore
     */
    public $model;
    /**
     * @type list
     * @multiple
     * @items params
     * @empty All
     * @translation gromver.platform
     */
    public $params;

    public function init()
    {
        parent::init();

        if (!isset($this->model)) {
            try {
                $attributes = \Yii::$app->user->getParamsArray();
            } catch(InvalidParamException $e) {
                $attributes = [];
            }

            $this->model = self::model();
            $this->model->setAttributes($attributes);
        }

        $visibleParams = $this->getVisibleParams();
        foreach ($this->model->attributes() as $attribute) {
            if (!in_array($attribute, $visibleParams)) {
                $this->model->undefineAttribute($attribute);
            }
        }
    }

    protected function launch()
    {
        echo $this->render('user/profile', [
            'model' => $this->model,
        ]);
    }

    private static function model()
    {
        return new ObjectModel(\Yii::$app->getModule('grom/user')->userParamsClass);
    }

    public static function params()
    {
        return self::model()->attributeLabels();
    }

    public function getVisibleParams()
    {
        return is_array($this->params) && count($this->params) ? $this->params : $this->model->attributes();
    }
}