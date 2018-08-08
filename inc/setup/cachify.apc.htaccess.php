<?php
/**
 * Cachify: Setup instructions for APC via htaccess.
 *
 * This file contains the setup isntructions view for APC caching using htaccess.
 *
 * @package    Cachify
 * @subpackage Setup Instructions
 */

/* Quit */
defined( 'ABSPATH' ) || exit;

$beginning = '&lt;Files index.php&gt;
	php_value auto_prepend_file ';

$ending = '/cachify/apc/proxy.php
&lt;/Files&gt;';

?>

	<table class="form-table">
		<tr>
			<th>
			<?php esc_html_e( '.htaccess APC setup', 'cachify' ); ?>
			</th>
			<td>
				<label for="cachify_setup">
					<?php esc_html_e( 'Please add the following lines to your .htaccess file', 'cachify' ); ?>
				</label>
			</td>
		</tr>
	</table>

	<div style="background:#fff;border:1px solid #ccc;padding:10px 20px">
		<pre><?php echo sprintf( '%s%s%s', $beginning, WP_PLUGIN_DIR, $ending ); ?></pre>
	</div>
