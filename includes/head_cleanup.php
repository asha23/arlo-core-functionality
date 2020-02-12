<?php

namespace arlo;

final class head_cleanup
{
	public function __construct()
	{

	}

	public function listen()
	{
		add_action('init', function () {
			remove_action('wp_head', 'feed_links_extra', 3);
			add_action('wp_head', 'ob_start', 1, 0);
			add_action('wp_head', function () {
				$pattern = '/.*' . preg_quote(esc_url(get_feed_link('comments_' . get_default_feed())), '/') . '.*[\r\n]+/';
				echo preg_replace($pattern, '', ob_get_clean());
			}, 3, 0);
			remove_action('wp_head', 'rsd_link');
			remove_action('wp_head', 'wlwmanifest_link');
			remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10);
			remove_action('wp_head', 'wp_generator');
			remove_action('wp_head', 'wp_shortlink_wp_head', 10);
			remove_action('wp_head', 'print_emoji_detection_script', 7);
			remove_action('admin_print_scripts', 'print_emoji_detection_script');
			remove_action('wp_print_styles', 'print_emoji_styles');
			remove_action('admin_print_styles', 'print_emoji_styles');
			remove_action('wp_head', 'wp_oembed_add_discovery_links');
			remove_action('wp_head', 'wp_oembed_add_host_js');
			remove_action('wp_head', 'rest_output_link_wp_head', 10);
			remove_filter('the_content_feed', 'wp_staticize_emoji');
			remove_filter('comment_text_rss', 'wp_staticize_emoji');
			remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
			add_filter('use_default_gallery_style', '__return_false');
			add_filter('emoji_svg_url', '__return_false');
			add_filter('the_generator', '__return_false');
		});

		add_filter('style_loader_tag', function ($input) {
			preg_match_all(
				"!<link rel='stylesheet'\s?(id='[^']+')?\s+href='(.*)' type='text/css' media='(.*)' />!",
				$input,
				$matches
			);
			if (empty($matches[2])) {
				return $input;
			}
			// Only display media if it is meaningful
			$media = $matches[3][0] !== '' && $matches[3][0] !== 'all' ? ' media="' . $matches[3][0] . '"' : '';
			return '<link rel="stylesheet" href="' . $matches[2][0] . '"' . $media . '>' . "\n";
		});
	}

	public function seed_rss_version() {
		return '';
	}

	public function seed_remove_wp_ver_css_js( $src ) {
		if ( strpos( $src, 'ver=' ) )
			$src = remove_query_arg( 'ver', $src );
		return $src;
	}

	public function seed_remove_wp_widget_recent_comments_style() {
		if ( has_filter( 'wp_head', 'wp_widget_recent_comments_style' ) ) {
			remove_filter( 'wp_head', 'wp_widget_recent_comments_style' );
		}
	}
	
	public function seed_remove_recent_comments_style() {
		global $wp_widget_factory;

		if (isset($wp_widget_factory->widgets['WP_Widget_Recent_Comments'])) {
			remove_action( 'wp_head', array($wp_widget_factory->widgets['WP_Widget_Recent_Comments'], 'recent_comments_style') );
		}
	}
	
	public function seed_gallery_style($css) {
		return preg_replace( "!<style type='text/css'>(.*?)</style>!s", '', $css );
	}

	public function seed_theme_support() {

		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'custom-background',
			array(
			'default-image' => '',  // background image default
			'default-color' => '', // background color default (dont add the #)
			'wp-head-callback' => '_custom_background_cb',
			'admin-head-callback' => '',
			'admin-preview-callback' => ''
			)
		);
	
		// rss thingy
		add_theme_support('automatic-feed-links');
	
		// adding post format support
		add_theme_support( 'post-formats',
			array(
				'aside',             // title less blurb
				'gallery',           // gallery of images
				'link',              // quick link to other site
				'image',             // an image
				'quote',             // a quick quote
				'status',            // a Facebook like status update
				'video',             // video
				'audio',             // audio
				'chat'               // chat transcript
			)
		);
	
		// wp menus
		add_theme_support( 'menus' );
	
		// registering wp3+ menus
	
		register_nav_menus(
			array(
				'main-nav' => __( 'The Main Menu', 'SEEDtheme' ),   // main nav in header
				'footer-links' => __( 'Footer Links', 'SEEDtheme' ) // secondary nav in footer
			)
		);
	}

	public function seed_filter_ptags_on_images($content){
		return preg_replace('/<p>\s*(<a .*>)?\s*(<img .* \/>)\s*(<\/a>)?\s*<\/p>/iU', '\1\2\3', $content);
	}

	public function seed_excerpt_more($more) {
		global $post;
		// edit here if you like
		return '...  <a class="excerpt-read-more" href="'. get_permalink($post->ID) . '" title="'. __( 'Read', 'SEEDtheme' ) . get_the_title($post->ID).'">'. __( '<p>&nbsp;</p><button class="btn btn-info">Read more <i class="fa fa-angle-double-right"></i></button>', 'SEEDtheme' ) .'</a>';
	}

	public function seed_theme_body_class($classes) {
		global $post;
		if (!$post) return $classes;
		$classes[] = 'page-'.$post->post_name;
		if ($post->post_parent) {
			$ppost = get_post($post->post_parent);
			$classes[] = 'section-'.$ppost->post_name;
		}
		return $classes;
	}

	public function seed_remove_admin_menus() {
		remove_menu_page( 'edit-comments.php' ); // comments
	}

	public function seed_remove_dashboard_meta() {
		remove_meta_box( 'dashboard_incoming_links', 'dashboard', 'normal' );
		remove_meta_box( 'dashboard_plugins', 'dashboard', 'normal' );
		remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
		remove_meta_box( 'dashboard_secondary', 'dashboard', 'normal' );
		remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
		remove_meta_box( 'dashboard_recent_drafts', 'dashboard', 'side' );
		remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
		remove_meta_box( 'dashboard_right_now', 'dashboard', 'normal' );
		remove_meta_box( 'dashboard_activity', 'dashboard', 'normal');
	}

	public function seed_remove_menu_items(){
		global $submenu;
		unset($submenu['themes.php'][6]); // remove customize link
	}

	
	function seed_remove_background_menu_item() {
		remove_theme_support( 'custom-background' );
	}

	public function seed_custom_login_logo() {
		echo '<style type="text/css">h1 a { background-image: url('.get_bloginfo('template_directory').'/build/images/custom-login-logo.png) !important; height:82px!important; background-size:164px!important; width:200px!important;}</style>';
	}

}