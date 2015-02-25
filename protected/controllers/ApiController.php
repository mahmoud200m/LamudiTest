<?php
/**
 * ApiController 
 * 
 * @uses CController
 * @author Mahmoud Gamal
 * built on Yii official tutorial for REST API best practises
 *
 */
class ApiController extends CController
{
    /**
     * Key which has to be in HTTP EMAIL and PASSWORD headers 
     */
    Const APPLICATION_ID = 'LAMUDI';

    private $format = 'json';
    private $body_for_test = '';

    /**
     * @return array action filters
     */
    public function filters()
    {
        return array();
    }

    /* checks the user auth before any action
     * 
     * @access protected
     * @return boolean
	 */
    protected function beforeAction($event){
    	$this->_checkAuth();
    	return true;
    }

    /* Shows a single item
     * 
     * @access public
     * @return array
     */
    public function actionList()
    {
        switch($_GET['model'])
        {
            case 'properties':
                $models = Properties::model()->findAll();
                break;
            default:
                return $this->_sendResponse(501, sprintf('Error: Mode <b>list</b> is not implemented for model <b>%s</b>',$_GET['model']) );
        }
        if(is_null($models)) {
            return $this->_sendResponse(200, sprintf('No items where found for model <b>%s</b>', $_GET['model']) );
        } else {
            $rows = array();
            foreach($models as $model){
                $rows[] = $model->attributes;
            }

            return $this->_sendResponse(200, CJSON::encode($rows));
        }
    }

    /* Shows a single item
     * 
     * @access public
     * @return array
     */
    public function actionView()
    {
        // Check if id was submitted via GET
        if(!isset($_GET['id']))
            return $this->_sendResponse(500, 'Error: Parameter <b>id</b> is missing' );

        switch($_GET['model'])
        {
            // Find respective model    
            case 'properties':
                $model = Properties::model()->findByPk($_GET['id']);
                break;
            default:
                return $this->_sendResponse(501, sprintf('Mode <b>view</b> is not implemented for model <b>%s</b>',$_GET['model']) );
        }
        if(is_null($model)) {
            return $this->_sendResponse(404, 'No Item found with id '.$_GET['id']);
        } else {
            return $this->_sendResponse(200, $this->_getObjectEncoded($_GET['model'], $model->attributes));
        }
    }

    /**
     * Creates a new item
     * 
     * @access public
     * @return array
     */
    public function actionCreate()
    {
        switch($_GET['model'])
        {
            // Get an instance of the respective model
            case 'properties':
                $model = new Properties;                    
                break;
            default:
                return $this->_sendResponse(501, sprintf('Mode <b>create</b> is not implemented for model <b>%s</b>',$_GET['model']) );
        }
        // Try to assign POST values to attributes
        foreach($_POST as $var=>$value) {
            // Does the model have this attribute?
            if($model->hasAttribute($var)) {
                $model->$var = $value;
            } else {
                // No, raise an error
                return $this->_sendResponse(500, sprintf('Parameter <b>%s</b> is not allowed for model <b>%s</b>', $var, $_GET['model']) );
            }
        }
        // Try to save the model
        if($model->save()) {
            // Saving was OK
            return $this->_sendResponse(200, $this->_getObjectEncoded($_GET['model'], $model->attributes) );
        } else {
            // Errors occurred
            $msg = "<h1>Error</h1>";
            $msg .= sprintf("Couldn't create model <b>%s</b>", $_GET['model']);
            $msg .= "<ul>";
            foreach($model->errors as $attribute=>$attr_errors) {
                $msg .= "<li>Attribute: $attribute</li>";
                $msg .= "<ul>";
                foreach($attr_errors as $attr_error) {
                    $msg .= "<li>$attr_error</li>";
                }        
                $msg .= "</ul>";
            }
            $msg .= "</ul>";
            return $this->_sendResponse(500, $msg );
        }

        var_dump($_REQUEST);
    }    

    /**
     * Update a single iten
     * 
     * @access public
     * @return array
     */
    public function actionUpdate()
    {
        // Get PUT parameters
        parse_str(file_get_contents('php://input'), $put_vars);

        switch($_GET['model'])
        {
            // Find respective model
            case 'properties':
                $model = Properties::model()->findByPk($_GET['id']);                    
                break;
            default:
                return $this->_sendResponse(501, sprintf('Error: Mode <b>update</b> is not implemented for model <b>%s</b>',$_GET['model']) );
        }
        if(is_null($model))
            return $this->_sendResponse(400, sprintf("Error: Didn't find any model <b>%s</b> with ID <b>%s</b>.",$_GET['model'], $_GET['id']) );
        
        // Try to assign PUT parameters to attributes
        foreach($put_vars as $var=>$value) {
            // Does model have this attribute?
            if($model->hasAttribute($var)) {
                $model->$var = $value;
            } else {
                // No, raise error
                return $this->_sendResponse(500, sprintf('Parameter <b>%s</b> is not allowed for model <b>%s</b>', $var, $_GET['model']) );
            }
        }
        // Try to save the model
        if($model->save()) {
            return $this->_sendResponse(200, sprintf('The model <b>%s</b> with id <b>%s</b> has been updated.', $_GET['model'], $_GET['id']) );
        } else {
            $msg = "<h1>Error</h1>";
            $msg .= sprintf("Couldn't update model <b>%s</b>", $_GET['model']);
            $msg .= "<ul>";
            foreach($model->errors as $attribute=>$attr_errors) {
                $msg .= "<li>Attribute: $attribute</li>";
                $msg .= "<ul>";
                foreach($attr_errors as $attr_error) {
                    $msg .= "<li>$attr_error</li>";
                }        
                $msg .= "</ul>";
            }
            $msg .= "</ul>";
            return $this->_sendResponse(500, $msg );
        }
    }

    /**
     * Deletes a single item
     * 
     * @access public
     * @return array
     */
    public function actionDelete()
    {
        switch($_GET['model'])
        {
            // Load the respective model
            case 'properties':
                $model = Properties::model()->findByPk($_GET['id']);                    
                break;
            default:
                return $this->_sendResponse(501, sprintf('Error: Mode <b>delete</b> is not implemented for model <b>%s</b>',$_GET['model']) );
        }
        // Was a model found?
        if(is_null($model)) {
            // No, raise an error
            return $this->_sendResponse(400, sprintf("Error: Didn't find any model <b>%s</b> with ID <b>%s</b>.",$_GET['model'], $_GET['id']) );
        }

        // Delete the model
        $num = $model->delete();
        if($num>0)
            return $this->_sendResponse(200, sprintf("Model <b>%s</b> with ID <b>%s</b> has been deleted.",$_GET['model'], $_GET['id']) );
        else
            return $this->_sendResponse(500, sprintf("Error: Couldn't delete model <b>%s</b> with ID <b>%s</b>.",$_GET['model'], $_GET['id']) );
    }


    /**
     * Sends the API response 
     * 
     * @param int $status 
     * @param string $body 
     * @param string $content_type 
     * @access private
     * @return array
     */
    private function _sendResponse($status = 200, $body = '', $content_type = 'text/html')
    {
        try {
            $status_header = 'HTTP/1.1 ' . $status . ' ' . $this->_getStatusCodeMessage($status);
            // set the status
            header($status_header);
            // set the content type
            header('Content-type: ' . $content_type);
        } catch (Exception $e) {
            
        }
        
        // pages with body are easy
        if($body != '')
        {
            // send the body
            echo $body;
            return array('status'=>$status, 'body'=>$body);
        }
        // we need to create the body if none is passed
        else
        {
            // create some body messages
            $message = $this->_getStatusCodeMessage($status, 'long');

            // servers don't always have a signature turned on (this is an apache directive "ServerSignature On")
            $signature = ($_SERVER['SERVER_SIGNATURE'] == '') ? $_SERVER['SERVER_SOFTWARE'] . ' Server at ' . $_SERVER['SERVER_NAME'] . ' Port ' . $_SERVER['SERVER_PORT'] : $_SERVER['SERVER_SIGNATURE'];

            // this should be templatized in a real-world solution
            $body = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
                        <html>
                            <head>
                                <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
                                <title>' . $status . ' ' . $this->_getStatusCodeMessage($status) . '</title>
                            </head>
                            <body>
                                <h1>' . $this->_getStatusCodeMessage($status) . '</h1>
                                <p>' . $message . '</p>
                                <hr />
                                <address>' . $signature . '</address>
                            </body>
                        </html>';

            echo $body;
            return array('status'=>$status, 'body'=>$body);
        }
    }          

    /**
     * Gets the message for a status code
     * 
     * @param mixed $status 
     * @access private
     * @return string
     */
    private function _getStatusCodeMessage($status, $type = 'short')
    {
    	
        $codes = Array(
            200 => array('short'=>'OK', 'long'=>'OK'),
            400 => array('short'=>'Bad Request', 'long'=>'Bad Request.'),
            401 => array('short'=>'Unauthorized', 'long'=>'You must be authorized to view this page.'),
            404 => array('short'=>'Not Found', 'long'=>'The requested URL ' . isset($_SERVER['REQUEST_URI'])?$_SERVER['REQUEST_URI']:"" . ' was not found.'),
            500 => array('short'=>'Internal Server Error', 'long'=>'The server encountered an error processing your request.'),
            501 => array('short'=>'Not Implemented', 'long'=>'The requested method is not implemented.'),
        );

        return (isset($codes[$status][$type])) ? $codes[$status][$type] : '';
    }

    /**
     * Checks if a request is authorized
     * 
     * @access private
     * @return void
     */
    private function _checkAuth()
    {
    	$headers = apache_request_headers();
 
        // Check if we have the EMAIL and PASSWORD HTTP headers set?
        if(!(isset($headers['X_'.self::APPLICATION_ID.'_EMAIL']) and isset($headers['X_'.self::APPLICATION_ID.'_PASSWORD']))) {
            // Error: Unauthorized
            return $this->_sendResponse(401);
        }
        $email = $headers['X_'.self::APPLICATION_ID.'_EMAIL'];
        $password = $headers['X_'.self::APPLICATION_ID.'_PASSWORD'];
        // Find the user
        $user=Users::model()->find('LOWER(email)=?',array(strtolower($email)));
        if($user===null) {
            // Error: Unauthorized
            return $this->_sendResponse(401, 'Error: User Email is invalid');
        } else if(!$user->validatePassword($password)) {
            // Error: Unauthorized
            return $this->_sendResponse(401, 'Error: User Password is invalid');
        }
    }

    /**
     * Returns the json or xml encoded array
     * 
     * @param mixed $model 
     * @param mixed $array Data to be encoded
     * @access private
     * @return void
     */
    private function _getObjectEncoded($model, $array)
    {
        if(isset($_GET['format']))
            $this->format = $_GET['format'];

        if($this->format=='json')
        {
            return CJSON::encode($array);
        }
        elseif($this->format=='xml')
        {
            $result = '<?xml version="1.0">';
            $result .= "\n<$model>\n";
            foreach($array as $key=>$value){
                $result .= "    <$key>".utf8_encode($value)."</$key>\n"; 
            }
            $result .= '</'.$model.'>';
            return $result;
        }
        else
        {
            return;
        }
    }

}

?>
