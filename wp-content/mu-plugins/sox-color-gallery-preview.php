<?php
/**
 * Plugin Name: Optica Simon - Color-only gallery preview
 * Description: On single product pages, shows a variation's photo gallery as soon as Color is selected, without requiring Lens and Size to also be chosen. Add-to-cart / price / stock still require the full exact match, unaffected. See project doc 'Setup Recap' (2026-08-21) for details.
 */

if (!defined('ABSPATH')) { exit; }

add_action('wp_footer', 'sox_color_gallery_preview_script');
function sox_color_gallery_preview_script() {
  if (!function_exists('is_product') || !is_product()) {
    return;
  }
  ?>
<script>
(function ($) {
	'use strict';

	function isQuickView() {
		return $('.single-product-content').hasClass('product-quick-view');
	}

	function buildGalleryHtml(imagesData) {
		var html = '';

		for (var key in imagesData) {
			if (!imagesData.hasOwnProperty(key)) {
				continue;
			}

			var img = imagesData[key];
			var itemHtml = '<div class="product-image-wrap"><figure class="woocommerce-product-gallery__image" data-thumb="' + img.thumbnail_src + '">';

			if (!isQuickView()) {
				itemHtml += '<a href="' + img.full_src + '" data-elementor-open-lightbox="no">';
			}

			var srcset = img.srcset ? 'srcset="' + img.srcset + '"' : '';

			itemHtml += '<img width="' + img.width + '" height="' + img.height + '" src="' + img.src + '" class="' + img.class + '" alt="' + (img.alt || '') + '" title="' + (img.title || '') + '" data-caption="' + (img.data_caption || '') + '" data-src="' + img.data_src + '" data-large_image="' + img.data_large_image + '" data-large_image_width="' + img.data_large_image_width + '" data-large_image_height="' + img.data_large_image_height + '" ' + srcset + ' sizes="' + img.sizes + '" />';

			if (!isQuickView()) {
				itemHtml += '</a>';
			}

			itemHtml += '</figure></div>';
			html += itemHtml;
		}

		return html;
	}

	function applyColorPreviewGallery($variationForm, imagesData) {
		if (!imagesData || !imagesData.length) {
			return;
		}

		var $pageWrapper = $variationForm.parents('.product, .main-page-wrapper');
		var $mainGallery = $pageWrapper.find('.woocommerce-product-gallery__wrapper');
		var $galleryEl = $pageWrapper.find('.woocommerce-product-gallery');

		if (imagesData.length > 1) {
			$galleryEl.addClass('wd-has-thumb');
		} else {
			$galleryEl.removeClass('wd-has-thumb');
		}

		$mainGallery.empty();
		$mainGallery.append(buildGalleryHtml(imagesData));

		$('.woocommerce-product-gallery__image').trigger('zoom.destroy');
		woodmartThemeModule.$document.trigger('wdReplaceMainGallery');
		if (!isQuickView()) {
			woodmartThemeModule.$document.trigger('wdReplaceMainGalleryNotQuickView');
		}
		woodmartThemeModule.$window.trigger('resize');

		if (!$mainGallery.hasClass('owl-carousel')) {
			return;
		}

		var refreshOwl = function () {
			$mainGallery.trigger('destroy.owl.carousel');
			$('.product-images').imagesLoaded(function () {
				var $owl = $mainGallery.owlCarousel(woodmartThemeModule.mainCarouselArg);
				$owl.trigger('refresh.owl.carousel');
				$owl.trigger('to.owl.carousel', 0);
			});
		};

		refreshOwl();

		var $thumbs = $pageWrapper.find('.images .thumbnails');

		if ($thumbs.hasClass('owl-carousel')) {
			$thumbs.trigger('to.owl.carousel', 0);
			$thumbs.find('.active-thumb').removeClass('active-thumb');
			$thumbs.find('.product-image-thumbnail').eq(0).addClass('active-thumb');
		} else if ($thumbs.hasClass('slick-slider')) {
			try {
				$thumbs.slick('slickGoTo', 0);
			} catch (e) {}
			$thumbs.find('.active-thumb').removeClass('active-thumb');
			$thumbs.find('.product-image-thumbnail').eq(0).addClass('active-thumb');
		}
	}

	function getColorVariations($form) {
		var raw = $form.data('product_variations');

		if (!raw || raw === true) {
			return null;
		}

		if (typeof raw === 'string') {
			try {
				raw = JSON.parse(raw);
			} catch (e) {
				return null;
			}
		}

		return raw;
	}

	function handleColorChange($form) {
		var $colorSelect = $form.find('select[name="attribute_pa_color"]');

		if (!$colorSelect.length) {
			return;
		}

		var variations = getColorVariations($form);

		if (!variations) {
			return;
		}

		var value = $colorSelect.val();

		if (!value) {
			return;
		}

		// If lens + size are already both chosen, WooCommerce/Woodmart's own
		// found_variation/show_variation flow already handles the gallery —
		// don't interfere.
		var $lensSelect = $form.find('select[name="attribute_pa_lens"]');
		var $sizeSelect = $form.find('select[name="attribute_pa_size"]');
		var lensChosen = !$lensSelect.length || !!$lensSelect.val();
		var sizeChosen = !$sizeSelect.length || !!$sizeSelect.val();

		if (lensChosen && sizeChosen) {
			return;
		}

		var match = null;

		for (var i = 0; i < variations.length; i++) {
			var v = variations[i];

			if (v.attributes && v.attributes.attribute_pa_color === value) {
				if (v.additional_variation_images && v.additional_variation_images.length) {
					match = v;
					break;
				}

				if (!match) {
					match = v;
				}
			}
		}

		if (!match) {
			return;
		}

		var imagesData = match.additional_variation_images;

		if (!imagesData || !imagesData.length) {
			if (match.image && match.image.src) {
				imagesData = [{
					width: match.image.width || 700,
					height: match.image.height || 350,
					src: match.image.src,
					full_src: match.image.url,
					thumbnail_src: match.image.src,
					class: 'wp-post-image',
					alt: match.image.alt || '',
					title: match.image.title || '',
					data_caption: match.image.caption || '',
					data_src: match.image.src,
					data_large_image: match.image.url,
					data_large_image_width: match.image.width || '',
					data_large_image_height: match.image.height || '',
					srcset: match.image.srcset || '',
					sizes: match.image.sizes || ''
				}];
			} else {
				return;
			}
		}

		applyColorPreviewGallery($form, imagesData);
	}

	$(document.body).on('change', 'select[name="attribute_pa_color"]', function () {
		var $form = $(this).closest('.variations_form');

		// Defer so this runs after WooCommerce core's own reset_data /
		// found_variation handling (and Woodmart's swatch reset) for this
		// same change event have already settled.
		setTimeout(function () {
			handleColorChange($form);
		}, 60);
	});
})(jQuery);

</script>
<?php
}
