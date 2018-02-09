<?php
/**
 * FindContent WP-CLI Package.
 *
 * @package   salcode\FindContent
 * @author    Sal Ferrarello
 * @license   MIT
 */

namespace salcode\FindContent;

use WP_CLI;
use WP_CLI_Command;
use WP_CLI\Utils;

/**
 * Command to find content in post_content and post meta.
 *
 * @package   salcode\FindContent
 * @author    Sal Ferrarello
 * @license   MIT
 */
class FindContentCommand extends WP_CLI_Command {

	/**
	 * The query string to find.
	 *
	 * @since 0.1.0
	 *
	 * @var string The value to be found in the content.
	 */
	protected $query;

	/**
	 * Treat query string as a regular expression.
	 *
	 * When true runs the search as a regular expression
	 * (without delimiters). The search becomes case-sensitive (i.e. no PCRE
	 * flags are added). Delimiters must be escaped if they occur in the
	 * expression.
	 *
	 * @since 0.2.0
	 *
	 * @var bool
	 */
	protected $regex = false;

	/**
	 * Pass PCRE modifiers to the regex search
	 * (e.g. 'i' for case-insensitivity).
	 *
	 * @since 0.2.0
	 *
	 * @var string
	 */
	protected $regex_flags = '';

	/**
	 * Delimiter to use for regex.
	 *
	 * The delimiter to use for the regex. It must be escaped if it appears
	 * in the search string. The default value is the result of `chr(1)`.
	 *
	 * @since 0.2.0
	 *
	 * @var string|null
	 */
	protected $regex_delimiter;

	/**
	 * Array of posts where query was found.
	 *
	 * @since 0.1.0
	 *
	 * @var array Array of results, where each result is a
	 *            key/value array representing a post.
	 */
	protected $post_results = [];

	/**
	 * Format to be used with when displaying results.
	 *
	 * @since 0.1.0
	 *
	 * @var string Format to use with format_items().
	 */
	protected $format='table';

	/**
	 * Field names to be used with format_items()
	 *
	 * Comma separated field names. Available fields
	 * are columns in wp_posts, columns in wp_postmeta,
	 * 'permalink', 'location', and 'query'.
	 *
	 * var string.
	 */
	protected $fields = 'ID,permalink,location';

	/**
	 * Find posts where the query appears in the post_content or post meta.
	 * ## Options
	 *
	 * <query>...
	 * : The query to find in the database content.
	 *
	 * [--regex]
	 * : Runs the search as a regular expression (without delimiters). The search becomes case-sensitive (i.e. no PCRE flags are added). Delimiters must be escaped if they occur in the expression.
	 *
	 * [--regex-flags=<regex-flags>]
	 * : Pass PCRE modifiers to the regex search (e.g. 'i' for case-insensitivity).
	 *
	 * [--regex-delimiter=<regex-delimiter>]
	 * : The delimiter to use for the regex. It must be escaped if it appears in the search string. The default value is the result of `chr(1)`.
	 *
	 * [--format=<format>]
	 * : Render output in a particular format.
	 * ---
	 * default: table
	 * options:
	 *   - table
	 *   - csv
	 *   - json
	 *   - count
	 *   - yaml
	 * ---
	 *
	 * [--fields=<fields>]
	 * : Limit the output to specific fields. Defauls to ID,permalink,location.
	 * ---
	 * available fields:
	 *    - Column names from wp_posts
	 *    - Column names from wp_postmeta
	 *    - permalink (for the associated post)
	 *    - location ('content' or 'postmeta')
	 *    - query (the string being queried)
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *     # Find instances of gravityform 7's shortcode.
	 *     $ wp find-content '[gravityform id="7"'
	 *     +----+-----------------------------+----------+
	 *     | ID | permalink                   | location |
	 *     +---+------------------------------+----------+
	 *     | 1  | http://wp.test/hello-world  | content  |
	 *     | 8  | http://wp.test/display-meta | postmeta |
	 *     +----+-----------------------------+----------+
	 *
	 *     # Find instances of "your first post".
	 *     $ wp find-content 'your first post'
	 *     +----+----------------------------+----------+
	 *     | ID | permalink                  | location |
	 *     +---+-----------------------------+----------+
	 *     | 1  | http://wp.test/hello-world | content  |
	 *     +----+----------------------------+----------+
	 *
	 *     # Find instances of 'Description of this example post.'
	 *     $ wp find-content 'Description of this example post.' --fields=ID,permalink,location,meta_key
	 *     +----+------------------------------+----------+----------------------+
	 *     | ID | permalink                    | location | meta_key             |
	 *     +----+------------------------------+----------+----------------------+
	 *     | 4  | http://wp.test/example-post/ | postmeta | _genesis_description |
	 *     +----+------------------------------+----------+----------------------+
	 *
	 *     # Find instances of gravityform 7 or 34's shortcode.
	 *     $ wp find-content 'gravityform id="7"' 'gravityform id="34"'
	 *     +----+-----------------------------+----------+
	 *     | ID | permalink                   | location |
	 *     +----+-----------------------------+----------+
	 *     | 1  | http://wp.test/hello-world  | content  |
	 *     | 3  | http://wp.test/signup       | content  |
	 *     | 8  | http://wp.test/display-meta | postmeta |
	 *     +----+-----------------------------+----------+
	 *
	 *     # Find instances of gravityform 7 or 34's shortcode and modify fields.
	 *     $ wp find-content 'gravityform id="7"' 'gravityform id="34"' --fields=ID,location,query
	 *     +----+----------+---------------------+
	 *     | ID | location | query               |
	 *     +----+----------+---------------------+
	 *     | 1  | content  | gravityform id="7"  |
	 *     | 3  | content  | gravityform id="34" |
	 *     | 8  | postmeta | gravityform id="7"  |
	 *     +----+----------+---------------------+
	 *
	 *     # Find instances of gravityform 7's shortcode format as yaml.
	 *     $ wp find-content '[gravityform id="7"' --format=yaml
	 *     ---
	 *     -
	 *       ID: "1"
	 *       permalink: http://wp.test/hello-world
	 *       location: content
	 *     -
	 *       ID: "8"
	 *       permalink: http://wp.test/display-meta
	 *       location: postmeta
	 */
	public function __invoke( $args, $assoc_args ) {
		$this->format = Utils\get_flag_value(
			$assoc_args,
			'format',
			$this->format
		);
		$this->fields = Utils\get_flag_value(
			$assoc_args,
			'fields',
			$this->fields
		);
		$this->regex = Utils\get_flag_value(
			$assoc_args,
			'regex',
			$this->regex
		);
		$this->regex_flags = Utils\get_flag_value(
			$assoc_args,
			'regex-flags',
			$this->regex_flags
		);
		$this->regex_delimiter = Utils\get_flag_value(
			$assoc_args,
			'regex-delimiter',
			chr(1)
		);

		while ( count( $args ) > 0 ) {
			$this->query = array_shift( $args );
			$this->search_post_content();
			$this->search_post_meta();
		}

		$this->display_results();
	}

	/**
	 * Sort and display results.
	 *
	 * @since 0.1.0
	 */
	protected function display_results() {
		usort(
			$this->post_results,
			array( $this, 'compare_results' )
		);

		Utils\format_items(
			$this->format,
			$this->post_results,
			$this->fields
		);
	}

	/**
	 * Comparison function for two results.
	 *
	 * Orders the results by ID.
	 *
	 * @since 0.1.0
	 *
	 * @param array $a Result for display.
	 * @param array $a Result for display.
	 * @return int -1, 0, 1 for $a<$b, $a==$b, $a>$b respectively.
	 */
	protected function compare_results( $a, $b ) {
		if ( (int) $a['ID'] === (int) $b['ID'] ) {
			return 0;
		}
		return (int) $a['ID'] < (int) $b['ID'] ? -1 : 1;
	}

	/**
	 * Populate the post_results with posts
	 * that contain the query.
	 *
	 * @since 0.1.0
	 */
	protected function search_post_content() {
		global $wpdb;
		$query = $wpdb->prepare( "
			SELECT
				{$this->get_select_values( 'content' )}
			FROM `wp_posts`
			{$this->get_where_clause( 'post_content' )}
			;",
			$this->query
		);

		$this->post_results = array_merge(
			$this->post_results,
			$this->get_post_results( $query )
		);
	}

	/**
	 * Populate the post_results with posts
	 * that contain the query in their post meta.
	 *
	 * @since 0.1.0
	 */
	protected function search_post_meta() {
		global $wpdb;
		$query = $wpdb->prepare( "
			SELECT
				{$this->get_select_values( 'postmeta' )}
			FROM `wp_postmeta`
				LEFT JOIN `wp_posts`
				ON `post_id`=`ID`
			{$this->get_where_clause( 'meta_value' )}
			;",
			$this->query
		);

		$this->post_results = array_merge(
			$this->post_results,
			$this->get_post_results( $query )
		);
	}

	/**
	 * Get string of SELECT values.
	 *
	 * @since 0.1.0
	 *
	 * @param string $location The location being queried:
	 *                         'content' or 'postmeta'.
	 */
	protected function get_select_values( $location = 'content' ) {
		$select_values = "
			`ID`,
			`post_author`,
			`post_date`,
			`post_date_gmt`,
			`post_content`,
			`post_title`,
			`post_excerpt`,
			`post_status`,
			`comment_status`,
			`ping_status`,
			`post_password`,
			`post_name`,
			`to_ping`,
			`pinged`,
			`post_modified`,
			`post_modified_gmt`,
			`post_content_filtered`,
			`post_parent`,
			`guid`,
			`menu_order`,
			`post_type`,
			`post_mime_type`,
			`comment_count`,
			'{$location}' as `location`,
			'{$this->query}' as `query`";

		if ( 'content' === $location ) {
			$select_values .= ",
				'' AS `meta_id`,
				'' AS `post_id`,
				'' AS `meta_key`,
				'' AS `meta_value`
			";
		} elseif ( 'postmeta' === $location ) {
			$select_values .= ",
				`meta_id`,
				`post_id`,
				`meta_key`,
				`meta_value`
			";
		}
		return $select_values;
	}

	/**
	 * Get SQL WHERE clause.
	 *
	 * @param string $content_column Either 'post_content' or 'meta_value'
	 * @return string SQL WHERE clause
	 */
	public function get_where_clause( $content_column = 'post_content' ) {
		if (
			! in_array(
				$content_column,
				array(
					'post_content',
					'meta_value',
				)
			)
		) {
			$content_column = 'post_content';
		}
		$where = "WHERE ${content_column} ";
		$where .= "LIKE '%%%s%%'";
		$where .= "
			AND `post_name` NOT LIKE '%-revision-%'
			AND `post_name` NOT LIKE '%-autosave-%'
		";
		return $where;
	}

	/**
	 * Query database for posts returning an array where each result
	 * is an associative array.
	 *
	 * In addition to database values, each result will have the
	 * permalink added.
	 *
	 * @since 0.1.0
	 *
	 * @param string $prepared_query Prepared SQL query.
	 */
	protected function get_post_results( $prepared_query ) {
		global $wpdb;
		return array_map(
			array( $this, 'add_permalink' ),
			$wpdb->get_results( $prepared_query, ARRAY_A )
		);
	}

	/**
	 * Add permalink to the array.
	 *
	 * If the key/value array includes the key 'ID',
	 * use this ID to determine the permalink.
	 *
	 * @since 0.1.0
	 *
	 * @param array
	 *   - 'ID' string - the post ID.
	 *   - other key/value pairs.
	 * @return array
	 *   The original key/value array with
	 *   'permalink' added.
	 */
	protected function add_permalink( $entry ) {
		if ( isset( $entry['ID'] ) ) {
			$entry['permalink'] = get_the_permalink( $entry['ID'] );
		}
		return $entry;
	}
}
