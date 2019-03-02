<?php
/**
 * @package   OpenCart
 * @author    Daniel Kerr
 * @copyright Copyright (c) 2005 - 2017, OpenCart, Ltd. (https://www.opencart.com/)
 * @license   https://opensource.org/licenses/GPL-3.0
 * @author    Daniel Kerr
 * @see       https://www.opencart.com
 */

/**
 * URL class.
 */
class Url {
	/** @var string */
	private $url;
	/** @var Controller[] */
	private $rewrite = array();

	/**
	 * Constructor.
	 *
	 * @param string $url
	 * @param string $ssl Unused
	 */
	public function __construct($url, $ssl = '') {
		$this->url = $url;
	}

	/**
	 *
	 *
	 * @param Controller $rewrite
	 *
	 * @return void
	 */
	public function addRewrite($rewrite) {
		$this->rewrite[] = $rewrite;
	}

	/**
	 *
	 *
	 * @param string          $route
	 * @param string|string[] $args
	 *
	 * @return string
	 */
	public function link($route, $args = '', $auto_admin_token = true) {
		$url = $this->url . 'index.php?route=' . (string)$route;

        // [admpub] Add user_token to admin link if it's not passed in
        if ($auto_admin_token && is_admin() && !empty(session()->data['user_token'])) {
            if (is_array($args)) {
                if(!array_key_exists('user_token', $args)) $args['user_token'] = session()->data['user_token'];
            } else if (!str_contains($args, 'user_token')) {
                $args .= '&user_token=' . session()->data['user_token'];
            }
        }

		if ($args) {
			if (is_array($args)) {
				$url .= '&amp;' . http_build_query($args, '', '&amp;');
			} else {
				$url .= str_replace('&', '&amp;', '&' . ltrim($args, '&'));
			}
		}

		foreach ($this->rewrite as $rewrite) {
			$url = $rewrite->rewrite($url);
		}

		return $url;
	}
}
