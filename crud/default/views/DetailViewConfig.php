<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/**
 * @var yii\web\View $this
 * @var yii\gii\generators\crud\Generator $generator
 */

/**
 * Require CSS in layout main with code bellow
 *
 * if (in_array(yii::$app->controller->action->id, ['create','view']))
 *     $this->registerCssFile($directoryAsset . "/css/layouts/form-kartik-detailview.css");
 *
 * OR add
 *
 * .kv-col-label {
 *   width: 15%;
 *   text-align: right;
 *   vertical-align: middle;
 * }
 * .kv-col-value1 { width: 85%; }
 * .kv-col-value2 { width: 35%; }
 * .kv-attribute { word-break: break-word; }
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

$commonAttr = "'attribute' => '%s',
                'labelColOptions' => ['class' => 'kv-col-label'],
                'valueColOptions' => ['class' => '%s'],%s";

$strTpl = [

    'column' => "
    [
        'columns' => [%s\n        ],
    ],",

    # Default No column
    ## 'default' => "\n    'columnName:format',",
    // 'default' => "\n    '%s',",

    'defaultInCol' => "
            [
                %s
            ],",

    'displayOnly' => "
            [
                %s
                'displayOnly' => true,
            ],",

    'datetime' => "
            [
                %s
                'type' => DetailView::INPUT_WIDGET,
                'widgetOptions' => [
                    'class' => DateControl::classname(),
                    'type' => DateControl::FORMAT_%s
                ],
                'format' => [
                    '%s', isset(Yii::\$app-> modules['datecontrol']['displaySettings']['%s'])
                        ? Yii::\$app->modules['datecontrol']['displaySettings']['%s']
                        : '%s'
                ],
                'displayOnly' => %s,
            ],",

    'switch' => "
            [
                %s
                'type' => DetailView::INPUT_SWITCH,
                'format' => 'raw',
                'value' => \$model->%s
                    ? '<span class=\"label label-success\">%s</span>'
                    : '<span class=\"label label-danger\">%s</span>',
                'widgetOptions' => [
                    'pluginOptions' => [
                        'onText' => '%s',
                        'offText' => '%s',
                    ]
                ],
            ],",

    'dropdown' => "
            [
                %s
                'type' => DetailView::INPUT_DROPDOWN_LIST,
                'items' => [%s],
            ],",

    'image' => "
            [
                %s
                'type' => DetailView::INPUT_FILEINPUT,
                'format' => ['image', ['style' => 'max-height: 120px;']],
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

    'text' => "
            [
                %s
                'type' => DetailView::INPUT_TEXTAREA,
                'options' => ['rows' => 3],
                'format' => 'raw',
                'value' => '<span class=\"text-justify\">' . \$model->%s . '</span>',
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
        $colValueClass = '1';
        if ($column2Enable) {
            $colValueClass = ($count % 2 == 1 && $count == $attrSize) ? 'kv-col-value1' : 'kv-col-value2';
        }

        $strColumn = $defaultValue = '';

        if (!empty($column->defaultValue)) {
            $defaultValue = "\n                // Default value in DB is '" . $column->defaultValue . "'";
        }

        $commonValue = sprintf($commonAttr, $column->name, $colValueClass, $defaultValue);

        if (!(stripos($column->name, 'create_by') === false) || !(stripos($column->name, 'update_by') === false)) {
            $strColumn = sprintf($strTpl['displayOnly'], $commonValue);
        } elseif (!(stripos($column->name, 'create_date') === false) || !(stripos($column->name, 'update_date') === false)) {
            $strColumn = sprintf(
                $strTpl['datetime'],
                $commonValue, 'DATETIME', 'datetime', 'datetime', 'datetime', 'd-m-Y H:i:s A', 'true'
            );
        } elseif ($column->type === 'date') {
            $strColumn = sprintf(
                $strTpl['datetime'],
                $commonValue, 'DATE', 'date', 'date', 'date', 'd-m-Y', 'false'
            );
        } elseif ($column->type === 'time') {
            $strColumn = sprintf(
                $strTpl['datetime'],
                $commonValue, 'TIME', 'time', 'time', 'time', 'H:i:s A', 'false'
            );
        } elseif ($column->type === 'datetime' || $column->type === 'timestamp') {
            $strColumn = sprintf(
                $strTpl['datetime'],
                $commonValue, 'DATETIME', 'datetime', 'datetime', 'datetime', 'd-m-Y H:i:s A', 'false'
            );
        } elseif ($column->type === 'text') {
            $strColumn = sprintf(
                $strTpl['text'],
                $commonValue, $column->name
            );
        } elseif (is_array($column->enumValues)) {
            // if($column->name=='reviewer') {echo "<pre>";print_r($column);exit;}
            if (sizeof($column->enumValues) == 2) {
                $arrEnum = $model->getTableSchema()->columns[$column->name]->enumValues;

                $onText = $arrEnum[0];
                $offText = $arrEnum[1];

                $strColumn = sprintf($strTpl['switch'], $commonValue, $column->name, $onText, $offText, $onText, $offText);
            } else {
                $arrEnum = $model->getTableSchema()->columns[$column->name]->enumValues;

                $strTemp = ' ';
                foreach ($arrEnum as $value) {
                    $strTemp .= "'$value' => " . (is_numeric($value) ? $value : "'$value'") . ", ";
                }

                $strColumn = sprintf($strTpl['dropdown'], $commonValue, $strTemp);
            }
        } elseif (
            stripos($column->name, '_id') === false
            && stripos($column->name, 'type') === false
            && (
                !(stripos($column->name, 'pic') === false)
                || !(stripos($column->name, 'img') === false)
                || !(stripos($column->name, 'image') === false)
                || !(stripos($column->name, 'file') === false)
            )
        ) {
            $strColumn = sprintf(
                $strTpl['image'],
                $commonValue, $column->name, $column->name, $column->name
            );
        } else {
            $strColumn = sprintf($strTpl['defaultInCol'], $commonValue);
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
            echo sprintf($strTpl['column'], $strColumn);
        }

        $count++;
    endforeach;
}
?>

];
