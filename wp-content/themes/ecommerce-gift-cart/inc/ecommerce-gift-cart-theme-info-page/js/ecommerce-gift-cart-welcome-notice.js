(function ($) {
    "use strict";

    // Install + Activate button
    $("#install-activate-button").on("click", function (e) {
        e.preventDefault();

        var button = $(this);
        button.prop("disabled", true)
              .text("Installing & Activating recommended plugins…")
              .addClass("processing-spinner");

        $.post(ecommerce_gift_cart_localize.ajax_url, {
            action: "ecommerce_gift_cart_install_and_activate_plugins",
            nonce: ecommerce_gift_cart_localize.nonce
        }, function (response) {
            if (response.success) {
                window.location.href = ecommerce_gift_cart_localize.redirect_url;
            } else {
                button.text(response.data?.message || "Installation failed");
            }
        });
    });

})(jQuery);
