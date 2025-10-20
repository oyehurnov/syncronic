<?php
use yii\helpers\Html;
/** @var array $files */
?>

<ul class="list-group mb-3">
    <?php foreach ($files as $file): ?>
        <li class="list-group-item">
            <div class="d-flex justify-content-between align-items-center">
                <label class="mb-0">
                    <?= Html::checkbox('files[]', false, [
                        'value' => $file,
                        'class' => 'file-checkbox',
                        'data-name' => $file
                    ]) ?>
                    <?= Html::encode($file) ?>
                </label>
                <button type="button"
                        class="btn btn-sm btn-danger btn-delete"
                        data-name="<?= Html::encode($file) ?>">
                    Delete
                </button>
            </div>
            <div class="preview-area small text-muted mt-1"
                 id="preview-<?= Html::encode($file) ?>"></div>
        </li>
    <?php endforeach; ?>
</ul>

<?php if (empty($files)): ?>
    <p><em>No SQL dump files found.</em></p>
<?php endif; ?>
