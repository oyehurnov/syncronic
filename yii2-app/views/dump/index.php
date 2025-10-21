<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use yii\helpers\Url;

$this->title = 'SQL Dump Parser Control Panel';
?>

<div class="dump-index">
    <h1><?= Html::encode($this->title) ?></h1>

    <!-- Upload Form -->
    <div class="card mb-4 p-3 border rounded bg-light">
        <h4>Upload New SQL Dump</h4>
        <?php $form = ActiveForm::begin([
                'id' => 'upload-form',
                'options' => ['enctype' => 'multipart/form-data'],
        ]); ?>

        <?= Html::fileInput('file', null, [
                'id' => 'upload-input',
                'class' => 'form-control mb-2'
        ]) ?>
        <?= Html::button('Upload', [
                'class' => 'btn btn-success',
                'id' => 'upload-btn'
        ]) ?>

        <?php ActiveForm::end(); ?>
    </div>

    <!-- File List -->
    <?php Pjax::begin(['id' => 'file-list-pjax', 'timeout' => 10000]); ?>
    <div class="file-list-section">
        <?= $this->render('_fileList', ['dataProvider' => $dataProvider]); ?>
    </div>
    <?php Pjax::end(); ?>

    <!-- Action Buttons -->
    <div class="mt-4">
        <?= Html::button('Parse Selected Dumps', [
                'class' => 'btn btn-primary',
                'id' => 'parse-button'
        ]) ?>

        <?= Html::a('Download parsed_news.xml', ['dump/download-xml'], [
                'class' => 'btn btn-info ms-3',
                'id' => 'download-xml',
                'target' => '_blank'
        ]) ?>
    </div>
</div>

<?php
$parseUrl   = Url::to(['dump/parse']);
$deleteUrl  = Url::to(['dump/delete']);
$uploadUrl  = Url::to(['dump/upload']);

$js = <<<JS
// === AJAX file upload (FormData) ===
$('#upload-btn').on('click', function(e) {
    e.preventDefault();
    var fileInput = $('#upload-input')[0];
    if (!fileInput.files.length) {
        alert('Please select a file to upload.');
        return;
    }

    var formData = new FormData();
    formData.append('file', fileInput.files[0]); // must match UploadedFile::getInstanceByName('file')

    $.ajax({
        url: '$uploadUrl',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                alert('File uploaded successfully.');
                $.pjax.reload({container:'#file-list-pjax'});
                $('#upload-input').val(''); // reset input
            } else {
                alert('Upload failed: ' + (response.error || 'Unknown error'));
            }
        },
        error: function(xhr) {
            alert('Server error: ' + xhr.statusText);
        }
    });
});

// === Handle Parse button ===
$('#parse-button').on('click', function() {
    let selected = $('#file-grid').yiiGridView('getSelectedRows');
    if (!selected.length) {
        alert('Please select at least one dump file to parse.');
        return;
    }
    $.post('$parseUrl', { files: selected }, function(response) {
        if (response.success) {
            alert('Parsing complete. XML generated.');
            $.pjax.reload({container:'#file-list-pjax'});
        } else {
            alert('Parsing failed: ' + (response.error || 'Unknown error'));
        }
    });
});

// === Handle file delete action ===
$(document).on('click', '.delete-dump', function(e) {
    e.preventDefault();
    if (!confirm('Delete this dump file?')) return;
    const file = $(this).data('file');
    $.post('$deleteUrl', { name: file }, function(response) {
        $.pjax.reload({container:'#file-list-pjax'});
    });
});
JS;

$this->registerJs($js);
?>
