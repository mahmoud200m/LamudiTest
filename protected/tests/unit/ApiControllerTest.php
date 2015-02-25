<?php

class ApiControllerTest extends CDbTestCase
{
	public function setUp() 
	{ 
        Yii::import('application.controllers.*');
		$this->api = new ApiController('api');
	} 

	public function tearDown() 
	{ 
        $_GET     = array();
        $_POST    = array();
		unset($this->api); 
	}

	public function testActionList()
	{                
		$controller = $this->api;
	    $this->assertTrue($controller!=null);
	    $this->assertInstanceOf('ApiController', $controller);
        
	    //wrong model check
        $_GET = array('model'=>'any_model_name');
	    $result = $controller->actionList();
		$this->assertEquals($result['status'], '501');

        //output check
        $_GET = array('model'=>'properties');
	    $result = $controller->actionList();

	    $models = Properties::model()->findAll();
		$rows = array();
		foreach($models as $model){
			$rows[] = $model->attributes;
		}
		$correct_result = CJSON::encode($rows);
		$this->assertEquals($result['status'], '200');
		$this->assertEquals($result['body'], $correct_result);
	}

	public function testActionView()
	{                
		$controller = $this->api;
	    $this->assertTrue($controller!=null);
	    $this->assertInstanceOf('ApiController', $controller);
		
		//no id check
        $_GET = array('model'=>'properties');
    	$result = $controller->actionView();
		$this->assertEquals($result['status'], '500');  

	    //wrong model check
        $_GET = array('model'=>'any_model_name', 'id'=>'1');
    	$result = $controller->actionView();
		$this->assertEquals($result['status'], '501'); 

		//invalide id check
        $_GET = array('model'=>'properties', 'id'=>'-1');
    	$result = $controller->actionView();
		$this->assertEquals($result['status'], '404');      

        //output check
        $_GET = array('model'=>'properties', 'id'=>'1');
	    $result = $controller->actionView();

        $model = Properties::model()->findByPk($_GET['id']);
		$correct_result = CJSON::encode($model->attributes);
		$this->assertEquals($result['status'], '200');
		$this->assertEquals($result['body'], $correct_result);
	}

	public function testActionCreate()
	{                
		$controller = $this->api;
	    $this->assertTrue($controller!=null);
	    $this->assertInstanceOf('ApiController', $controller);
		
	    //wrong model check
        $_GET = array('model'=>'any_model_name');
    	$result = $controller->actionCreate();
		$this->assertEquals($result['status'], '501'); 

		//wrong parameter check
		$_GET = array('model'=>'properties');
		$_POST = array('any_parameter'=>'any value');
    	$result = $controller->actionCreate();
		$this->assertEquals($result['status'], '500'); 

		//invalid parameter value
		$_GET = array('model'=>'properties');
		$_POST = array('title'=>'any value', 'price'=>'any value');
    	$result = $controller->actionCreate();
		$this->assertEquals($result['status'], '500'); 
	}


	public function testActionUpdate()
	{                
		$controller = $this->api;
	    $this->assertTrue($controller!=null);
	    $this->assertInstanceOf('ApiController', $controller);
		
	    //wrong model check
        $_GET = array('model'=>'any_model_name');
    	$result = $controller->actionUpdate();
		$this->assertEquals($result['status'], '501'); 

		//invalide id check
        $_GET = array('model'=>'properties', 'id'=>'-1');
    	$result = $controller->actionUpdate();
		$this->assertEquals($result['status'], '400');     
	}

	public function testActionDelete()
	{                
		$controller = $this->api;
	    $this->assertTrue($controller!=null);
	    $this->assertInstanceOf('ApiController', $controller);
		
	    //wrong model check
        $_GET = array('model'=>'any_model_name');
    	$result = $controller->actionDelete();
		$this->assertEquals($result['status'], '501'); 

		//invalide id check
        $_GET = array('model'=>'properties', 'id'=>'-1');
    	$result = $controller->actionDelete();
		$this->assertEquals($result['status'], '400');     
	}
}
