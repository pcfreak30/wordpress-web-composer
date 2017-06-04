<?php

use pcfreak30\Web\Composer;

require_once __DIR__ . '/web-composer/bootstrap.php';
if ( ! class_exists( 'WordPress_Web_Composer' ) ):
	/**
	 * Class WordPress_Web_Composer
	 * @SuppressWarnings(PHPMD.ShortVariable)
	 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
	 */
	class WordPress_Web_Composer {
		/**
		 * @var \pcfreak30\Web\Composer
		 */
		protected $web_composer;

		/**
		 * @var string
		 */
		protected $id;
		/**
		 * @var bool
		 */
		protected $run_separate_request;

		/**
		 * @var string
		 */
		protected $action;

		/**
		 * WordPress_Web_Composer constructor.
		 */
		public function __construct( $id, $run_separate_request = false ) {
			$this->web_composer         = new Composer();
			$this->id                   = $id;
			$this->run_separate_request = $run_separate_request;
			if ( $this->run_separate_request ) {
				$this->action = "web-composer-install_{$this->id}";
				$this->setup_hooks();
			}
		}

		/**
		 * @param $target string
		 */
		public function set_install_target( $target ) {
			$this->web_composer->setInstallTarget( $target );
		}

		/**
		 * @param $target string
		 */
		public function set_download_target( $target ) {
			$this->web_composer->setDownloadTarget( $target );
		}

		/**
		 * @return \pcfreak30\Web\Composer
		 */
		public function get_web_composer() {
			return $this->web_composer;
		}

		/**
		 *
		 */
		protected function set_default_upload_dir() {
			$upload_dir = wp_upload_dir();
			$this->web_composer->setDownloadTarget( trailingslashit( $upload_dir['basedir'] ) . 'composer.phar' );
		}

		/**
		 * @return bool
		 */
		public function run() {
			if ( $this->run_separate_request ) {
				$url    = add_query_arg(
					array(
						'action' => $this->action,
					),
					wp_nonce_url( admin_url( 'admin_post' ) )
				);
				$result = wp_remote_get( $url );
				if ( is_wp_error( $result ) ) {
					$result = false;
				}

				return $result;
			}

			return $this->do_run();
		}

		/**
		 * @return bool
		 */
		protected function do_run() {
			$target = $this->web_composer->getInstallTarget();
			if ( empty( $target ) ) {
				return false;
			}

			return $this->web_composer->run();
		}

		/**
		 *
		 */
		protected function setup_hooks() {
			add_action( "admin_post_nopriv_web-composer-install_{$this->id}", array( $this, 'do_install' ) );
		}

		/**
		 * @return bool
		 */
		public function do_install() {
			if ( check_ajax_referer( $this->action ) ) {
				return $this->web_composer->run();
			}

			return false;
		}
	}

endif;