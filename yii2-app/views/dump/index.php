<?php
use yii\helpers\Html;
use yii\web\View;

$this->title = 'SQL Dump Manager';
?>

<h1><?= Html::encode($this->title) ?></h1>

<div id="dump-manager">

    <h3>Available Dumps</h3>
    <form id="parse-form">
        <div id="file-list">
            <?= $this->render('_fileList', ['files' => $files]) ?>
        </div>
        <button type="submit" class="btn btn-primary mb-3">Parse & Export XML</button>
    </form>

    <div id="xml-result" class="alert alert-success d-none mt-2"></div>

    <hr>
    <h3>Upload New Dump</h3>
    <form id="upload-form" enctype="multipart/form-data">
        <input type="file" name="dumpFile" accept=".sql" required>
        <button type="submit" class="btn btn-success">Upload</button>
    </form>
</div>

<?php
$deleteUrl = \yii\helpers\Url::to(['dump/delete']);
$uploadUrl = \yii\helpers\Url::to(['dump/upload']);
$listUrl   = \yii\helpers\Url::to(['dump/list']);
$parseUrl  = \yii\helpers\Url::to(['dump/parse']);

$js = <<<JS
function refreshList() {
    $.get('$listUrl', function(data) {
        $('#file-list').html(data);
    });
}

// Upload file (AJAX)
$('#upload-form').on('submit', function(e) {
    e.preventDefault();
    var formData = new FormData(this);
    $.ajax({
        url: '$uploadUrl',
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function(resp) {
            if (resp.success) {
                refreshList();
            } else {
                alert(resp.error || 'Upload failed');
            }
        }
    });
});

// Delete file (AJAX)
$(document).on('click', '.btn-delete', function() {
    var name = $(this).data('name');
    if (!confirm('Delete ' + name + '?')) return;

    $.ajax({
        url: '$deleteUrl',
        type: 'POST',
        data: { name: name },
        success: function(resp) {
            if (resp.success) {
                refreshList();
            } else {
                alert(resp.error || 'Delete failed');
            }
        },
        error: function(xhr) {
            alert('HTTP ' + xhr.status + ': ' + xhr.statusText);
        }
    });
});


// Parse files to XML (AJAX)
$('#parse-form').on('submit', function(e) {
    e.preventDefault();
    var data = $(this).serialize();
    $.post('$parseUrl', data, function(resp) {
        if (resp.success) {
    $('#xml-result')
      .removeClass('d-none')
      .html('✅ XML generated (' + resp.count + ' posts): ' +
        '<a href="' + resp.xmlUrl + '" target="_blank">Download parsed_news.xml</a>');
        } else {
            alert(resp.error || 'Parsing failed');
        }
    });
});
JS;
$this->registerJs($js, View::POS_READY);
?>
