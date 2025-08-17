<?php
define("ORDERFORM_ID",			$orderform_settings->id);
define("WHOIS_SHOWTLDS", 		isset($orderform_settings->OtherSettings->domain->ShowTLDs) ? $orderform_settings->OtherSettings->domain->ShowTLDs : 'all');
define("RESULT_URL",			(isset($orderform_settings->OtherSettings->domain->ResultURL) && $orderform_settings->OtherSettings->domain->ResultURL) ? $orderform_settings->OtherSettings->domain->ResultURL : '');
define("LINK_TO_ORDERFORM",		(isset($orderform_settings->OtherSettings->domain->OrderFormURL) && $orderform_settings->OtherSettings->domain->OrderFormURL) ? $orderform_settings->OtherSettings->domain->OrderFormURL : ORDERFORM_URL_SAME_DOMAIN);
define('GROUP_DOMAIN', 			$orderform_settings->ProductGroups->domain);  
define('POPULAR_LIST', 			isset($orderform_settings->OtherSettings->domain->PopularList) ? $orderform_settings->OtherSettings->domain->PopularList : '');