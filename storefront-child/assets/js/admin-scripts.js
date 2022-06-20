(function ($) {
	'use strict';

	$( 'a[href="#variable_product_options"]' ).on( 'click', function() {
		setTimeout( () => {
			$('input[name*="_prod_color_var"]').wpColorPicker();
		}, 1000);
	} );
	
	function imageUploader() {
		$(document).off('click', '.add-variation-img');
		$(document).on('click', '.add-variation-img', addImage);
		$(document).on('click', '.remove-variation-img', removeImage);
		$('.woocommerce_variation').each(function () {
		var optionsWrapper = $(this).find('.options');
		var galleryWrapper = $(this).find('.variation-imgs__wrapper');
		galleryWrapper.insertBefore(optionsWrapper);
		});
	}
	
	function addImage(event) {
		event.preventDefault();
		event.stopPropagation();
		var that = this;
		var file_frame = 0;
		var product_variation_id = $(this).data('product_variation_id');
	
		var _prev_image = $(this).parents('.variation-imgs__wrapper').find('input').map(function () {
		return Number($(this).val());
		}).get();
	
		if (typeof wp !== 'undefined' && wp.media && wp.media.editor) {
		if (file_frame) {
			file_frame.open();
			return;
		}
	
		file_frame = wp.media.frames.select_image = wp.media({
			title: 'Choose Image',
			button: {
			text: 'Add Image'
			},
			library: {
			type: ['image']
			},
			multiple: true
		});
		file_frame.on('select', function () {
			var images = file_frame.state().get('selection').toJSON();
			var html = images.map(function (image) {
			if (image.type === 'image') {
	
				if (_prev_image.indexOf(image.id) === -1) {
				var id = image.id,
					image_sizes = image.sizes;
				image_sizes = image_sizes === undefined ? {} : image_sizes;
				var thumbnail = image_sizes.thumbnail,
					full = image_sizes.full;
				var url = thumbnail ? thumbnail.url : full.url;
				var template = wp.template('variation-image');
				return template({
					id: id,
					url: url,
					product_variation_id: product_variation_id
				});
				} else {
				alert('Cannot add duplicate items.');
				}
			}
			}).join('');
			$(that).parent().prev().find('.variation-imgs').append(html);
			sortable();
			variationChanged(that);
		});
		file_frame.open();
		}
	}
	
	function removeImage(event) {
		event.preventDefault();
		event.stopPropagation();
		var that = this;
		variationChanged(this);
		setTimeout(function () {
		$(that).parent().remove();
		}, 1);
	}
	
	function variationChanged(element) {
		$(element).closest('.woocommerce_variation').addClass('variation-needs-update');
		$('button.cancel-variation-changes, button.save-variation-changes').removeAttr('disabled');
		$('#variable_product_options').trigger('woocommerce_variations_input_changed');
	}
	
	function sortable() {
		$('.variation-imgs').sortable({
		items: 'li.image',
		cursor: 'move',
		scrollSensitivity: 40,
		forcePlaceholderSize: true,
		forceHelperSize: false,
		helper: 'clone',
		opacity: 0.65,
		placeholder: 'rtwpvg-sortable-placeholder',
		start: function start(event, ui) {
			ui.item.css('background-color', '#f6f6f6');
		},
		stop: function stop(event, ui) {
			ui.item.removeAttr('style');
		},
		update: function update() {
			variationChanged(this);
		}
		});
	}
	
	$('#woocommerce-product-data').on('woocommerce_variations_loaded', function () {
		imageUploader();
		sortable();
		if ($.fn.wpColorPicker) {
			$('input[name*="_prod_color_var"]').wpColorPicker();
		}
	});
	$('#variable_product_options').on('woocommerce_variations_added', function () {
		imageUploader();
		sortable();
		if ($.fn.wpColorPicker) {
			$('input[name*="_prod_color_var"]').wpColorPicker();
		}
	});
	
})(jQuery);
