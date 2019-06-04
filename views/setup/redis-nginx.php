<?php
/**
 * Cachify: Setup instructions for Redis with nginx.
 *
 * This file contains the setup isntructions view for Redis caching using nginx.
 *
 * @package    Cachify
 * @subpackage Setup Instructions
 */

/* Quit */
defined( 'ABSPATH' ) || exit;
?>

	<table class="form-table">
		<tr>
			<th>
				<?php esc_html_e( 'nginxconf Redis setup', 'cachify' ); ?>
			</th>
			<td>
				<label for="cachify_setup">
					<?php esc_html_e( 'Please add the following lines to your nginx.conf', 'cachify' ); ?>
				</label>
			</td>
		</tr>

		<tr>
			<th>
				<?php esc_html_e( 'Notes', 'cachify' ); ?>
			</th>
			<td>
				<ul style="list-style-type:circle">
					<li>
						<?php esc_html_e( 'For domains with FQDN, the variable ${http_host} must be used instead of ${host}.', 'cachify' ); ?>
					</li>
					<li>
						<?php
						echo sprintf(
							/* translators: parameters contain a single nginx config line each */
							esc_html__( 'If you have errors please try to change %1$s to %2$s This forces IPv4 because some servers that allow ipv4 and ipv6 are configured to bind Redis to ipv4 only.', 'cachify' ),
							'redis_pass localhost:6379;',
							'redis_pass 127.0.0.1:6379;'
						);
						?>
					</li>
				</ul>
			</td>
		</tr>
	</table>

	<div style="background:#fff;border:1px solid #ccc;padding:10px 20px"><pre>
## GZIP
gzip_static on;

## CHARSET
charset utf-8;

## INDEX LOCATION
location / {
	error_page 404 405 = @nocache;

	if ( $query_string ) {
		return 405;
	}
	if ( $request_method = POST ) {
		return 405;
	}
	if ( $request_uri ~ "/wp-" ) {
		return 405;
	}
	if ( $http_cookie ~ (wp-postpass|wordpress_logged_in|comment_author)_ ) {
		return 405;
	}

	default_type text/html;
	add_header X-Powered-By Cachify;
	set $redis_key $host$uri;
	redis_pass localhost:6379;
}

## NOCACHE LOCATION
location @nocache {
	try_files $uri $uri/ /index.php?$args;
}
</pre></div>
