<?php
/**
 * @link https://github.com/gromver/yii2-cmf.git#readme
 * @copyright Copyright (c) Gayazov Roman, 2014
 * @license https://github.com/gromver/yii2-cmf/blob/master/LICENSE
 * @package yii2-cmf
 * @version 1.0.0
 */

namespace gromver\cmf\backend\modules\menu\controllers;

use kartik\widgets\Alert;
use gromver\modulequery\ModuleQuery;
use Yii;
use gromver\cmf\common\models\MenuItem;
use gromver\cmf\backend\modules\menu\models\MenuItemSearch;
use yii\db\ActiveRecord;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * Class ItemController implements the CRUD actions for Menu model.
 * @package yii2-cmf
 * @author Gayazov Roman <gromver5@gmail.com>
 */

class ItemController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post', 'delete'],
                    'bulk-delete' => ['post'],
                    'publish' => ['post'],
                    'unpublish' => ['post'],
                    'status' => ['post'],
                    'type-items' => ['post'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['create', 'update', 'ordering', 'publish', 'unpublish', 'status', 'routers'],
                        'roles' => ['update'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['delete', 'bulk-delete'],
                        'roles' => ['delete'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['index', 'view', 'type-items', 'select'],
                        'roles' => ['read'],
                    ],
                ]
            ]
        ];
    }

    /**
     * Lists all MenuItem models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new MenuItemSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Lists all MenuItem models with linkType == LINK_ROUTE.
     * @return mixed
     */
    public function actionSelect()
    {
        $searchModel = new MenuItemSearch();
        $params = Yii::$app->request->getQueryParams();
        $params['MenuItemSearch']['link_type'] = MenuItem::LINK_ROUTE;
        $dataProvider = $searchModel->search($params);

        Yii::$app->cmf->layout = 'modal';

        return $this->render('select', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    /**
     * Lists all routers.
     * @return mixed
     */
    public function actionRouters()
    {
        Yii::$app->cmf->layout = 'modal';

        $items = ModuleQuery::instance()->implement('\gromver\cmf\backend\interfaces\MenuRouterInterface')->orderBy('desktopOrder')->execute('getMenuRoutes');

        return $this->render('routers', [
                'items' => $items
            ]);
    }

    /**
     * Displays a single MenuItem model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new MenuItem model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @param null $menu_type_id
     * @param null $sourceId
     * @param null $language
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionCreate($menu_type_id = null, $sourceId = null, $language = null)
    {
        $model = new MenuItem();
        $model->loadDefaultValues();
        $model->status = MenuItem::STATUS_PUBLISHED;
        $model->language = Yii::$app->language;

        if (isset($menu_type_id)) $model->menu_type_id = $menu_type_id;

        if (isset($sourceId) && $language) {
            $sourceModel = $this->findModel($sourceId);
            /** @var MenuItem $parentItem */
            // если локализуемый пункт меню имеет родителя, то пытаемся найти релевантную локализацию для родителя создаваемого пункта меню
            if (!($sourceModel->level > 2 && $parentItem = @$sourceModel->parent->translations[$language])) {
                $parentItem = MenuItem::find()->roots()->one();
            }

            $model->language = $language;
            $model->parent_id = $parentItem->id;
            $model->menu_type_id = $sourceModel->menu_type_id;
            $model->translation_id = $sourceModel->translation_id;
            $model->alias = $sourceModel->alias;
            $model->status = $sourceModel->status;
            $model->link = $sourceModel->link;
            $model->link_type = $sourceModel->link_type;
            $model->ordering = $sourceModel->ordering;
            $model->layout_path = $sourceModel->layout_path;
            $model->access_rule = $sourceModel->access_rule;
            $model->link_params = $sourceModel->link_params;
        } else {
            $sourceModel = null;
        }

        $linkParamsModel = $model->getLinkParamsModel();

        if ($model->load(Yii::$app->request->post()) && $linkParamsModel->load(Yii::$app->request->post()) && $model->validate() && $linkParamsModel->validate()) {
            $model->setLinkParams($linkParamsModel->toArray());
            $model->save(false);

            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                    'model' => $model,
                    'linkParamsModel' => $linkParamsModel,
                    'sourceModel' => $sourceModel
                ]);
        }
    }

    /**
     * Updates an existing MenuItem model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $linkParamsModel = $model->getLinkParamsModel();

        if ($model->load(Yii::$app->request->post()) && $linkParamsModel->load(Yii::$app->request->post()) && $model->validate() && $linkParamsModel->validate()) {
            $model->setLinkParams($linkParamsModel->toArray());
            $model->save(false);

            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                    'model' => $model,
                    'linkParamsModel' => $linkParamsModel,
                ]);
        }
    }

    /**
     * Deletes an existing MenuItem model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        if ($model->descendants()->count()) {
            Yii::$app->session->setFlash(Alert::TYPE_DANGER, Yii::t('gromver.cmf', "It's impossible to remove menu item ID:{id} so far it contains descendants.", ['id' => $model->id]));
         } else {
            $model->deleteNode();
        }

        if (Yii::$app->request->getIsDelete()) {
            return $this->redirect(ArrayHelper::getValue(Yii::$app->request, 'referrer', ['index']));
        }

        return $this->redirect(['index']);
    }

    public function actionBulkDelete()
    {
        $data = Yii::$app->request->getBodyParam('data', []);

        $models = MenuItem::findAll(['id' => $data]);

        foreach ($models as $model) {
            /** @var MenuItem $model */
            if ($model->descendants()->count()) continue;

            if(!$model->getIsDeletedRecord()) $model->deleteNode();
        }

        if (!Yii::$app->request->getIsAjax())
            return $this->redirect(ArrayHelper::getValue(Yii::$app->request, 'referrer', ['index']));
    }

    public function actionOrdering()
    {
        $data = Yii::$app->request->getBodyParam('data', []);
        //$roots = [];

        foreach ($data as $id => $order) {
            if ($target = MenuItem::findOne($id)) {
                $target->updateAttributes(['ordering' => intval($order)]);
            }
        }

        MenuItem::find()->roots()->one()->reorderNode('ordering');
        (new MenuItem())->trigger(ActiveRecord::EVENT_AFTER_UPDATE);    //фиксируем изменение таблицы в \gromver\cmf\common\models\Table

        return $this->redirect(ArrayHelper::getValue(Yii::$app->request, 'referrer', ['index']));
    }

    public function actionPublish($id)
    {
        $model = $this->findModel($id);

        $model->status = MenuItem::STATUS_PUBLISHED;
        $model->save();

        return $this->redirect(ArrayHelper::getValue(Yii::$app->request, 'referrer', ['index']));
    }

    public function actionUnpublish($id)
    {
        $model = $this->findModel($id);

        $model->status = MenuItem::STATUS_UNPUBLISHED;
        $model->save();

        return $this->redirect(ArrayHelper::getValue(Yii::$app->request, 'referrer', ['index']));
    }

    public function actionStatus($id, $status)
    {
        $model = $this->findModel($id);

        $model->status = $status;
        if (!$model->save()) {
            Yii::$app->session->setFlash(Alert::TYPE_DANGER, $model->getFirstError('status'));
        }

        return $this->redirect(ArrayHelper::getValue(Yii::$app->request, 'referrer', ['index']));
    }

    public function actionTypeItems($update_item_id = null, $selected = '')
    {
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $typeId = $parents[0];
                $language = $parents[1];
                //исключаем редактируемый пункт и его подпункты из списка
                if (!empty($update_item_id) && $updateItem = MenuItem::findOne($update_item_id)) {
                    $excludeIds = array_merge([$update_item_id], $updateItem->descendants()->select('id')->column());
                    //если выбранный тип меню совпадает с типом меню редактируемого пункта, выбираем текущее значение родительского элемента
                    $selected = $updateItem->menu_type_id == $typeId ? $updateItem->parent_id : '';
                } else {
                    $excludeIds = [];
                }

                $out = array_map(function($value) {
                    return [
                        'id' => $value['id'],
                        'name' => str_repeat(" • ", $value['level'] - 1) . $value['title']
                    ];
                }, MenuItem::find()->noRoots()->type($typeId)->language($language)->orderBy('lft')->andWhere(['not in', 'id', $excludeIds])->asArray()->all());
                /** @var MenuItem $root */
                $root = MenuItem::find()->roots()->one();
                array_unshift($out, [
                    'id' => $root->id,
                    'name' => Yii::t('gromver.cmf', 'Root')
                ]);

                echo Json::encode(['output' => $out, 'selected' => $selected ? $selected : $root->id]);
                return;
            }
        }
        echo Json::encode(['output' => '', 'selected' => $selected]);
    }

    /**
     * Finds the MenuItem model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return MenuItem the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = MenuItem::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('gromver.cmf', 'The requested page does not exist.'));
        }
    }
}
