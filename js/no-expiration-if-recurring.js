jQuery(document).ready(function($) {
	$('.expiration_info').hide();
	$('#expiration-label').css('color', '#aaa');
	$('#expiration').attr('disabled', true);
	// $('.trial_info').hide();
	$('#expiration').change(function() {
		if($(this).is(':checked')) {
			$('.expiration_info').show();
		} else {
			$('.expiration_info').hide();
		}
	});
	$('#recurring').change(function() {
		if($(this).is(':checked')) {
			$('#expiration').attr('disabled', this.checked).attr('checked', false);
			$('.expiration_info').hide();
			$('#expiration-label').css('color', '#aaa');
			$('.recurring_info').show();
			$('.trial_info').hide();
		} else {
			$('#expiration').attr('disabled', false);
			$('#expiration-label').css('color', 'black');
			$('.recurring_info').hide();
			// $('.expiration_info').show();
		} 
	});
	$('#custom_trial').change(function() {
		if ($('#custom_trial').is(':checked')) {
			$('.trial_info').show();
		} else {
			$('.trial_info').hide();
		}
	});
	// Return a helper with preserved width of cells
	// from http://www.foliotek.com/devblog/make-table-rows-sortable-using-jquery-ui-sortable/
	var fixHelper = function(e, ui) {
		ui.children().each(function() {
			$(this).width($(this).width());
		});
		return ui;
	};

	$("table.membership-levels tbody").sortable({
		helper: fixHelper,
		placeholder: 'testclass',
		forcePlaceholderSize: true,
		update: update_level_order
	});

	function update_level_order(event, ui) {
		level_order = [];
		$("table.membership-levels tbody tr").each(function() {
			$(this).removeClass('alternate');
			level_order.push(parseInt( $("td:first", this).text()));
		});

		//update styles
		$("table.membership-levels tbody tr:odd").each(function() {
			$(this).addClass('alternate');
		});

		data = {
			action: 'pmpro_update_level_order',
			level_order: level_order
		};

		$.post(ajaxurl, data, function(response) {
		});
	}
});