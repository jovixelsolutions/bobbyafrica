<?php
/**
 * Latest Blogs
 */
return array(
	'title'      => esc_html__( 'Latest Blogs', 'ecommerce-gift-cart' ),
	'categories' => array( 'ecommerce-gift-cart', 'Latest Blogs' ),
	'content'    => '<!-- wp:group {"style":{"spacing":{"margin":{"top":"0","bottom":"0"}},"color":{"background":"#ededef"}}} -->
<div class="wp-block-group has-background" style="background-color:#ededef;margin-top:0;margin-bottom:0"><!-- wp:spacer {"height":"50px"} -->
<div style="height:50px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:heading {"textAlign":"center","level":3,"className":"wow fadeInUp","style":{"typography":{"fontSize":"20px","fontStyle":"normal","fontWeight":"500","letterSpacing":"2px"},"elements":{"link":{"color":{"text":"var(--wp--preset--color--extra-secondary)"}}},"color":{"text":"var(--wp--preset--color--extra-secondary)"}},"fontFamily":"ecommerce-gift-cart-roboto"} -->
<h3 class="wp-block-heading has-text-align-center wow fadeInUp has-text-color has-link-color has-ecommerce-gift-cart-roboto-font-family" style="color:var(--wp--preset--color--extra-secondary);font-size:20px;font-style:normal;font-weight:500;letter-spacing:2px">HANDMADE SHOP</h3>
<!-- /wp:heading -->

<!-- wp:heading {"textAlign":"center","level":3,"className":"wow fadeInUp","style":{"typography":{"fontSize":"40px","lineHeight":"1.3","fontStyle":"normal","fontWeight":"600"},"elements":{"link":{"color":{"text":"var:preset|color|background"}}}},"textColor":"background","fontFamily":"ecommerce-gift-cart-roboto"} -->
<h3 class="wp-block-heading has-text-align-center wow fadeInUp has-background-color has-text-color has-link-color has-ecommerce-gift-cart-roboto-font-family" style="font-size:40px;font-style:normal;font-weight:600;line-height:1.3">Find Something You Love</h3>
<!-- /wp:heading -->

<!-- wp:query {"queryId":38,"query":{"perPage":10,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"exclude","inherit":true},"metadata":{"categories":["posts"],"patternName":"core/query-grid-posts","name":"Grid"}} -->
<div class="wp-block-query"><!-- wp:group {"layout":{"type":"constrained","contentSize":"80%"}} -->
<div class="wp-block-group"><!-- wp:query {"queryId":59,"query":{"perPage":12,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":false,"parents":[],"format":[]},"metadata":{"categories":["posts"],"patternName":"core/query-grid-posts","name":"Grid"},"className":"blog-area","layout":{"type":"default"}} -->
<div class="wp-block-query blog-area"><!-- wp:post-template {"layout":{"type":"grid","columnCount":3,"minimumColumnWidth":null}} -->
<!-- wp:group {"className":"post-main-area wow fadeInUp","style":{"spacing":{"padding":{"top":"30px","right":"30px","bottom":"30px","left":"30px"}},"border":{"radius":"10px","color":"#e7e7e7","width":"1px"}},"backgroundColor":"foreground","layout":{"inherit":false}} -->
<div class="wp-block-group post-main-area wow fadeInUp has-border-color has-foreground-background-color has-background" style="border-color:#e7e7e7;border-width:1px;border-radius:10px;padding-top:30px;padding-right:30px;padding-bottom:30px;padding-left:30px"><!-- wp:post-title {"isLink":true,"style":{"typography":{"fontSize":"22px"},"elements":{"link":{"color":{"text":"var:preset|color|primary"}}}},"textColor":"primary","fontFamily":"ecommerce-gift-cart-roboto"} /-->

<!-- wp:post-excerpt {"excerptLength":20,"style":{"elements":{"link":{"color":{"text":"#747474"}}},"color":{"text":"#747474"},"typography":{"fontSize":"16px"}},"fontFamily":"ecommerce-gift-cart-roboto"} /-->

<!-- wp:post-date {"style":{"elements":{"link":{"color":{"text":"var(--wp--preset--color--extra-secondary)"}}},"color":{"text":"var(--wp--preset--color--extra-secondary)"}},"fontFamily":"ecommerce-gift-cart-roboto"} /--></div>
<!-- /wp:group -->
<!-- /wp:post-template --></div>
<!-- /wp:query --></div>
<!-- /wp:group --></div>
<!-- /wp:query -->

<!-- wp:spacer {"height":"50px"} -->
<div style="height:50px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer --></div>
<!-- /wp:group -->',
);