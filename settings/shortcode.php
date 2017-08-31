<?php
//////////////////////////////////////////////////////
// Shortcode [social-mentions show="#something"]
//////////////////////////////////////////////////////

function social_mentions_shortcode( $the_hashtags ) {
	$sm_options = get_option( 'social_mentions_options' );
	$load_masonry = $sm_options['social_mentions_masonry_enabled'];
	$load_fontawesome = $sm_options['social_mentions_fontawesome_enabled'];

	// Load Masonry
	if ( ! empty( $load_masonry ) && 'yes' == $load_masonry ) {
		wp_enqueue_script( 'social-mentions-masonry' );
		wp_enqueue_script( 'social-mentions-imagesloaded' );
	}

	// Load FontAwesome
	if ( ! empty( $load_fontawesome ) && 'yes' == $load_fontawesome ) {
		wp_enqueue_style( 'social-mentions-fontawesome' );
	}

	// create array from hashtags
	$hashtags = explode( ',', $the_hashtags['show'] );
	$remove_hashtag_spaces = str_replace( ' ', '', $hashtags );
	$cleantags = str_replace( '#', '', $remove_hashtag_spaces );

	function social_mentions_find_terms( $cleantags ) {
		$the_terms = array();
		foreach ( $cleantags as $cleantag ) {
			$the_terms[] .= $cleantag;
		}
		return $the_terms;
	}

	$the_posts = new WP_Query(
		array(
			'tax_query' => array(
				array(
					'taxonomy' => 'socment-hashtags',
					'terms' => social_mentions_find_terms( $cleantags ),
					'relation' => 'OR',
					'field' => 'slug',
					'operator' => 'IN',
				),
			),
			'post_status' => 'publish',
			'orderby' => 'DATE',
			'order' => 'DESC',
			'posts_per_page' => '-1',
		)
	);

	$hashtag_css_classes = $sm_options['social_mentions_hashtag_css'];

	if ( $the_posts->have_posts() ) {
		$output = '';
		$output .= '<div id="socment-hashtag-grid">';
		while ( $the_posts->have_posts() ) {
			$the_posts->the_post();

			$hashtag_post_type = get_post_type();
			$hashtag_id = get_the_ID();
			$hashtag_meta_id = get_post_meta( $hashtag_id, 'socment_meta_id', true );
			$hashtag_meta_name = get_post_meta( $hashtag_id, 'socment_meta_name', true );
			$hashtag_meta_username = get_post_meta( $hashtag_id, 'socment_meta_username', true );
			$hashtag_meta_profile_url = get_post_meta( $hashtag_id, 'socment_meta_profile_url', true );
			$hashtag_meta_profile_img = get_post_meta( $hashtag_id, 'socment_meta_profile_img', true );
			$hashtag_meta_img = get_post_meta( $hashtag_id, 'socment_meta_img', true );
			$hashtag_meta_url = get_post_meta( $hashtag_id, 'socment_meta_url', true );
			$hashtag_date = get_the_date();

			$output .= '<div id="socment-hashtag-holder" class="' . $hashtag_css_classes . '">';
			$output .= '<div id="socment-hashtag-header">';
			$output .= '<div id="socment-hashtag-userimg">';
			$output .= '<img alt="" src="' . $hashtag_meta_profile_img . '"/>';
			$output .= '</div>';
			$output .= '<div id="socment-hashtag-username">';
			$output .= '<a href="' . $hashtag_meta_profile_url . '" title="' . $hashtag_meta_username . '">';
			$output .= $hashtag_meta_name . '<br/>(' . $hashtag_meta_username . ')';
			$output .= '</a>';
			$output .= '</div>';
			$output .= '</div>';
			$output .= '<div id="socment-hashtag-content">';
			$output .= '<div id="socment-hashtag-content-text">';
			$output .= get_the_content();
			$output .= '</div>';
			if ( '' != $hashtag_meta_img ) {
				$output .= '<div id="socment-hashtag-img">';
				$output .= '<img alt="' . $hashtag_meta_username . '" src="' . $hashtag_meta_img . '"/>';
				$output .= '</div>';
			}
			$output .= '</div>';
			$output .= '<div id="socment-hashtag-footer">';
			$output .= '<div id="socment-hashtag-readmore">';
			if ( 'socment-twitter' == $hashtag_post_type ) {
				$output .= '<a href="' . $hashtag_meta_url . '">';
				$output .= '<i class="fa fa-twitter"></i>';
				$output .= '</a>';
			} elseif ( 'socment-instagram' == $hashtag_post_type ) {
				$output .= '<a href="' . $hashtag_meta_url . '">';
				$output .= '<i class="fa fa-instagram"></i>';
				$output .= '</a>';
			} elseif ( 'socment-googleplus' == $hashtag_post_type ) {
				$output .= '<a href="' . $hashtag_meta_url . '">';
				$output .= '<i class="fa fa-google-plus"></i>';
				$output .= '</a>';
			} elseif ( 'socment-flickr' == $hashtag_post_type ) {
				$output .= '<a href="' . $hashtag_meta_url . '">';
				$output .= '<i class="fa fa-flickr"></i>';
				$output .= '</a>';
			}
			$output .= '</div>';
			$output .= '<div id="socment-hashtag-date">';
			$output .= $hashtag_date;
			$output .= '</div>';
			$output .= '</div>';
			$output .= '</div>';
		}
		$output .= '</div>';
		if ( ! empty( $load_masonry ) && 'yes' == $load_masonry ) {
			$output .= '
			<script>
			(function($){
				$(document).ready(function(){
					var $socialGrid = $(\'#socment-hashtag-grid\').masonry({
						itemSelector: \'#socment-hashtag-holder\'
					});
	
					$socialGrid.imagesLoaded().progress( function() {
						$socialGrid.masonry(\'layout\');
					});
				})
			})(jQuery)
			</script>';
		}

		return $output;
	} else {
		return 'No mentions to show yet!';
	}

}// end social_mentions_shortcode

add_shortcode( 'social-mentions', 'social_mentions_shortcode' );
