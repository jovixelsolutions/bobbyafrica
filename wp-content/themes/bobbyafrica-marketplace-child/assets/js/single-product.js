(function ($) {
  'use strict';

  if (!$('.single-product').length) {
    return;
  }

  if ($('.ast-sticky-add-to-cart').length) {
    $('.marketplace-mobile-buy-bar').remove();
  }

  $(document).on('click', '.single_buy_now_button', function () {
    var $form = $(this).closest('form.cart');
    if (!$form.length) {
      return;
    }

    $('<input>', {
      type: 'hidden',
      name: 'buy_now',
      value: '1'
    }).appendTo($form);
  });

  $(document.body).on('found_variation', function (event, variation) {
    if (!variation || typeof variation.price_html === 'undefined') {
      return;
    }

    var $mobilePrice = $('.marketplace-mobile-buy-price');
    if ($mobilePrice.length && variation.price_html) {
      $mobilePrice.html(variation.price_html);
    }
  });

  $(document.body).on('reset_data hide_variation', function () {
    var $mobilePrice = $('.marketplace-mobile-buy-price');
    if ($mobilePrice.length) {
      var originalText = $mobilePrice.data('original-price');
      if (!originalText) {
        originalText = $mobilePrice.html();
      }
      $mobilePrice.html(originalText);
    }
  });

  $(document).ready(function () {
    var $mobilePrice = $('.marketplace-mobile-buy-price');
    if ($mobilePrice.length) {
      $mobilePrice.data('original-price', $mobilePrice.html());
    }
  });
})(jQuery);
