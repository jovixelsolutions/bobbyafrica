<?php 
/**
 * Default About Section
 */
return array(
	'title'      => esc_html__( 'About Section', 'ecommerce-gift-cart' ),
	'categories' => array( 'ecommerce-gift-cart', 'About Section' ),
	'content'    => '<!-- wp:group {"className":"testimonial-box","style":{"spacing":{"margin":{"top":"0","bottom":"0"},"padding":{"top":"var:preset|spacing|40","bottom":"0"}}},"layout":{"type":"constrained","contentSize":"80%"}} -->
<div class="wp-block-group testimonial-box" style="margin-top:0;margin-bottom:0;padding-top:var(--wp--preset--spacing--40);padding-bottom:0"><!-- wp:columns {"verticalAlignment":"center"} -->
<div class="wp-block-columns are-vertically-aligned-center"><!-- wp:column {"verticalAlignment":"center","width":"50%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:50%"><!-- wp:image {"id":40,"sizeSlug":"full","linkDestination":"none","className":"wow fadeInUp"} -->
<figure class="wp-block-image size-full wow fadeInUp"><img src="' . esc_url( get_theme_file_uri( '/assets/images/about-img.png' ) ) . '" alt="" class="wp-image-40"/></figure>
<!-- /wp:image --></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","width":"50%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:50%"><!-- wp:paragraph {"className":"wow fadeInUp","style":{"color":{"text":"var(--wp--preset--color--extra-secondary)"},"elements":{"link":{"color":{"text":"var(--wp--preset--color--extra-secondary)"}}},"typography":{"fontSize":"18px"}}} -->
<p class="wow fadeInUp has-text-color has-link-color" style="color:var(--wp--preset--color--extra-secondary);font-size:18px">WELCOME TO GIFT HEART</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"className":"wow fadeInUp","style":{"typography":{"fontSize":"45px","fontStyle":"normal","fontWeight":"500"}},"fontFamily":"ecommerce-gift-cart-roboto"} -->
<h2 class="wp-block-heading wow fadeInUp has-ecommerce-gift-cart-roboto-font-family" style="font-size:45px;font-style:normal;font-weight:500">Find The Perfect Gift,<br>Every Time</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"wow fadeInUp","fontFamily":"ecommerce-gift-cart-roboto"} -->
<p class="wow fadeInUp has-ecommerce-gift-cart-roboto-font-family">Quisque at porttitor magna. Nam nisi nulla, lobortis non porta sed, pulv<br>inara est. Morbi luctus hendrerit leo, sed facilisis ante dignissi eget.<br>Phas ellus lacus ipsum, cursus et rhoncus nec, dapibus et erat. Cras<br>dictum tel lus sit ametting neque finibus, nec malesuada tell ornare.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"wow fadeInUp","fontFamily":"ecommerce-gift-cart-roboto"} -->
<p class="wow fadeInUp has-ecommerce-gift-cart-roboto-font-family">Phas ellus lacus ipsum, cursus et rhoncus nec, dapibus et erat. Cras<br>dictum tel lus sit ametting neque finibus, nec malesuada tell ornare.</p>
<!-- /wp:paragraph -->

<!-- wp:columns {"verticalAlignment":"center","className":"product-col"} -->
<div class="wp-block-columns are-vertically-aligned-center product-col"><!-- wp:column {"verticalAlignment":"center"} -->
<div class="wp-block-column is-vertically-aligned-center"><!-- wp:paragraph {"fontFamily":"ecommerce-gift-cart-roboto"} -->
<p class="has-ecommerce-gift-cart-roboto-font-family">Classic Decorative Gift</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"fontFamily":"ecommerce-gift-cart-roboto"} -->
<p class="has-ecommerce-gift-cart-roboto-font-family">Stylish Dad Accessory</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"fontFamily":"ecommerce-gift-cart-roboto"} -->
<p class="has-ecommerce-gift-cart-roboto-font-family">Discounted Gift Cards</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center"} -->
<div class="wp-block-column is-vertically-aligned-center"><!-- wp:paragraph {"fontFamily":"ecommerce-gift-cart-roboto"} -->
<p class="has-ecommerce-gift-cart-roboto-font-family">Paper Gift Box</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"fontFamily":"ecommerce-gift-cart-roboto"} -->
<p class="has-ecommerce-gift-cart-roboto-font-family">Debit/Credit Cards</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"fontFamily":"ecommerce-gift-cart-roboto"} -->
<p class="has-ecommerce-gift-cart-roboto-font-family">Goods &amp; Services</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"textColor":"background","style":{"color":{"background":"var(--wp--preset--color--extra-primary)"},"elements":{"link":{"color":{"text":"var:preset|color|background"}}}}} -->
<div class="wp-block-button"><a class="wp-block-button__link has-background-color has-text-color has-background has-link-color wp-element-button" style="background-color:var(--wp--preset--color--extra-primary)">Read More</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->',
);