jQuery(document).ready(function(){  
    jQuery('#the-list').on('click', '.editinline', function(){  
		
		inlineEditPost.revert();

		var post_id = jQuery(this).closest('tr').attr('id');
		
		post_id = post_id.replace("post-", "");
		
		var $wp_predictive_search_inline_data = jQuery('#wp_predictive_search_inline_' + post_id );
		
		var predictive_search_focuskw 				= $wp_predictive_search_inline_data.find('.predictive_search_focuskw').text();
		var predictive_search_exclude_item 			= $wp_predictive_search_inline_data.find('.ps_exclude_item').text();
		
		jQuery('#wp-predictive-search-fields-quick textarea[name="_predictive_search_focuskw"]', '.inline-edit-row').text(predictive_search_focuskw);
		
		if (predictive_search_exclude_item=='yes') {
			jQuery('#wp-predictive-search-fields-quick input[name="ps_exclude_item"]', '.inline-edit-row').prop('checked', true); 
		} else {
			jQuery('#wp-predictive-search-fields-quick input[name="ps_exclude_item"]', '.inline-edit-row').prop('checked', false); 
		}
    });  
    
    jQuery('#wpbody').on('click', '#doaction, #doaction2', function(){  

		jQuery('select, input.text', '.inline-edit-row').val('');
		jQuery('select option', '.inline-edit-row').prop('checked', false);
		jQuery('#wp-predictive-search-fields-bulk .wp-predictive-search-keyword-value').hide();
		
	});
	
	 jQuery('#wpbody').on('change', '#wp-predictive-search-fields-bulk .change_to', function(){  
    
    	if (jQuery(this).val() > 0) {
    		jQuery(this).closest('div').find('.wp-predictive-search-keyword-value').show();
    	} else {
    		jQuery(this).closest('div').find('.wp-predictive-search-keyword-value').hide();
    	}
    
    });
});  