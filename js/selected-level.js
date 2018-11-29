jQuery(document).ready(function($) {
	$('#dropdown-levels').change(function() {
		if ( '' !== this.value ) {
			$('#return-levels').html(' You selected Level ' + this.value);
		} else {
			$('#return-levels').html('You need to select a Level');
		}
	});
});
