<?php

use gromver\cmf\frontend\modules\main\Module;
use gromver\widgets\ModalIFrame;
use yii\bootstrap\NavBar;
use yii\bootstrap\Nav;
use yii\bootstrap\Modal;
use yii\helpers\Html;

NavBar::begin([
    'brandLabel' => Yii::$app->cmf->siteName,
    'brandUrl' => Yii::$app->homeUrl,
    'options' => [
        'class' => 'navbar-inverse navbar-fixed-top',
    ],
]); ?>

<?= Html::beginForm(['/cmf/search/default/index'], 'get', ['class' => 'navbar-form navbar-left',  'role' => "search"]) ?>

<div class="input-group">
    <?= Html::textInput('q', null, ['class' => 'form-control', 'placeholder' => Yii::t('gromver.cmf', 'Search ...')]) ?>
    <span class="input-group-btn">
            <?= Html::submitButton('<i class="glyphicon glyphicon-search"></i>', ['class' => 'btn btn-default']) ?>
        </span>
</div>

<?= Html::endForm() ?>

<?php if (Yii::$app->user->can('administrate')) { ?>
    <div class="input-group navbar-left">
        <?= Html::tag('span', Yii::t('gromver.cmf', 'Editing mode'), ['class' => 'navbar-text']) . '&nbsp;' ?>
        <div class="btn-group">
            <?php if (Yii::$app->cmf->mode === Module::MODE_EDIT) {
                echo Html::button(Yii::t('gromver.cmf', 'On'), ['class'=>'btn btn-success navbar-btn btn-xs active']);
                echo Html::a(Yii::t('gromver.cmf', 'Off'), ['/cmf/default/mode', 'mode' => Module::MODE_VIEW, 'backUrl' => Yii::$app->request->getUrl()], ['class'=>'btn btn-default navbar-btn btn-xs']);
            } else {
                echo Html::a(Yii::t('gromver.cmf', 'On'), ['/cmf/default/mode', 'mode' => Module::MODE_EDIT, 'backUrl' => Yii::$app->request->getUrl()], ['class'=>'btn btn-default navbar-btn btn-xs']);
                echo Html::button(Yii::t('gromver.cmf', 'Off'), ['class'=>'btn btn-success navbar-btn btn-xs active']);
            } ?>
        </div>
    </div>
<?php }

if (Yii::$app->user->isGuest) { ?>
    <div class="input-group navbar-right">
        <?= Html::tag('span', Yii::t('gromver.cmf', 'Language'), ['class' => 'navbar-text']) . '&nbsp;' ?>
        <div class="btn-group">
            <?= implode('', array_map(function($language) {
                return Html::a($language, Yii::$app->urlManager->createUrl(Yii::$app->getHomeUrl(), $language), ['class' => 'btn navbar-btn btn-xs' . ($language === Yii::$app->language ? ' btn-primary active' : ' btn-default')]);
            }, Yii::$app->languages)) ?>
        </div>
    </div>
    <?php
    $loginUrl = Yii::$app->user->loginUrl;
    $loginUrl['modal'] = 1;

    echo ModalIFrame::widget([
        'buttonOptions' => [
            'tag' => 'div',
            'class' => 'navbar-text navbar-right'
        ],
        'modalOptions' => [
            'size' => Modal::SIZE_DEFAULT,
            'closeButton' => false
        ],
        'iframeOptions' => [
            'height' => '320px'
        ],
        'buttonContent' => '<i class="glyphicon glyphicon-log-in"></i>&nbsp;&nbsp;' . Html::a(Yii::t('gromver.cmf', 'Login'), $loginUrl, ['class' => 'navbar-link'])
    ]);
} else {
    $items = [];
    if(Yii::$app->user->can('administrate')) {
        $items[] = ['label' => '<i class="glyphicon glyphicon-cog"></i> ' . Yii::t('gromver.cmf', 'Admin Panel'), 'url' => Yii::$app->urlManagerBackend->createUrl('/')];
        /*$items[] = ModalIFrame::widget([
            'buttonOptions' => [
                'tag' => 'li'
            ],
            'modalOptions' => [
                'size' => Modal::SIZE_LARGE,
                'closeButton' => false
            ],
            'buttonContent' => Html::a('<i class="glyphicon glyphicon-pencil"></i> ' . Yii::t('gromver.cmf', 'Configuration'), ['/cmf/default/params', 'modal' => 1])
        ]);*/
        $items[] = ['label' => '<i class="glyphicon glyphicon-trash"></i> ' . Yii::t('gromver.cmf', 'Flush Cache'), 'url' => ['/cmf/default/flush-cache']];
        $items[] = '<li class="divider"></li>';
    }
    $items[] = ['label' => '<i class="glyphicon glyphicon-log-out"></i> ' . Yii::t('gromver.cmf', 'Logout'), 'url' => ['/cmf/auth/default/logout']]; ?>

    <div class="navbar-right">

        <?= Nav::widget([
            'options' => ['class' => 'navbar-nav navbar-left'],
            'items' => [
                [
                    'label' => '<i class="glyphicon glyphicon-user"></i> ' . Yii::$app->user->identity->username,
                    'items' => $items,
                ],
            ],
            'encodeLabels' => false
        ]) ?>

        <div class="input-group navbar-left">
            <?= Html::tag('span', Yii::t('gromver.cmf', 'Language'), ['class' => 'navbar-text']) . '&nbsp;' ?>
            <div class="btn-group">
                <?= implode('', array_map(function($language) {
                    return Html::a($language, Yii::$app->urlManager->createUrl(Yii::$app->getHomeUrl(), $language), ['class' => 'btn navbar-btn btn-xs' . ($language === Yii::$app->language ? ' btn-primary active' : ' btn-default')]);
                }, Yii::$app->languages)) ?>
            </div>
        </div>

    </div>
<?php } ?>

<?php NavBar::end() ?>
