<?php

add_action( 'admin_notices', 'flatsome_maintenance_admin_notice' );

function flatsome_maintenance_admin_notice() {
	$screen       = get_current_screen();
	$advanced_url = get_admin_url() . 'admin.php?page=optionsframework&tab=';
	$errors       = flatsome_envato()->registration->get_errors();

	if ( get_theme_mod( 'maintenance_mode', 0 ) && get_theme_mod( 'maintenance_mode_admin_notice', 1 ) ) {
		?>
		<div class="notice notice-info">
				<p><?php echo sprintf( __( 'Flatsome Maintenance Mode is <strong>active</strong>. Please don\'t forget to <a href="%s">deactivate</a> it as soon as you are done.', 'flatsome-admin' ), $advanced_url . 'of-option-maintenancemode' ); ?></p>
		</div>
		<?php
	}

	if ( in_array( $screen->id, array( 'update-core', 'update-core-network' ), true ) && ! flatsome_envato()->registration->is_registered() ) {
		?>
		<div class="updated"><p><?php echo sprintf( __( '<a href="%s">Please enter your purchase code</a> to activate Flatsome and get one-click updates.', 'flatsome' ), esc_url_raw( network_admin_url( 'admin.php?page=flatsome-panel' ) ) ); ?></p></div>';
		<?php
	}

	if (
		count( $errors ) &&
		flatsome_envato()->registration->get_option( 'show_notice' ) &&
		$screen->id !== 'toplevel_page_flatsome-panel'
	) {
		?>
		<div id="flatsome-notice" class="notice notice-warning notice-alt is-dismissible">
			<h3 class="notice-title"><?php esc_html_e( 'Flatsome issues', 'flatsome' ); ?></h3>
			<?php foreach ( $errors as $error ) : ?>
				<?php echo wpautop( $error ); ?>
			<?php endforeach; ?>
			<p>
				<a href="<?php echo esc_url_raw( admin_url( 'admin.php?page=flatsome-panel' ) ); ?>">
					<?php esc_html_e( 'Manage registration', 'flatsome' ); ?>
				</a>
			</p>
			<p>
				<a href="<?php echo esc_url_raw( UXTHEMES_ACCOUNT_URL ); ?>" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e( 'Manage your licenses', 'flatsome' ); ?>
					<span class="dashicons dashicons-external" style="vertical-align:middle;font-size:18px;text-decoration: none;"></span>
				</a>
			</p>
			<script>
				jQuery(function($){
					$('#flatsome-notice').on('click', '.notice-dismiss', function(){
						$.post('<?php echo admin_url( 'admin-ajax.php?action=flatsome_registration_dismiss_notice' ) ?>');
					});
				});
			</script>
		</div>
		<?php
	}
}
