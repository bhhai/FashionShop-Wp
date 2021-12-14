<?php
/**
 * Wishlist header
 *
 * @author YITH
 * @package YITH\Wishlist\Templates\Wishlist\View
 * @version 3.0.0
 */

/**
 * Template variables:
 *
 * @var $wishlist \YITH_WCWL_Wishlist Current wishlist
 * @var $is_custom_list bool Whether current wishlist is custom
 * @var $can_user_edit_title bool Whether current user can edit title
 * @var $form_action string Action for the wishlist form
 * @var $page_title string Page title
 * @var $fragment_options array Array of items to use for fragment generation
 */

if ( ! defined( 'YITH_WCWL' ) ) {
	exit;
} // Exit if accessed directly
?>

<?php do_action( 'yith_wcwl_before_wishlist_form', $wishlist ); ?>

<form
	id="yith-wcwl-form"
	action="<?php echo esc_attr( $form_action ); ?>"
	method="post"
	class="woocommerce yith-wcwl-form wishlist-fragment"
	data-fragment-options="<?php echo wc_esc_json( wp_json_encode( $fragment_options ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>"
>

	<!-- TITLE -->
	<?php
	do_action( 'yith_wcwl_before_wishlist_title', $wishlist );

	if ( ! empty( $page_title ) ) :
		?>
		<div class="wishlist-title-container">
			<div class="wishlist-title <?php echo ( $can_user_edit_title ) ? 'wishlist-title-with-form' : ''; ?>">
				<?php echo wp_kses_post( apply_filters( 'yith_wcwl_wishlist_title', '<h2>' . $page_title . '</h2>' ) ); ?>
				<?php if ( $can_user_edit_title ) : ?>
					<a class="btn button show-title-form">
						<?php echo yith_wcwl_kses_icon( apply_filters( 'yith_wcwl_edit_title_icon', '<i class="fa fa-pencil"></i>' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<?php esc_html_e( 'Edit title', 'yith-woocommerce-wishlist' ); ?>
					</a>
				<?php endif; ?>
			</div>
			<?php if ( $can_user_edit_title ) : ?>
				<div class="hidden-title-form">
					<input type="text" value="<?php echo esc_attr( $page_title ); ?>" name="wishlist_name"/>
					<div class="edit-title-buttons">
						<a role="button" href="#" class="hide-title-form">
							<?php echo yith_wcwl_kses_icon( apply_filters( 'yith_wcwl_cancel_wishlist_title_icon', '<i class="fa fa-remove"></i>' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</a>
						<a role="button" href="#" class="save-title-form">
							<?php echo yith_wcwl_kses_icon( apply_filters( 'yith_wcwl_save_wishlist_title_icon', '<i class="fa fa-check"></i>' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</a>
					</div>
				</div>
			<?php endif; ?>
		</div>
		<?php
	endif;

	do_action( 'yith_wcwl_before_wishlist', $wishlist );
	?>
