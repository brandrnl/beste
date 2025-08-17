//Prevent conflict with other libraries
jQuery.noConflict();

jQuery(document).ready(function(){
	jQuery('.o_form #order_submit_btn').click(function(){
		jQuery('form[name="OrderForm"]').submit();
		return false;	
	});
	
	jQuery('.o_form .wf_form_submit').click(function(){
		// Update action and post form
		jQuery('input[name="action"]').val('check_domain=' + jQuery('input[name="check_domain"]').val());
		jQuery('form[name="OrderForm"]').submit();	
	});
	
	jQuery('.o_form input[name="HostingType"]').change(function(){
		switch(jQuery(this).val()){
			case 'new':
				jQuery('.o_form #HostingTypeDiv_new').show();
				jQuery('.o_form #HostingTypeDiv_existing').hide();
				jQuery('.o_form #HostingTypeDiv_none').hide();
				break;
			case 'existing':
				jQuery('.o_form #HostingTypeDiv_new').hide();
				jQuery('.o_form #HostingTypeDiv_existing').show();
				jQuery('.o_form #HostingTypeDiv_none').hide();
				break;
			default: 
				jQuery('.o_form #HostingTypeDiv_new').hide();
				jQuery('.o_form #HostingTypeDiv_existing').hide();
				jQuery('.o_form #HostingTypeDiv_none').show();
				break;
		}
	});
	
	jQuery('.o_form input[name="DomainType"]').change(function(){
		switch(jQuery(this).val()){
			case 'new':
				jQuery('.o_form #DomainTypeDiv_new').show();
				jQuery('.o_form #DomainTypeDiv_existing').hide();
				
				// If there is a choice for default domain, show
				jQuery('.o_form .hosting_default_domain').show();
				break;
			case 'existing':
				jQuery('.o_form #DomainTypeDiv_new').hide();
				jQuery('.o_form #DomainTypeDiv_existing').show();
				
				// If there is a choice for default domain, hide
				jQuery('.o_form .hosting_default_domain').hide();
				break;
		}
	});
	
	jQuery('.package_box').click(function(){

		if(jQuery(this).find('input[type="radio"]').prop('checked')){
			// Already checked
			return true;
		}

        // Check radiobutton and call click event for package_box active selection
		jQuery(this).find('input[type="radio"]').prop('checked',true).click();
	
	});

	jQuery('input[name="Hosting"], input[name="HostingResponsive"]').click(function(){

        // Check if we are in a package_box
        if(jQuery(this).parents('.package_box').html() == undefined)
        {
            return;
        }

		// Remove all active class
		jQuery('.package_box').removeClass('active');

		// Check this one
		jQuery(this).parents('.package_box').addClass('active');

	});

	// Custom nameservers
	jQuery('.o_form input[name="CustomNameServers"]').change(function(){
		if(jQuery(this).prop('checked')){
			jQuery('#CustomNameServersDiv').show();
		}else{
			jQuery('#CustomNameServersDiv').hide();
		}
	});
	
	// Show price for select fields
	jQuery('select.show_amount_span').change(function(){
		showAmountSpan(jQuery(this).attr('name'));
	});
	jQuery('select.show_amount_span').each(function(){
		showAmountSpan(jQuery(this).attr('name'));
	});
	
	// Customer data
	jQuery('.o_form input[name="ExistingCustomer"]').change(function(){
		if(jQuery(this).prop('checked')){
			jQuery('#NewCustomerDiv').hide();
			jQuery('#ExistingCustomerDiv').show();
			
			// Hide custom invoice address by default
			jQuery('.o_form input[name="CustomInvoiceAddress"]').prop('checked',false).parents('label').hide();
			jQuery('#CustomInvoiceAddressDiv').hide();
		}else{
			jQuery('#NewCustomerDiv').show();
			jQuery('#ExistingCustomerDiv').hide();
			
			// Show option for custom invoice address
			jQuery('.o_form input[name="CustomInvoiceAddress"]').parents('label').show();
		}
	});
	
	jQuery('.CustomerForm input[name="CompanyName"]').keyup(function(){
		if(jQuery(this).val()){
			jQuery('#CustomerCompanyDiv').slideDown();
		}else{
			jQuery('#CustomerCompanyDiv').slideUp();
		}
	});
	
	jQuery('.o_form input[name="CustomInvoiceAddress"]').change(function(){
		if(jQuery(this).prop('checked')){
			jQuery('#CustomInvoiceAddressDiv').show();
		}else{
			jQuery('#CustomInvoiceAddressDiv').hide();
		}
	});
	
	jQuery('.o_form select[name="Country"], .o_form select[name="InvoiceCountry"], .CustomerForm select[name="WHOISCountry"]').change(function(){
		var PrefixName = jQuery(this).attr('name').replace('Country','');
		getCountryStates(jQuery(this).val(), PrefixName, jQuery('.o_form input[name="'+PrefixName+'State"]').val());
	});
	
	// Custom WHOIS
	jQuery('.o_form input[name="CustomWHOIS"]').change(function(){
		if(jQuery(this).prop('checked')){
			jQuery('#CustomWHOISDiv').show();
		}else{
			jQuery('#CustomWHOISDiv').hide();
		}
	});
	
	jQuery('.CustomerForm input[name="WHOISCompanyName"]').keyup(function(){
		if(jQuery(this).val()){
			jQuery('#WHOISCompanyDiv').slideDown();
		}else{
			jQuery('#WHOISCompanyDiv').slideUp();
		}
	});

    // If WHOISHandle select exists, get details from selected handle
    if(jQuery('.CustomerForm select[name="WHOISHandle"] option:checked').val() != undefined && jQuery('.CustomerForm select[name="WHOISHandle"] option:checked').val() != '')
    {
        getHandleDetails(jQuery('.CustomerForm select[name="WHOISHandle"] option:checked').val());
    }

    // On change of WHOISHandle, we want tho hide/show div and retrieve details from the handle
    jQuery('.CustomerForm select[name="WHOISHandle"]').change(function(){
        if(jQuery('.CustomerForm select[name="WHOISHandle"] option:checked').val() == '')
        {
            jQuery('#CustomWHOISDivNew').show();
            jQuery('#CustomWHOISDivExisting').html('').hide();
        }
        else
        {
            getHandleDetails(jQuery('.CustomerForm select[name="WHOISHandle"] option:checked').val());
        }
    });
	
	// Payment methods
	jQuery('.payment_box').click(function(){
		
		if(jQuery(this).find('input[name="PaymentMethod"]').prop('checked')){
			// Already checked
			return true;
		}
		
		jQuery(this).find('input[name="PaymentMethod"]').click();
	
	});
	
	jQuery('input[name="PaymentMethod"]').click(function(){
	
		// Remove all active class
		jQuery('.payment_box_details').hide();
		jQuery('.payment_box').removeClass('active');
		
		// Check this one
		jQuery(this).prop('checked', true);
		jQuery(this).parents('.payment_box').addClass('active');
		
		jQuery(this).parents('.payment_box').find('.payment_box_details').show();
		
	});
	
	jQuery('.check_existing_customer_link').click(function(){
		jQuery('input[name="action"]').val('check_existing_customer');
		jQuery('form[name="OrderForm"]').submit();	
	});
	
	// Discount coupon
	jQuery('.discount_coupon_add').click(function(){
		jQuery('#discountDiv').show();
		jQuery(this).hide();
	});
	jQuery('.discount_coupon_confirm').click(function(){
		jQuery('input[name=action]').val('discount'); 
		jQuery('form[name=OrderForm]').submit();
	});
	
	// WordPress iframe wrapper
	jQuery('body').click(function(){
		setTimeout(function(){ postIframeHeight(); }, 100);
	});
    jQuery(window).resize(function(){
        setTimeout(function(){ postIframeHeight(); }, 100);
    });

	// Onload
	setTimeout(function(){ postIframeHeight(); }, 100);
    // On load, send information to parent frame
    window.parent.postMessage("iframe_reload", "*");
});

function postIframeHeight()
{
    var frameHeight = Math.max(
        document.documentElement.scrollHeight,
        document.documentElement.offsetHeight
    );
    window.parent.postMessage("iframe_click_" + frameHeight, "*");
}

function removeDomainFromCart(domain)
{
	jQuery('input[name="action"]').val('remove_domain=' + domain);
	jQuery('form[name="OrderForm"]').submit();	
}

function showAmountSpan(SelectName)
{
	// Hide all spans for this select
	jQuery('span.amount_span_' + SelectName).hide();
	
	// Show correct span for this select
	jQuery('#amount_span_' + jQuery('select[name="'+SelectName+'"]').val().replace(/[^0-9a-z-_]/gi, '')).show();
}

function getCountryStates(countrycode, prefixName, value){
	// If no country is selected
	if(countrycode == '')
	{
		jQuery('input[name="'+prefixName+'State"]').show();
		jQuery('select[name="'+prefixName+'StateCode"]').val('').hide();
		return false;
	}
	
	jQuery.post("?", { step: "get_states", countrycode: countrycode},function(data){
		if(data.type == 'select')
		{
			jQuery('input[name="'+prefixName+'State"]').val('').hide();
			jQuery('select[name="'+prefixName+'StateCode"]').html(data.options).show();
			jQuery('select[name="'+prefixName+'StateCode"]').val(value);
		}	
		else
		{
			jQuery('input[name="'+prefixName+'State"]').val(value).show();
			jQuery('select[name="'+prefixName+'StateCode"]').html('').val('').hide();
		}
	},'json');
}

function getHandleDetails(handle_id)
{
    // Show divs
    jQuery('#CustomWHOISDivNew').hide();
    jQuery('#CustomWHOISDivExisting').show();

    jQuery.post("?", { step: "get_handledetail", handle_details: handle_id},function(data){
        jQuery('#CustomWHOISDivExisting').html(data);
    },'html');
}