<?php
/**
 * Settings page under Settings > External Link Tagger.
 *
 * @package External_Link_Tagger
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ELT_Settings {

    const PAGE_SLUG   = 'external-link-tagger';
    const OPTION_GROUP = 'elt_settings_group';

    public static function init(): void {
        add_action( 'admin_menu',    [ __CLASS__, 'register_page' ] );
        add_action( 'admin_init',    [ __CLASS__, 'register_settings' ] );
        add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue' ] );
    }

    public static function register_page(): void {
        add_options_page(
            __( 'External Link Tagger', 'external-link-tagger' ),
            __( 'External Link Tagger', 'external-link-tagger' ),
            'manage_options',
            self::PAGE_SLUG,
            [ __CLASS__, 'render_page' ]
        );
    }

    public static function register_settings(): void {
        register_setting(
            self::OPTION_GROUP,
            ELT_OPTIONS_KEY,
            [
                'type'              => 'array',
                'sanitize_callback' => [ __CLASS__, 'sanitize' ],
            ]
        );

        add_settings_section(
            'elt_main_section',
            __( 'Where to tag links', 'external-link-tagger' ),
            '__return_false',
            self::PAGE_SLUG
        );

        $fields = [
            'tag_content'  => __( 'Post / page content', 'external-link-tagger' ),
            'tag_excerpts' => __( 'Post excerpts', 'external-link-tagger' ),
            'tag_widgets'  => __( 'Text widgets', 'external-link-tagger' ),
            'add_noopener' => __( 'Add <code>rel="noopener noreferrer"</code>', 'external-link-tagger' ),
        ];

        foreach ( $fields as $key => $label ) {
            add_settings_field(
                'elt_' . $key,
                $label,
                [ __CLASS__, 'render_checkbox' ],
                self::PAGE_SLUG,
                'elt_main_section',
                [ 'key' => $key ]
            );
        }
    }

    /**
     * Render a single checkbox field.
     *
     * @param array{key: string} $args
     */
    public static function render_checkbox( array $args ): void {
        $options = get_option( ELT_OPTIONS_KEY, [] );
        $key     = $args['key'];
        $checked = ! empty( $options[ $key ] );
        ?>
        <input
            type="checkbox"
            name="<?php echo esc_attr( ELT_OPTIONS_KEY . '[' . $key . ']' ); ?>"
            value="1"
            <?php checked( $checked ); ?>
            id="elt_<?php echo esc_attr( $key ); ?>"
        />
        <?php
    }

    /**
     * Sanitize settings on save.
     *
     * @param mixed $input
     * @return array<string, string>
     */
    public static function sanitize( mixed $input ): array {
        $keys = [ 'tag_content', 'tag_excerpts', 'tag_widgets', 'add_noopener' ];
        $clean = [];
        foreach ( $keys as $key ) {
            $clean[ $key ] = isset( $input[ $key ] ) ? '1' : '';
        }
        return $clean;
    }

    public static function enqueue( string $hook ): void {
        if ( 'settings_page_' . self::PAGE_SLUG !== $hook ) {
            return;
        }
        wp_enqueue_style(
            'elt-admin',
            ELT_PLUGIN_URL . 'assets/css/admin.css',
            [],
            ELT_VERSION
        );
    }

    public static function render_page(): void {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have permission to access this page.', 'external-link-tagger' ) );
        }
        ?>
        <div class="wrap elt-wrap">
            <h1><?php esc_html_e( 'External Link Tagger', 'external-link-tagger' ); ?></h1>
            <p class="elt-subtitle">
                <?php esc_html_e( 'External links in the selected areas will automatically receive target="_blank" and, optionally, rel="noopener noreferrer".', 'external-link-tagger' ); ?>
            </p>
            <form method="post" action="options.php">
                <?php
                settings_fields( self::OPTION_GROUP );
                do_settings_sections( self::PAGE_SLUG );
                submit_button( __( 'Save Settings', 'external-link-tagger' ) );
                ?>
            </form>
        </div>
        <?php
    }
}
