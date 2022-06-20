<?php
/**
 * Returns version string for assets.
 *
 * @return null|string
 */
function _get_asset_version() {

	return defined( 'ENV_DEV' ) ? gmdate( 'YmdHis' ) : wp_get_theme()->get( 'Version' );

}

/**
 * Theme scripts and styles
 *
 * @return void
 */
function storefront_child_enqueue_styles_scripts() {

	wp_enqueue_style( 'storefront-child-style', get_stylesheet_directory_uri() . '/style.css', array(), '1.0' );

	$asset_version = _get_asset_version();

	wp_enqueue_script(
		'storefront-child-scripts',
		get_stylesheet_directory_uri() . '/assets/js/scripts.js',
		array( 'jquery' ),
		$asset_version,
		true
	);

}
add_action( 'wp_enqueue_scripts', 'storefront_child_enqueue_styles_scripts' );

/**
 * Admin scripts and styles
 *
 * @return void
 */
function storefront_child_admin_scripts( $hook ) {
	$asset_version = _get_asset_version();

	wp_enqueue_script( 'wp-color-picker' );
	wp_enqueue_style( 'wp-color-picker' );

	wp_enqueue_script( 'storefront-child-admin-script', get_stylesheet_directory_uri() . '/assets/js/admin-scripts.js', array( 'wp-color-picker' ), $asset_version, true );
}
add_action( 'admin_enqueue_scripts', 'storefront_child_admin_scripts' );


/**
 * Add custom color field to variation
 *
 * @param string $loop
 * @param array $variation_data
 * @param object $variation
 */
function storefront_child_color_field( $loop, $variation_data, $variation ) {
	woocommerce_wp_text_input(
		array(
			'id'          => '_prod_color_var[' . $variation->ID . ']',
			'label'       => 'Цвет кнопки',
			'description' => 'Цвет в hex',
			'desc_tip'    => true,
			'placeholder' => '#ff0000',
			'value'       => get_post_meta( $variation->ID, '_prod_color_var', true ),
		)
	);
}
add_action( 'woocommerce_product_after_variable_attributes', 'storefront_child_color_field', 10, 3 );

/**
 * Saves custom variation field value
 */
function storefront_child_save_variation_color_field( $post_id ) {
	$woocommerce__prod_color_var = $_POST['_prod_color_var'][ $post_id ];
	if ( isset( $woocommerce__prod_color_var ) && ! empty( $woocommerce__prod_color_var ) ) {
		update_post_meta( $post_id, '_prod_color_var', esc_attr( $woocommerce__prod_color_var ) );
	}
}
add_action( 'woocommerce_save_product_variation', 'storefront_child_save_variation_color_field', 10, 2 );

/**
 * Renders choose color buttons.
 */
function storefront_child_render_color_btns() {
	global $product;

	$default_attributes = $product->get_default_attributes();
	$default_color      = ! empty( $default_attributes['pa_color'] ) ? $default_attributes['pa_color'] : 'red';

	$variations = $product->get_available_variations();
	?>

	<div class="choose-color">Выберите цвет:</div>
	<div class="variation-colors">
		<?php
		foreach ( $variations as $variation ) {
			$variations_color = get_post_meta( $variation['variation_id'], '_prod_color_var', true );

			$attr_name = $variation['attributes']['attribute_pa_color'];
			$checked   = ( $attr_name === $default_color ) ? ' checked' : '';
			?>
			<div class="variation-color__btn">
				<input type="radio" name="variation_color" value="<?php echo esc_attr( $attr_name ); ?>" id="variation_color_<?php echo esc_attr( sanitize_title( $attr_name ) ); ?>"<?php echo esc_attr( $checked ); ?>>
				<label for="variation_color_<?php echo esc_attr( sanitize_title( $attr_name ) ); ?>"><span style="background-color: <?php echo $variations_color; ?>"><i class="fas fa-check"></i></span></label>
			</div>
			<?php
		}
		?>
	</div>
	<?php
}
add_action( 'woocommerce_after_variations_table', 'storefront_child_render_color_btns' );

/**
 * Add custom image field to variation
 *
 * @param string $loop
 * @param array $variation_data
 * @param object $variation
 */
function storefront_child_image_fields( $loop, $variation_data, $variation ) {
	$variation_id     = absint( $variation->ID );
	$variation_images = get_post_meta( $variation_id, 'variation_images', true );
	?>
	<div class="form-row form-row-full variation-imgs__wrapper">
		<h4><?php esc_html_e( 'Variation Images', 'woo-product-variation-gallery' ); ?></h4>
		<div class="variation-imgs__container">
			<ul class="variation-imgs">
				<?php
				if ( is_array( $variation_images ) && ! empty( $variation_images ) ) {
					$variation_images = array_values( array_unique( $variation_images ) );
					foreach ( $variation_images as $image_id ) :
						$image = wp_get_attachment_image_src( $image_id );
						if ( empty( $image[0] ) ) {
							continue;
						}
						?>
						<li class="image">
							<input type="hidden" name="variation-imgs[<?php echo esc_attr( $variation_id ); ?>][]" value="<?php echo $image_id; ?>">
							<img src="<?php echo esc_url( $image[0] ); ?>">
							<a href="#" class="delete remove-variation-img"><span class="dashicons dashicons-dismiss"></span></a>
						</li>
						<?php
					endforeach;
				}
				?>
			</ul>
		</div>
		<p class="add-image__wrapper hide-if-no-js">
			<a href="#" data-product_variation_loop="<?php echo absint( $loop ); ?>"
				data-product_variation_id="<?php echo esc_attr( $variation_id ); ?>"
				class="button add-variation-img"><?php esc_html_e( 'Add Images', 'woo-product-variation-gallery' ); ?></a>
		</p>
	</div>
	<?php
}
add_action( 'woocommerce_product_after_variable_attributes', 'storefront_child_image_fields', 10, 3 );

/**
 * Saves custom variation image field value
 */
function storefront_child_save_variation_images( $variation_id, $loop ) {
	if ( isset( $_POST['variation-imgs'] ) ) {
		if ( isset( $_POST['variation-imgs'][ $variation_id ] ) ) {
			$image_ids = (array) array_map( 'absint', $_POST['variation-imgs'][ $variation_id ] );
			$image_ids = array_values( array_unique( $image_ids ) );
			update_post_meta( $variation_id, 'variation_images', $image_ids );
		} else {
			delete_post_meta( $variation_id, 'variation_images' );
		}
	} else {
		delete_post_meta( $variation_id, 'variation_images' );
	}
}
add_action( 'woocommerce_save_product_variation', 'storefront_child_save_variation_images', 10, 2 );

/**
 * Adds image thumbnail template for wp.template in admin-scripts.js.
 */
function storefront_child_variation_image_templates() {
	?>
	<script type="text/html" id="tmpl-variation-image">
		<li class="image">
			<input type="hidden" name="variation-imgs[{{data.product_variation_id}}][]" value="{{data.id}}">
			<img src="{{data.url}}">
			<a href="#" class="delete remove-variation-img"><span class="dashicons dashicons-dismiss"></span></a>
		</li>
	</script>
	<style>
		.variation-imgs {
			display: flex;
			flex-wrap: wrap;
		}
		.variation-imgs li {
			position: relative;
			width: 80px;
			margin: 0 5px 0 0;
			padding: 0;
		}
		.variation-imgs li img {
			max-width: 100%;
			height: auto;
		}
		.variation-imgs li a {
			position: absolute;
			top: 0;
			right: 0;
			text-decoration: none;
		}
	</style>
	<?php
}
add_action( 'admin_footer', 'storefront_child_variation_image_templates' );

/**
 * An example of rendering additional images for variation
 */
function storefront_child_render_variation_imgs() {
	global $product;
	$variations = $product->get_available_variations();
	?>
		<?php
		foreach ( $variations as $variation ) {
			$variation_imgs = get_post_meta( $variation['variation_id'], 'variation_images', true );
			if ( is_array( $variation_imgs ) && ! empty( $variation_imgs ) ) {
				foreach ( $variation_imgs as $image_id ) {
					$image_thumb_url = wp_get_attachment_thumb_url( $image_id );
					$image_full_url  = wp_get_attachment_image_src( $image_id, 'full' );
					?>
					<div data-thumb="<?php echo esc_url( $image_thumb_url ); ?>" data-thumb-alt="" class="woocommerce-product-gallery__image"><a href="<?php echo esc_url( $image_full_url ); ?>"><?php echo wp_get_attachment_image( $image_id ); ?></a></div>
					<?php
				}
			}
		}
		?>
	<?php
}
add_action( 'woocommerce_product_thumbnails', 'storefront_child_render_variation_imgs' );
