<?php
error_reporting(E_PARSE & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);

// Start a session.
if(!isset($_SESSION))
{
    /**
     * If the WHOIS page or orderform is included on a different (sub)domain, it can be necessary to change the
     * samesite attribute of the session cookie. By changing the following value to true, new session cookies will be
     * available on other domains as well.
     * Notice: this will only work in newer browsers, when running on a secure connection (https://)
     *
     * @var boolean $orderform_should_support_crossdomain
     */
    $orderform_should_support_crossdomain = false;

    // set the session name, this way the sessions set are unique to the clientarea, prevents sharing sessions on the same domain with other applications
    // generate a suffix based on the database name
    include_once 'connect.php';
    $suffix = (defined('DB_NAME')) ? substr(md5(DB_NAME),0,8) : '';
    session_name('wfhc' . $suffix);

    // Secure session cookie (httponly & secure flags).
    $current_session_params = session_get_cookie_params();
    $http_only = true;
    $secure_flag =  ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443);
    $samesite = $orderform_should_support_crossdomain ? 'None' : 'Lax'; // None, Strict or Lax

    if(PHP_VERSION_ID < 70300) {
        session_set_cookie_params($current_session_params['lifetime'], $current_session_params['path'] .'; samesite='.$samesite, $current_session_params['domain'], $secure_flag, $http_only);
    } else {
        session_set_cookie_params([
                'lifetime' => $current_session_params['lifetime'],
                'path' => $current_session_params['path'],
                'domain' => $current_session_params['domain'],
                'secure' => $secure_flag,
                'httponly' => $http_only,
                'samesite' => $samesite
        ]);
    }

    session_start();
}

// Load files & classes
$autoload_path = array();
spl_autoload_register('hostfact_autoloader');
require_once "application/base_functions.php";

$settings = Setting_Model::getInstance();

// Check if we have a cart-ID, otherwise use default
if(isset($_POST['cart']) && $_POST['cart'])
{
	$order_form_id = $_POST['cart'];
}
elseif(isset($_GET['cart']) && $_GET['cart'])
{
	$order_form_id = $_GET['cart'];
}
elseif(isset($_SESSION['cart']) && $_SESSION['cart'])
{
	$order_form_id = $_SESSION['cart'];
}
else
{
	$order_form_id = $settings->get('DEFAULT_ORDERFORM');
}

// Get orderform settings
$orderform_settings = $settings->loadOrderForm($order_form_id);

if(!$orderform_settings)
{
	// Form does not exists or is inactive
	fatal_error('The order form is not enabled.','At this time you can not place an order via this order form.');
}
$_SESSION['cart'] = $order_form_id;

// when used as iframe in the client area, we set the language by URL
if(isset($_GET['lang']) && $_GET['lang'] && in_array($_GET['lang'], array('nl_NL', 'en_EN', 'de_DE', 'fr_FR')))
{
	define("LANG", $_GET['lang']);
	$_SESSION['OrderForm'.$orderform_settings->id]['orderform_lang'] = $_GET['lang'];
}
elseif(isset($_SESSION['OrderForm'.$orderform_settings->id]['orderform_lang']) && $_SESSION['OrderForm'.$orderform_settings->id]['orderform_lang'] && in_array($_SESSION['OrderForm'.$orderform_settings->id]['orderform_lang'], array('nl_NL', 'en_EN', 'de_DE', 'fr_FR')))
{
	define("LANG", $_SESSION['OrderForm'.$orderform_settings->id]['orderform_lang']);
}
else
{
	define("LANG", $orderform_settings->Language);
}

define("ORDERFORM_URL", $settings->get('ORDERFORM_URL'));
// URL for same domain, get url without http/https and domainname
$orderform_path = str_replace(array('https://','http://'),'',ORDERFORM_URL);
define("ORDERFORM_URL_SAME_DOMAIN", substr($orderform_path,strpos($orderform_path,'/')));
define("SHOW_VAT_INCLUDED", ($orderform_settings->VatCalcMethod == 'incl' || ($orderform_settings->VatCalcMethod == 'default' && VAT_CALC_METHOD == 'incl')) ? true : false); // true for amounts including VAT, false for amounts excluding VAT

// Load language file
if(@file_exists("includes/language/".LANG."/".LANG.".php"))
{
	require_once "includes/language/".LANG."/".LANG.".php";
}
else
{
	fatal_error('A file is missing', 'Can not find the necessary language file for the language "'.LANG.'"');
}

/**
 * hostfact_autoloader()
 * Loads correct files for classes
 * 
 * @param mixed $class
 * @return void
 */
function hostfact_autoloader($class)
{
	global $autoload_path;
	
	// Load application classes always first
	if(@file_exists("application/".strtolower($class).".php"))
	{
		require_once "application/".strtolower($class).".php";
		return true;
	}
	else
	{
		// Load model
		$paths_to_check = array_merge($autoload_path, array(''));
		foreach($paths_to_check as $path)
		{
			
			if(strpos($class, 'Controller') !== false && @file_exists($path."controllers/".strtolower($class).".php"))
			{
				require_once $path."controllers/".strtolower($class).".php";
				return true;
			}
			elseif(strpos($class, 'Model') !== false && @file_exists($path."models/".strtolower($class).".php"))
			{
				require_once $path."models/".strtolower($class).".php";
				return true;
			}
		}
		
		
	}
	
	fatal_error('A file is missing', 'Can not find the necessary file for class "'.$class.'".');
	return false;
}

/**
 * add_autoload_path()
 * 
 * @param mixed $path
 * @return void
 */
function add_autoload_path($path)
{
	global $autoload_path;
	$autoload_path[] = $path.'/';
}

/**
 * fatal_error()
 * End the script and trhows a nice looking error
 * 
 * @param mixed $title
 * @param mixed $msg
 * @return void
 */
function fatal_error($title, $msg)
{
	die('<html><head><title>Error</title><style type="text/css">body{margin:40px;font-family:Verdana;font-size:12px;color:#000;}#content{border:1px solid #999;background-color:#fff;padding: 25px;width:600px;position:absolute;left:50%;margin-left:-300px;}a{color:#000099;}h1{font-weight:normal;font-size:14px;color:#990000;margin:0 0 4px 0;}</style></head><body><div id="content"><h1>'.$title.'</h1>'.$msg.'</div></body></html>');
}


function show_amount_span($element, $select_name = false)
{
	global $_LANG;
	if($select_name)
	{
		?><span id="amount_span_<?php echo preg_replace('/[^0-9a-z-_]/i', '', $element->ProductCode); ?>" class="amount_span amount_span_<?php echo $select_name; ?>" style="display:none;"><?php echo w_money((SHOW_VAT_INCLUDED) ? $element->PriceIncl : $element->PriceExcl); ?> <?php if($element->PricePeriod){ echo w_period(1, $element->PricePeriod, 'per'); } ?></span><?php
	}
	else
	{
		?><span class="amount_span"><?php echo w_money((SHOW_VAT_INCLUDED) ? $element->PriceIncl : $element->PriceExcl); ?> <?php if($element->PricePeriod){ echo w_period(1, $element->PricePeriod, 'per'); } ?></span><?php
	} 
}

function w_money($amount, $currency_sign = 'both')
{
	switch($currency_sign)
	{
		case 'left': 	return CURRENCY_SIGN_LEFT.' '.number_format($amount,AMOUNT_DEC_PLACES,AMOUNT_DEC_SEPERATOR,AMOUNT_THOU_SEPERATOR); break;
		case 'right': 	return number_format($amount,AMOUNT_DEC_PLACES,AMOUNT_DEC_SEPERATOR,AMOUNT_THOU_SEPERATOR).' '.CURRENCY_SIGN_RIGHT; break;
		default: 		return CURRENCY_SIGN_LEFT.' '.number_format($amount,AMOUNT_DEC_PLACES,AMOUNT_DEC_SEPERATOR,AMOUNT_THOU_SEPERATOR).' '.CURRENCY_SIGN_RIGHT; break;
	}
}

function w_currency($position = 'left')
{
	if($position == 'right')
	{
		return (CURRENCY_SIGN_RIGHT) ? CURRENCY_SIGN_RIGHT: '&nbsp;';
	}
	else
	{
		return (CURRENCY_SIGN_LEFT) ? CURRENCY_SIGN_LEFT: '&nbsp;';
	}
}

function w_period($periods = 1, $periodic = false, $period_type = 'period'){
	global $_LANG;
	
	$string = '';
	
	if($period_type == 'per')
	{
		
		if(isset($_LANG['array_periods'][$periodic]) && $periodic)
		{
			$string .= $_LANG['per'].' ' . $_LANG['array_periods'][$periodic];
		}
	}
	else
	{
		if(!is_numeric($periods) && $periodic === false)
		{
			// Extract periodic
			preg_match_all('/[a-z]+/',$periods,$matches);
			$periodic = !empty($matches[0]) ? $matches[0][0] : '';
			
			// Extract periods
			preg_match_all('/[0-9]+/',$periods,$matches);
			$periods = !empty($matches[0]) ? $matches[0][0] : 1;
		}
		
		if(!$periodic)
		{
			$string .= $_LANG['array_periods'][''];
		}
		elseif($periods == 1)
		{
			$string .= (isset($_LANG['array_periods'][$periodic])) ? $periods . ' ' . $_LANG['array_periods'][$periodic] : $_LANG['array_periods'][''];
		}
		else
		{
			$string .= (isset($_LANG['array_periods_plural'][$periodic])) ? $periods . ' ' . $_LANG['array_periods_plural'][$periodic] : $_LANG['array_periods'][''];
		}
			
	}
	
	return $string;
}

function __($message, $namespace = 'hostfact')
{
	global $_LANG, $_plugin_language_array;
	
	if($namespace && $namespace != 'hostfact')
	{		
		return (isset($_plugin_language_array[$namespace][$message])) ? $_plugin_language_array[$namespace][$message] : '';
	}
	else
	{
		return (isset($_LANG[$message])) ? $_LANG[$message] : '';
	}
}

function print_r_pre($array){
	echo "<pre>";
	print_r($array);
	echo "</pre>";
}

function getFirstMailAddress($emailAddress)
{
	$emailAddress = check_email_address($emailAddress, 'convert');
	// Check if we have multiple email addresses
	if(strpos($emailAddress, ';'))
	{
		// If so, use the first
		$arrayEmailAddress = explode(';', $emailAddress);
		return $arrayEmailAddress[0];
	}
	else
	{
		return $emailAddress;
	}
	
}


function check_email_address($emailAddresses, $param = 'multiple', $formatTo = ';')
{
	$emailAddresses = htmlspecialchars_decode($emailAddresses);
	
	switch($param)
	{
		case 'single':
			return is_email($emailAddresses);
		break;
		case 'convert':
		case 'multiple':
			// Checks for contact data
			if(strlen($emailAddresses) > 0)
			{
				$formattedEmailAddresses = array();
				$emailAddresses = str_replace(',', ';', $emailAddresses);
					
				$arrayEmailAddress = array();
				$explodeEmail = explode(';',$emailAddresses);
				foreach($explodeEmail AS $emailAddress)
				{
					if(strlen($emailAddress) > 0 && is_email(trim($emailAddress)) === FALSE)
					{
						if($param == 'convert'){ continue; }
						return FALSE;
					}
					elseif(strlen($emailAddress) > 0 && $param == 'convert')
					{
						$formattedEmailAddresses[] = trim($emailAddress);
					}
				}
				
				if(!empty($formattedEmailAddresses))
				{
					return str_replace('&amp;','&',htmlspecialchars(implode($formatTo, $formattedEmailAddresses)));
				}
			}
			
			if($param == 'convert')
			{
				return '';
			}
			else
			{
				return TRUE;
			}
		break;
		
	}
}

function is_email($email){

  if (substr_count( $email, "@" ) > 1) {
    return false;
  }

	// old -- if(preg_match("/^[_a-z0-9-]+(\.[_a-z.0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,63})/", strtolower(trim(rtrim($email)))) == 0) {
    // any character allowed except /, \, space, @, <, >
    // /u at the end makes it UTF-8 compatible
    if(preg_match("/^[^\\\ \/@<>.]+(\.[^\\\ \/@<>.]+)*@[^\\\ \/@<>.]+(\.[^\\\ \/@<>.]+)*(\.[^\\\ \/@<>.]{2,63})$/u", strtolower(trim(rtrim($email)))) == 0) {
		return false;
    }                        
	return true;
}

function escapeArray($array){
		foreach($array as $k=>$v)
		{
			if(is_string($v))
			{
				$array[$k] = htmlspecialchars($v);
			}
		}
		return $array;
	}