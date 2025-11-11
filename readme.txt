=== KeyCDN WebP Image Optimization ===
Contributors: gb_simon
Tags: webp, optimization, keycdn, cdn, images
Requires at least: 5.0
Tested up to: 6.8
Stable tag: 1.0.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Automatically converts WordPress images to WebP using picture elements. Works with WP Offload Media and KeyCDN for on-the-fly conversion.

== Description ==

KeyCDN WebP Image Optimization delivers production-ready WebP delivery for WordPress sites that offload media to object storage. It upgrades each qualifying `<img>` element into a `<picture>` block that prefers CDN-generated WebP assets while leaving the original file available for browsers that lack WebP support.

= Quick Start Checklist =

1. Install and configure WP Offload Media so your uploads live on S3-compatible storage (DigitalOcean Spaces, Amazon S3, etc.).
2. Point KeyCDN (or another compatible CDN) at the offloaded bucket and enable on-the-fly format conversion via the `?format=webp` query parameter.
3. Activate this plugin and adjust the toggles you need under `Settings -> KeyCDN WebP`.

= Why You'll Love It =

* **Zero duplication:** No extra WebP files saved to disk because conversion happens at the CDN edge.
* **Performance focused:** Smart detection limits processing to true WordPress and ACF media while skipping plugin or third-party assets.
* **Responsive ready:** Enhanced mode keeps `srcset`, `sizes`, and editor-defined dimensions in sync for every registered image size.
* **Debug aware:** Optional HTML comments reveal exactly how each image was processed without touching the rendered page.

= Feature Spotlight =

* **Automatic Conversion:** Upgrades `<img>` tags into `<picture>` elements with WebP-first `source` nodes.
* **Smart Detection:** Recognizes WordPress classes, ACF attributes, and uploads paths while skipping non-WordPress images.
* **CDN Integration:** Applies WebP and resize directives that mirror WordPress size variants so you only fetch the bytes you need.
* **Browser Fallback:** Leaves the original image as the final `<img>` child when WebP is unsupported.

= Example Output =

```
<picture>
  <source type="image/webp"
          srcset="https://cdn.example.com/uploads/2025/10/hero.jpg?width=1024&format=webp&quality=80">
  <img src="https://cdn.example.com/uploads/2025/10/hero.jpg"
       alt="KeyCDN WebP example"
       width="1024"
       height="512"
       loading="lazy">
</picture>
```

= Workflow =

1. **Detection:** Identify WordPress images via core classes, uploads paths, and ACF metadata.
2. **Conversion:** Wrap the image in `<picture>` with WebP sources and original fallbacks while preserving `srcset`, `sizes`, and lazy attributes.
3. **CDN Rewrite:** Append `?format=webp&quality=X` plus resize parameters that reflect the requested WordPress variant.
4. **Delivery:** Modern browsers load WebP from the CDN; legacy browsers fall back to the original file automatically.

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/keycdn-webp-optimizer/` or install via WP-CLI: `wp plugin install keycdn-webp-optimizer --activate`.
2. Activate the plugin through the **Plugins** screen in WordPress.
3. Visit **Settings -> KeyCDN WebP** to enable WebP conversion, enhanced mode, and optional debug output.
4. Confirm WP Offload Media is active and successfully offloading uploads.
5. Verify your CDN honors the `?format=webp` and optional resize (`?width=`) parameters.

== Prerequisites ==

**WP Offload Media**

* Must be installed, licensed, and configured to offload uploads to S3-compatible storage.

**KeyCDN (or compatible CDN)**

* Needs to support format conversion via URL parameters such as `?format=webp`.
* Ensure resize directives mirror WordPress size variants when requested.

== Frequently Asked Questions ==

= Do I need WP Offload Media? =

Yes. The plugin is purpose-built for environments where the media library is offloaded. Local-only libraries will not benefit from the CDN rewrite logic.

= What CDNs are supported? =

Any CDN that can convert images on-the-fly using query parameters, including KeyCDN, Cloudflare, Amazon CloudFront, Fastly, BunnyCDN, and similar services.

= Will this affect my existing images? =

No. The plugin only augments the markup sent to the browser. Original files remain untouched and act as the fallback within the `<picture>` element.

= Can I exclude certain images? =

Absolutely. The processor automatically skips Instagram feeds, third-party plugin assets, existing WebP files, and any image that lacks WordPress metadata.

= What's the difference between Basic and Enhanced mode? =

**Basic Mode** rewrites straightforward `<img>` tags, while **Enhanced Mode** also ports `srcset`, `sizes`, and custom data attributes to guarantee responsive behavior.

= How do I know if it's working? =

1. Check the status cards on the settings page.
2. Temporarily enable debug mode to reveal HTML comments.
3. Inspect images via browser DevTools to confirm the `<picture>` structure.
4. Review CDN logs for requests containing `?format=webp`.

== Screenshots ==

1. Admin settings page with prerequisites information.
2. Configuration options and real-time status indicators.
3. Debug mode showing conversion comments in markup.

== Changelog ==

= 1.0.3 =

* Security: Fixed all output escaping issues to prevent XSS vulnerabilities.
* Security: Replaced all `_e()` calls with `esc_html_e()` for proper escaping.
* Security: Escaped all URLs, variables, and translated strings.
* Code quality: Removed discouraged `load_plugin_textdomain()` function (WordPress handles translations automatically since 4.6).
* Code quality: Removed invalid `Network` header from plugin file.
* Documentation: Created proper `readme.txt` file for WordPress.org compatibility.
* Documentation: Fixed short description length to meet WordPress.org requirements.

= 1.0.2 =

* Enhancement: Derive CDN resize parameters from the actual attachment variant so medium, thumbnail, and custom crops request correctly sized images.
* Enhancement: Preserve editor-specified dimensions while clamping requests to the underlying file's maximum size.

= 1.0.1 =

* Fix: Preserve responsive `sizes`/`data-sizes` attributes by copying them to `<source>` within `<picture>` for images inserted via Add Media and other WP/ACF images. Prevents incorrect source selection and layout issues.

= 1.0.0 =

* Initial release of KeyCDN WebP Image Optimization.
* Automatic `<img>` to `<picture>` conversion.
* WP Offload Media integration.
* KeyCDN support.
* Admin settings page.
* Smart image detection.
* Enhanced mode for responsive images.
* Debug mode for troubleshooting.

== Upgrade Notice ==

= 1.0.3 =

Security and code quality improvements. All output is now properly escaped, and the plugin follows WordPress.org coding standards.

= 1.0.2 =

Improves CDN resizing logic to honour WordPress size variants (including custom crops) so smaller images no longer load full-size assets.

= 1.0.1 =

Copies `sizes`/`data-sizes` to `<source>` to fix responsive image selection in converted `<picture>` elements.

= 1.0.0 =

Initial release of KeyCDN WebP Image Optimization plugin.

== Technical Details ==

**Settings Options**

* `keycdn_webp_enabled` -- Enable or disable WebP conversion globally.
* `keycdn_webp_enhanced` -- Enable enhanced mode for `srcset` handling.
* `keycdn_webp_debug` -- Toggle debug HTML comments.
* `keycdn_webp_quality` -- Set WebP quality (1-100).

**Detection Logic**

* WordPress attachment classes (`wp-image-123`).
* WordPress size classes (`size-thumbnail`, etc.).
* Advanced Custom Fields data attributes.
* WordPress uploads directory (`/wp-content/uploads/`).
* Focal point styles (`object-position`).
* Descriptive alt text (10+ characters).

**Exclusions**

* Instagram feed images (`sb-instagram-feed-images`).
* Existing WebP assets (`.webp` extension).
* Plugin or static assets that live under `/plugins/`.
* Images without meaningful WordPress metadata.

== Support ==

For feature requests, bug reports, or contributions, open an issue or pull request on GitHub. Documentation updates and feedback are always welcome.

== Privacy Policy ==

This plugin does not collect, store, or transmit personal data. All processing occurs server-side within your WordPress installation.

