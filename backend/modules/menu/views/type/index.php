<?php

use yii\helpers\Html;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel gromver\cmf\backend\modules\menu\models\MenuTypeSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('gromver.cmf', 'Menu Types');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="menu-index">

    <div class="page-header">
        <h1><?= Html::encode($this->title) ?></h1>
    </div>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?php /*<p>
        <?= Html::a(Yii::t('gromver.cmf', 'Create {modelClass}', [
    'modelClass' => 'Menu',
]), ['create'], ['class' => 'btn btn-success']) ?>
    </p>*/?>

    <?= GridView::widget([
        'id' => 'table-grid',
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'pjax' => true,
        'pjaxSettings' => [
            'neverTimeout' => true,
        ],
        'columns' => [
            ['class' => 'yii\grid\CheckboxColumn'],
            ['attribute' => 'id', 'width' => '60px'],
            [
                'attribute' => 'title',
                'format' => 'html'
            ],
            'alias',
            [
                'header' => 'Items',
                'value' => function($model) {
                        /** @var $model \gromver\cmf\common\models\MenuType */
                        return Html::a('('.$model->getItems()->count().')', ['item/index', 'MenuItemSearch[root]' => $model->id], ['data-pjax' => 0]);
                    },
                'format' => 'raw'
            ],
            [
                'class' => 'kartik\grid\ActionColumn',
                'deleteOptions' => ['data-method'=>'delete']
            ],
        ],
        'responsive' => true,
        'hover' => true,
        'condensed' => true,
        'floatHeader' => true,
        'bordered' => false,
        'panel' => [
            'heading' => '<h3 class="panel-title"><i class="glyphicon glyphicon-th-list"></i> ' . Html::encode($this->title) . ' </h3>',
            'type' => 'info',
            'before' => Html::a('<i class="glyphicon glyphicon-plus"></i> ' . Yii::t('gromver.cmf', 'Add'), ['create'], ['class' => 'btn btn-success', 'data-pjax' => '0']),
            'after' => Html::a('<i class="glyphicon glyphicon-repeat"></i> ' . Yii::t('gromver.cmf', 'Reset List'), ['index'], ['class' => 'btn btn-info']),
            'showFooter' => false
        ],
    ]) ?>

</div>