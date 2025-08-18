//Prevent conflict with other libraries
var whois_js_loaded = false;

jQuery(document).ready(function(){
	
	if(whois_js_loaded){
		return;
	}
	
	whois_js_loaded = true;
	
	jQuery('.w_form form').submit(function() {
		jQuery(this).find('.wf_form_submit').click();
		return false;	
	});
	
	jQuery('.w_form .wf_form_submit').click(function(){
		
		var whois_form_element = jQuery(this).parents('form');

		if(jQuery(whois_form_element).find('input[name="result"]').val() == 'extern'){
			document.location = jQuery(whois_form_element).find('input[name="newpage"]').val() + '?domain=' + encodeURIComponent(jQuery(whois_form_element).find('input[name="whois_domain"]').val());
		}else{

			jQuery.post(jQuery(whois_form_element).attr('action'), {whois_domain: jQuery(whois_form_element).find('input[name="whois_domain"]').val(), result_type: jQuery(whois_form_element).find('input[name="result"]').val() }, function(responseText, textStatus, XMLHttpRequest) {
	
				if(jQuery(whois_form_element).find('input[name="result"]').val() == 'inline'){
	
					jQuery('div.w_result').after(responseText).remove();
					
					// WordPress iframe wrapper
                    postIframeHeight();
				}else{
					document.location = jQuery(whois_form_element).find('input[name="newpage"]').val();
				}
				
			});		
		}
	});

    // On load, send information to parent frame
    window.parent.postMessage("iframe_reload", "*");
	
});
jQuery(document).ready(function(){
    // Haal de domeinnaam op uit de URL (queryparameter)
    var urlParams = new URLSearchParams(window.location.search);
    var domainParam = urlParams.get('domain'); // Haal de waarde van 'domain' op uit de URL
    
    // Als er een domeinnaam is opgegeven in de URL, stel deze in het inputveld in en sla op in localStorage
    if (domainParam) {
        jQuery('input[name="whois_domain"]').val(domainParam);
        localStorage.setItem('lastSearchedDomain', domainParam); // Sla de laatste domeinnaam op
        // Trigger direct een controle alsof de gebruiker het formulier heeft ingediend
        jQuery('.wf_form_submit').trigger('click');
    } else {
        // Als er geen domein in de URL staat, controleer localStorage
        var lastDomain = localStorage.getItem('lastSearchedDomain');
        if (lastDomain) {
            jQuery('input[name="whois_domain"]').val(lastDomain);
            // Trigger de controle voor de opgeslagen domeinnaam
            jQuery('.wf_form_submit').trigger('click');
        }
    }

    // Als een nieuwe zoekopdracht wordt ingediend, overschrijf de opgeslagen domeinnaam
    jQuery('.wf_form_submit').click(function() {
        var newDomain = jQuery('input[name="whois_domain"]').val();
        if (newDomain) {
            localStorage.setItem('lastSearchedDomain', newDomain); // Sla de nieuwe domeinnaam op
        }
    });

    // Verwijder de domeinnaam uit localStorage als het uit de winkelwagen wordt verwijderd
    jQuery('.remove_link').click(function() {
        localStorage.removeItem('lastSearchedDomain');
    });
});


function w_check_next_domain() {
    if (jQuery('.w_result table tr.domain_tr td.domain_td_unchecked:visible').length == 0) {
        return true;
    }

    var ToCheck = jQuery('.w_result table tr.domain_tr td.domain_td_unchecked:visible').first();
    var sld = jQuery('input[name="whois_domain"]').val();

    jQuery.post(jQuery(ToCheck).parents('form').attr('action'), { check_domain: jQuery(ToCheck).parent().attr('id').replace('domain_tr_', '').replace('_', '.') }, function (response, textStatus, XMLHttpRequest) {

        jQuery(ToCheck).removeClass('domain_td_unchecked');

        switch (response.status) {
            case 'available':
                jQuery(ToCheck).addClass('domain_td_checked_available');
                jQuery(ToCheck).parent().addClass('domain_td_checked_available');
                // Alleen tekst toevoegen, geen span
                jQuery(ToCheck).html(response.text);
                break;

            case 'unavailable':
                jQuery(ToCheck).addClass('domain_td_checked_unavailable');
                jQuery(ToCheck).parent().addClass('domain_td_checked_unavailable');
                // Alleen tekst toevoegen, geen span
                jQuery(ToCheck).html(response.text);
                break;

            case 'error':
                jQuery(ToCheck).addClass('domain_td_checked_error');
                jQuery(ToCheck).parent().addClass('domain_td_checked_unavailable');
                jQuery(ToCheck).html(response.text);
                break;
        }

        // Vervang de actie kolom inhoud
        var actionCell = jQuery(ToCheck).parent().find('.domain_td_order');
        actionCell.html(response.link);
        
        // Voor mobiel: verwijder de PNG afbeeldingen uit de links
        if (window.innerWidth <= 700) {
            actionCell.find('img').remove(); // Verwijder alle img tags
            actionCell.find('.order_link').html(''); // Maak de link leeg
            actionCell.find('.remove_link').html(''); // Maak de link leeg
        }
        
        jQuery(ToCheck).parent().find('.domain_td_order').find('a.order_link').bind('click', function (event) { w_order_domain(jQuery(this)); });
        jQuery(ToCheck).parent().find('.domain_td_order').find('a.remove_link').bind('click', function (event) { w_remove_domain(jQuery(this)); });

        if (jQuery('.w_result table tr.domain_tr td.domain_td_unchecked:visible').length > 0) {
            w_check_next_domain();
        }

        postIframeHeight();

    }, 'json');
}




function w_show_other_tlds(obj){
	jQuery('.show_other_tlds').hide();
	jQuery('.w_result table tbody.domain_tbody_other').show();
	w_check_next_domain();
}

function w_order_domain(obj){
	var ToOrder = jQuery(obj).parents('tr.domain_tr').attr('id').replace('domain_tr_','').replace('_','.');
	
	jQuery.post(jQuery(obj).parents('form').attr('action'), {order_domain: ToOrder}, function(response, textStatus, XMLHttpRequest) {
		
		// Update table
		jQuery(obj).parents('tr.domain_tr').find('.domain_td_order').html(response.ordered).find('a.remove_link').bind('click', function(event) { w_remove_domain(jQuery(this)); });
		
		// Update cart counter
		w_update_cart_count(response.count);
		
	}, 'json');
}

function w_remove_domain(obj){
	var ToOrder = jQuery(obj).parents('tr.domain_tr').attr('id').replace('domain_tr_','').replace('_','.');
	
	jQuery.post(jQuery(obj).parents('form').attr('action'), {remove_domain: ToOrder}, function(response, textStatus, XMLHttpRequest) {
		
		// Update table
		jQuery(obj).parents('tr.domain_tr').find('.domain_td_order').html(response.link).find('a.order_link').bind('click', function(event) { w_order_domain(jQuery(this)); });
		
		// Update cart counter
		w_update_cart_count(response.count);
		
	}, 'json');
}

function w_update_cart_count(count){
	// Update next-div
	jQuery('.goto_orderform').find('span.cart_count').html(count);
	if(count > 0){
		jQuery('.goto_orderform').show();
	}else{
		jQuery('.goto_orderform').hide();			
	}
}

function postIframeHeight()
{
    var frameHeight = Math.max(
        document.documentElement.scrollHeight,
        document.documentElement.offsetHeight
    );
    window.parent.postMessage("iframe_click_" + frameHeight, "*");
}
jQuery(document).ready(function() {
   
});
