<?php
/**
 * FindContent WP-CLI Package.
 *
 * @package   salcode\FindContent
 * @author    Sal Ferrarello
 * @license   MIT
 */

namespace salcode\FindContent;

if ( ! class_exists( 'WP_CLI' ) ) {
	return;
}

$autoload = dirname( __FILE__ ) . '/vendor/autoload.php';
if ( is_readable( $autoload ) ) {
	require_once $autoload;
}
\WP_CLI::add_command( 'find-content', FindContentCommand::class );
