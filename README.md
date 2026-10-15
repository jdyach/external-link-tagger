# External Link Tagger

A WordPress plugin that automatically adds `target="_blank"` and `rel="noopener noreferrer"` to external links in post content, excerpts, and text widgets — with a simple settings page to control where it applies.

[![Try it live in WordPress Playground](https://img.shields.io/badge/Try%20it%20live-WordPress%20Playground-3858e9?logo=wordpress&logoColor=white&style=for-the-badge)](https://playground.wordpress.net/?blueprint-url=https://raw.githubusercontent.com/jdyach/external-link-tagger/main/_playground/blueprint.json)

The live demo runs a real WordPress site in your browser with this plugin, the [Fieldnote](https://github.com/jdyach/fieldnote) block theme and sample content. It opens a post mixing internal and external links. External links are highlighted (demo-only styling) to show which ones received `target="_blank"` and `rel="noopener noreferrer"`. Nothing to install.

## Features

- Processes links in **post content**, **excerpts**, and **text widgets** (each toggleable)
- Uses **DOMDocument** for reliable HTML parsing — no fragile regex
- Detects external links by comparing the link hostname against `home_url()`, so subdomains of your own site are never tagged
- Skips non-HTTP links (`mailto:`, `tel:`, `#anchors`, `javascript:`) automatically
- Preserves any existing `rel` attribute values — only appends what's missing
- Settings page under **Settings → External Link Tagger**
- Defaults all options to on at activation via `register_activation_hook`

## Installation

1. Upload the `external-link-tagger` folder to `/wp-content/plugins/`
2. Activate through **Plugins → Installed Plugins**
3. Optionally adjust which areas are tagged under **Settings → External Link Tagger**

## How It Works

The plugin hooks into `the_content`, `the_excerpt`, and `widget_text` filters. Each filter runs the HTML through `DOMDocument::loadHTML()`, iterates all `<a>` tags, checks whether the `href` is external, and sets `target` and `rel` attributes. The modified HTML is returned from `saveHTML()`.

Settings are stored as a single serialized array in `wp_options` via the WordPress Settings API.

## WordPress Concepts Demonstrated

| Concept | Where Used |
|---|---|
| Content filters (`the_content`, `the_excerpt`, `widget_text`) | `class-elt-tagger.php` |
| `DOMDocument` HTML parsing | `class-elt-tagger.php` |
| `home_url()` + `wp_parse_url()` for host comparison | `class-elt-tagger.php` |
| Settings API (`register_setting`, `add_settings_section/field`) | `class-elt-settings.php` |
| `register_activation_hook` for default options | `external-link-tagger.php` |
| `add_options_page` | `class-elt-settings.php` |
| `wp_parse_args` for option defaults | `class-elt-tagger.php` |

## File Structure

```
external-link-tagger/
├── external-link-tagger.php        # Bootstrap, constants, activation hook
├── README.md
├── assets/
│   └── css/
│       └── admin.css               # Settings page styles
└── includes/
    ├── class-elt-tagger.php        # Core DOMDocument link processing
    └── class-elt-settings.php      # Settings API page
```

## Requirements

- WordPress 6.0+
- PHP 8.0+

## License

GPL-2.0-or-later
