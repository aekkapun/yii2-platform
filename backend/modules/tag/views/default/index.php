<?php

use yii\helpers\Html;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel gromver\cmf\backend\modules\tag\models\TagSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('gromver.cmf', 'Tags');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="tag-index">

    <div class="page-header">
        <h1><?= Html::encode($this->title) ?></h1>
    </div>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?php /*<p>
        <?= Html::a(Yii::t('gromver.cmf', 'Create {modelClass}', [
    'modelClass' => 'Tag',
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

            ['attribute'=>'id', 'width'=>'50px'],
            [
                'attribute' => 'language',
                'value' => function($model) {
                        /** @var $model \gromver\cmf\common\models\Tag */
                        return \gromver\cmf\backend\widgets\Translator::widget(['model' => $model]);
                    },
                'format' => 'html',
                'filter' => Yii::$app->getLanguagesList()
            ],
            'title',
            'alias',
            [
                'attribute' => 'group',
                'filter' => \yii\helpers\ArrayHelper::map(\gromver\cmf\common\models\Tag::find()->groupBy('group')->andWhere('[[group]]!="" AND [[group]] IS NOT NULL')->all(), 'group', 'group')
            ],
            [
                'attribute' => 'status',
                'value' => function($model) {
                        /** @var $model \gromver\cmf\common\models\Tag */
                        return $model->status === \gromver\cmf\common\models\Tag::STATUS_PUBLISHED ? Html::a('<i class="glyphicon glyphicon-ok-circle"></i>', \yii\helpers\Url::to(['unpublish', 'id' => $model->id]), ['class' => 'btn btn-default btn-xs', 'data-pjax'=>'0', 'data-method'=>'post']) : Html::a('<i class="glyphicon glyphicon-remove-circle"></i>', \yii\helpers\Url::to(['publish', 'id' => $model->id]), ['class' => 'btn btn-default btn-xs', 'data-pjax' => '0', 'data-method' => 'post']);
                    },
                'filter' => \gromver\cmf\common\models\Tag::statusLabels(),
                'format' => 'raw',
                'width'=>'80px'
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
            'heading' => '<h3 class="panel-title"><i class="glyphicon glyphicon-th-list"></i> '.Html::encode($this->title).' </h3>',
            'type' => 'info',
            'before' => Html::a('<i class="glyphicon glyphicon-plus"></i> Add', ['create'], ['class' => 'btn btn-success', 'data-pjax' => 0]),
            'after' =>
                Html::a('<i class="glyphicon glyphicon-trash"></i> ' . Yii::t('gromver.cmf', 'Delete'), ['bulk-delete'], ['class' => 'btn btn-danger', 'data-pjax'=>'0', 'onclick'=>'processAction(this); return false']) . ' ' .
                Html::a('<i class="glyphicon glyphicon-repeat"></i> ' . Yii::t('gromver.cmf', 'Reset List'), ['index'], ['class' => 'btn btn-info']),
            'showFooter' => false
        ],
    ]) ?>
</div>

<script>
    function processAction(el) {
        var $el = $(el),
            $grid = $('#table-grid'),
            selection = $grid.yiiGridView('getSelectedRows')
        if(!selection.length) {
            alert(<?= json_encode(Yii::t('gromver.cmf', 'Select items.')) ?>)
            return
        }

        $.post($el.attr('href'), {data:selection}, function(response){
            $grid.yiiGridView('applyFilter')
        })
    }
</script>