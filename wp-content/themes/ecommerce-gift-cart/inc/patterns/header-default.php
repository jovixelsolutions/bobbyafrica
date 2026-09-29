<?php 
/**
 * Default Header
 */

$content = '<!-- wp:group {"className":"main-header","style":{"spacing":{"padding":{"right":"0","left":"0","top":"15px","bottom":"15px"}}},"backgroundColor":"background","layout":{"type":"constrained","contentSize":"80%"}} -->
<div class="wp-block-group main-header has-background-background-color has-background" style="padding-top:15px;padding-right:0;padding-bottom:15px;padding-left:0"><!-- wp:columns {"verticalAlignment":"center"} -->
<div class="wp-block-columns are-vertically-aligned-center"><!-- wp:column {"verticalAlignment":"center","width":"30%","className":"shortcode-top"} -->
<div class="wp-block-column is-vertically-aligned-center shortcode-top" style="flex-basis:30%"><!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap"}} -->
<div class="wp-block-group"><!-- wp:shortcode -->
[gtranslate]
<!-- /wp:shortcode -->

<!-- wp:shortcode -->
[woocs]
<!-- /wp:shortcode --></div>
<!-- /wp:group --></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","width":"60%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:60%"><!-- wp:navigation {"textColor":"foreground","overlayTextColor":"primary","metadata":{"ignoredHookedBlocks":["woocommerce/customer-account"]},"style":{"typography":{"fontStyle":"normal","fontWeight":"400","letterSpacing":"0px","textTransform":"capitalize","fontSize":"15px"}},"fontFamily":"ecommerce-gift-cart-roboto","layout":{"type":"flex","justifyContent":"center"}} -->
<!-- wp:navigation-link {"label":"Home","type":"","url":"#","kind":"custom","isTopLevelLink":true} /-->

<!-- wp:navigation-link {"label":"About","type":"","url":"#","kind":"custom","isTopLevelLink":true} /-->

<!-- wp:navigation-link {"label":"Blog","type":"","url":"#","kind":"custom","isTopLevelLink":true} /-->

<!-- wp:navigation-link {"label":"Shop","type":"","url":"#","kind":"custom","isTopLevelLink":true} /-->

<!-- wp:navigation-link {"label":"Template","type":"","url":"#","kind":"custom","isTopLevelLink":true} /-->

<!-- wp:navigation-link {"label":"Gallery","type":"","url":"#","kind":"custom","isTopLevelLink":true} /-->

<!-- wp:navigation-link {"label":"Contact","type":"","url":"#","kind":"custom","isTopLevelLink":true} /-->

<!-- wp:navigation-link {"label":"Buy Now","type":"link","opensInNewTab":true,"url":"' . esc_url( ECOMMERCE_GIFT_CART_BUY_NOW ) . '","kind":"custom","className":"buy-now-button"} /-->
<!-- /wp:navigation --></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","width":"10%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:10%"><!-- wp:social-links {"iconColor":"foreground","iconColorValue":"#ffffff","size":"has-normal-icon-size","className":"is-style-logos-only","style":{"spacing":{"blockGap":{"left":"10px"}}},"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"right"}} -->
<ul class="wp-block-social-links has-normal-icon-size has-icon-color is-style-logos-only"><!-- wp:social-link {"url":"#","service":"facebook"} /-->

<!-- wp:social-link {"url":"#","service":"instagram"} /-->

<!-- wp:social-link {"url":"#","service":"x"} /--></ul>
<!-- /wp:social-links --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->

<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|30","bottom":"var:preset|spacing|30"}}},"layout":{"type":"constrained","contentSize":"80%"}} -->
<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--30);padding-bottom:var(--wp--preset--spacing--30)"><!-- wp:columns {"verticalAlignment":"center"} -->
<div class="wp-block-columns are-vertically-aligned-center"><!-- wp:column {"verticalAlignment":"center","width":"20%","className":"shipping-box columnn-1","style":{"border":{"width":"0px","style":"none"}}} -->
<div class="wp-block-column is-vertically-aligned-center shipping-box columnn-1" style="border-style:none;border-width:0px;flex-basis:20%"><!-- wp:group {"className":"logo-box","style":{"border":{"width":"0px","style":"none"}},"layout":{"type":"flex","flexWrap":"nowrap"}} -->
<div class="wp-block-group logo-box" style="border-style:none;border-width:0px"><!-- wp:site-logo {"width":80,"shouldSyncIcon":true} /-->

<!-- wp:site-title {"style":{"elements":{"link":{"color":{"text":"var:preset|color|background"}}},"typography":{"fontSize":"25px","fontStyle":"normal","fontWeight":"700"}},"textColor":"background","fontFamily":"ecommerce-gift-cart-roboto"} /--></div>
<!-- /wp:group --></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","width":"40%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:40%"><!-- wp:group {"className":"header-search","style":{"color":{"background":"#f2f2f2"},"border":{"radius":{"topLeft":"30px","topRight":"30px","bottomLeft":"30px","bottomRight":"30px"}},"spacing":{"padding":{"top":"10px","bottom":"10px","left":"20px","right":"20px"}}},"layout":{"type":"flex","flexWrap":"nowrap"}} -->
<div class="wp-block-group header-search has-background" style="border-top-left-radius:30px;border-top-right-radius:30px;border-bottom-left-radius:30px;border-bottom-right-radius:30px;background-color:#f2f2f2;padding-top:10px;padding-right:20px;padding-bottom:10px;padding-left:20px"><!-- wp:search {"label":"Search","showLabel":false,"placeholder":"Search......","widthUnit":"%","buttonText":"Search","buttonPosition":"button-inside","buttonUseIcon":true,"className":"header-search-box","style":{"spacing":{"margin":{"top":"0","bottom":"0","left":"0","right":"0"}},"elements":{"link":{"color":{"text":"var:preset|color|background"}}},"color":{"background":"#00000000"}},"textColor":"background"} /-->
';

if (class_exists('WooCommerce')) {
	$content .= '
<!-- wp:categories {"taxonomy":"product_cat","displayAsDropdown":true,"showLabel":false} /-->
';
}

$content .= '
</div><!-- /wp:group --></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","width":"25%","className":"shipping-box columnn-3","style":{"border":{"width":"0px","style":"none"}}} -->
<div class="wp-block-column is-vertically-aligned-center shipping-box columnn-3" style="border-style:none;border-width:0px;flex-basis:25%"><!-- wp:columns {"verticalAlignment":"center"} -->
<div class="wp-block-columns are-vertically-aligned-center"><!-- wp:column {"verticalAlignment":"center","width":"30%","className":"contact-icon"} -->
<div class="wp-block-column is-vertically-aligned-center contact-icon" style="flex-basis:30%"><!-- wp:html -->
<i class="fas fa-phone"></i>
<!-- /wp:html --></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","width":"70%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:70%"><!-- wp:paragraph {"style":{"color":{"text":"#929191"},"elements":{"link":{"color":{"text":"#929191"}}},"typography":{"fontSize":"14px","fontStyle":"normal","fontWeight":"400"}},"fontFamily":"ecommerce-gift-cart-roboto"} -->
<p class="has-text-color has-link-color has-ecommerce-gift-cart-roboto-font-family" style="color:#929191;font-size:14px;font-style:normal;font-weight:400">Call Us</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"style":{"elements":{"link":{"color":{"text":"var:preset|color|background"}}},"typography":{"fontStyle":"normal","fontWeight":"700","fontSize":"18px"},"spacing":{"margin":{"top":"0","bottom":"0"}}},"textColor":"background","fontFamily":"ecommerce-gift-cart-roboto"} -->
<p class="has-background-color has-text-color has-link-color has-ecommerce-gift-cart-roboto-font-family" style="margin-top:0;margin-bottom:0;font-size:18px;font-style:normal;font-weight:700">+121 365 47890</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","width":"15%","className":"shipping-box columnn-4","style":{"border":{"width":"0px","style":"none"}}} -->
<div class="wp-block-column is-vertically-aligned-center shipping-box columnn-4" style="border-style:none;border-width:0px;flex-basis:15%"><!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"right"}} -->
<div class="wp-block-group"><!-- wp:woocommerce/customer-account {"displayStyle":"icon_only"} /-->

<!-- wp:image {"id":233,"sizeSlug":"full","linkDestination":"none"} -->
<figure class="wp-block-image size-full"><img src="' . esc_url( get_theme_file_uri( '/assets/images/heart.png' ) ) .'" alt="" class="wp-image-233"/></figure>
<!-- /wp:image -->

<!-- wp:woocommerce/mini-cart {"productCountVisibility":"always","className":"header-cart","fontFamily":"ecommerce-gift-cart-roboto","style":{"typography":{"fontWeight":"500","fontStyle":"normal"}}} /--></div>
<!-- /wp:group --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->';

return array(
	'title'      => esc_html__( 'Default Header', 'ecommerce-gift-cart' ),
	'categories' => array( 'ecommerce-gift-cart', 'header' ),
	'content'    => $content,
);