<?php
// Start orderform
require_once "config.php";
require_once "whois.config.php";

/**
 * Route request to correct action of WHOIS controller.
 */
$whois = new Whois_Controller();

// Store type of whois form in object
if(isset($_GET['type']))
{
	$whois->setType($_GET['type']);	
}

// If JS loaded, display gives information about page to load
if(isset($_GET['display']))
{
	
	$whois->setJSLoaded(true);	
	
	switch($_GET['display'])
	{
		case 'form':
			$whois->getForm();
			exit;
			break;
		case 'results': 
			$whois->setType('inline');
			
			if(isset($_GET['form']) && $_GET['form'] == 'show')
			{
				$whois->set('hide_domain_form', false);
			}
			else
			{
				$whois->set('hide_domain_form', true);
			}
			
			$whois->getResultTable();
			exit;
			break;
	}
}

// catch AJAX calls
if(isset($_POST['check_domain']))
{
	echo $whois->ajaxCheckDomain($_POST['check_domain']);
	exit;
}
elseif(isset($_POST['whois_domain']))
{
	$whois->ajaxSaveDomain($_POST['whois_domain']);
	
	if(isset($_POST['result_type']) && $_POST['result_type'] == 'inline'){
		$whois->setType('inline');
		$whois->setIncludeWhoisHeader(false);
		$whois->set('hide_domain_form', true);
		$whois->getResultTable();
	}
	exit;

}
elseif(isset($_POST['order_domain']))
{
	$whois->ajaxOrderDomain($_POST['order_domain']);
}
elseif(isset($_POST['remove_domain']))
{
	$whois->ajaxRemoveDomain($_POST['remove_domain']);
}

// Catch post and get to WHOIS form
if(isset($_GET['domain']) && $_GET['domain'])
{
	$whois->ajaxSaveDomain($_GET['domain']);
}
elseif(isset($_POST['domain']) && $_POST['domain'])
{
	$whois->ajaxSaveDomain($_POST['domain']);
}

// Load standalone pages
if(!isset($_SESSION['whois_'.ORDERFORM_ID.'_domain']))
{
	// Load default form, including header and footer
	$whois->getForm();
}
else
{
	// Load result page
	$whois->getResultTable();
}

?>