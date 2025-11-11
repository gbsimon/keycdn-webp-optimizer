# KeyCDN WebP Image Optimization

> Modern WebP delivery for WordPress media libraries offloaded via WP Offload Media + KeyCDN.

| Meta             | Value                                                                 |
| ---------------- | --------------------------------------------------------------------- |
| **Contributors** | `gb_simon`                                                            |
| **Tags**         | webp, optimization, keycdn, cdn, images, performance                  |
| **Requires**     | WordPress 5.0+ · PHP 7.4+                                             |
| **Tested up to** | WordPress 6.8                                                         |
| **Stable tag**   | 1.0.2                                                                 |
| **License**      | GPLv2 or later ([license](https://www.gnu.org/licenses/gpl-2.0.html)) |

Automatically converts WordPress images to WebP using `<picture>` elements, integrates with WP Offload Media, and leverages KeyCDN for on-the-fly, no-duplicate conversions.

---

## Table of Contents

- [Highlights](#highlights)
- [Quick Start](#quick-start)
- [Feature Spotlight](#feature-spotlight)
- [Example Output](#example-output)
- [Workflow](#workflow)
- [Installation](#installation)
- [Prerequisites](#prerequisites)
- [FAQ](#faq)
- [Changelog](#changelog)
- [Upgrade Notice](#upgrade-notice)
- [Technical Details](#technical-details)
- [Support](#support)
- [Privacy](#privacy)

---

## Highlights

- **Edge-Powered WebP:** CDN performs real-time WebP conversions—no duplicate files or background jobs.
- **WordPress Native:** Targets core/ACF images only; plugin assets and external embeds are left untouched.
- **Responsive-Ready:** Maintains `srcset`, `sizes`, lazy-loading, and editor-defined dimensions.
- **Debug-Friendly:** Optional HTML comments give you a transparent audit trail during development.

---

## Quick Start

- [ ] Configure **WP Offload Media** to push uploads to S3-compatible storage (DigitalOcean Spaces, Amazon S3, etc.).
- [ ] Point **KeyCDN (or another compatible CDN)** at the bucket and enable format conversion via `?format=webp`.
- [ ] Install & activate **KeyCDN WebP Image Optimization**.
- [ ] Visit **Settings → KeyCDN WebP** to toggle features such as Enhanced Mode and Debug Mode.

> [!TIP]
> Keep a browser console open with DevTools when first testing—it's the quickest way to verify the in-page `<picture>` markup and CDN responses.

---

## Feature Spotlight

| Feature                  | What it delivers                                                                                    |
| ------------------------ | --------------------------------------------------------------------------------------------------- |
| **Automatic Conversion** | Upgrades `<img>` tags into `<picture>` blocks with WebP-first `<source>` elements.                  |
| **Smart Detection**      | Validates WordPress classes, uploads paths, and ACF data before rewriting to avoid false positives. |
| **CDN Integration**      | Appends WebP and width parameters that mirror the requested WordPress size variant.                 |
| **Browser Fallback**     | Leaves the original image as the final `<img>` element, ensuring compatibility everywhere.          |

---

## Example Output

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

---

## Workflow

1. **Detection** – Identify WordPress images via core classes, uploads paths, and ACF metadata.
2. **Conversion** – Wrap each image in a `<picture>` element, preserving `srcset`, `sizes`, lazy-loading, and data attributes.
3. **CDN Rewrite** – Append `?format=webp&quality=X` plus WordPress-aware resize parameters.
4. **Delivery** – Browsers that support WebP consume the new source; older browsers use the fallback `<img>`.

---

## Installation

1. Upload the plugin to `/wp-content/plugins/keycdn-webp-optimizer/` or install via WP-CLI:
   ```
   wp plugin install keycdn-webp-optimizer --activate
   ```
2. Activate through the **Plugins** screen.
3. Configure settings under **Settings → KeyCDN WebP**.
4. Confirm WP Offload Media is offloading uploads successfully.
5. Verify your CDN honors both `?format=webp` and resize (`?width=`) query parameters.

---

## Prerequisites

**WP Offload Media**

- Installed, licensed, and configured to offload to S3-compatible storage.

**KeyCDN (or compatible CDN)**

- Supports on-the-fly format conversion via URL parameters (like `?format=webp`).
- Can respect width directives for WordPress size variants.

---

## FAQ

**Do I need WP Offload Media?**  
Yes—this plugin assumes your media library is offloaded. Local-only libraries won't benefit from the CDN rewrite logic.

**Which CDNs are supported?**  
Any CDN that understands format/resize parameters, including KeyCDN, Cloudflare, Amazon CloudFront, Fastly, BunnyCDN, and similar services.

**Will this change my original images?**  
No. The plugin only adjusts markup. Source files remain untouched and serve as fallbacks.

**Can I exclude certain images?**  
Yes. Instagram feeds, third-party plugin assets, existing WebP files, and images without WordPress metadata are skipped automatically.

**What’s the difference between Basic and Enhanced Mode?**  
Basic Mode rewrites simple `<img>` tags. Enhanced Mode also carries over `srcset`, `sizes`, and custom data attributes for responsive layouts.

**How do I confirm it’s working?**  
Check the settings page status cards, enable debug comments temporarily, inspect markup via DevTools, and monitor CDN logs for `?format=webp` requests.

---

## Changelog

### 1.0.2

- Enhancement: Derive CDN resize parameters from the attachment variant so thumbnails request correctly sized assets.
- Enhancement: Preserve editor-defined dimensions while clamping requests to the original file’s max size.

### 1.0.1

- Fix: Mirror responsive `sizes` and `data-sizes` attributes into `<source>` nodes for proper responsive behavior.

### 1.0.0

- Initial release of KeyCDN WebP Image Optimization with automatic `<img>` to `<picture>` conversion, WP Offload Media integration, KeyCDN support, admin settings, smart detection, enhanced mode, and debug tooling.

---

## Upgrade Notice

- **1.0.2** – Honors WordPress size variants (including custom crops) so smaller images don’t fall back to the full-size asset.
- **1.0.1** – Copies `sizes`/`data-sizes` into `<source>` elements to maintain responsive rendering.
- **1.0.0** – Initial release.

---

## Technical Details

**Settings Options**

- `keycdn_webp_enabled` — Toggle site-wide WebP conversion.
- `keycdn_webp_enhanced` — Enable enhanced `srcset` handling.
- `keycdn_webp_debug` — Surface HTML comments for debugging.
- `keycdn_webp_quality` — Define WebP quality (1-100).

**Detection Logic**

- WordPress attachment classes (`wp-image-123`).
- WordPress size classes (`size-thumbnail`, etc.).
- Advanced Custom Fields data attributes.
- Uploads directory checks (`/wp-content/uploads/`).
- Focal point styles (`object-position`).
- Descriptive alt text (10+ characters).

**Exclusions**

- Instagram feed images (`sb-instagram-feed-images`).
- Existing WebP assets (`.webp` extension).
- Plugin or static assets within `/plugins/`.
- Images lacking WordPress metadata.

---

## Support

Open an issue or pull request on GitHub for feature requests, bug reports, or documentation tweaks. Contributions are welcome.

---

## Privacy

The plugin does not collect, store, or transmit personal data. All processing happens within your WordPress installation.
