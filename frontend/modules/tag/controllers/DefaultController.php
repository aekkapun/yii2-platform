<?php
/**
 * @link https://github.com/gromver/yii2-cmf.git#readme
 * @copyright Copyright (c) Gayazov Roman, 2014
 * @license https://github.com/gromver/yii2-grom/blob/master/LICENSE
 * @package yii2-cmf
 * @version 1.0.0
 */

namespace gromver\platform\frontend\modules\tag\controllers;

use gromver\platform\common\models\Tag;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use Yii;

/**
 * Class DefaultController
 * @package yii2-cmf
 * @author Gayazov Roman <gromver5@gmail.com>
 */
class DefaultController extends Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->loadModel($id)
        ]);
    }

    public function actionPosts($id, $category_id = null)
    {
        return $this->render('posts', [
            'model' => $this->loadModel($id),
            'categoryId' => $category_id
        ]);
    }

    public function loadModel($id)
    {
        if(!($model = Tag::findOne($id))) {
            throw new NotFoundHttpException(Yii::t('gromver.platform', 'The requested tag does not exist..'));
        }

        return $model;
    }
}
