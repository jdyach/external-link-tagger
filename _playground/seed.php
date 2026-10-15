<?php
/**
 * Demo content for the WordPress Playground "Try it live" link.
 *
 * Runs once inside Playground: blueprint.json embeds this file (rebuild it
 * with `php bin/build-blueprint.php`). Creates a post mixing internal and external links. A demo-only style highlights every link the plugin tagged, since target and rel attributes are otherwise invisible.
 *
 * Not loaded by the plugin.
 *
 * @package External_Link_Tagger
 */

require_once '/wordpress/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

wp_set_current_user( 1 );

// Start from a clean site: remove the default "Hello world!" post and sample page.
foreach ( get_posts( array( 'post_type' => array( 'post', 'page' ), 'numberposts' => -1, 'post_status' => 'any' ) ) as $demo_old ) {
	wp_delete_post( $demo_old->ID, true );
}

update_option( 'blogname', 'External Link Tagger' );
update_option( 'blogdescription', 'Live demo' );
// Pretty permalinks. Set them through WP_Rewrite, not update_option(): in
// Playground the routing rules already exist by now, and only this updates
// WordPress's in-memory copy so the flush rebuilds them correctly.
$GLOBALS['wp_rewrite']->set_permalink_structure( '/%postname%/' );
flush_rewrite_rules();

/**
 * Upload one of the Fieldnote theme's images and return its attachment ID.
 *
 * @param string $name Image file name without extension.
 * @return int Attachment ID, or 0 when the theme image is unavailable.
 */
function demo_theme_image( $name ) {
	$source = get_theme_file_path( "assets/images/$name.webp" );
	if ( ! file_exists( $source ) ) {
		return 0;
	}
	$upload = wp_upload_bits( "$name.webp", null, file_get_contents( $source ) );
	if ( ! empty( $upload['error'] ) ) {
		return 0;
	}
	$id = wp_insert_attachment(
		array(
			'post_mime_type' => 'image/webp',
			'post_title'     => $name,
			'post_status'    => 'inherit',
		),
		$upload['file']
	);
	wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $upload['file'] ) );
	return $id;
}

/**
 * A long article in block markup: H2 sections with H3 subsections.
 *
 * @param array $sections Section titles.
 * @param int   $paras    Paragraphs per section.
 * @return string Block markup.
 */
function demo_article( $sections, $paras = 2 ) {
	$text = 'A good website is less about the launch and more about the years after it. The people editing it next need clear patterns, sensible defaults and documentation they will actually read. Small decisions made early, like naming, structure and what is configurable, decide how easy every later change will be.';
	$html = '';
	foreach ( $sections as $section ) {
		$html .= '<!-- wp:heading --><h2 class="wp-block-heading">' . esc_html( $section ) . '</h2><!-- /wp:heading -->';
		for ( $i = 0; $i < $paras; $i++ ) {
			$html .= '<!-- wp:paragraph --><p>' . $text . '</p><!-- /wp:paragraph -->';
		}
		$html .= '<!-- wp:heading {"level":3} --><h3 class="wp-block-heading">' . esc_html( $section ) . ' checklist</h3><!-- /wp:heading -->';
		$html .= '<!-- wp:paragraph --><p>' . $text . '</p><!-- /wp:paragraph -->';
	}
	return $html;
}

/**
 * Insert a published post or page.
 *
 * @param array $args wp_insert_post() arguments.
 * @return int Post ID.
 */
function demo_post( $args ) {
	return wp_insert_post( array_merge( array( 'post_status' => 'publish' ), $args ) );
}

/**
 * Write a small demo-only must-use plugin. These files exist only inside
 * the Playground demo; they are not part of the plugin.
 *
 * @param string $name File name.
 * @param string $code PHP source.
 */
function demo_mu_plugin( $name, $code ) {
	wp_mkdir_p( WPMU_PLUGIN_DIR );
	file_put_contents( trailingslashit( WPMU_PLUGIN_DIR ) . $name, $code );
}

/** Skip the block editor's welcome guide so visitors land straight on the content. */
function demo_skip_welcome_guide( $extra = array() ) {
	update_user_meta(
		1,
		$GLOBALS['wpdb']->get_blog_prefix() . 'persisted_preferences',
		array_merge(
			array(
				'core/edit-post' => array( 'welcomeGuide' => false ),
				'core'           => array( 'welcomeGuide' => false ),
				'_modified'      => gmdate( 'c' ),
			),
			$extra
		)
	);
}

demo_mu_plugin(
	'demo-highlight-external-links.php',
	<<<'PHP'
<?php
/**
 * Plugin Name: Highlight tagged links (Playground only)
 * Description: Makes the target/rel attributes added by External Link Tagger visible in the demo.
 */
add_action(
	'wp_head',
	function () {
		echo '<style>a[target="_blank"][rel~="noopener"]{outline:2px dashed #a8451f;outline-offset:3px;border-radius:3px}a[target="_blank"][rel~="noopener"]::after{content:" \2197";font-weight:700;color:#a8451f}</style>';
	}
);
PHP
);

$about = demo_post(
	array(
		'post_type'  => 'page',
		'post_title' => 'About',
	)
);
demo_post(
	array(
		'post_title'   => 'Link tagging demo',
		'post_name'    => 'link-tagging-demo',
		'post_content' => '<!-- wp:paragraph {"fontSize":"large"} --><p class="has-large-font-size">Links to other sites (highlighted with a dashed outline and an arrow in this demo) were given <code>target="_blank"</code> and <code>rel="noopener noreferrer"</code> automatically. Links to this site were left alone.</p><!-- /wp:paragraph -->'
			. '<!-- wp:heading --><h2 class="wp-block-heading">External links</h2><!-- /wp:heading -->'
			. '<!-- wp:list --><ul class="wp-block-list"><!-- wp:list-item --><li>The <a href="https://developer.wordpress.org/plugins/">WordPress Plugin Handbook</a></li><!-- /wp:list-item --><!-- wp:list-item --><li><a href="https://github.com/jdyach">jdyach on GitHub</a></li><!-- /wp:list-item --><!-- wp:list-item --><li><a href="https://www.w3.org/WAI/">W3C Web Accessibility Initiative</a></li><!-- /wp:list-item --></ul><!-- /wp:list -->'
			. '<!-- wp:heading --><h2 class="wp-block-heading">Internal links (unchanged)</h2><!-- /wp:heading -->'
			. '<!-- wp:list --><ul class="wp-block-list"><!-- wp:list-item --><li><a href="' . esc_url( get_permalink( $about ) ) . '">Our About page</a></li><!-- /wp:list-item --><!-- wp:list-item --><li><a href="/">The home page</a> (relative link)</li><!-- /wp:list-item --></ul><!-- /wp:list -->'
			. '<!-- wp:paragraph --><p>Open your browser\'s developer tools and inspect any link to see the attributes. Settings live under <strong>Settings → External Link Tagger</strong>.</p><!-- /wp:paragraph -->',
	)
);
