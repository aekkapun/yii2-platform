<?php
/**
 * @var $this yii\web\View
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $itemLayout string
 * @var $category null|\gromver\cmf\common\models\Category
 * @var $listViewOptions array
 */
use kartik\icons\Icon;

Icon::map($this, Icon::EL);
?>

<div class="row">
    <div class="col-sm-4 pull-right">
        <?= \gromver\cmf\frontend\widgets\Calendar::widget([
            'id' => 'news-calendar',
            'categoryId' => $category ? $category->id : null,
            'year' => date('Y'),
            'month' => date('m'),
            'day' => date('d'),
        ]) ?>

        <h3>Теги</h3>

        <?= \gromver\cmf\frontend\widgets\TagPostCloud::widget([
            'id' => 'posts-tags',
            'categoryId' => $category ? $category->id : null
        ]) ?>
    </div>
    <div class="col-sm-8">
        <?= \yii\helpers\Html::a(Icon::show('rss', [], Icon::EL), $category ? ['/cmf/news/post/rss', 'category_id' => $category->id] : ['/cmf/news/post/rss'], ['class' => 'btn btn-warning btn-xs pull-right']) ?>

        <?= \yii\widgets\ListView::widget(array_merge([
            'dataProvider' => $dataProvider,
            'itemView' => $itemLayout,
            'summary' => '',
            'viewParams' => [
                'postListWidget' => $this->context
            ]
        ], $listViewOptions)) ?>
    </div>
</div>
