<?php

// Set all settings to constants
define("COMPANY_NAME",				$settings->get('COMPANY_NAME'));
define("COMPANY_EMAIL",				$settings->get('COMPANY_EMAIL'));
define("COMPANY_AV_PDF",			$settings->get('COMPANY_AV_PDF'));

define("ORDERMAIL_SENT", 			$settings->get('ORDERMAIL_SENT')); // yes | no
define("ORDERMAIL_SENT_BCC", 		$settings->get('ORDERMAIL_SENT_BCC')); // Emailaddress for bcc

define("ORDERFORM_CSS_COLOR", 		$settings->get('ORDERFORM_CSS_COLOR'));
define("DOMAIN_AUTH_KEY_REQUIRED",	$settings->get('DOMAIN_AUTH_KEY_REQUIRED')); // yes | no


// Orderform specific settings
define("ORDERFORM_ID",				$orderform_settings->id);
define("ORDERFORM_TYPE", 			$orderform_settings->Type); // domain | hosting | other
define("SHOW_SELECT_PRICE",			$orderform_settings->ShowPrices); // yes | no
define("DISCOUNTCOUPON_VISIBLE",	$orderform_settings->ShowDiscountCoupon); // yes | no
define("PERIOD_CHOICE",				$orderform_settings->PeriodChoice); // yes | default | no
define("PERIOD_DEFAULT_PERIODS",	$orderform_settings->PeriodDefaultPeriods);
define("PERIOD_DEFAULT_PERIODIC",	$orderform_settings->PeriodDefaultPeriodic);

define('GROUP_PRODUCTS', 			$orderform_settings->ProductGroups->main); // for orderform_type other
define('GROUP_OPTIONS', 			$orderform_settings->ProductGroups->options);
define('GROUP_DOMAIN', 				$orderform_settings->ProductGroups->domain);  
define('GROUP_HOSTING', 			$orderform_settings->ProductGroups->hosting); 

define("HOSTING_AVAILABLE",				(isset($orderform_settings->OtherSettings->hosting->Available)) 	? $orderform_settings->OtherSettings->hosting->Available : 'no');
define("HOSTING_THEME", 				(isset($orderform_settings->OtherSettings->hosting->Theme)) 		? $orderform_settings->OtherSettings->hosting->Theme : 'simple'); // simple | packages | compare
define("HOSTING_DEFAULT_DOMAIN_CHOICE",	(isset($orderform_settings->OtherSettings->hosting->DefaultDomain)) ? $orderform_settings->OtherSettings->hosting->DefaultDomain : 'no'); // yes | no
define("HOSTING_DEFAULT_PACKAGE",		(isset($orderform_settings->OtherSettings->hosting->DefaultPackage)) ? $orderform_settings->OtherSettings->hosting->DefaultPackage : 0);

define("DOMAIN_AVAILABLE",				(isset($orderform_settings->OtherSettings->domain->Available)) 		? $orderform_settings->OtherSettings->domain->Available : 'no');
define("DOMAIN_CUSTOM_WHOIS",			(isset($orderform_settings->OtherSettings->domain->CustomWhois)) 	? $orderform_settings->OtherSettings->domain->CustomWhois : 'no'); // yes | no
define("DOMAIN_OWN_NAMESERVERS",		(isset($orderform_settings->OtherSettings->domain->OwnNameservers)) ? $orderform_settings->OtherSettings->domain->OwnNameservers : 'no'); // yes | no
define('POPULAR_LIST', 					isset($orderform_settings->OtherSettings->domain->PopularList) ? $orderform_settings->OtherSettings->domain->PopularList : '');  

if(PERIOD_CHOICE == 'yes')
{
	$period_choice_options = array();
	foreach($orderform_settings->PeriodChoiceOptions as $tmp_period){
		$period_choice_options[] = $tmp_period->Periods . $tmp_period->Periodic;
	} 
}

if(HOSTING_THEME == 'packages')
{
	define("PACKAGES_GRID",			(isset($orderform_settings->OtherSettings->hosting->PackagesGrid)) ? $orderform_settings->OtherSettings->hosting->PackagesGrid : 4); // 4 | 3 | 2 | 1
	
	$package_descriptions = array();
	
	if(isset($orderform_settings->OtherSettings->hosting->PackagesDescription)){ 
		foreach($orderform_settings->OtherSettings->hosting->PackagesDescription as $prod_id => $prod_desc){
			$package_descriptions[$prod_id] = $prod_desc;
		}
	}
}
elseif(HOSTING_THEME == 'compare')
{
	$compare_matrix = array();
	$compare_matrix['legend'] = $orderform_settings->OtherSettings->hosting->CompareLabels;

	if(isset($orderform_settings->OtherSettings->hosting->CompareValues)){ 
		foreach($orderform_settings->OtherSettings->hosting->CompareValues as $prod_id => $prod_values){
			$compare_matrix[$prod_id] = $prod_values;
		}
	}
}