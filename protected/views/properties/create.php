<?php
/* @var $this PropertiesController */
/* @var $model Properties */

$this->breadcrumbs=array(
	'Properties'=>array('index'),
	'Create',
);

$this->menu=array(
	array('label'=>'List Properties', 'url'=>array('index')),
	array('label'=>'Manage Properties', 'url'=>array('admin')),
);
?>

<h1>Create Properties</h1>

<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>