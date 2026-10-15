<?php
/**
 * Builds _playground/blueprint.json from _playground/seed.php.
 *
 * The seed lives in a real PHP file so it can be linted and tested; this
 * script embeds it into the blueprint's runPHP step.
 *
 * Usage: php bin/build-blueprint.php
 *
 * @package External_Link_Tagger
 */

$root = dirname( __DIR__ );
$seed = file_get_contents( $root . '/_playground/seed.php' );

$blueprint = array(
	'$schema'           => 'https://playground.wordpress.net/blueprint-schema.json',
	'meta'              => array(
		'title'       => 'External Link Tagger demo',
		'description' => 'Creates a post mixing internal and external links. A demo-only style highlights every link the plugin tagged, since target and rel attributes are otherwise invisible.',
		'author'      => 'jdyach',
		'categories'  => array( 'Plugin' ),
	),
	'landingPage'       => '/link-tagging-demo/',
	'preferredVersions' => array(
		'php' => '8.3',
		'wp'  => 'latest',
	),
	'features'          => array( 'networking' => true ),
	'login'             => true,
	'steps'             => array(
		array(
			'step'      => 'installTheme',
			'themeData' => array(
				'resource' => 'git:directory',
				'url'      => 'https://github.com/jdyach/fieldnote',
				'ref'      => 'main',
			),
			'options'   => array( 'activate' => true ),
		),
		array(
			'step'       => 'installPlugin',
			'pluginData' => array(
				'resource' => 'git:directory',
				'url'      => 'https://github.com/jdyach/external-link-tagger',
				'ref'      => 'main',
			),
			'options'    => array( 'activate' => true ),
		),
		array(
			'step' => 'runPHP',
			'code' => $seed,
		),
	),
);

file_put_contents(
	$root . '/_playground/blueprint.json',
	json_encode( $blueprint, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . "\n"
);
echo "Wrote _playground/blueprint.json\n";
