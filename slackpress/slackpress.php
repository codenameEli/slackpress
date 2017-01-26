<?php

add_action( 'shutdown', 'slackpress_init' );

function slackpress_init()
{
	$webhook_url = ""; // Browse Apps > Custom Integrations > Incoming WebHooks > Edit configuration - Setup Instructions - Webhook URL
	$site_info = slackpress_get_site_info();
	$payload = array(
		"text" =>  "Checkup",
		"username" =>  $site_info['site_title'],
		"attachments" =>  array(
		   array(
				"fallback" => "",
				"pretext" => "",
				"color" => "good",
				"fields" => array(
					array(
						"title" => "WordPress Version",
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
