<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model gromver\cmf\common\models\WidgetConfig */

$this->title = Yii::t('gromver.cmf', 'Create Widget Config');
$this->params['breadcrumbs'][] = ['label' => Yii::t('gromver.cmf', 'Widget Configs'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="widget-config-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
