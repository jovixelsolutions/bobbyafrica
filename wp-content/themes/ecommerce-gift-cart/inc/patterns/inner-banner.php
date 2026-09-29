<?php 
/**
 * Header Inner Banner
 */
return array(
	'title'      => esc_html__( 'Inner Banner', 'ecommerce-gift-cart' ),
	'categories' => array( 'ecommerce-gift-cart', 'Inner Banner' ),
	'content'    => '<!-- wp:cover {"url":"' . esc_url( get_theme_file_uri( '/assets/images/sale2.png' ) ) .'","id":136,"dimRatio":20,"overlayColor":"background","isUserOverlayColor":true,"minHeight":450,"minHeightUnit":"px","isDark":false,"sizeSlug":"large","className":"banner-wrap","style":{"spacing":{"padding":{"top":"0","right":"0","bottom":"0","left":"0"},"margin":{"top":"0","bottom":"0"}}}} -->
<div class="wp-block-cover is-light banner-wrap" style="margin-top:0;margin-bottom:0;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0;min-height:450px"><img class="wp-block-cover__image-background wp-image-136 size-large" alt="" src="' . esc_url( get_theme_file_uri( '/assets/images/sale2.png' ) ) .'" data-object-fit="cover"/><span aria-hidden="true" class="wp-block-cover__background has-background-background-color has-background-dim-20 has-background-dim"></span><div class="wp-block-cover__inner-container"><!-- wp:post-title {"textAlign":"center","style":{"typography":{"fontSize":"60px"},"elements":{"link":{"color":{"text":"var:preset|color|foreground"}}}},"textColor":"foreground"} /-->

<!-- wp:group {"className":"metas-main","layout":{"type":"constrained","contentSize":"80%"}} -->
<div class="wp-block-group metas-main"><!-- wp:group {"className":"meta-div","style":{"elements":{"link":{"color":{"text":"var:preset|color|primary"}}}},"textColor":"primary","fontSize":"upper-heading","fontFamily":"","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"center"}} -->
<div class="wp-block-group meta-div has-primary-color has-text-color has-link-color has-upper-heading-font-size"><!-- wp:post-author {"showAvatar":false,"className":"meta-author","style":{"layout":{"selfStretch":"fit","flexSize":null},"elements":{"link":{"color":{"text":"var:preset|color|foreground"}}},"typography":{"fontSize":"16px"}},"textColor":"foreground"} /-->

<!-- wp:paragraph {"style":{"elements":{"link":{"color":{"text":"var:preset|color|foreground"}}}},"textColor":"foreground"} -->
<p class="has-foreground-color has-text-color has-link-color">| </p>
<!-- /wp:paragraph -->

<!-- wp:post-date {"metadata":{"bindings":{"datetime":{"source":"core/post-data","args":{"field":"date"}}}},"className":"meta-date","style":{"elements":{"link":{"color":{"text":"var:preset|color|foreground"}}},"typography":{"fontSize":"16px"}},"textColor":"foreground"} /-->

<!-- wp:paragraph {"style":{"elements":{"link":{"color":{"text":"var:preset|color|foreground"}}}},"textColor":"foreground"} -->
<p class="has-foreground-color has-text-color has-link-color">| </p>
<!-- /wp:paragraph -->

<!-- wp:post-terms {"term":"category","className":"meta-category","style":{"elements":{"link":{"color":{"text":"var:preset|color|foreground"}}},"typography":{"fontSize":"16px"}},"textColor":"foreground"} /--></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div></div>
<!-- /wp:cover -->',
);