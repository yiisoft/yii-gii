<?php
declare(strict_types=1);

/**
 * @var Yiisoft\Yii\Gii\Generator\CRUD\Command $command
 * @var Yiisoft\ActiveRecord\ActiveRecord $model
 * @var string $action the action ID
 */

use Yiisoft\Strings\StringHelper;
use Yiisoft\Yii\DataView\Field\DataField;

$uses = [
    \Yiisoft\View\WebView::class,
    \Yiisoft\Yii\DataView\DetailView::class,
];
$columns = [
    'SerialColumn::create()',
];
foreach ($model->getTableSchema()->getColumns() as $columnSchema) {
    switch ($columnSchema->getPhpType()) {
        default:
            $uses[] = DataField::class;
            $columns[] = sprintf(
                "DataField::create()->attribute('%s')",
                $columnSchema->getName(),
            );
    }
}

?>
<?= "<?php\n"; ?>
declare(strict_types=1);

<?php foreach (array_unique($uses) as $use) {
    echo sprintf('use %s;' . PHP_EOL, $use);
} ?>

/**
* @var WebView $this
* @var <?= $model::class ?> $model
*/
<?= '?>' ?>

<h1><?= StringHelper::baseName($model::class) . '/' . $command->getControllerID() ?></h1>

<?= '<?=' ?> DetailView::widget()
    ->fields(
        <?= implode(
            ',' . PHP_EOL . "\t\t",
            array_map(
                fn (string $column) => $column,
                $columns
            )
        ) ?>
    )
    ->data($model)
    ->render()
