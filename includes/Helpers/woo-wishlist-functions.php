<?php
/**
 * 
 * @package  Woo Wishlist
 */


/**
* Get the value of a settings field.
*
* @param string $option settings field name
* @param string $section the section name this field belongs to
* @param string $default default text if it's not found
* @return mixed
*/
function wowl_get_option( $option, $section, $default = '' ) {
	$options = get_option( $section );
	if ( isset( $options[$option] ) ) {
	return $options[$option];
	}
	return $default;
}


/**
 * This function will run when "Add to Cart" is clicked. It will check the URL parameters
 * and will perform functionality according parameter's values.
 * 
 * @since 1.0
 * @package woo-wishlist 
 */
function wowl_add_to_cart ( $cart_item_key, $product_id ) {
	if ( 'on' === $_GET['remove-from-wishlist'] ) {
		$wowl_user_wl	= get_user_meta( get_current_user_id(), 'wishlist', true );
		$wowl_item		= array_search( $product_id, $wowl_user_wl );

		if ( ! empty( $wowl_item ) || 0 === $wowl_item  ) {
			unset( $wowl_user_wl[$wowl_item] );
			$wowl_user_wl = array_values( $wowl_user_wl );
			update_user_meta( get_current_user_id(), 'wishlist', $wowl_user_wl );
		}
	}

	if ( 'on' === $_GET['redirect-to-cart'] ) {
		wp_safe_redirect( wc_get_cart_url() );
	}
}
add_action( 'woocommerce_add_to_cart', 'wowl_add_to_cart', 10, 2 );


/**
 * Renders the wishlist page content and is hooked into the add_shortcode() hook.
 * 
 * @since 1.0
 * @package woo-wishlist 
 */
function wwl_wishlist_shortcode() {
	ob_start();
	$wowl_user_wl		= get_user_meta( get_current_user_id(), 'wishlist', true );
	$wowl_table_opts	= wowl_get_option( 'page_option_table_show', 'wowl_page_opt_section' );

	if ( empty( $wowl_user_wl ) ) {
		echo '<h2>Wishlist is empty.</h2>';
		return;
	}
	?>
	<table class="wwl-table">
		<tr class="wwl-table-titles">
		<?php if ( !empty( $wowl_table_opts['remove_left'] ) ) : ?>
			<td>Remove</td>
		<?php endif; ?>

			<td>Image</td>
			<td>Name</td>

		<?php if ( !empty( $wowl_table_opts['price'] ) ) : ?>
			<td>Price</td>
		<?php endif; ?>

		<?php if ( !empty( $wowl_table_opts['stock'] ) ) : ?>
			<td>Stock</td>
		<?php endif; ?>

		<?php if ( !empty( $wowl_table_opts['remove_right'] ) ) : ?>
			<td>Remove</td>
		<?php endif; ?>

		<?php if ( !empty( $wowl_table_opts['add_to_cart'] ) ) : ?>
			<td>Add to Cart</td>
		<?php endif; ?>
		</tr>

		<?php 
			foreach ( $wowl_user_wl as $wl_item ) :
				$wowl = wc_get_product( $wl_item );
		?>

		<tr id="<?php esc_attr_e( $wowl->get_id() ); ?>" >
		<?php if ( !empty( $wowl_table_opts['remove_left'] ) ) : ?>
			<td>X</td>
		<?php endif; ?>
		
			<td><?php echo $wowl->get_image(); ?></td>

			<td><?php echo $wowl->get_name(); ?></td>
		
		<?php if ( !empty( $wowl_table_opts['price'] ) ) : ?>
			<td><?php echo $wowl->get_price(); ?></td>
		<?php endif; ?>
		
		<?php if ( !empty( $wowl_table_opts['stock'] ) ) : ?>
			<td><?php echo $wowl->get_stock_status(); ?></td>
		<?php endif; ?>

		<?php if ( !empty( $wowl_table_opts['remove_right'] ) ) :
		_e('<td><a class="wwl-rfc wwl-rfc-btn" data-product='. esc_attr( $wowl->get_id() ) .' href="">Remove</a></td>');
		?>

		<?php endif; ?>
		
		<?php if ( !empty( $wowl_table_opts['add_to_cart'] ) ) : ?>
			<?php if ( 'simple' == $wowl->get_type() ) : ?>
				<td>
				<?php 
					echo '<a class="wwl-atc" href="'. $wowl->add_to_cart_url() .'&redirect-to-cart='. wowl_get_option( 'page_option_redirect', 'wowl_page_opt_section' ) .'&remove-from-wishlist='. wowl_get_option( 'page_option_remove', 'wowl_page_opt_section' ) .'">Add to Cart</a>';
				?>
				</td>
			<?php endif; ?>

			<?php if ( 'external' == $wowl->get_type() ) :
				$url = $wowl->product_url;
			?>
				<td>
				<?php 
					echo '<a class="wwl-atc" href="'. $url .'?remove-from-wishlist='. wowl_get_option( 'page_option_remove', 'wowl_page_opt_section' ) .'">Add to Cart</a>';
				?>
				</td>
			<?php endif; ?>

			<?php if ( 'variable' == $wowl->get_type() || 'grouped' == $wowl->get_type() ) : 
				$url = get_permalink( $wowl->get_id() );	
			?>
				<td>
				<?php 
					echo '<a class="wwl-atc" href="'. $url .'?redirect-to-cart='. wowl_get_option( 'page_option_redirect', 'wowl_page_opt_section' ) .'&remove-from-wishlist='. wowl_get_option( 'page_option_remove', 'wowl_page_opt_section' ) .'">Add to Cart</a>';
				?>
				</td>
			<?php endif; ?>
		<?php endif; ?>
		</tr>

		<?php endforeach; ?>

	</table>
	<?php
		$wowl_share			= wowl_get_option( 'page_option_social_share', 'wowl_page_opt_section' );
		$wowl_share_opts	= wowl_get_option( 'page_option_share', 'wowl_page_opt_section' );
		$wowl_share_title	= wowl_get_option( 'page_option_share_title', 'wowl_page_opt_section' );
		$wowl_page_id		= wowl_get_option( 'page_option_select', 'wowl_page_opt_section' );
		$wowl_page_link		= get_permalink( $wowl_page_id );
		$wowl_wa_url = '';
		if ( wp_is_mobile() ) {
			$wowl_wa_url = 'whatsapp://send?text=' . $wowl_share_title . '-' . urlencode( $wowl_page_link );
		} else {
			$wowl_wa_url = 'https://web.whatsapp.com/send?text=' . $wowl_share_title . '-' . urlencode( $wowl_page_link );
		}

		if ( 'on' === $wowl_share ) {
		?>
		<h4 class="wowl-share-title">Share on:</h4>
		<ul class="wowl-share">
			<?php if ( !empty( $wowl_share_opts['facebook'] ) ): ?>
				<li>
					<a target="_blank" rel="noopener" class="facebook" href="https://www.facebook.com/sharer.php?u=<?php echo urlencode( $wowl_share_title ); ?>&p[title]=<?php echo esc_attr( $wowl_share_title ); ?>" title="<?php esc_html_e( 'Facebook', 'woo-whislist' ); ?>">
					<img class="wowl-si"  src= '<?php echo WWL_PLUGIN_URL; ?>assets/images/facebook.svg'>
					</a>
				</li>
			<?php endif; ?>
			<?php if ( !empty( $wowl_share_opts['twitter'] ) ): ?>
				<li>
					<a target="_blank" rel="noopener" class="twitter" href="https://twitter.com/share?url=<?php echo urlencode( $wowl_share_title ); ?>" title="<?php esc_html_e( 'Twitter', 'woo-whislist' ); ?>">
					<img class="wowl-si"  src= '<?php echo WWL_PLUGIN_URL; ?>assets/images/twitter.svg'> 
					</a>
				</li>
			<?php endif; ?>
			<?php if ( !empty( $wowl_share_opts['pinterest'] ) ): ?>
				<li>
					<a href="">
					<img class="wowl-si"  src= '<?php echo WWL_PLUGIN_URL; ?>assets/images/pinterest.svg'> 
					</a>
				</li>
			<?php endif; ?>
			<?php if ( !empty( $wowl_share_opts['whatsapp'] ) ): ?>
				<li>
					<a href="<?php echo esc_attr( $wowl_wa_url ); ?>" data-action="share/whatsapp/share" target="_blank" rel="noopener" title="<?php esc_html_e( 'WhatsApp', 'woo-wishlist' ); ?>">
					<img class="wowl-si" src= '<?php echo WWL_PLUGIN_URL; ?>assets/images/whatsapp.svg'> 
					</a>
				</li>
			<?php endif; ?>

		</ul>
		<?php
		}
	return ob_get_clean();
}
add_shortcode( 'wwl_wishlist_shortcode', 'wwl_wishlist_shortcode' );