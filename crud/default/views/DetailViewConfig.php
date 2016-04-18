<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/**
 * @var yii\web\View $this
 * @var yii\gii\generators\crud\Generator $generator
 */

echo "<?php\n";
?>

use yii\helpers\Html;
use kartik\detail\DetailView;
use kartik\datecontrol\DateControl;

<?php
$model = new $generator->modelClass;

# Divide 2 column if fields grater than $divideGraterFields
$divideGraterFields = 9;
$attrSize = sizeof($model->attributes());
$column2Enable = $attrSize > $divideGraterFields;

$strTpl = [

    'column' => "
    [
        'columns' => [%s\n        ],
    ],",

    // Default No column
    'default' => "\n    '%s',",

    'defaultInCol' => "
            [
                'attribute' => '%s',
                'valueColOptions' => ['style' => 'width: %s'],
            ],",

    'displayOnly' => "
            [
                'attribute' => '%s',
                'valueColOptions' => ['style' => 'width: %s'],
                'displayOnly' => true,
            ],",

    'datetime' => "
            [
                'attribute' => '%s',
                'valueColOptions' => ['style' => 'width: %s'],
                'type' => DetailView::INPUT_WIDGET,
                'widgetOptions' => [
                    'class' => DateControl::classname(),
                    'type' => DateControl::FORMAT_%s
                ],
                'format' => [
                    '%s', (isset(Yii::\$app-> modules['datecontrol']['displaySettings']['%s']))
                        ? Yii::\$app->modules['datecontrol']['displaySettings']['%s']
                        : '%s'
                ],
                'displayOnly' => %s,
            ],",

    'enum' => "
            [
                'attribute' => '%s',
                'valueColOptions' => ['style' => 'width: %s'],
                'type' => DetailView::INPUT_DROPDOWN_LIST,
                'items' => [%s],
            ],",

    'image' => "
            [
                'attribute' => '%s',
                'valueColOptions' => ['style' => 'width: %s'],
                'format' => ['image', ['style' => 'max-width: 80px; max-height: 80px;']],
                'type' => DetailView::INPUT_FILEINPUT,
                'widgetOptions' => [
                    'pluginOptions' => [
                        'overwriteInitial' => false,
                        'initialPreview' => [
                            (!empty(\$model->%s)
                                ? Html::img(\$model->%s, ['class' => 'file-preview-image', 'alt' => \$model->%s])
                                : null
                            ),
                        ],
                        'allowedFileExtensions' => ['jpg', 'gif', 'png'],
                        'showUpload' => false,
                        'showCaption' => false,
                    ],
                    'options' => ['accept' => 'image/*'],
                ],
            ],",

];

echo '$detailViewConfig = [';

if (($tableSchema = $generator->getTableSchema()) === false) {
    foreach ($generator->getColumnNames() as $name) {
        echo sprintf($strTpl['default'], $name);
    }
} else {
    $count = 1;
    $col1 = '';
    foreach ($generator->getTableSchema()->columns as $column):
        $colWidth = '80%';
        if ($column2Enable) {
            $colWidth = ($count % 2 == 1 && $count == $attrSize) ? '80%' : '30%';
        }

        $strColumn = '';

        if (!(stripos($column->name, 'create_by') === false) || !(stripos($column->name, 'update_by') === false)) {
            $strColumn = sprintf($strTpl['displayOnly'], $column->name, $colWidth);
        } elseif (!(stripos($column->name, 'create_date') === false) || !(stripos($column->name, 'update_date') === false)) {
            $strColumn = sprintf(
                $strTpl['datetime'],
                $column->name, $colWidth, 'DATETIME', 'datetime', 'datetime', 'datetime', 'd-m-Y H:i:s A', 'true'
            );
        } elseif ($column->type === 'date') {
            $strColumn = sprintf(
                $strTpl['datetime'],
                $column->name, $colWidth, 'DATE', 'date', 'date', 'date', 'd-m-Y', 'false'
            );
        } elseif ($column->type === 'time') {
            $strColumn = sprintf(
                $strTpl['datetime'],
                $column->name, $colWidth, 'TIME', 'time', 'time', 'time', 'H:i:s A', 'false'
            );
        } elseif ($column->type === 'datetime' || $column->type === 'timestamp') {
            $strColumn = sprintf(
                $strTpl['datetime'],
                $column->name, $colWidth, 'DATETIME', 'datetime', 'datetime', 'datetime', 'd-m-Y H:i:s A', 'false'
            );
        } elseif (is_array($column->enumValues)) {
            $arrEnum = $model->getTableSchema()->columns[$column->name]->enumValues;

            $strTemp = '';
            foreach ($arrEnum as $key => $value) {
                $strTemp .= "'$value' => " . (is_numeric($value) ? $value : "'$value'") . ", ";
            }

            $strColumn = sprintf($strTpl['enum'], $column->name, $colWidth, $strTemp);
        } elseif (((stripos($column->name, '_id') === false) && (stripos($column->name, 'type') === false))
            && (!(stripos($column->name, 'image') === false) || !(stripos($column->name, 'file') === false))
        ) {
            $strColumn = sprintf(
                $strTpl['image'],
                $column->name, $colWidth, $column->name, $column->name, $column->name
            );
        } elseif ($column2Enable) {
            $strColumn = sprintf($strTpl['defaultInCol'], $column->name, $colWidth);
        }

        if ($column2Enable) {
            if ($count % 2 == 0 || $count == $attrSize) {
                $strColumn = $col1 . $strColumn;
                $col1 = '';

                echo sprintf($strTpl['column'], $strColumn);
            } else {
                $col1 = $strColumn;
            }
        } else {
            if (empty($strColumn)) {
                $format = $generator->generateColumnFormat($column);
                echo sprintf($strTpl['default'], $column->name . ($format === 'text' ? '' : ":$format"));
            } else {
                echo sprintf($strTpl['column'], $strColumn);
            }
        }

        $count++;
    endforeach;
}
?>

];
