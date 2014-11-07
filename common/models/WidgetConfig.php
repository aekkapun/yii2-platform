<?php
/**
 * @link https://github.com/gromver/yii2-cmf.git#readme
 * @copyright Copyright (c) Gayazov Roman, 2014
 * @license https://github.com/gromver/yii2-cmf/blob/master/LICENSE
 * @package yii2-cmf
 * @version 1.0.0
 */

namespace gromver\cmf\common\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\Json;

/**
 * This is the model class for table "cms_widget_config".
 * @package yii2-cmf
 * @author Gayazov Roman <gromver5@gmail.com>
 *
 * @property integer $id
 * @property string $widget_id
 * @property string $widget_class
 * @property string $language
 * @property string $context
 * @property string $url
 * @property string $params
 * @property integer $valid
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $created_by
 * @property integer $updated_by
 * @property string $lock
 */
class WidgetConfig extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%cms_widget_config}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['widget_id', 'widget_class'], 'required'],
            [['params'], 'string'],
            [['valid', 'created_at', 'updated_at', 'created_by', 'updated_by', 'lock'], 'integer'],
            [['widget_id'], 'string', 'max' => 50],
            [['widget_class'], 'string', 'max' => 255],
            [['context', 'url'], 'string', 'max' => 1024],
            [['language'], 'default', 'value' => function () {
                return Yii::$app->language;
            }],
            [['language'], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('gromver.cmf', 'ID'),
            'widget_id' => Yii::t('gromver.cmf', 'Widget ID'),
            'widget_class' => Yii::t('gromver.cmf', 'Widget Class'),
            'language' => Yii::t('gromver.cmf', 'Language'),
            'context' => Yii::t('gromver.cmf', 'Context'),
            'url' => Yii::t('gromver.cmf', 'Url'),
            'params' => Yii::t('gromver.cmf', 'Params'),
            'valid' => Yii::t('gromver.cmf', 'Valid'),
            'created_at' => Yii::t('gromver.cmf', 'Created At'),
            'updated_at' => Yii::t('gromver.cmf', 'Updated At'),
            'created_by' => Yii::t('gromver.cmf', 'Created By'),
            'updated_by' => Yii::t('gromver.cmf', 'Updated By'),
            'lock' => Yii::t('gromver.cmf', 'Lock'),
        ];
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
            BlameableBehavior::className(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getParamsArray()
    {
        return Json::decode($this->params);
    }

    public function setParamsArray($value)
    {
        $this->params = Json::encode($value);
    }

}
