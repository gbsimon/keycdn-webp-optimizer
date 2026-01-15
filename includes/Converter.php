<?php
/**
 * WebP Converter
 * 
 * Handles the conversion of img tags to picture elements with WebP support
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Initialize converter hooks
 */
function tomtom_image_optim_init_converter() {
	// Only enable if WebP is enabled and we're on frontend
	if ( get_option( 'keycdn_webp_enabled', true ) && ! is_admin() ) {
		add_action( 'template_redirect', 'tomtom_image_optim_enable_webp_picture_elements', 1 );
		add_action( 'shutdown', 'tomtom_image_optim_flush_output_buffer', 999 );
	}
}
add_action( 'init', 'tomtom_image_optim_init_converter' );

/**
 * Track the buffer level when we start buffering
 */
function tomtom_image_optim_get_initial_buffer_level() {
	static $initial_level = null;
	return $initial_level;
}

/**
 * Set the initial buffer level
 */
function tomtom_image_optim_set_initial_buffer_level( $level ) {
	static $initial_level = null;
	$initial_level = $level;
}

/**
 * Enable WebP picture elements via output buffering
 */
function tomtom_image_optim_enable_webp_picture_elements() {
	// Store the buffer level before we start our buffer
	$initial_level = ob_get_level();
	tomtom_image_optim_set_initial_buffer_level( $initial_level );
	
	// Start output buffering with our callback
	ob_start( 'tomtom_image_optim_process_html' );
}

/**
 * Flush output buffer on shutdown to ensure proper cleanup
 */
function tomtom_image_optim_flush_output_buffer() {
	$initial_level = tomtom_image_optim_get_initial_buffer_level();
	$current_level = ob_get_level();
	
	// Only flush if we started a buffer and the current level indicates our buffer is still active
	// Our buffer should be at initial_level + 1 (we added one buffer)
	if ( $initial_level !== null && $current_level === $initial_level + 1 ) {
		// Our buffer is the topmost one and exactly one level above where we started
		// This means it's definitely our buffer - safe to flush
		if ( ob_get_level() > 0 ) {
			ob_end_flush();
		}
	}
	// If current_level > initial_level + 1, other buffers were started after ours
	// In that case, WordPress will handle flushing all buffers at shutdown,
	// but we've ensured our buffer is properly tracked and will be processed
}

/**
 * Process HTML content
 */
function tomtom_image_optim_process_html( $html ) {
	// Use enhanced version if enabled
	if ( get_option( 'keycdn_webp_enhanced', true ) ) {
		return tomtom_image_optim_convert_images_to_webp_picture_enhanced( $html );
	} else {
		return tomtom_image_optim_convert_images_to_webp_picture( $html );
	}
}
    
/**
 * Convert img tags to picture elements with WebP support
 * Only targets images from WordPress/ACF functions
 * @param string $html HTML content
 * @return string Modified HTML with picture elements
 */
function tomtom_image_optim_convert_images_to_webp_picture( $html ) {
        // First, remove any existing picture elements from processing
        $picture_pattern = '/<picture[^>]*>.*?<\/picture>/is';
        $picture_matches = array();
        $html = preg_replace_callback($picture_pattern, function($matches) use (&$picture_matches) {
            $placeholder = '<!--PICTURE_PLACEHOLDER_' . count($picture_matches) . '-->';
            $picture_matches[] = $matches[0];
            return $placeholder;
        }, $html);
        
        // Only target images that have WordPress-specific attributes or classes
        // This targets images from wp_get_attachment_image, the_post_thumbnail, ACF, etc.
        $pattern = '/<img([^>]*?)src=["\'](https?:\/\/[^"\']*\.(jpg|jpeg|png)(?:\?[^"\']*)?)["\']([^>]*?)>/i';
        
        $html = preg_replace_callback($pattern, function($matches) {
            $img_attributes = $matches[1];
            $original_src = $matches[2];
            $file_extension = $matches[3];
            $remaining_attributes = $matches[4];
            
            // First, check for exclusions (Instagram feed, plugins, etc.)
            // Exclude Instagram feed images
            if (preg_match('/sb-instagram-feed-images/', $original_src) || 
                preg_match('/instagram-feed/', $original_src)) {
                return $matches[0]; // Return unchanged
            }
            
            // Exclude images that are already WebP
            if (preg_match('/\.webp(\?|$)/', $original_src)) {
                return $matches[0]; // Return unchanged
            }
            
            // Exclude plugin images (common patterns)
            if (preg_match('/\/plugins\//', $original_src)) {
                return $matches[0]; // Return unchanged
            }
            
            // Only process images that have WordPress-specific attributes
            $all_attributes = $img_attributes . $remaining_attributes;
            
            // Check for WordPress-specific attributes that indicate this is a WordPress image
            $is_wordpress_image = false;
            
            // Check for WordPress attachment classes
            if (preg_match('/wp-image-\d+/', $all_attributes)) {
                $is_wordpress_image = true;
            }
            
            // Check for WordPress size classes
            $wordpress_sizes = array('thumbnail', 'medium', 'large', 'full', 'miniature-article', 'contenu-alterné', 'carré', 'témoignage');
            $wordpress_sizes_pattern = implode('|', $wordpress_sizes);
            if (preg_match('/size-(' . $wordpress_sizes_pattern . ')/', $all_attributes)) {
                $is_wordpress_image = true;
            }
            
            // Check for ACF-specific attributes
            if (preg_match('/data-[^=]*="[^"]*"/', $all_attributes)) {
                $is_wordpress_image = true;
            }
            
            // Check for WordPress-generated alt attributes (usually descriptive)
            if (preg_match('/alt="[^"]{10,}"/', $all_attributes)) {
                $is_wordpress_image = true;
            }
            
            // Check if image is from WordPress uploads directory (ACF images)
            if (preg_match('/\/wp-content\/uploads\//', $original_src)) {
                $is_wordpress_image = true;
            }
            
            // Check for focal point style (ACF images often have this)
            if (preg_match('/style="[^"]*object-position:/', $all_attributes)) {
                $is_wordpress_image = true;
            }
            
            // If it's not a WordPress image, return unchanged
            if (!$is_wordpress_image) {
                return $matches[0];
            }
            
			$quality        = get_option( 'keycdn_webp_quality', 80 );
			$all_attributes = $img_attributes . $remaining_attributes;
			$attachment_id  = tomtom_image_optim_extract_attachment_id_from_attributes( $all_attributes );
			$size_slug      = tomtom_image_optim_extract_size_slug_from_attributes( $all_attributes );
			$metadata       = $attachment_id ? wp_get_attachment_metadata( $attachment_id ) : null;
			$dimensions     = tomtom_image_optim_merge_dimensions(
				tomtom_image_optim_extract_dimensions_from_attributes( $all_attributes ),
				tomtom_image_optim_get_dimensions_from_metadata( $attachment_id, $size_slug, $metadata )
			);

			$variant_details = tomtom_image_optim_resolve_variant_details( $attachment_id, $size_slug, $dimensions, $original_src, $metadata );
			$size_slug       = $variant_details['size_slug'];
			$dimensions      = $variant_details['dimensions'];
			$is_full_size    = in_array( $size_slug, array( 'full', 'original' ), true );
			$target_width    = ! empty( $dimensions['width'] ) ? (int) $dimensions['width'] : null;

			$webp_params = array(
				'format'  => 'webp',
				'quality' => $quality,
			);

			if ( ! $is_full_size && $target_width ) {
				$webp_params['width'] = $target_width;
			}

			$webp_src = tomtom_image_optim_build_cdn_url_with_params( $original_src, $webp_params );

			$fallback_params = array();
			if ( ! $is_full_size && $target_width ) {
				$fallback_params['width'] = $target_width;
			}

			$fallback_src = tomtom_image_optim_build_cdn_url_with_params( $original_src, $fallback_params );
			
			// Extract sizes-related attributes from original <img>
			$sizes_attr = '';
			$all_attrs_for_sizes = $img_attributes . $remaining_attributes;
			if ( preg_match( '/\s(data-lazy-sizes|data-sizes|sizes)=("|\')([^"\']*)(\2)/i', $all_attrs_for_sizes, $m ) ) {
				// m[1] is the attribute name, m[3] is the value
				$sizes_attr = ' ' . $m[1] . '="' . esc_attr( $m[3] ) . '"';
			}

			// Build picture element
			$picture = '<picture>';
			$picture .= '<source srcset="' . esc_attr( $webp_src ) . '" type="image/webp"' . $sizes_attr . '>';
			$picture .= '<img' . $img_attributes . 'src="' . esc_attr( $fallback_src ) . '"' . $remaining_attributes . '>';
			$picture .= '</picture>';
			
			// Add debug info if enabled
			if ( get_option( 'keycdn_webp_debug', false ) ) {
				$picture = '<!-- WebP Conversion: ' . $original_src . ' -> ' . $webp_src . ' -->' . $picture;
			}
			
			return $picture;
        }, $html);
        
        // Restore the original picture elements
        foreach ($picture_matches as $index => $picture_html) {
            $html = str_replace('<!--PICTURE_PLACEHOLDER_' . $index . '-->', $picture_html, $html);
        }
        
	return $html;
}

/**
 * Enhanced version that handles srcset attributes
 * Only targets images from WordPress/ACF functions
 */
function tomtom_image_optim_convert_images_to_webp_picture_enhanced( $html ) {
        // First, remove any existing picture elements from processing
        $picture_pattern = '/<picture[^>]*>.*?<\/picture>/is';
        $picture_matches = array();
        $html = preg_replace_callback($picture_pattern, function($matches) use (&$picture_matches) {
            $placeholder = '<!--PICTURE_PLACEHOLDER_' . count($picture_matches) . '-->';
            $picture_matches[] = $matches[0];
            return $placeholder;
        }, $html);
        
        // Only target images that have WordPress-specific attributes or classes
        $pattern = '/<img([^>]*?)src=["\'](https?:\/\/[^"\']*\.(jpg|jpeg|png)(?:\?[^"\']*)?)["\']([^>]*?)>/i';
        
        $html = preg_replace_callback($pattern, function($matches) {
            $img_attributes = $matches[1];
            $original_src = $matches[2];
            $file_extension = $matches[3];
            $remaining_attributes = $matches[4];
            
            // First, check for exclusions (Instagram feed, plugins, etc.)
            // Exclude Instagram feed images
            if (preg_match('/sb-instagram-feed-images/', $original_src) || 
                preg_match('/instagram-feed/', $original_src)) {
                return $matches[0]; // Return unchanged
            }
            
            // Exclude images that are already WebP
            if (preg_match('/\.webp(\?|$)/', $original_src)) {
                return $matches[0]; // Return unchanged
            }
            
            // Exclude plugin images (common patterns)
            if (preg_match('/\/plugins\//', $original_src)) {
                return $matches[0]; // Return unchanged
            }
            
            // Only process images that have WordPress-specific attributes
            $all_attributes = $img_attributes . $remaining_attributes;
            
            // Check for WordPress-specific attributes that indicate this is a WordPress image
            $is_wordpress_image = false;
            
            // Check for WordPress attachment classes
            if (preg_match('/wp-image-\d+/', $all_attributes)) {
                $is_wordpress_image = true;
            }
            
            // Check for WordPress size classes
            $wordpress_sizes = array('thumbnail', 'medium', 'large', 'full', 'miniature-article', 'contenu-alterné', 'carré', 'témoignage');
            $wordpress_sizes_pattern = implode('|', $wordpress_sizes);
            if (preg_match('/size-(' . $wordpress_sizes_pattern . ')/', $all_attributes)) {
                $is_wordpress_image = true;
            }
            
            // Check for ACF-specific attributes
            if (preg_match('/data-[^=]*="[^"]*"/', $all_attributes)) {
                $is_wordpress_image = true;
            }
            
            // Check for WordPress-generated alt attributes (usually descriptive)
            if (preg_match('/alt="[^"]{10,}"/', $all_attributes)) {
                $is_wordpress_image = true;
            }
            
            // Check if image is from WordPress uploads directory (ACF images)
            if (preg_match('/\/wp-content\/uploads\//', $original_src)) {
                $is_wordpress_image = true;
            }
            
            // Check for focal point style (ACF images often have this)
            if (preg_match('/style="[^"]*object-position:/', $all_attributes)) {
                $is_wordpress_image = true;
            }
            
            // If it's not a WordPress image, return unchanged
            if (!$is_wordpress_image) {
                return $matches[0];
            }
            
            // Check if there's a srcset attribute
            $srcset_match = '';
            $webp_srcset = '';
            
            if (preg_match('/\s(srcset)=("|\')([^"\']*)(\2)/i', $all_attributes, $srcset_matches)) {
                $srcset_match = $srcset_matches[3];
            }

			$quality       = get_option( 'keycdn_webp_quality', 80 );
			$attachment_id = tomtom_image_optim_extract_attachment_id_from_attributes( $all_attributes );
			$size_slug     = tomtom_image_optim_extract_size_slug_from_attributes( $all_attributes );
			$metadata      = $attachment_id ? wp_get_attachment_metadata( $attachment_id ) : null;
			$dimensions    = tomtom_image_optim_merge_dimensions(
				tomtom_image_optim_extract_dimensions_from_attributes( $all_attributes ),
				tomtom_image_optim_get_dimensions_from_metadata( $attachment_id, $size_slug, $metadata )
			);

			$variant_details = tomtom_image_optim_resolve_variant_details( $attachment_id, $size_slug, $dimensions, $original_src, $metadata );
            $size_slug       = $variant_details['size_slug'];
            $dimensions      = $variant_details['dimensions'];

            $is_full_size = in_array($size_slug, array('full', 'original'), true);
            $base_width   = !$is_full_size && !empty($dimensions['width']) ? (int) $dimensions['width'] : null;

            $webp_params = array(
                'format'  => 'webp',
                'quality' => $quality,
            );

            if ($base_width) {
                $webp_params['width'] = $base_width;
            }

			$webp_src = tomtom_image_optim_build_cdn_url_with_params( $original_src, $webp_params );

			$fallback_params = array();
			if ( $base_width ) {
				$fallback_params['width'] = $base_width;
			}
			$fallback_src = tomtom_image_optim_build_cdn_url_with_params( $original_src, $fallback_params );

			if ( ! empty( $srcset_match ) ) {
				$webp_srcset     = tomtom_image_optim_build_srcset_with_params( $srcset_match, $webp_params, $base_width );
				$fallback_srcset = tomtom_image_optim_build_srcset_with_params( $srcset_match, $fallback_params, $base_width );

                if (!empty($fallback_srcset)) {
                    $new_srcset_attr = ' srcset="' . esc_attr($fallback_srcset) . '"';
                    $replaced        = false;

                    $img_attributes = preg_replace('/\s(srcset)=("|\')[^"\']*(\2)/i', $new_srcset_attr, $img_attributes, 1, $count);
                    if ($count > 0) {
                        $replaced = true;
                    }

                    if (!$replaced) {
                        $remaining_attributes = preg_replace('/\s(srcset)=("|\')[^"\']*(\2)/i', $new_srcset_attr, $remaining_attributes, 1, $count);
                        if ($count > 0) {
                            $replaced = true;
                        }
                    }

                    if (!$replaced) {
                        $remaining_attributes .= $new_srcset_attr;
                    }
                }
            }
            
            // Extract sizes-related attributes from original <img>
            $sizes_attr = '';
            $all_attrs_for_sizes = $img_attributes . $remaining_attributes;
            if (preg_match('/\s(data-lazy-sizes|data-sizes|sizes)=("|\')([^"\']*)(\2)/i', $all_attrs_for_sizes, $m)) {
                $sizes_attr = ' ' . $m[1] . '="' . esc_attr($m[3]) . '"';
            }

            // Build picture element
            $picture = '<picture>';
            
            // Add WebP source with srcset if available
            if (!empty($webp_srcset)) {
                $picture .= '<source srcset="' . esc_attr($webp_srcset) . '" type="image/webp"' . $sizes_attr . '>';
            } else {
                $picture .= '<source srcset="' . esc_attr($webp_src) . '" type="image/webp"' . $sizes_attr . '>';
            }

            $picture .= '<img' . $img_attributes . 'src="' . esc_attr($fallback_src) . '"' . $remaining_attributes . '>';
            $picture .= '</picture>';
            
            // Add debug info if enabled
            if (get_option('keycdn_webp_debug', false)) {
                $debug_info = '<!-- WebP Enhanced Conversion: ' . $original_src . ' -> ' . $webp_src;
                if ($webp_srcset) {
                    $debug_info .= ' (with srcset)';
                }
                $debug_info .= ' -->';
                $picture = $debug_info . $picture;
            }
            
            return $picture;
        }, $html);
        
        // Restore the original picture elements
        foreach ($picture_matches as $index => $picture_html) {
            $html = str_replace('<!--PICTURE_PLACEHOLDER_' . $index . '-->', $picture_html, $html);
        }
        
	return $html;
}

/**
 * Build a CDN URL with the supplied query parameters, overriding any existing values for the same keys.
 *
 * @param string $url    Base URL.
 * @param array  $params Query parameters to append.
 *
 * @return string
 */
function tomtom_image_optim_build_cdn_url_with_params( $url, array $params = array() ) {
        if (empty($params)) {
            return $url;
        }

        $filtered_params = array();
        foreach ($params as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            $filtered_params[$key] = $value;
        }

        if (empty($filtered_params)) {
            return $url;
        }

        $url = remove_query_arg(array_keys($filtered_params), $url);

	return add_query_arg( $filtered_params, $url );
}

/**
 * Extract attachment ID from image attributes.
 *
 * @param string $attributes Attribute string from img tag.
 *
 * @return int|null
 */
function tomtom_image_optim_extract_attachment_id_from_attributes( $attributes ) {
        if (preg_match('/wp-image-(\d+)/', $attributes, $match)) {
            return (int) $match[1];
        }

	return null;
}

/**
 * Extract size slug from image attributes.
 *
 * @param string $attributes Attribute string from img tag.
 *
 * @return string|null
 */
function tomtom_image_optim_extract_size_slug_from_attributes( $attributes ) {
        if (preg_match('/size-([a-z0-9\-_]+)/', $attributes, $match)) {
            return strtolower($match[1]);
        }

	return null;
}

/**
 * Extract width and height attributes from markup.
 *
 * @param string $attributes Attribute string from img tag.
 *
 * @return array{width:int|null,height:int|null}
 */
function tomtom_image_optim_extract_dimensions_from_attributes( $attributes ) {
        $dimensions = array(
            'width'  => null,
            'height' => null,
        );

        if (preg_match('/\swidth=["\'](\d+)["\']/', $attributes, $match)) {
            $dimensions['width'] = (int) $match[1];
        }

        if (preg_match('/\sheight=["\'](\d+)["\']/', $attributes, $match)) {
            $dimensions['height'] = (int) $match[1];
        }

	return $dimensions;
}

/**
 * Merge two dimension arrays giving precedence to precise metadata values.
 *
 * @param array $primary   Dimensions extracted from markup.
 * @param array $secondary Dimensions extracted from metadata.
 *
 * @return array
 */
function tomtom_image_optim_merge_dimensions( array $primary, array $secondary ) {
        foreach (array('width', 'height') as $key) {
            if (empty($primary[$key]) && !empty($secondary[$key])) {
                $primary[$key] = (int) $secondary[$key];
            }
        }

	return $primary;
}

/**
 * Retrieve dimensions for a specific size from attachment metadata.
 *
 * @param int|null    $attachment_id Attachment ID.
 * @param string|null $size_slug     Size slug.
 *
 * @return array{width:int|null,height:int|null}
 */
function tomtom_image_optim_get_dimensions_from_metadata( $attachment_id, $size_slug, $metadata = null ) {
        $dimensions = array(
            'width'  => null,
            'height' => null,
        );

        if (empty($attachment_id)) {
            return $dimensions;
        }

        if (null === $metadata) {
            $metadata = wp_get_attachment_metadata($attachment_id);
        }

        if (!is_array($metadata)) {
            return $dimensions;
        }

        if ($size_slug && !empty($metadata['sizes']) && is_array($metadata['sizes'])) {
            foreach ($metadata['sizes'] as $name => $info) {
                if (sanitize_title_with_dashes($name) === $size_slug) {
                    if (isset($info['width'])) {
                        $dimensions['width'] = (int) $info['width'];
                    }
                    if (isset($info['height'])) {
                        $dimensions['height'] = (int) $info['height'];
                    }
                    return $dimensions;
                }
            }
        }

        if (isset($metadata['width'], $metadata['height'])) {
            $dimensions['width']  = (int) $metadata['width'];
            $dimensions['height'] = (int) $metadata['height'];
        }

	return $dimensions;
}

/**
 * Build an updated srcset string with resizing parameters applied to each entry.
 *
 * @param string   $srcset     Original srcset string.
 * @param array    $base_params Base parameters to apply to every URL.
 * @param int|null $base_width  Base width for calculating density descriptors.
 *
 * @return string
 */
function tomtom_image_optim_build_srcset_with_params( $srcset, array $base_params, $base_width = null ) {
        if (empty($srcset)) {
            return '';
        }

        $entries = array();
        $parts   = explode(',', $srcset);

        foreach ($parts as $part) {
            $part = trim($part);

            if ('' === $part) {
                continue;
            }

            $segments   = preg_split('/\s+/', $part);
            $url        = array_shift($segments);
            $descriptor = implode(' ', $segments);

            $params = $base_params;

			$descriptor_width = tomtom_image_optim_extract_width_from_descriptor( $descriptor, $base_width );
			if ( $descriptor_width ) {
				$params['width'] = $descriptor_width;
			} else {
				unset( $params['width'] );
			}

			$entries[] = trim( tomtom_image_optim_build_cdn_url_with_params( $url, $params ) . ( $descriptor ? ' ' . $descriptor : '' ) );
        }

	return implode( ', ', $entries );
}

/**
 * Refine size information using attachment metadata and the requested source.
 *
 * @param int|null  $attachment_id Attachment ID.
 * @param string|null $size_slug   Initial size slug guess.
 * @param array     $dimensions    Current dimensions.
 * @param string    $src           Image source URL.
 * @param array|null $metadata     Optional attachment metadata.
 *
 * @return array{size_slug:?string,dimensions:array}
 */
function tomtom_image_optim_resolve_variant_details( $attachment_id, $size_slug, array $dimensions, $src, $metadata = null ) {
        $resolved_slug   = $size_slug;
        $resource_width  = null;
        $resource_height = null;
	$basename        = tomtom_image_optim_get_basename_from_url( $src );

        if ($attachment_id) {
            if (null === $metadata) {
                $metadata = wp_get_attachment_metadata($attachment_id);
            }

            if (is_array($metadata)) {
                if ($resolved_slug && !empty($metadata['sizes']) && is_array($metadata['sizes'])) {
                    foreach ($metadata['sizes'] as $name => $info) {
                        if (sanitize_title_with_dashes($name) === $resolved_slug) {
                            $resource_width  = isset($info['width']) ? (int) $info['width'] : null;
                            $resource_height = isset($info['height']) ? (int) $info['height'] : null;
                            break;
                        }
                    }
                }

                if (null === $resource_width && !empty($metadata['sizes']) && $basename) {
                    foreach ($metadata['sizes'] as $name => $info) {
                        if (isset($info['file']) && wp_basename($info['file']) === $basename) {
                            $resource_width  = isset($info['width']) ? (int) $info['width'] : null;
                            $resource_height = isset($info['height']) ? (int) $info['height'] : null;
                            $resolved_slug   = sanitize_title_with_dashes($name);
                            break;
                        }
                    }
                }

                if (null === $resource_width && isset($metadata['width'])) {
                    $resource_width = (int) $metadata['width'];
                }

                if (null === $resource_height && isset($metadata['height'])) {
                    $resource_height = (int) $metadata['height'];
                }
            }
        }

	if ( null === $resource_width || null === $resource_height ) {
		$filename_dimensions = tomtom_image_optim_extract_dimensions_from_filename( $basename );

            if (null === $resource_width && !empty($filename_dimensions['width'])) {
                $resource_width = (int) $filename_dimensions['width'];
            }

            if (null === $resource_height && !empty($filename_dimensions['height'])) {
                $resource_height = (int) $filename_dimensions['height'];
            }
        }

        if (!empty($dimensions['width']) && $resource_width && $dimensions['width'] > $resource_width) {
            $dimensions['width'] = $resource_width;
        } elseif (empty($dimensions['width']) && $resource_width) {
            $dimensions['width'] = $resource_width;
        }

        if (!empty($dimensions['height']) && $resource_height && $dimensions['height'] > $resource_height) {
            $dimensions['height'] = $resource_height;
        } elseif (empty($dimensions['height']) && $resource_height) {
            $dimensions['height'] = $resource_height;
        }

	return array(
		'size_slug'  => $resolved_slug,
		'dimensions' => $dimensions,
	);
}

/**
 * Extract dimensions from a filename suffix (e.g. file-300x200.jpg).
 *
 * @param string $filename Filename to inspect.
 *
 * @return array{width:int|null,height:int|null}
 */
function tomtom_image_optim_extract_dimensions_from_filename( $filename ) {
        $dimensions = array(
            'width'  => null,
            'height' => null,
        );

        if (empty($filename)) {
            return $dimensions;
        }

        if (preg_match('/-(\d+)x(\d+)(?=\.[^.]+$)/', $filename, $match)) {
            $dimensions['width']  = (int) $match[1];
            $dimensions['height'] = (int) $match[2];
        }

	return $dimensions;
}

/**
 * Get basename from a URL without its query component.
 *
 * @param string $url URL to parse.
 *
 * @return string|null
 */
function tomtom_image_optim_get_basename_from_url( $url ) {
        if (empty($url)) {
            return null;
        }

        $query_position = strpos($url, '?');
        if ($query_position !== false) {
            $url = substr($url, 0, $query_position);
        }

	return wp_basename( $url );
}

/**
 * Extract a width from a srcset descriptor.
 *
 * @param string   $descriptor Descriptor string (e.g. "300w" or "2x").
 * @param int|null $base_width Base width for density descriptors.
 *
 * @return int|null
 */
function tomtom_image_optim_extract_width_from_descriptor( $descriptor, $base_width = null ) {
        if (preg_match('/(\d+)w/', $descriptor, $match)) {
            return (int) $match[1];
        }

        if (preg_match('/(\d+(?:\.\d+)?)x/', $descriptor, $match) && $base_width) {
            return (int) round($base_width * (float) $match[1]);
        }

	return null;
}

/**
 * Get WebP conversion statistics
 * @return array Conversion stats
 */
function tomtom_image_optim_get_webp_conversion_stats() {
	static $stats = null;
	
	if ( $stats === null ) {
		$stats = array(
			'conversions' => 0,
			'webp_enabled' => get_option( 'keycdn_webp_enabled', true ),
			'enhanced_mode' => get_option( 'keycdn_webp_enhanced', true )
		);
	}
	
	return $stats;
}
