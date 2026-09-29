<?php
/**
 * Post One Column
 */
return array(
	'title'      => esc_html__( 'Post One Column', 'ecommerce-gift-cart' ),
	'categories' => array( 'ecommerce-gift-cart', 'Post One Column' ),
	'content'    => '<!-- wp:group {"tagName":"main","style":{"color":{"background":"#f4f4f4"},"spacing":{"margin":{"top":"0","bottom":"0"}}}} -->
<main class="wp-block-group has-background" style="background-color:#f4f4f4;margin-top:0;margin-bottom:0"><!-- wp:spacer {"height":"50px"} -->
<div style="height:50px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:query {"queryId":38,"query":{"perPage":10,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":false},"metadata":{"categories":["posts"],"patternName":"core/query-grid-posts","name":"Grid"}} -->
<div class="wp-block-query">

<!-- wp:group {"layout":{"type":"constrained","contentSize":"80%"}} -->
<div class="wp-block-group"><!-- wp:query {"queryId":59,"query":{"perPage":10,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date"},"metadata":{"categories":["posts"],"patternName":"core/query-grid-posts","name":"Grid"},"className":"blog-area","layout":{"type":"default"}} -->
<div class="wp-block-query blog-area"><!-- wp:post-template {"style":{"border":{"radius":{"topLeft":"20px","bottomRight":"20px"}}},"layout":{"type":"grid","columnCount":1}} -->
<!-- wp:group {"className":"post-main-area wow fadeInUp","style":{"spacing":{"padding":{"top":"30px","right":"30px","bottom":"30px","left":"30px"}},"border":{"radius":{"topLeft":"30px","topRight":"0px","bottomLeft":"0px","bottomRight":"30px"},"color":"#e7e7e7","width":"1px"}},"backgroundColor":"foreground","layout":{"inherit":false}} -->
<div class="wp-block-group post-main-area wow fadeInUp has-border-color has-foreground-background-color has-background" style="border-color:#e7e7e7;border-width:1px;border-top-left-radius:30px;border-top-right-radius:0px;border-bottom-left-radius:0px;border-bottom-right-radius:30px;padding-top:30px;padding-right:30px;padding-bottom:30px;padding-left:30px"><!-- wp:post-featured-image {"isLink":true,"style":{"border":{"radius":"10px"},"spacing":{"margin":{"bottom":"20px"}}}} /-->

<!-- wp:post-title {"isLink":true,"style":{"typography":{"fontSize":"22px"},"elements":{"link":{"color":{"text":"var:preset|color|primary"}}}},"textColor":"primary","fontFamily":"ecommerce-gift-cart-inter"} /-->

<!-- wp:post-excerpt {"excerptLength":20,"style":{"elements":{"link":{"color":{"text":"#747474"}}},"color":{"text":"#747474"},"typography":{"fontSize":"16px"}},"fontFamily":"ecommerce-gift-cart-inter"} /-->

<!-- wp:group {"style":{"spacing":{"margin":{"top":"10px"}}},"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"left"}} -->
<div class="wp-block-group" style="margin-top:10px"><!-- wp:post-author {"showAvatar":false,"style":{"typography":{"fontSize":"14px"},"color":{"text":"#666666"},"spacing":{"margin":{"right":"15px"}}}} /-->

<!-- wp:post-date {"style":{"typography":{"fontSize":"14px"},"color":{"text":"#666666"},"spacing":{"margin":{"right":"15px"}}}} /--></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
<!-- /wp:post-template -->

<!-- wp:query-pagination {"className":"post-pgntion","style":{"elements":{"link":{"color":{"text":"var:preset|color|primary"}}}},"textColor":"primary"} -->
<!-- wp:query-pagination-previous /-->

<!-- wp:query-pagination-numbers /-->

<!-- wp:query-pagination-next /-->
<!-- /wp:query-pagination --></div>
<!-- /wp:query --></div>
<!-- /wp:group --></div>
<!-- /wp:query -->

<!-- wp:spacer {"height":"50px"} -->
<div style="height:50px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer --></main>
<!-- /wp:group -->',
);