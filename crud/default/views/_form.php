<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;


/**
 * @var yii\web\View $this
 * @var yii\gii\generators\crud\Generator $generator
 */

/** @var \yii\db\ActiveRecord $model */
$model = new $generator->modelClass;
$safeAttributes = $model->safeAttributes();
if (empty($safeAttributes)) {
    $safeAttributes = $model->attributes();
}

# Divide 2 column if fields grater than $divideGraterFields
$divideGraterFields = 9;
$attrSize = sizeof($safeAttributes);
$column2Enable = $attrSize > $divideGraterFields;

echo "<?php\n";

?>

use yii\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\builder\Form;
use kartik\datecontrol\DateControl;

/**
 * @var yii\web\View $this
 * @var <?= ltrim($generator->modelClass, '\\') ?> $model
 * @var yii\widgets\ActiveForm $form
 */
?>

<div class="box box-primary <?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-form">

  <?= "<?php " ?>$form = ActiveForm::begin(['type' => ActiveForm::TYPE_<?php echo $column2Enable ? 'VERTICAL' : 'HORIZONTAL'; ?>]); ?>

    <div class="box-header with-border">
      <h3 class="box-title">
        <?= "<?php echo " ?>$model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update');?>
        <?php echo Inflector::pluralize(Inflector::camel2words(StringHelper::basename($generator->modelClass))) . "\n"; ?>
      </h3>

      <div class="box-tools pull-right">
        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
      </div>
    </div>
    <!-- /.box-header -->
    <div class="box-body">

      <?= "<?php" ?>

        echo Form::widget([

            'model' => $model,
            'form' => $form,
            'columns' => <?php echo $column2Enable ? 2 : 1; ?>,
            'attributes' => [

<?php foreach ($safeAttributes as $attribute): ?>
                <?= $generator->generateActiveField($attribute) . "\n\n"; ?>
<?php endforeach; ?>
            ]

        ]);

      ?>

    </div>
    <!-- /.box-body -->
    <div class="box-footer">
      <div class="row">
        <div class="col-md-4 col-md-push-8">
          <?= "<?php" ?>

            echo Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'),
                ['class' => ($model->isNewRecord ? 'btn btn-success' : 'btn btn-primary') . ' pull-right']
            );
          ?>
        </div>
      </div>
    </div>

  <?= "<?php" ?> ActiveForm::end(); ?>

</div>
<!-- /.box -->