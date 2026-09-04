<?php

class Mapster_Feature_Writer {

  // Maps feature type string (from SDK) to WordPress post type, geometry ACF
  // field name, style ACF field, style ACF value, and ACF group IDs used for
  // initialising defaults on new posts (mirrors CSV import behaviour).
  private const TYPE_INFO = [
    'marker'          => ['post_type' => 'mapster-wp-location', 'geo_field' => 'location', 'style_field' => 'location_style', 'style_value' => 'marker',        'acf_groups' => ['group_6163732e0426e', 'group_6163d357655f4']],
    'circle'          => ['post_type' => 'mapster-wp-location', 'geo_field' => 'location', 'style_field' => 'location_style', 'style_value' => 'circle',        'acf_groups' => ['group_6163732e0426e', 'group_6163d357655f4']],
    'symbol'          => ['post_type' => 'mapster-wp-location', 'geo_field' => 'location', 'style_field' => 'location_style', 'style_value' => 'label',         'acf_groups' => ['group_6163732e0426e', 'group_6163d357655f4']],
    '3dObject'        => ['post_type' => 'mapster-wp-location', 'geo_field' => 'location', 'style_field' => 'location_style', 'style_value' => '3d-model',      'acf_groups' => ['group_6163732e0426e', 'group_6163d357655f4']],
    'line'            => ['post_type' => 'mapster-wp-line',     'geo_field' => 'line',     'style_field' => null,            'style_value' => null,             'acf_groups' => ['group_616377d62836b', 'group_6163d357655f4']],
    'polygon'         => ['post_type' => 'mapster-wp-polygon',  'geo_field' => 'polygon',  'style_field' => 'polygon_style', 'style_value' => 'fill',           'acf_groups' => ['group_616379566202f', 'group_6163d357655f4']],
    '3d-polygon'      => ['post_type' => 'mapster-wp-polygon',  'geo_field' => 'polygon',  'style_field' => 'polygon_style', 'style_value' => 'fill-extrusion', 'acf_groups' => ['group_616379566202f', 'group_6163d357655f4']],
    'image-polygon'   => ['post_type' => 'mapster-wp-polygon',  'geo_field' => 'polygon',  'style_field' => 'polygon_style', 'style_value' => 'fill-image',     'acf_groups' => ['group_616379566202f', 'group_6163d357655f4']],
    'pattern-polygon' => ['post_type' => 'mapster-wp-polygon',  'geo_field' => 'polygon',  'style_field' => 'polygon_style', 'style_value' => 'fill-pattern',   'acf_groups' => ['group_616379566202f', 'group_6163d357655f4']],
  ];

  // Fields present on every feature type regardless of geometry.
  // Each entry: [feature_dot_path, acf_field_name, optional_flag]
  // Flags: 'number' = cast to float; 'bool_int' = cast bool to int for storage.
  private const SHARED_FIELD_MAP = [
    ['interactivity.interaction',                          'interaction'],
    ['interactivity.click_mobile',                         'click_on_mobile',                   'bool_int'],
    ['interactivity.default_zoom',                         'default_zoom_level',                 'number'],
    ['interactivity.direct_link.enabled',                  'open_link_on_click',                 'bool_int'],
    ['interactivity.direct_link.url',                      'click_link_url'],
    ['interactivity.popup.enabled',                        'enable_popup',                       'bool_int'],
    ['interactivity.popup.content.header',                 'popup_header_text'],
    ['interactivity.popup.content.image_type',             'popup_image_type'],
    ['interactivity.popup.content.body',                   'popup_body_text'],
    ['interactivity.popup.content.button',                 'popup_button_text'],
    ['interactivity.popup.content.button_url',             'popup_button_url'],
    ['interactivity.popup.content.modal',                  'popup_modal_details'],
    ['interactivity.popup.options.button_action',          'popup_button_action'],
    ['interactivity.popup.options.render_iframe.enabled',  'popup_render_shortcode',             'bool_int'],
    ['interactivity.popup.options.keep_open_on_hover',     'keep_popup_open_on_hover',           'bool_int'],
    ['interactivity.popup.options.open_on_load',           'open_popup_on_load',                 'bool_int'],
    ['interactivity.popup.options.permanent',              'popup_permanent',                    'bool_int'],
    ['metadata.store.enabled',                             'store_locator_fields',               'bool_int'],
    ['metadata.store.address',                             'address'],
    ['metadata.store.phone',                               'phone_number'],
    ['metadata.store.website',                             'website'],
    ['metadata.store.description',                         'locator_description'],
    ['metadata.store.use_custom_button',                   'custom_button_show_custom_button',   'bool_int'],
    ['metadata.store.custom_button_icon',                  'custom_button_icon'],
    ['metadata.store.custom_button_url',                   'custom_button_url'],
    ['metadata.store.show_hours',                          'hours_show_hours',                   'bool_int'],
    ['metadata.store.hours.monday',                        'hours_monday'],
    ['metadata.store.hours.tuesday',                       'hours_tuesday'],
    ['metadata.store.hours.wednesday',                     'hours_wednesday'],
    ['metadata.store.hours.thursday',                      'hours_thursday'],
    ['metadata.store.hours.friday',                        'hours_friday'],
    ['metadata.store.hours.saturday',                      'hours_saturday'],
    ['metadata.store.hours.sunday',                        'hours_sunday'],
    ['metadata.store.show_socials',                        'social_media_show_social_media',     'bool_int'],
    ['metadata.store.socials.facebook',                    'social_media_facebook'],
    ['metadata.store.socials.twitter',                     'social_media_twitter'],
    ['metadata.store.socials.linkedin',                    'social_media_linkedin'],
    ['metadata.store.socials.instagram',                   'social_media_instagram'],
    ['metadata.store.socials.tiktok',                      'social_media_tiktok'],
    ['metadata.store.socials.youtube',                     'social_media_youtube'],
    ['metadata.store.socials.pinterest',                   'social_media_pinterest'],
  ];

  private const MARKER_FIELD_MAP = [
    ['properties.color',          'marker_color'],
    ['properties.scale',          'marker_scale',                       'number'],
    ['properties.rotation',       'marker_rotation',                    'number'],
    ['properties.anchor',         'marker_anchor'],
    ['properties.hover.enabled',  'marker_hover_effects_hover_enabled', 'bool_int'],
    ['properties.hover.color',    'marker_hover_effects_hover_color'],
    ['properties.hover.scale',    'marker_hover_effects_hover_scale',   'number'],
    ['properties.hover.rotation', 'marker_hover_effects_hover_rotation','number'],
  ];

  private const CIRCLE_FIELD_MAP = [
    ['properties.color',                'circle_color'],
    ['properties.radius',               'circle_radius',                             'number'],
    ['properties.opacity',              'circle_opacity',                            'number'],
    ['properties.stroke-width',         'circle_stroke-width',                       'number'],
    ['properties.stroke-color',         'circle_stroke-color'],
    ['properties.stroke-opacity',       'circle_stroke-opacity',                     'number'],
    ['properties.hover.enabled',        'circle_hover_effects_hover_enabled',        'bool_int'],
    ['properties.hover.color',          'circle_hover_effects_hover_color'],
    ['properties.hover.radius',         'circle_hover_effects_hover_radius',         'number'],
    ['properties.hover.opacity',        'circle_hover_effects_hover_opacity',        'number'],
    ['properties.hover.stroke-width',   'circle_hover_effects_hover_stroke-width',   'number'],
    ['properties.hover.stroke-color',   'circle_hover_effects_hover_stroke-color'],
    ['properties.hover.stroke-opacity', 'circle_hover_effects_hover_stroke-opacity', 'number'],
    ['properties.options.static-size',  'circle_circle-static-size',                'bool_int'],
  ];

  private const SYMBOL_FIELD_MAP = [
    ['properties.text.enabled',        'label_label_on',                                        'bool_int'],
    ['properties.text.field',          'label_text_properties_text-field'],
    ['properties.text.font',           'label_text_properties_text-font'],
    ['properties.text.size',           'label_text_properties_text-size',                       'number'],
    ['properties.text.color',          'label_text_properties_text-color'],
    ['properties.text.opacity',        'label_text_properties_text-opacity',                    'number'],
    ['properties.text.rotate',         'label_text_properties_text-rotate',                     'number'],
    ['properties.text.halo-width',     'label_text_properties_text-halo-width',                 'number'],
    ['properties.text.halo-color',     'label_text_properties_text-halo-color'],
    ['properties.text.halo-blur',      'label_text_properties_text-halo-blur',                  'number'],
    ['properties.icon.enabled',        'icon_icon_on',                                          'bool_int'],
    ['properties.icon.size',           'icon_icon_properties_icon-size',                        'number'],
    ['properties.icon.opacity',        'icon_icon_properties_icon-opacity',                     'number'],
    ['properties.icon.rotate',         'icon_icon_properties_icon-rotate',                      'number'],
    ['properties.icon.anchor',         'icon_icon_properties_icon-anchor'],
    ['properties.icon.static-size',    'icon_icon_properties_icon-static-size',                 'bool_int'],
    ['properties.hover.enabled',       'icon_icon_properties_hover_effects_hover_enabled',      'bool_int'],
    ['properties.hover.icon.opacity',  'icon_icon_properties_hover_effects_hover_opacity',      'number'],
  ];

  private const MODEL_FIELD_MAP = [
    // 3d_model_file is a file upload — not writable via this path
    ['properties.scale',               '3d_model_scale',               'number'],
    ['properties.rotation.x_rotation', '3d_model_rotation_x_rotation', 'number'],
    ['properties.rotation.y_rotation', '3d_model_rotation_y_rotation', 'number'],
    ['properties.rotation.z_rotation', '3d_model_rotation_z_rotation', 'number'],
  ];

  private const LINE_FIELD_MAP = [
    ['properties.color',          'color'],
    ['properties.width',          'width',                   'number'],
    ['properties.opacity',        'opacity',                 'number'],
    ['properties.dashed.enabled', 'dashed_line',             'bool_int'],
    ['properties.hover.enabled',  'hover_effects_hover_enabled', 'bool_int'],
    ['properties.hover.color',    'hover_effects_hover_color'],
    ['properties.hover.width',    'hover_effects_hover_width',   'number'],
    ['properties.hover.opacity',  'hover_effects_hover_opacity', 'number'],
  ];

  private const POLYGON_FIELD_MAP = [
    ['properties.color',              'color'],
    ['properties.opacity',            'opacity',                  'number'],
    ['properties.outline-color',      'outline-color'],
    ['properties.hover.enabled',      'hover_effects_hover_enabled', 'bool_int'],
    ['properties.hover.color',        'hover_effects_hover_color'],
    ['properties.hover.opacity',      'hover_effects_opacity',       'number'],
    ['properties.hover.outline-color','hover_effects_outline-color'],
  ];

  private const POLYGON_3D_FIELD_MAP = [
    ['properties.3d.base',        '3d_polygon_base',    'number'],
    ['properties.3d.height',      '3d_polygon_height',  'number'],
    ['properties.hover.3d.base',  'hover_effects_base', 'number'],
    ['properties.hover.3d.height','hover_effects_height','number'],
  ];

  // ===========================================================================
  // Public API
  // ===========================================================================

  /**
   * Create a new WP post for the given feature, initialise ACF defaults, then
   * write all field values.  Returns the new post ID.
   */
  public function create_feature($feature) {
    $type = $feature['metadata']['type'] ?? null;
    $info = self::TYPE_INFO[$type] ?? null;
    if (!$info) return 0;

    $post_id = wp_insert_post([
      'post_type'   => $info['post_type'],
      'post_status' => 'publish',
      'post_title'  => $feature['metadata']['title'] ?? $type,
    ]);
    if (is_wp_error($post_id) || !$post_id) return 0;

    // Initialise ACF defaults exactly as the CSV importer does
    foreach ($info['acf_groups'] as $group_id) {
      mapster_setDefaults(acf_get_fields($group_id), $post_id);
    }

    // Set style field on create (not changed on update)
    if ($info['style_field']) {
      update_field($info['style_field'], $info['style_value'], $post_id);
    }

    $this->write_feature_to_acf($post_id, $feature, true);
    return $post_id;
  }

  /**
   * Write all writable fields from a feature array to ACF.
   *
   * @param bool $is_new  True when called from create_feature() (style field already set there).
   */
  public function write_feature_to_acf($post_id, $feature, $is_new = false) {
    $type = $feature['metadata']['type'] ?? null;
    $info = self::TYPE_INFO[$type] ?? null;
    if (!$info) return;

    // Style field (marker/circle/label/3d-model, fill/fill-extrusion/etc.) — lets
    // the editor switch a feature's type after creation. Skipped on create since
    // create_feature() already set it before defaults/geometry are written.
    if (!$is_new && $info['style_field']) {
      update_field($info['style_field'], $info['style_value'], $post_id);
    }

    // Post title
    $title = $feature['metadata']['title'] ?? null;
    if ($title !== null) {
      wp_update_post(['ID' => $post_id, 'post_title' => $title]);
    }

    // Categories: array of term IDs sent from client
    $cats = $feature['metadata']['categories'] ?? null;
    if (is_array($cats)) {
      $term_ids = array_map('intval', array_column($cats, 'term_id'));
      if (!empty($term_ids)) {
        wp_set_object_terms($post_id, $term_ids, 'wp-map-category');
      }
    }

    // Geometry — always update (supports moved points / reshaped polygons)
    $geometry = $feature['geometry'] ?? null;
    if ($geometry !== null) {
      $geojson = json_encode([
        'type'     => 'FeatureCollection',
        'features' => [[
          'type'       => 'Feature',
          'properties' => new stdClass(),
          'geometry'   => $geometry,
        ]],
      ]);
      update_field($info['geo_field'], $geojson, $post_id);
    }

    // popup.style_id: post object field — pass int ID to set, false to clear
    $style_id = $feature['interactivity']['popup']['style_id'] ?? null;
    if ($style_id !== null) {
      update_field('popup_style', $style_id ? intval($style_id) : false, $post_id);
    }

    // icon.image: {id, url} on read — write just the attachment ID
    $icon_image = $feature['properties']['icon']['image'] ?? null;
    if ($icon_image !== null) {
      $img_id = is_array($icon_image) ? ($icon_image['id'] ?? false) : false;
      update_field('icon_icon_properties_icon-image', $img_id ? intval($img_id) : false, $post_id);
    }

    // popup.content.featured_image: {id, url} on read — write just the attachment ID
    $featured = $feature['interactivity']['popup']['content']['featured_image'] ?? null;
    if ($featured !== null) {
      $img_id = is_array($featured) ? ($featured['id'] ?? false) : false;
      update_field('popup_featured_image', $img_id ? intval($img_id) : false, $post_id);
    }

    // popup.content.images: [{id, url}, ...] on read — write array of attachment IDs
    $images = $feature['interactivity']['popup']['content']['images'] ?? null;
    if ($images !== null) {
      $ids = array_values(array_filter(array_map(
        fn($img) => is_array($img) && !empty($img['id']) ? intval($img['id']) : 0,
        $images
      )));
      update_field('field_61dcb4a861391', implode(',', $ids), $post_id);
    }

    // direct_link.target: "_blank" → 1, "_self" → 0
    $target = $feature['interactivity']['direct_link']['target'] ?? null;
    if ($target !== null) {
      update_field('click_link_open_in_new_tab', (int) ($target === '_blank'), $post_id);
    }

    // Shared fields present on every type
    $this->apply_field_map($post_id, self::SHARED_FIELD_MAP, $feature);

    // Type-specific property fields + complex array fields
    switch ($type) {
      case 'marker':
        $this->apply_field_map($post_id, self::MARKER_FIELD_MAP, $feature);
        break;

      case 'circle':
        $this->apply_field_map($post_id, self::CIRCLE_FIELD_MAP, $feature);
        break;

      case 'symbol':
        $this->apply_field_map($post_id, self::SYMBOL_FIELD_MAP, $feature);
        $this->write_indexed($post_id, $feature, 'properties.text.translate', [
          'label_text_properties_text-translate-x',
          'label_text_properties_text-translate-y',
        ]);
        $this->write_indexed($post_id, $feature, 'properties.icon.translate', [
          'icon_icon_properties_icon-translate-x',
          'icon_icon_properties_icon-translate-y',
        ]);
        break;

      case '3dObject':
        $this->apply_field_map($post_id, self::MODEL_FIELD_MAP, $feature);
        break;

      case 'line':
        $this->apply_field_map($post_id, self::LINE_FIELD_MAP, $feature);
        $this->write_indexed($post_id, $feature, 'properties.dashed.dash_line', [
          'dash_properties_dash_length',
          'dash_properties_gap_length',
        ]);
        break;

      case 'polygon':
        $this->apply_field_map($post_id, self::POLYGON_FIELD_MAP, $feature);
        break;

      case '3d-polygon':
        $this->apply_field_map($post_id, self::POLYGON_FIELD_MAP, $feature);
        $this->apply_field_map($post_id, self::POLYGON_3D_FIELD_MAP, $feature);
        break;

      case 'pattern-polygon':
        $this->apply_field_map($post_id, self::POLYGON_FIELD_MAP, $feature);
        $pattern = $feature['properties']['pattern'] ?? null;
        if ($pattern !== null) update_field('pattern', $pattern, $post_id);
        break;

      case 'image-polygon':
        // polygon_image is a file/image upload — not writable via this path
        $this->apply_field_map($post_id, self::POLYGON_FIELD_MAP, $feature);
        break;
    }
  }

  // ===========================================================================
  // Map association sync
  // ===========================================================================

  /**
   * After save-mapmaker processes features, reconcile the three relationship
   * fields (locations / lines / polygons) and add_custom_posts on the map post:
   *
   *  - Existing IDs no longer present in the incoming payload are unlinked
   *    (post is NOT deleted, just removed from the map's field).
   *  - Newly created feature IDs are appended to the correct field.
   *
   * Category-sourced features are intentionally excluded on the frontend before
   * calling save-mapmaker, so they never appear in $features and are never
   * touched here.
   *
   * @param int   $map_id     The map's WP post ID.
   * @param array $features   The full features payload from the request body.
   * @param array $new_id_map Map of tempId → real post ID for features just created.
   */
  public function sync_direct_associations($map_id, $features, $new_id_map) {
    // Collect IDs of every non-new feature still present in the payload.
    $surviving_ids = [];
    foreach ($features as $f) {
      if (empty($f['_new']) && !empty($f['metadata']['id'])) {
        $surviving_ids[] = intval($f['metadata']['id']);
      }
    }

    $type_to_field = [
      'mapster-wp-location' => 'locations',
      'mapster-wp-line'     => 'lines',
      'mapster-wp-polygon'  => 'polygons',
    ];

    foreach ($type_to_field as $post_type => $field) {
      $current     = get_field($field, $map_id) ?: [];
      $current_ids = array_map(fn($p) => $p->ID, $current);

      // Keep existing directly-associated IDs that are still in the payload.
      $kept = array_values(array_intersect($current_ids, $surviving_ids));

      // Append newly created posts of this type.
      foreach (array_values($new_id_map) as $real_id) {
        if (get_post_type(intval($real_id)) === $post_type) {
          $kept[] = intval($real_id);
        }
      }

      update_field($field, $kept, $map_id);
    }

    // Custom posts — same removal logic, no new additions (new features are
    // always mapster post types, never custom posts).
    $current_custom     = get_field('add_custom_posts', $map_id) ?: [];
    $current_custom_ids = array_map(fn($p) => $p->ID, $current_custom);
    update_field('add_custom_posts', array_values(array_intersect($current_custom_ids, $surviving_ids)), $map_id);
  }

  // ===========================================================================
  // Private helpers
  // ===========================================================================

  private function apply_field_map($post_id, $map, $feature) {
    foreach ($map as $entry) {
      [$path, $acf_key, $flag] = array_pad($entry, 3, null);
      $value = $this->get_nested($feature, $path);
      if ($value === null) continue;

      if ($flag === 'number') {
        update_field($acf_key, floatval($value), $post_id);
      } elseif ($flag === 'bool_int') {
        update_field($acf_key, (int) (bool) $value, $post_id);
      } else {
        update_field($acf_key, is_bool($value) ? (int) $value : $value, $post_id);
      }
    }
  }

  // Writes two numeric ACF fields from a two-element array at $path.
  private function write_indexed($post_id, $feature, $path, $acf_keys) {
    $arr = $this->get_nested($feature, $path);
    if (!is_array($arr)) return;
    foreach ($acf_keys as $i => $key) {
      if (isset($arr[$i])) update_field($key, floatval($arr[$i]), $post_id);
    }
  }

  private function get_nested($data, $path) {
    foreach (explode('.', $path) as $key) {
      if (!is_array($data) || !array_key_exists($key, $data)) return null;
      $data = $data[$key];
    }
    return $data;
  }

}
