jQuery(document).ready(function($) {
	$('#filter-level-submit').click(function(e) {
		e.preventDefault();
		$.ajax({
			type: "GET",
			// type: "POST",
			url: ajaxurl,
			// dataType: 'html',
			data: {
				'action' : 'filter_levels_request',
				'filter' : $('#this-pmpro-level').val(),
				'thispage' : pmpro_filter_object.pmpro_filter_page,
				'pmpro_object' : pmpro,
				'pmpro_filter_url' : pmpro_filter_object.pmpro_filter_ajaxurl,
				'pmpro_filter_nonce' : pmpro_filter_object.pmpro_filter_nonce,
			},
			success:function(data) {
				// user_table = data.substring(data.indexOf('<table'), data.indexOf('</table>') + 8);
				// $( '#list-table-replace table' ).html(user_table);
				$('#pmpro-level-return').html(data);
				console.log(data);
			},
			error: function(jqXHR, textStatus, errorThrown){
				console.log(errorThrown);
			}
		});  
	});      
});
