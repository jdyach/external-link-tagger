<?php
/**
 * Core link-tagging logic: hooks into content filters and processes HTML
 * with DOMDocument to find and modify external anchor tags.
 *
 * @package External_Link_Tagger
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ELT_Tagger {

    public static function init(): void {
        $options = self::get_options();

        if ( ! empty( $options['tag_content'] ) ) {
            add_filter( 'the_content', [ __CLASS__, 'tag_links' ], 20 );
        }
        if ( ! empty( $options['tag_excerpts'] ) ) {
            add_filter( 'the_excerpt', [ __CLASS__, 'tag_links' ], 20 );
        }
        if ( ! empty( $options['tag_widgets'] ) ) {
            add_filter( 'widget_text', [ __CLASS__, 'tag_links' ], 20 );
        }
    }

    /**
     * Parse the HTML string, find external links, and add the required attributes.
     *
     * @param string $content Raw HTML content.
     * @return string Modified HTML content.
     */
    public static function tag_links( string $content ): string {
        if ( empty( $content ) || ! str_contains( $content, '<a ' ) ) {
            return $content;
        }

        $options     = self::get_options();
        $add_noopener = ! empty( $options['add_noopener'] );
        $site_host   = wp_parse_url( home_url(), PHP_URL_HOST );

        // Use DOMDocument for reliable HTML parsing — no regex on HTML.
        $dom = new DOMDocument();
        libxml_use_internal_errors( true );

        // UTF-8 wrapper prevents DOMDocument from mangling encoded characters.
        $dom->loadHTML(
            '<?xml encoding="UTF-8">' . $content,
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();

        $anchors = $dom->getElementsByTagName( 'a' );

        // Iterate over a static array since we'll be modifying attributes.
        foreach ( iterator_to_array( $anchors ) as $anchor ) {
            $href = $anchor->getAttribute( 'href' );

            if ( empty( $href ) ) {
                continue;
            }

            // Skip non-http(s) links: mailto:, tel:, #anchors, javascript:, etc.
            if ( ! str_starts_with( $href, 'http://' ) && ! str_starts_with( $href, 'https://' ) ) {
                continue;
            }

            $link_host = wp_parse_url( $href, PHP_URL_HOST );

            // Skip internal links (same host or subdomain of this site).
            if ( $link_host && str_ends_with( $link_host, $site_host ) ) {
                continue;
            }

            // Add target="_blank".
            $anchor->setAttribute( 'target', '_blank' );

            // Build rel attribute — preserve any existing values, add ours if missing.
            if ( $add_noopener ) {
                $existing_rel = $anchor->getAttribute( 'rel' );
                $rel_parts    = $existing_rel ? explode( ' ', $existing_rel ) : [];

                if ( ! in_array( 'noopener', $rel_parts, true ) ) {
                    $rel_parts[] = 'noopener';
                }
                if ( ! in_array( 'noreferrer', $rel_parts, true ) ) {
                    $rel_parts[] = 'noreferrer';
                }

                $anchor->setAttribute( 'rel', implode( ' ', $rel_parts ) );
            }
        }

        // Extract the body content, stripping the wrapper DOMDocument adds.
        $output = $dom->saveHTML();

        // Remove the XML declaration we added.
        $output = str_replace( '<?xml encoding="UTF-8">', '', $output );

        return trim( $output );
    }

    /**
     * Returns the plugin options with defaults as fallback.
     *
     * @return array<string, string>
     */
    private static function get_options(): array {
        $defaults = [
            'tag_content'  => '1',
            'tag_excerpts' => '1',
            'tag_widgets'  => '1',
            'add_noopener' => '1',
        ];
        return wp_parse_args( get_option( ELT_OPTIONS_KEY, [] ), $defaults );
    }
}
