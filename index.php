<?php

/*
Plugin Name: Exclude Post From Recent Comments Widget
Description: Excludes a post from `Recent Comments` Widget
Author: Ionuț Staicu
Version: 1.0.0
Author URI: http://ionutstaicu.com
Slug: exclude-post-from-recent-comments-widget
License: GPL2
 */

if (!defined('ABSPATH')) {
	exit;
}

define('EXCLUDE_POST_FROM_RECENT_COMMENTS_WIDGET_VERSION', '1.0.0');

define('EXCLUDE_POST_FROM_RECENT_COMMENTS_WIDGET_BASEFILE', __FILE__);
define('EXCLUDE_POST_FROM_RECENT_COMMENTS_WIDGET_URL', plugin_dir_url(__FILE__));
define('EXCLUDE_POST_FROM_RECENT_COMMENTS_WIDGET_PATH', plugin_dir_path(__FILE__));

add_action('add_meta_boxes', function () {
	add_meta_box(
		'ntz_exclude_post_from_recent_comments_widget',
		__('Exclude post from Recent Comments', 'exclude-post-from-recent-comments-widget'),
		'ntz_exclude_post_from_recent_comments_widget',
		'post',
		'side'
	);
});

function ntz_exclude_post_from_recent_comments_widget($post)
{
	$options = get_option('ntz_exclude_post_from_recent_comments_widget', []);

	$status = in_array($post->ID, $options);

	$input = sprintf('<input type="checkbox" name="ntz_exclude_post_from_recent_comments_widget" value="1" %s>', checked($status, 1, false));

	printf('<p><label>%s %s</label></p>',
		$input,
		__('Exclude post from <em>Recent Comments</em> widget?')
	);
}

add_action('save_post', function ($postID) {
	if (!current_user_can('edit_post', $postID) || wp_is_post_autosave($postID) || wp_is_post_revision($postID)) {
		return;
	}

	$options = get_option('ntz_exclude_post_from_recent_comments_widget', []);

	unset($options[$postID]);

	if (absint($_REQUEST['ntz_exclude_post_from_recent_comments_widget'] ?? 0)) {
		$options[$postID] = $postID;
	}

	$options = array_filter($options);

	update_option('ntz_exclude_post_from_recent_comments_widget', $options);
});

add_filter('widget_comments_args', function ($args) {
	$options = get_option('ntz_exclude_post_from_recent_comments_widget', []);

	if (!empty($options)) {
		$args['post__not_in'] = array_values($options);
	}

	return $args;
}, 10, 1);
