<?php

use yii\grid\GridView;
use yii\helpers\Html;

/** @var yii\data\ArrayDataProvider $dataProvider */
?>

<?= GridView::widget([
        'id' => 'file-grid',
        'dataProvider' => $dataProvider,
        'columns' => [
                ['class' => 'yii\grid\CheckboxColumn'],
                [
                        'attribute' => 'name',
                        'label' => 'Dump File',
                        'format' => 'raw',
                        'value' => fn($model) => Html::encode($model['name']),
                ],
                [
                        'attribute' => 'size',
                        'label' => 'Size (KB)',
                        'value' => fn($model) => round(((float)($model['size'] ?? 0)) / 1024, 2),
                ],
                [
                        'class' => 'yii\grid\ActionColumn',
                        'template' => '{delete}',
                        'buttons' => [
                                'delete' => fn($url, $model) =>
                                Html::a('<i class="bi bi-trash"></i> Delete', '#', [
                                        'class' => 'btn btn-danger btn-sm delete-dump',
                                        'data-file' => $model['name'],
                                ]),
                        ],
                ],
        ],
        'summary' => false,
        'options' => ['class' => 'table-responsive'],
]);
?>
