<?php 
/**
 * Default Product Section
 */
return array(
	'title'      => esc_html__( 'Product Section', 'ecommerce-gift-cart' ),
	'categories' => array( 'ecommerce-gift-cart', 'Product Section' ),
	'content'    => '<!-- wp:group {"className":"latest-product","layout":{"type":"constrained","contentSize":"80%"}} -->
<div class="wp-block-group latest-product"><!-- wp:spacer {"height":"50px"} -->
<div style="height:50px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:heading {"textAlign":"center","level":3,"className":"wow fadeInUp","style":{"typography":{"fontStyle":"normal","fontWeight":"500","textTransform":"uppercase"},"elements":{"link":{"color":{"text":"var(--wp--preset--color--extra-secondary)"}}},"color":{"text":"var(--wp--preset--color--extra-secondary)"}},"fontSize":"small","fontFamily":"ecommerce-gift-cart-roboto"} -->
<h3 class="wp-block-heading has-text-align-center wow fadeInUp has-text-color has-link-color has-ecommerce-gift-cart-roboto-font-family has-small-font-size" style="color:var(--wp--preset--color--extra-secondary);font-style:normal;font-weight:500;text-transform:uppercase">Shop online</h3>
<!-- /wp:heading -->

<!-- wp:heading {"textAlign":"center","className":"wow fadeInUp","style":{"typography":{"fontSize":"35px","lineHeight":"1.5","fontStyle":"normal","fontWeight":"500"},"elements":{"link":{"color":{"text":"#2a0502"}}},"color":{"text":"#2a0502"}},"fontFamily":"ecommerce-gift-cart-roboto"} -->
<h2 class="wp-block-heading has-text-align-center wow fadeInUp has-text-color has-link-color has-ecommerce-gift-cart-roboto-font-family" style="color:#2a0502;font-size:35px;font-style:normal;font-weight:500;line-height:1.5">Our Latest Products</h2>
<!-- /wp:heading -->

<!-- wp:columns {"verticalAlignment":"center","className":"product-col wow fadeInUp"} -->
<div class="wp-block-columns are-vertically-aligned-center product-col wow fadeInUp"><!-- wp:column {"verticalAlignment":"center","style":{"color":{"background":"#ededef"},"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}}} -->
<div class="wp-block-column is-vertically-aligned-center has-background" style="background-color:#ededef;padding-top:var(--wp--preset--spacing--60);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--60);padding-left:var(--wp--preset--spacing--40)"><!-- wp:image {"id":23,"sizeSlug":"full","linkDestination":"none","align":"center"} -->
<figure class="wp-block-image aligncenter size-full"><img src="' . esc_url( get_theme_file_uri( '/assets/images/product1.png' ) ) .'" alt="" class="wp-image-23"/></figure>
<!-- /wp:image -->

<!-- wp:heading {"textAlign":"center","level":3,"style":{"color":{"text":"#2a0502"},"elements":{"link":{"color":{"text":"#2a0502"}}},"typography":{"fontStyle":"normal","fontWeight":"400","fontSize":"20px"}},"fontFamily":"ecommerce-gift-cart-roboto"} -->
<h3 class="wp-block-heading has-text-align-center has-text-color has-link-color has-ecommerce-gift-cart-roboto-font-family" style="color:#2a0502;font-size:20px;font-style:normal;font-weight:400"><a href="#">Black Smart Watch</a></h3>
<!-- /wp:heading -->

<!-- wp:heading {"textAlign":"center","level":4,"style":{"color":{"text":"var(--wp--preset--color--extra-tertiary)"},"elements":{"link":{"color":{"text":"var(--wp--preset--color--extra-tertiary)"}}},"typography":{"fontStyle":"normal","fontWeight":"400","fontSize":"22px"}},"fontFamily":"ecommerce-gift-cart-roboto"} -->
<h4 class="wp-block-heading has-text-align-center has-text-color has-link-color has-ecommerce-gift-cart-roboto-font-family" style="color:var(--wp--preset--color--extra-tertiary);font-size:22px;font-style:normal;font-weight:400">$50</h4>
<!-- /wp:heading --></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","style":{"color":{"background":"#ededef"},"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}}} -->
<div class="wp-block-column is-vertically-aligned-center has-background" style="background-color:#ededef;padding-top:var(--wp--preset--spacing--60);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--60);padding-left:var(--wp--preset--spacing--40)"><!-- wp:image {"id":27,"sizeSlug":"full","linkDestination":"none","align":"center"} -->
<figure class="wp-block-image aligncenter size-full"><img src="' . esc_url( get_theme_file_uri( '/assets/images/product2.png' ) ) .'" alt="" class="wp-image-27"/></figure>
<!-- /wp:image -->

<!-- wp:heading {"textAlign":"center","level":3,"style":{"color":{"text":"#2a0502"},"elements":{"link":{"color":{"text":"#2a0502"}}},"typography":{"fontStyle":"normal","fontWeight":"400","fontSize":"20px"}},"fontFamily":"ecommerce-gift-cart-roboto"} -->
<h3 class="wp-block-heading has-text-align-center has-text-color has-link-color has-ecommerce-gift-cart-roboto-font-family" style="color:#2a0502;font-size:20px;font-style:normal;font-weight:400"><a href="#">Brown Teddy Bear</a></h3>
<!-- /wp:heading -->

<!-- wp:heading {"textAlign":"center","level":4,"style":{"color":{"text":"var(--wp--preset--color--extra-tertiary)"},"elements":{"link":{"color":{"text":"var(--wp--preset--color--extra-tertiary)"}}},"typography":{"fontStyle":"normal","fontWeight":"400","fontSize":"22px"}},"fontFamily":"ecommerce-gift-cart-roboto"} -->
<h4 class="wp-block-heading has-text-align-center has-text-color has-link-color has-ecommerce-gift-cart-roboto-font-family" style="color:var(--wp--preset--color--extra-tertiary);font-size:22px;font-style:normal;font-weight:400">$30</h4>
<!-- /wp:heading --></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","style":{"color":{"background":"#ededef"},"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}}} -->
<div class="wp-block-column is-vertically-aligned-center has-background" style="background-color:#ededef;padding-top:var(--wp--preset--spacing--60);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--60);padding-left:var(--wp--preset--spacing--40)"><!-- wp:image {"id":28,"sizeSlug":"full","linkDestination":"none","align":"center"} -->
<figure class="wp-block-image aligncenter size-full"><img src="' . esc_url( get_theme_file_uri( '/assets/images/product3.png' ) ) .'" alt="" class="wp-image-28"/></figure>
<!-- /wp:image -->

<!-- wp:heading {"textAlign":"center","level":3,"style":{"color":{"text":"#2a0502"},"elements":{"link":{"color":{"text":"#2a0502"}}},"typography":{"fontStyle":"normal","fontWeight":"400","fontSize":"20px"}},"fontFamily":"ecommerce-gift-cart-roboto"} -->
<h3 class="wp-block-heading has-text-align-center has-text-color has-link-color has-ecommerce-gift-cart-roboto-font-family" style="color:#2a0502;font-size:20px;font-style:normal;font-weight:400"><a href="#">Christmas Gift Box</a></h3>
<!-- /wp:heading -->

<!-- wp:heading {"textAlign":"center","level":4,"style":{"color":{"text":"var(--wp--preset--color--extra-tertiary)"},"elements":{"link":{"color":{"text":"var(--wp--preset--color--extra-tertiary)"}}},"typography":{"fontStyle":"normal","fontWeight":"400","fontSize":"22px"}},"fontFamily":"ecommerce-gift-cart-roboto"} -->
<h4 class="wp-block-heading has-text-align-center has-text-color has-link-color has-ecommerce-gift-cart-roboto-font-family" style="color:var(--wp--preset--color--extra-tertiary);font-size:22px;font-style:normal;font-weight:400">$100</h4>
<!-- /wp:heading --></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","style":{"color":{"background":"#ededef"},"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}}} -->
<div class="wp-block-column is-vertically-aligned-center has-background" style="background-color:#ededef;padding-top:var(--wp--preset--spacing--60);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--60);padding-left:var(--wp--preset--spacing--40)"><!-- wp:image {"id":29,"sizeSlug":"full","linkDestination":"none","align":"center"} -->
<figure class="wp-block-image aligncenter size-full"><img src="' . esc_url( get_theme_file_uri( '/assets/images/product4.png' ) ) .'" alt="" class="wp-image-29"/></figure>
<!-- /wp:image -->

<!-- wp:heading {"textAlign":"center","level":3,"style":{"color":{"text":"#2a0502"},"elements":{"link":{"color":{"text":"#2a0502"}}},"typography":{"fontStyle":"normal","fontWeight":"400","fontSize":"20px"}},"fontFamily":"ecommerce-gift-cart-roboto"} -->
<h3 class="wp-block-heading has-text-align-center has-text-color has-link-color has-ecommerce-gift-cart-roboto-font-family" style="color:#2a0502;font-size:20px;font-style:normal;font-weight:400"><a href="#">Cute mini Bag</a></h3>
<!-- /wp:heading -->

<!-- wp:heading {"textAlign":"center","level":4,"style":{"color":{"text":"var(--wp--preset--color--extra-tertiary)"},"elements":{"link":{"color":{"text":"var(--wp--preset--color--extra-tertiary)"}}},"typography":{"fontStyle":"normal","fontWeight":"400","fontSize":"22px"}},"fontFamily":"ecommerce-gift-cart-roboto"} -->
<h4 class="wp-block-heading has-text-align-center has-text-color has-link-color has-ecommerce-gift-cart-roboto-font-family" style="color:var(--wp--preset--color--extra-tertiary);font-size:22px;font-style:normal;font-weight:400">$50</h4>
<!-- /wp:heading --></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->

<!-- wp:columns {"verticalAlignment":"center","className":"product-col wow fadeInUp"} -->
<div class="wp-block-columns are-vertically-aligned-center product-col wow fadeInUp"><!-- wp:column {"verticalAlignment":"center","style":{"color":{"background":"#ededef"},"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}}} -->
<div class="wp-block-column is-vertically-aligned-center has-background" style="background-color:#ededef;padding-top:var(--wp--preset--spacing--60);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--60);padding-left:var(--wp--preset--spacing--40)"><!-- wp:image {"id":31,"sizeSlug":"full","linkDestination":"none","align":"center"} -->
<figure class="wp-block-image aligncenter size-full"><img src="' . esc_url( get_theme_file_uri( '/assets/images/product5.png' ) ) .'" alt="" class="wp-image-31"/></figure>
<!-- /wp:image -->

<!-- wp:heading {"textAlign":"center","level":3,"style":{"color":{"text":"#2a0502"},"elements":{"link":{"color":{"text":"#2a0502"}}},"typography":{"fontStyle":"normal","fontWeight":"400","fontSize":"20px"}},"fontFamily":"ecommerce-gift-cart-roboto"} -->
<h3 class="wp-block-heading has-text-align-center has-text-color has-link-color has-ecommerce-gift-cart-roboto-font-family" style="color:#2a0502;font-size:20px;font-style:normal;font-weight:400"><a href="#">iPhone Case</a></h3>
<!-- /wp:heading -->

<!-- wp:heading {"textAlign":"center","level":4,"style":{"color":{"text":"var(--wp--preset--color--extra-tertiary)"},"elements":{"link":{"color":{"text":"var(--wp--preset--color--extra-tertiary)"}}},"typography":{"fontStyle":"normal","fontWeight":"400","fontSize":"22px"}},"fontFamily":"ecommerce-gift-cart-roboto"} -->
<h4 class="wp-block-heading has-text-align-center has-text-color has-link-color has-ecommerce-gift-cart-roboto-font-family" style="color:var(--wp--preset--color--extra-tertiary);font-size:22px;font-style:normal;font-weight:400">$50</h4>
<!-- /wp:heading --></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","style":{"color":{"background":"#ededef"},"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}}} -->
<div class="wp-block-column is-vertically-aligned-center has-background" style="background-color:#ededef;padding-top:var(--wp--preset--spacing--60);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--60);padding-left:var(--wp--preset--spacing--40)"><!-- wp:image {"id":32,"sizeSlug":"full","linkDestination":"none","align":"center"} -->
<figure class="wp-block-image aligncenter size-full"><img src="' . esc_url( get_theme_file_uri( '/assets/images/product6.png' ) ) .'" alt="" class="wp-image-32"/></figure>
<!-- /wp:image -->

<!-- wp:heading {"textAlign":"center","level":3,"style":{"color":{"text":"#2a0502"},"elements":{"link":{"color":{"text":"#2a0502"}}},"typography":{"fontStyle":"normal","fontWeight":"400","fontSize":"20px"}},"fontFamily":"ecommerce-gift-cart-roboto"} -->
<h3 class="wp-block-heading has-text-align-center has-text-color has-link-color has-ecommerce-gift-cart-roboto-font-family" style="color:#2a0502;font-size:20px;font-style:normal;font-weight:400"><a href="#">Stuffed Animal Toy</a></h3>
<!-- /wp:heading -->

<!-- wp:heading {"textAlign":"center","level":4,"style":{"color":{"text":"var(--wp--preset--color--extra-tertiary)"},"elements":{"link":{"color":{"text":"var(--wp--preset--color--extra-tertiary)"}}},"typography":{"fontStyle":"normal","fontWeight":"400","fontSize":"22px"}},"fontFamily":"ecommerce-gift-cart-roboto"} -->
<h4 class="wp-block-heading has-text-align-center has-text-color has-link-color has-ecommerce-gift-cart-roboto-font-family" style="color:var(--wp--preset--color--extra-tertiary);font-size:22px;font-style:normal;font-weight:400">$10</h4>
<!-- /wp:heading --></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","style":{"color":{"background":"#ededef"},"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}}} -->
<div class="wp-block-column is-vertically-aligned-center has-background" style="background-color:#ededef;padding-top:var(--wp--preset--spacing--60);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--60);padding-left:var(--wp--preset--spacing--40)"><!-- wp:image {"id":33,"sizeSlug":"full","linkDestination":"none","align":"center"} -->
<figure class="wp-block-image aligncenter size-full"><img src="' . esc_url( get_theme_file_uri( '/assets/images/product7.png' ) ) .'" alt="" class="wp-image-33"/></figure>
<!-- /wp:image -->

<!-- wp:heading {"textAlign":"center","level":3,"style":{"color":{"text":"#2a0502"},"elements":{"link":{"color":{"text":"#2a0502"}}},"typography":{"fontStyle":"normal","fontWeight":"400","fontSize":"20px"}},"fontFamily":"ecommerce-gift-cart-roboto"} -->
<h3 class="wp-block-heading has-text-align-center has-text-color has-link-color has-ecommerce-gift-cart-roboto-font-family" style="color:#2a0502;font-size:20px;font-style:normal;font-weight:400"><a href="#">Water Bottle</a></h3>
<!-- /wp:heading -->

<!-- wp:heading {"textAlign":"center","level":4,"style":{"color":{"text":"var(--wp--preset--color--extra-tertiary)"},"elements":{"link":{"color":{"text":"var(--wp--preset--color--extra-tertiary)"}}},"typography":{"fontStyle":"normal","fontWeight":"400","fontSize":"22px"}},"fontFamily":"ecommerce-gift-cart-roboto"} -->
<h4 class="wp-block-heading has-text-align-center has-text-color has-link-color has-ecommerce-gift-cart-roboto-font-family" style="color:var(--wp--preset--color--extra-tertiary);font-size:22px;font-style:normal;font-weight:400">$75</h4>
<!-- /wp:heading --></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","style":{"color":{"background":"#ededef"},"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}}} -->
<div class="wp-block-column is-vertically-aligned-center has-background" style="background-color:#ededef;padding-top:var(--wp--preset--spacing--60);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--60);padding-left:var(--wp--preset--spacing--40)"><!-- wp:image {"id":34,"sizeSlug":"full","linkDestination":"none","align":"center"} -->
<figure class="wp-block-image aligncenter size-full"><img src="' . esc_url( get_theme_file_uri( '/assets/images/product8.png' ) ) .'" alt="" class="wp-image-34"/></figure>
<!-- /wp:image -->

<!-- wp:heading {"textAlign":"center","level":3,"style":{"color":{"text":"#2a0502"},"elements":{"link":{"color":{"text":"#2a0502"}}},"typography":{"fontStyle":"normal","fontWeight":"400","fontSize":"20px"}},"fontFamily":"ecommerce-gift-cart-roboto"} -->
<h3 class="wp-block-heading has-text-align-center has-text-color has-link-color has-ecommerce-gift-cart-roboto-font-family" style="color:#2a0502;font-size:20px;font-style:normal;font-weight:400"><a href="#"></a><a href="#">Watermelon Sipper</a></h3>
<!-- /wp:heading -->

<!-- wp:heading {"textAlign":"center","level":4,"style":{"color":{"text":"var(--wp--preset--color--extra-tertiary)"},"elements":{"link":{"color":{"text":"var(--wp--preset--color--extra-tertiary)"}}},"typography":{"fontStyle":"normal","fontWeight":"400","fontSize":"22px"}},"fontFamily":"ecommerce-gift-cart-roboto"} -->
<h4 class="wp-block-heading has-text-align-center has-text-color has-link-color has-ecommerce-gift-cart-roboto-font-family" style="color:var(--wp--preset--color--extra-tertiary);font-size:22px;font-style:normal;font-weight:400">$80</h4>
<!-- /wp:heading --></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->

<!-- wp:spacer {"height":"50px"} -->
<div style="height:50px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer --></div>
<!-- /wp:group -->',
);