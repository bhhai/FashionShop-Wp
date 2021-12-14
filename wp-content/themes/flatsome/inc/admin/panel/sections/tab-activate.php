<?php
/**
 * Welcome screen getting started template
 */

?>
<div id="tab-activate" class="col cols panel flatsome-panel">
	<div class="inner-panel">
		<h3><?php esc_html_e( 'Theme registration', 'flatsome' ); ?></h3>
		<?php echo flatsome_envato()->admin->render_directory_warning(); ?>
		<?php echo flatsome_envato()->admin->render_registration_form(); ?>
	</div>
</div>
