<?php


class SlackPress
{
	public $payload = array();
	public $webhook_url = "https://hooks.slack.com/services/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";
	public $site_info;

	function __construct()
	{
		$this->add_actions();
	}

	function add_actions()
	{
		add_action( 'admin_init', array($this, 'init') );
	}

	function init()
	{
		$this->site_info = $this->get_site_info();
		$this->payload = $this->get_payload();

		$this->check_core();
		$this->check_theme();
		$this->check_plugins();

		if ( count( $this->payload->attachments ) === 0 ) {
			$attachment = $this->create_attachment(':thumbsup: All Good!');

			$this->add_attachment($attachment);
		}

		$this->deliver_payload();

	}

	function deliver_payload()
	{
		$delivered = get_transient( 'slackpress_delivery' );

		if ( false === $delivered ) {

			$response = wp_remote_post( $this->webhook_url, array(
					'method' => 'POST',
					'body' => json_encode($this->payload)
				)
			);

			set_transient( 'slackpress_delivery', true, 60 * 60 * 24 );
		}
	}

	function create_attachment($title, $value='', $color='good')
	{
		$attachment = array(
			"fallback" => "",
			"pretext" => "",
			"color" => $color,
			"fields" => array(
				array(
					"title" => $title,
					"value" => $value,
					"short" => true
				)
			)
		);

		return $attachment;
	}

	function check_core()
	{
		$status = get_site_transient('update_core');
		$attachment = array();

		if ( ! empty( $status ) && $status->updates ) {

			if ( $status->updates[0]->response !== 'latest' ) {
				$attachment = $this->create_attachment('WordPress update available:', $status->updates[0]->version, 'warning');
			}

			$this->add_attachment($attachment);
		}
	}

	function check_plugins()
	{
		$info = get_site_transient( 'update_plugins' );

		if ( ! empty($info) && $info->response ) {
			$plugins_with_updates = $info->response;

			foreach ($plugins_with_updates as $plugin) {
				$attachment = $this->create_attachment($plugin->slug . ' update available:', $plugin->new_version, 'warning');

				$this->add_attachment($attachment);
			}
		}
	}

	function check_theme()
	{
		if ( function_exists( 'genesis_update_check' ) ) {

			$update = genesis_update_check();

			if ( ! empty( $update ) ) {

				$attachment = $this->create_attachment('Genesis update available:', $update['new_version'], 'warning');

				$this->add_attachment($attachment);
			}
		}
	}

	function add_attachment($attachment) {
		array_push( $this->payload['attachments'], $attachment);
	}

	function get_payload()
	{
		$payload = array(
			"text" =>  "Health Report - " . date('F jS Y'),
			"username" =>  $this->site_info['site_title'],
			"icon_url" => WPMU_PLUGIN_URL . "/slackpress/assets/slackpress-logo.png",
			"attachments" => array()
		);

		return $payload;
	}

	function get_site_info()
	{
		$site_info = array(
			'site_title' => get_bloginfo('blogname'),
			'wp_version' => get_bloginfo('version'),
			'theme' => get_bloginfo('theme')
		);

		return $site_info;
	}
}

new SlackPress();_transient( 'slackpress_delivery', true, 60 * 60 * 24 );
	// }
}
