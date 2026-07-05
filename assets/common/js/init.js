const spa_modal = ( show = true ) => {
	const modal = document.getElementById( 'smart-post-aggregator-modal' );
	if ( show ) {
		modal.style.display = '';
	} else {
		modal.style.display = 'none';
	}
}