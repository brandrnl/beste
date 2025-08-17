<?php
// Fix for IE-iframes
header('P3P: CP="CAO PSA OUR"');

// Start orderform
require_once "config.php";
require_once "orderform.config.php";

switch(ORDERFORM_TYPE){
	case 'domain':	
		$orderform = new DomainForm_Controller();
		$orderform->router();
		break;
	case 'hosting':	
		$orderform = new HostingForm_Controller();
		$orderform->router();
		break;
	case 'custom':
		$orderform = new $orderform_settings->OtherSettings->custom->ControllerName;
		$orderform->router();
		break;
	default:	
		// If we have a plugin with the same name, add plugin path to autoload
		if(file_exists('plugins/'.strtolower(ORDERFORM_TYPE)))
		{
			add_autoload_path('plugins/'.strtolower(ORDERFORM_TYPE));
			
			// Load language file
			$_plugin_language_array[strtolower(ORDERFORM_TYPE)] = array();
			if(file_exists('plugins/'.strtolower(ORDERFORM_TYPE).'/includes/language/'.LANG.'/'.LANG.'.php'))
			{
				$tmp_lang = $_LANG;
				$_LANG = array();
				include_once 'plugins/'.strtolower(ORDERFORM_TYPE).'/includes/language/'.LANG.'/'.LANG.'.php';
			
				$_plugin_language_array[strtolower(ORDERFORM_TYPE)] = $_LANG;	
				$_LANG = $tmp_lang;
			}
			
			$controller_name = ucfirst(ORDERFORM_TYPE).'_Controller'; 
		}
		else
		{
			$controller_name = 'OrderForm_Controller';
		}
		
		$orderform = new $controller_name;
		$orderform->router();
		break;
}