=== KeyCDN WebP Image Optimization ===
Contributors: your-username
Tags: webp, optimization, keycdn, cdn, images, performance
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Automatically converts WordPress images to WebP format using picture elements. Works with WP Offload Media and KeyCDN for on-the-fly WebP conversion.

== Description ==

KeyCDN WebP Image Optimization automatically converts your WordPress images to WebP format using modern `<picture>` elements. The plugin works seamlessly with WP Offload Media and KeyCDN to provide on-the-fly WebP conversion without storing duplicate files.

= Key Features =

* **Automatic Conversion**: Converts `<img>` tags to `<picture>` elements with WebP support
* **Smart Detection**: Only processes WordPress/ACF images, excludes plugins and third-party content
* **CDN Integration**: Works with WP Offload Media and KeyCDN for on-the-fly conversion
* **Responsive Images**: Enhanced mode handles `srcset` attributes for complete coverage
* **Browser Fallback**: Automatically serves original format to unsupported browsers
* **Admin Control**: Easy-to-use settings page with real-time status
* **Debug Mode**: Optional HTML comments for troubleshooting
* **Size Aware**: Automatically matches CDN resize parameters (including custom crops) to each requested WordPress image size

= How It Works =

1. **Detection**: Identifies WordPress images using classes, uploads path, and ACF attributes
2. **Conversion**: Converts `<img>` tags to `<picture>` elements with WebP source
3. **CDN Integration**: Adds `?format=webp&quality=X` along with size-appropriate `?width=` parameters to image URLs
4. **Browser Support**: Modern browsers load WebP, older browsers get original format

= Prerequisites =

**WP Offload Media Plugin**
* Must be installed and configured
* Images should be offloaded to Digital Ocean Spaces or similar S3-compatible storage

**KeyCDN Configuration**
* Your CDN must support format conversion via URL parameters
* Enable `?format=webp` parameter support in your CDN settings
* The CDN should convert images to WebP on-the-fly

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/keycdn-webp-optimizer` directory
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Go to Settings > KeyCDN WebP to configure the plugin
4. Ensure WP Offload Media is installed and configured
5. Verify your CDN supports `?format=webp` parameter

== Frequently Asked Questions ==

= Do I need WP Offload Media? =

Yes, this plugin is designed to work with WP Offload Media. It processes images that have been offloaded to your CDN and adds WebP conversion parameters to the URLs.

= What CDNs are supported? =

The plugin works with any CDN that supports format conversion via URL parameters, including:
* KeyCDN
* Cloudflare
* Amazon CloudFront
* Any CDN with similar functionality

= Will this affect my existing images? =

No, the plugin only adds WebP sources as alternatives. Original images remain unchanged and are used as fallbacks for older browsers.

= Can I exclude certain images? =

Yes, the plugin automatically excludes:
* Instagram feed images
* Plugin images
* Already converted WebP images
* Images without WordPress-specific attributes

= What's the difference between Basic and Enhanced mode? =

* **Basic Mode**: Converts simple `<img>` tags
* **Enhanced Mode**: Also handles responsive images with `srcset` attributes for complete coverage

= How do I know if it's working? =

1. Check the status section in Settings > KeyCDN WebP
2. Enable debug mode to see HTML comments
3. Use browser developer tools to inspect converted images
4. Check your CDN logs for WebP requests

== Screenshots ==

1. Admin settings page with prerequisites information
2. Configuration options with real-time status
3. Debug mode showing conversion comments in HTML

== Changelog ==

= 1.0.2 =
* Enhancement: Derive CDN resize parameters from the actual attachment variant so medium, thumbnail, and custom crops request correctly sized images (no more full-size fallbacks).
* Enhancement: Preserve editor-specified dimensions while clamping requests to the underlying file’s maximum size.

= 1.0.1 =
* Fix: Preserve responsive `sizes`/`data-sizes` attributes by copying them to `<source>` within `<picture>` for images inserted via Add Media and other WP/ACF images. Prevents incorrect source selection/layout issues.

= 1.0.0 =
* Initial release
* Automatic img to picture element conversion
* WP Offload Media integration
* KeyCDN support
* Admin settings page
* Smart image detection
* Enhanced mode for responsive images
* Debug mode for troubleshooting

== Upgrade Notice ==

= 1.0.2 =
Improves CDN resizing logic to honour WordPress size variants (including custom crops) so smaller images no longer load full-size assets.

= 1.0.1 =
Copies `sizes`/`data-sizes` to `<source>` to fix responsive image selection in converted `<picture>` elements.

= 1.0.0 =
Initial release of KeyCDN WebP Image Optimization plugin.

== Technical Details ==

**Settings Options:**
* `keycdn_webp_enabled` - Enable/disable WebP conversion
* `keycdn_webp_enhanced` - Enable enhanced mode for srcset handling
* `keycdn_webp_debug` - Enable debug mode for troubleshooting
* `keycdn_webp_quality` - WebP quality setting (1-100)

**Detection Logic:**
* WordPress attachment classes (`wp-image-123`)
* WordPress size classes (`size-thumbnail`, etc.)
* ACF data attributes
* WordPress uploads directory (`/wp-content/uploads/`)
* Focal point styles (`object-position`)
* Descriptive alt text (10+ characters)

**Exclusions:**
* Instagram feed images (`sb-instagram-feed-images`)
* Already WebP images (`.webp` extension)
* Plugin images (`/plugins/` path)
* Images without WordPress attributes

== Support ==

For support, feature requests, or bug reports, please visit the plugin's GitHub repository or contact the developer.

== Privacy Policy ==

This plugin does not collect, store, or transmit any personal data. All processing is done locally on your WordPress installation.

