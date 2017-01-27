<?php

add_action( 'shutdown', 'slackpress_init' );

function slackpress_init()
{
	$webhook_url = "https://hooks.slack.com/services/T0292SS5A/aaa000/00000000";
	$site_info = slackpress_get_site_info();
	$payload = array(
		"text" =>  "Health Report - " . date('F jS Y'),
		"username" =>  $site_info['site_title'],
		"icon_url" => WPMU_PLUGIN_URL . "/slackpress/assets/slackpress-logo.png",
		"attachments" =>  array(
		   array(
				"fallback" => "",
				"pretext" => "",
				"color" => "good",
				"fields" => array(
					array(
						"title" => "WordPress Version:",
						"value" => $site_info['wp_version'],
						"short" => true
					)
				)
		   )
		)
	);

	slackpress_deliver_payload($webhook_url, $payload);
}

function slackpress_get_site_info()
{
	$site_info = array(
		'site_title' => get_bloginfo('blogname'),
		'wp_version' => get_bloginfo('version'),
		'theme' => get_bloginfo('theme'),
		'plugins' => get_plugins(),
	);

	return $site_info;
}

function slackpress_deliver_payload($url, $payload)
{
	$delivered = get_transient( 'slackpress_delivery' );

	if ( false === $delivered ) {

		$response = wp_remote_post( $url, array(
				'method' => 'POST',
				'body' => json_encode($payload)
			)
		);

		set_transient( 'slackpress_delivery', true, 60 * 60 * 24 );
	}
}
