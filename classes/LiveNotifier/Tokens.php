<?php
namespace LiveNotifier;

/**
 * Create, get and validate tokens used for authenticating
 * notification subscriptions.
 */
class Tokens {
	/**
	 * Elgg_database
	 */
	private $db;

	/**
	 * Elgg configuration object
	 */
	private $config;

	/**
	 * Set up database and Elgg configuration
	 */
	public function __construct(\Elgg_Database $db, $config) {
		$this->db = $db;
		$this->config = $config;
	}

	/**
	 * Create a token that can be used to authenticate user
	 *
	 * @param ElggUser $user   The user who needs a token
	 * @param int      $expire Token lifetime (default 60 minutes)
	 * @return string|boolean The created token or false on failure
	 */
	public function createToken($user, $expire = 60) {
		$site_guid = $this->config->site_guid;

		$time = time();
		$time += 60 * $expire;
		$token = md5(rand() . microtime() . $user->username . $time . $site_guid);

		$dbprefix = $this->db->getTablePrefix();

		$success = $this->db->insertData("INSERT into {$dbprefix}users_apisessions
			(user_guid, site_guid, token, expires) values
			({$user->guid}, $site_guid, '$token', '$time')
			on duplicate key update token='$token', expires='$time'");

		if ($success) {
			return $token;
		}

		return false;
	}

	/**
	 * Get an existing token for user
	 *
	 * @param ElggUser $user
	 * @return string|boolean $token The token or false on failure
	 */
	public function getToken ($user) {
		$site_guid = $this->config->site_guid;

		$timestamp = time();

		$dbprefix = $this->db->getTablePrefix();

		$token = $this->db->getData("SELECT * FROM {$dbprefix}users_apisessions
			WHERE user_guid={$user->guid} AND site_guid=$site_guid AND expires > $timestamp");

		if ($token) {
			$token = $token[0]->token;
		} else {
			$token = false;
		}

		return $token;
	}

	/**
	 * Check that token exists and is valid
	 *
	 * @param string $token
	 * @return boolean
	 */
	public function validateToken($token) {
		$token = $this->db->sanitizeString($token);

		$time = time();

		$dbprefix = $this->db->getTablePrefix();

		$site_guid = $this->config->site_guid;

		$user = get_data_row("SELECT * from {$dbprefix}users_apisessions
			where token='$token' and site_guid=$site_guid and $time < expires");

		if ($user) {
			return true;
		} else {
			return false;
		}
	}
}
