<?php

class EDD_Deploy_Client_Admin extends EDD_Deploy_Client {

	private $api_url;

	function __construct( $store_url ) {

		$this->api_url = trailingslashit( $store_url );

		add_action( 'admin_footer', array( $this, 'register_scripts' ) );
		add_action( 'admin_menu',   array( $this, 'admin_menu' ) );

		add_thickbox();

	}

	function admin_menu () {

		add_options_page( 'Deployer Demo', 'Deployer Demo', 'install_plugins', 'deployer-demo', array( $this, 'settings_page' ) );
	}

	public function register_scripts() {
		wp_enqueue_script( 'edd_deployer_script', EDD_DEPLOY_PLUGIN_URL . 'assets/js/edd-deploy.js', array( 'jquery' ) );
	}

	function get_downloads() {

		$api_params = array( 'edd_action' => 'get_downloads' );
		$request    = wp_remote_post( $this->api_url . '/?edd_action=get_downloads' );

		if ( is_wp_error( $request ) ) {
			return;
		}

		$request = json_decode( wp_remote_retrieve_body( $request ), true );

		return $request;

	}

	function settings_page() {

		echo '<style>.deploy-item { width: 32%; margin-right: 1%; float: left; min-width: 250px; } .deploy-item h3.hndle { padding: 0 1em 1em 1em; } .deploy-item img { width: 100%; height: auto; }</style>';

		echo '<div class="wrap">';
		echo '<h2>' . __( 'Deployer Demo', 'prefix' ) . '</h2>';

		$downloads = $this->get_downloads();

		$i = 0;

		$plugins = $downloads['plugins'];
		$themes  = $downloads['themes'];

		foreach ( $plugins as $download ) {

			if ( ! $download['bundle'] ) {

				$data_free   = (int) $download['free'];
				$disabled    = $this->is_plugin_installed( $download['title'] ) ? ' disabled="disabled" ' : '';
				$button_text = $this->is_plugin_installed( $download['title'] ) ? __( 'Installed' ) : __( 'Install' );

				$i = $i == 3 ? 0 : $i;
				if ( $i == 0 ) {
					echo '<div style="clear:both; display: block; float: none;"></div>';
				}

				echo '<div id="' . sanitize_title( $download['title'] ) . '" class="deploy-item postbox plugin">';
					echo '<h3 class="hndle"><span>' . $download['title'] . '</span></h3>';
					echo '<div class="inside">';
						echo '<div class="main">';

							if ( '' != $download['thumbnail'] ) {
								echo '<img class="deployer-item-image" src="' . $download['thumbnail'][0] . '">';
							}

							if ( '' != $download['description'] ) {
								echo '<p class="deployer-item-description">' . $download['description'] . '</p>';
							}

							echo '<p class="deployer-actions">';
								echo '<span class="spinner"></span>';
								echo '<button class="button button-primary" data-free="' . $data_free . '"' . $disabled . 'data-deploy="' . $download['title'] . '">' . $button_text . '</button>';
							echo '</p>';
						echo '</div>';
					echo '</div>';
				echo '</div>';

				$i++;

			}

		}

		echo '<div id="edd_deployer_license_thickbox" style="display:none;">';
		echo '<h3>' . __( 'Enter your license') . '</h3>';
		echo '<form action="" method="post" id="edd_deployer_license_form">';
		echo '<input style="width: 100%" type="text" id="edd_deployer_license"/>';
		echo '<button style="margin-top: 10px" type="submit" class="button button-primary">' . __( 'Submit' ) . '</button>';
		echo '</form>';
		echo '</div>';

		echo '</div>';

	}
}
