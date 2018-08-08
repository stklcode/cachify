<?php
/**
 * Cachify: Redis caching backend.
 *
 * This file contains the backend class for Redis caching.
 *
 * @package   Cachify
 */

/**
 * Cachify_REDIS
 */
final class Cachify_REDIS {

	/**
	 * Redis-Object
	 *
	 * @since  2.4.0
	 * @var    Redis
	 */
	private static $_redis;

	/**
	 * Availability check
	 *
	 * @since   2.4.0
	 *
	 * @return  boolean  true/false  TRUE when installed
	 */
	public static function is_available() {
		return class_exists( 'Redis' ) && isset( $_SERVER['SERVER_SOFTWARE'] ) && strpos( strtolower( $_SERVER['SERVER_SOFTWARE'] ), 'nginx' ) !== false;
	}

	/**
	 * Caching method as string
	 *
	 * @since   2.4.0
	 *
	 * @return  string  Caching method
	 */
	public static function stringify_method() {
		return 'Redis';
	}

	/**
	 * Store item in cache
	 *
	 * @since   2.4.0
	 *
	 * @param   string  $hash       Hash of the entry [ignored].
	 * @param   string  $data       Content of the entry.
	 * @param   integer $lifetime   Lifetime of the entry.
	 * @param   bool    $sig_detail Show details in signature.
	 */
	public static function store_item( $hash, $data, $lifetime, $sig_detail ) {
		/* Do not store empty data. */
		if ( empty( $data ) ) {
			trigger_error( __METHOD__ . ': Empty input.', E_USER_WARNING );
			return;
		}

		/* Server connect */
		if ( ! self::_connect_server() ) {
			return;
		}

		/* Add item */
		self::$_redis->set(
			self::_file_path(),
			$data . self::_cache_signature( $sig_detail ),
			$lifetime
		);
	}

	/**
	 * Read item from cache
	 *
	 * @since   2.0.7
	 * @change  2.0.7
	 *
	 * @param   string $hash  Hash of the entry.
	 * @return  mixed         Content of the entry
	 */
	public static function get_item( $hash ) {
		/* Server connect */
		if ( ! self::_connect_server() ) {
			return false;
		}

		/* Get item */
		return self::$_redis->get(
			self::_file_path()
		);
	}

	/**
	 * Delete item from cache
	 *
	 * @since   2.0.7
	 * @change  2.0.7
	 *
	 * @param   string $hash  Hash of the entry.
	 * @param   string $url   URL of the entry [optional].
	 */
	public static function delete_item( $hash, $url = '' ) {
		/* Server connect */
		if ( ! self::_connect_server() ) {
			return;
		}

		/* Delete */
		self::$_redis->delete(
			self::_file_path( $url )
		);
	}

	/**
	 * Clear the cache
	 *
	 * @since   2.0.7
	 * @change  2.0.7
	 */
	public static function clear_cache() {
		/* Server connect */
		if ( ! self::_connect_server() ) {
			return;
		}

		/* Flush */
		self::$_redis->flushDB();
	}

	/**
	 * Print the cache.
	 *
	 * @since   2.0.7
	 * @change  2.4.0
	 *
	 * @param   bool  $sig_detail  Show details in signature.
	 * @param   array $cache       Array of cache values.
	 */
	public static function print_cache( $sig_detail, $cache ) {
		// No string?
		if ( ! is_string( $cache ) || empty( $cache ) ) {
			return;
		}

		/*
		 * The following block intentionally outputs (cached) HTML.
		 * phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
		 */
		echo $cache;
		// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped

		// Quit.
		exit;
	}

	/**
	 * Get the cache size
	 *
	 * @since   2.4.0
	 *
	 * @return  mixed  Cache size
	 */
	public static function get_stats() {
		/* Server connect */
		if ( ! self::_connect_server() ) {
			return null;
		}

		/* Info */
		$data = self::$_redis->info();

		/* No stats? */
		if ( empty( $data ) ) {
			return null;
		}

		/* Empty */
		if ( empty( $data['used_memory'] ) ) {
			return null;
		}

		return $data['used_memory'];
	}

	/**
	 * Generate signature
	 *
	 * @since   2.4.0
	 *
	 * @param   bool $detail  Show details in signature.
	 * @return  string        Signature string
	 */
	private static function _cache_signature( $detail ) {
		return sprintf(
			"\n\n<!-- %s\n%s @ %s -->",
			'Cachify | http://cachify.de',
			( $detail ? 'Redis' : __( 'Generated', 'cachify' ) ),
			date_i18n(
				'd.m.Y H:i:s',
				current_time( 'timestamp' )
			)
		);
	}

	/**
	 * Path of cache file
	 *
	 * @since   2.4.0
	 *
	 * @param   string $path  Request URI or permalink [optional].
	 * @return  string        Path to cache file
	 */
	private static function _file_path( $path = null ) {
		$path_parts = wp_parse_url( $path ? $path : $_SERVER['REQUEST_URI'] );

		return trailingslashit(
			sprintf(
				'%s%s',
				$_SERVER['HTTP_HOST'],
				$path_parts['path']
			)
		);
	}

	/**
	 * Connect to Redis server
	 *
	 * @since   2.4.0
	 *
	 * @hook    array  cachify_redis_servers  Array with Redis servers
	 *
	 * @return  boolean  true/false  TRUE on success
	 */
	private static function _connect_server() {
		/* Not enabled? */
		if ( ! self::is_available() ) {
			return false;
		}

		/* Already connected */
		if ( is_object( self::$_redis ) ) {
			return true;
		}

		/* Init */
		self::$_redis = new Redis();

		/* Connect */
		$servers = (array) apply_filters(
			'cachify_redis_servers',
			array(
				array(
					'127.0.0.1',
					6379,
				),
			)
		);

		$connected = true;

		foreach ( $servers as $server ) {
			// Suppress warnings raised by connect. The boolean result is everything needed here.
			$connected = @self::$_redis->connect( $server[0], $server[1] ) && $connected;
		}

		return $connected;
	}
}
