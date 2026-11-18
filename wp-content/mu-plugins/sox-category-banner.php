<?php
/**
 * Plugin Name: SOX Category Banner
 * Description: Full-width category header on product-category pages (banner image + H1 + tagline) and Sunglasses/Eyeglasses filter pills on brand pages. Fields live under Products → Categories → edit a category → "Brand banner". Delete this file to remove everything (then LiteSpeed Cache → Purge All).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ---------- helpers ---------- */

function sox_cb_type_ids() {
	static $ids = null;
	if ( null === $ids ) {
		$s   = get_term_by( 'slug', 'sunglasses', 'product_cat' );
		$o   = get_term_by( 'slug', 'eyeglasses', 'product_cat' );
		$ids = array(
			'sun'     => $s ? (int) $s->term_id : 0,
			'optical' => $o ? (int) $o->term_id : 0,
		);
	}
	return $ids;
}

function sox_cb_count( $term_id, $type_id ) {
	$q = new WP_Query(
		array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'tax_query'      => array(
				array( 'taxonomy' => 'product_cat', 'terms' => array( (int) $term_id ) ),
				array( 'taxonomy' => 'product_cat', 'terms' => array( (int) $type_id ) ),
			),
		)
	);
	return (int) $q->found_posts;
}

function sox_cb_current_range() {
	$r = isset( $_GET['range'] ) ? sanitize_key( wp_unslash( $_GET['range'] ) ) : '';
	return in_array( $r, array( 'sun', 'optical' ), true ) ? $r : '';
}

/* ---------- admin fields ---------- */

function sox_cb_img_row( $key, $label, $help, $id ) {
	$src = $id ? wp_get_attachment_image_url( $id, 'medium' ) : '';
	?>
	<tr class="form-field sox-cb-img">
		<th scope="row"><label><?php echo esc_html( $label ); ?></label></th>
		<td>
			<input type="hidden" name="<?php echo esc_attr( $key ); ?>" value="<?php echo $id ? (int) $id : ''; ?>">
			<div class="sox-cb-prev" style="margin-bottom:8px"><?php echo $src ? '<img src="' . esc_url( $src ) . '" style="max-width:320px;height:auto;display:block">' : ''; ?></div>
			<button type="button" class="button sox-cb-pick">Choose image</button>
			<button type="button" class="button-link sox-cb-clear" style="color:#b32d2e;margin-left:8px">Remove</button>
			<p class="description"><?php echo esc_html( $help ); ?></p>
		</td>
	</tr>
	<?php
}

add_action(
	'product_cat_edit_form_fields',
	function ( $term ) {
		$d    = (int) get_term_meta( $term->term_id, 'sox_banner_desktop', true );
		$m    = (int) get_term_meta( $term->term_id, 'sox_banner_mobile', true );
		$t    = (string) get_term_meta( $term->term_id, 'sox_banner_tagline', true );
		$tone = get_term_meta( $term->term_id, 'sox_banner_text', true ) === 'dark' ? 'dark' : 'light';
		wp_nonce_field( 'sox_cb_save', 'sox_cb_nonce' );
		?>
		<tr class="form-field"><th colspan="2" style="padding-bottom:0"><h2 style="margin:20px 0 0">Brand banner</h2></th></tr>
		<?php
		sox_cb_img_row( 'sox_banner_desktop', 'Banner image (desktop)', '2400 × 750 px JPG, under 300 KB. No text on the image; keep the left ~40% calm, the title sits there.', $d );
		sox_cb_img_row( 'sox_banner_mobile', 'Banner image (mobile)', '1200 × 900 px JPG. Optional: if empty, the desktop image is cropped to fit phones. Title sits bottom-left.', $m );
		?>
		<tr class="form-field">
			<th scope="row"><label for="sox_banner_tagline">Banner tagline</label></th>
			<td><input type="text" name="sox_banner_tagline" id="sox_banner_tagline" value="<?php echo esc_attr( $t ); ?>" maxlength="120">
			<p class="description">One line under the brand name. Leave empty for name only.</p></td>
		</tr>
		<tr class="form-field">
			<th scope="row"><label for="sox_banner_text">Banner text colour</label></th>
			<td><select name="sox_banner_text" id="sox_banner_text">
				<option value="light" <?php selected( $tone, 'light' ); ?>>White text (dark images)</option>
				<option value="dark" <?php selected( $tone, 'dark' ); ?>>Black text (light images)</option>
			</select></td>
		</tr>
		<?php
	},
	20
);

add_action(
	'edited_product_cat',
	function ( $term_id ) {
		if ( ! isset( $_POST['sox_cb_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sox_cb_nonce'] ) ), 'sox_cb_save' ) ) {
			return;
		}
		if ( ! current_user_can( 'manage_product_terms' ) ) {
			return;
		}
		foreach ( array( 'sox_banner_desktop', 'sox_banner_mobile' ) as $k ) {
			$v = isset( $_POST[ $k ] ) ? absint( $_POST[ $k ] ) : 0;
			$v ? update_term_meta( $term_id, $k, $v ) : delete_term_meta( $term_id, $k );
		}
		$t = isset( $_POST['sox_banner_tagline'] ) ? sanitize_text_field( wp_unslash( $_POST['sox_banner_tagline'] ) ) : '';
		'' !== $t ? update_term_meta( $term_id, 'sox_banner_tagline', $t ) : delete_term_meta( $term_id, 'sox_banner_tagline' );
		$tone = ( isset( $_POST['sox_banner_text'] ) && 'dark' === $_POST['sox_banner_text'] ) ? 'dark' : 'light';
		update_term_meta( $term_id, 'sox_banner_text', $tone );
		do_action( 'litespeed_purge_all' );
	}
);

add_action(
	'admin_enqueue_scripts',
	function ( $hook ) {
		if ( ! in_array( $hook, array( 'term.php', 'edit-tags.php' ), true ) || ( isset( $_GET['taxonomy'] ) && 'product_cat' !== $_GET['taxonomy'] ) ) {
			return;
		}
		wp_enqueue_media();
		$js = <<<'JS'
jQuery(function($){
	$(document).on('click','.sox-cb-pick',function(e){
		e.preventDefault();
		var td=$(this).closest('td');
		var f=wp.media({title:'Choose banner image',multiple:false,library:{type:'image'}});
		f.on('select',function(){
			var a=f.state().get('selection').first().toJSON();
			td.find('input[type=hidden]').val(a.id);
			var u=(a.sizes&&a.sizes.medium)?a.sizes.medium.url:a.url;
			td.find('.sox-cb-prev').html('<img src="'+u+'" style="max-width:320px;height:auto;display:block">');
		});
		f.open();
	});
	$(document).on('click','.sox-cb-clear',function(e){
		e.preventDefault();
		var td=$(this).closest('td');
		td.find('input[type=hidden]').val('');
		td.find('.sox-cb-prev').empty();
	});
});
JS;
		wp_add_inline_script( 'jquery-core', $js );
	}
);

/* ---------- Sunglasses / Eyeglasses filter (?range=sun|optical) ---------- */

add_action(
	'pre_get_posts',
	function ( $q ) {
		if ( is_admin() || ! $q->is_main_query() || ! $q->is_tax( 'product_cat' ) ) {
			return;
		}
		$r   = sox_cb_current_range();
		$ids = sox_cb_type_ids();
		if ( ! $r || empty( $ids[ $r ] ) ) {
			return;
		}
		$tq   = (array) $q->get( 'tax_query' );
		$tq[] = array(
			'taxonomy'         => 'product_cat',
			'field'            => 'term_id',
			'terms'            => array( $ids[ $r ] ),
			'include_children' => false,
		);
		$q->set( 'tax_query', $tq );
	},
	20
);

/* ---------- front-end header ---------- */

add_filter(
	'body_class',
	function ( $c ) {
		if ( is_product_category() ) {
			$c[] = 'sox-has-cb';
		}
		return $c;
	}
);

add_action( 'woodmart_after_header', 'sox_cb_render', 20 );

function sox_cb_render() {
	if ( ! is_product_category() ) {
		return;
	}
	$term = get_queried_object();
	if ( ! $term || empty( $term->term_id ) ) {
		return;
	}
	$tid  = (int) $term->term_id;
	$d    = (int) get_term_meta( $tid, 'sox_banner_desktop', true );
	$m    = (int) get_term_meta( $tid, 'sox_banner_mobile', true );
	$tag  = (string) get_term_meta( $tid, 'sox_banner_tagline', true );
	$tone = get_term_meta( $tid, 'sox_banner_text', true ) === 'dark' ? 'dark' : 'light';

	// Admin-only preview: ?sox_cb_preview=<attachment id> (not cached for logged-in users).
	if ( isset( $_GET['sox_cb_preview'] ) && current_user_can( 'manage_options' ) ) {
		$d = absint( $_GET['sox_cb_preview'] );
		if ( isset( $_GET['sox_cb_tag'] ) ) {
			$tag = sanitize_text_field( wp_unslash( $_GET['sox_cb_tag'] ) );
		}
	}
	if ( $d && ! wp_attachment_is_image( $d ) ) {
		$d = 0;
	}
	if ( $m && ! wp_attachment_is_image( $m ) ) {
		$m = 0;
	}

	// Sunglasses / Eyeglasses pills: only on non-type categories that carry both.
	$ids   = sox_cb_type_ids();
	$pills = array();
	if ( ! in_array( $tid, $ids, true ) && $ids['sun'] && $ids['optical'] ) {
		if ( sox_cb_count( $tid, $ids['sun'] ) && sox_cb_count( $tid, $ids['optical'] ) ) {
			$cur  = sox_cb_current_range();
			$base = get_term_link( $term );
			if ( ! is_wp_error( $base ) ) {
				$pills = array(
					array( 'All', $base, '' === $cur ),
					array( 'Sunglasses', add_query_arg( 'range', 'sun', $base ), 'sun' === $cur ),
					array( 'Eyeglasses', add_query_arg( 'range', 'optical', $base ), 'optical' === $cur ),
				);
			}
		}
	}

	$name = $term->name;
	?>
	<style id="sox-cb-css">
	.sox-has-cb .main-page-wrapper{padding-top:0}
	.sox-cb{position:relative;margin:0 0 28px}
	.sox-cb--img{background:#1a1a1a;overflow:hidden}
	.sox-cb__media,.sox-cb__media img{display:block;width:100%}
	.sox-cb__media img{aspect-ratio:32/10;height:auto;max-height:640px;object-fit:cover;object-position:center}
	.sox-cb--img::after{content:"";position:absolute;inset:0;pointer-events:none}
	.sox-cb--light::after{background:linear-gradient(90deg,rgba(0,0,0,.55) 0%,rgba(0,0,0,.25) 38%,rgba(0,0,0,0) 62%)}
	.sox-cb--dark::after{background:linear-gradient(90deg,rgba(255,255,255,.6) 0%,rgba(255,255,255,.25) 38%,rgba(255,255,255,0) 62%)}
	.sox-cb__inner{position:absolute;inset:0;margin:0 auto;display:flex;align-items:center;z-index:1}
	.sox-cb__title{margin:0 0 10px;font-size:clamp(32px,4.6vw,64px);line-height:1.04;font-weight:600;letter-spacing:-.01em;text-transform:none}
	.sox-cb__tag{margin:0;max-width:460px;font-size:clamp(15px,1.5vw,19px);line-height:1.45}
	.sox-cb--light .sox-cb__title{color:#fff}.sox-cb--light .sox-cb__tag{color:rgba(255,255,255,.92)}
	.sox-cb--dark .sox-cb__title{color:#111}.sox-cb--dark .sox-cb__tag{color:#333}
	.sox-cb--plain{padding:44px 0 4px;margin-bottom:20px}
	.sox-cb--plain .sox-cb__title{color:#242424;font-size:clamp(30px,3.6vw,48px)}
	.sox-cb--plain .sox-cb__tag{color:#777}
	.sox-cb-pills{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:24px}
	.sox-cb-pills a{display:inline-block;padding:8px 18px;border:1px solid #242424;border-radius:999px;font-size:12px;font-weight:600;letter-spacing:.06em;text-transform:uppercase;color:#242424;line-height:1.3;transition:background .2s,color .2s}
	.sox-cb-pills a:hover,.sox-cb-pills a.is-active{background:#242424;color:#fff}
	@media (max-width:767px){
		.sox-cb__media img{aspect-ratio:4/3;max-height:none}
		.sox-cb__inner{align-items:flex-end;padding-bottom:22px}
		.sox-cb--light::after{background:linear-gradient(0deg,rgba(0,0,0,.62) 0%,rgba(0,0,0,.2) 45%,rgba(0,0,0,0) 70%)}
		.sox-cb--dark::after{background:linear-gradient(0deg,rgba(255,255,255,.7) 0%,rgba(255,255,255,.2) 45%,rgba(255,255,255,0) 70%)}
		.sox-cb{margin-bottom:20px}
		.sox-cb--plain{padding-top:28px}
		.sox-cb-pills{margin-bottom:18px}
		.sox-cb-pills a{padding:7px 14px}
	}
	</style>
	<?php if ( $d ) : ?>
		<section class="sox-cb sox-cb--img sox-cb--<?php echo esc_attr( $tone ); ?>">
			<picture class="sox-cb__media">
				<?php if ( $m ) : ?>
					<source media="(max-width:767px)" srcset="<?php echo esc_attr( wp_get_attachment_image_srcset( $m, 'full' ) ?: wp_get_attachment_image_url( $m, 'full' ) ); ?>" sizes="100vw">
				<?php endif; ?>
				<?php
				echo wp_get_attachment_image(
					$d,
					'full',
					false,
					array(
						'alt'            => '',
						'sizes'          => '100vw',
						'loading'        => 'eager',
						'fetchpriority'  => 'high',
						'decoding'       => 'async',
						'class'          => 'skip-lazy no-lazyload',
						'data-skip-lazy' => '1',
					)
				);
				?>
			</picture>
			<div class="container sox-cb__inner">
				<div class="sox-cb__text">
					<h1 class="sox-cb__title"><?php echo esc_html( $name ); ?></h1>
					<?php if ( '' !== $tag ) : ?><p class="sox-cb__tag"><?php echo esc_html( $tag ); ?></p><?php endif; ?>
				</div>
			</div>
		</section>
	<?php else : ?>
		<section class="sox-cb sox-cb--plain">
			<div class="container">
				<h1 class="sox-cb__title"><?php echo esc_html( $name ); ?></h1>
				<?php if ( '' !== $tag ) : ?><p class="sox-cb__tag"><?php echo esc_html( $tag ); ?></p><?php endif; ?>
			</div>
		</section>
	<?php endif; ?>
	<?php if ( $pills ) : ?>
		<nav class="container sox-cb-pills" aria-label="<?php esc_attr_e( 'Product type' ); ?>">
			<?php foreach ( $pills as $p ) : ?>
				<a href="<?php echo esc_url( $p[1] ); ?>"<?php echo $p[2] ? ' class="is-active" aria-current="page"' : ''; ?>><?php echo esc_html( $p[0] ); ?></a>
			<?php endforeach; ?>
		</nav>
	<?php endif; ?>
	<?php
}
