<?php

function mapster_get_field_map() {

  $FIELD_NUMBER        = 'number';
  $FIELD_INVERT        = 'invert';
  $FIELD_CONCAT_UNITS  = 'concat_units';
  $FIELD_NONEMPTY_BOOL = 'nonempty_bool';
  $FIELD_COMPLEX       = 'complex';
  $FIELD_READONLY      = 'readonly';
  $FIELD_RAW           = 'raw';

  return array(

    // -------------------------------------------------------------------------
    // Top-level identity
    // -------------------------------------------------------------------------
    ['config.id',                        null,                             $FIELD_READONLY],  // post_id
    ['config.element',                   null,                             $FIELD_READONLY],  // "mapster-wp-maps-{id}"
    ['config.mapbox_token',              'map_type_access_token'],
    ['config.google_key',                null,                             $FIELD_READONLY],  // settings page, not map post

    // -------------------------------------------------------------------------
    // Style
    // -------------------------------------------------------------------------
    ['config.style.style',               null,                             $FIELD_COMPLEX],   // returnStyleJSON — derived from provider + token + style_id
    ['config.style.snazzy',              'map_type_snazzy_map_style',      $FIELD_COMPLEX],   // returnStringOrJSON + false-if-empty check
    ['config.style.image',               null,                             $FIELD_COMPLEX],   // returnStyleImage — derived from provider + image field
    ['config.style.terrain',             'map_type_terrain'],
    ['config.style.buildings_3d',        'map_type_buildings_3d'],
    ['config.style.globe.enabled',       'map_type_globe'],
    ['config.style.globe.background',    'map_type_globe_background'],
    ['config.style.general_3d.enabled',  'load_3d_model_libraries'],
    ['config.style.lighting',            null,                             $FIELD_COMPLEX],   // returnStyleLighting — derived from provider + style_id
    ['config.style.projection',          'map_type_projection'],
    ['config.style.language',            'map_type_map_language'],
    ['config.style.duplicate_horizontally', 'map_type_duplicate_horizontally_copy'],

    // -------------------------------------------------------------------------
    // View
    // -------------------------------------------------------------------------
    ['config.view.on_load',              'view_initial_load'],
    ['config.view.auto_bounds',          null,                             $FIELD_READONLY],  // hardcoded false
    ['config.view.ip',                   null,                             $FIELD_READONLY],  // $_SERVER['REMOTE_ADDR']
    ['config.view.manual.bounds',        null,                             $FIELD_READONLY],  // hardcoded false
    ['config.view.manual.latitude',      'view_manual_latitude',           $FIELD_NUMBER],
    ['config.view.manual.longitude',     'view_manual_longitude',          $FIELD_NUMBER],
    ['config.view.manual.zoom',          'view_manual_zoom',               $FIELD_NUMBER],
    ['config.view.manual.pitch',         'view_manual_pitch',              $FIELD_NUMBER],
    ['config.view.manual.bearing',       'view_manual_rotation',           $FIELD_NUMBER],    // note: ACF key is view_manual_rotation
    ['config.view.padding',              'view_padding_around_bounds',     $FIELD_NUMBER],
    ['config.view.globe_animation.enabled',   'view_globe_animation'],
    ['config.view.globe_animation.speed',     'view_globe_animation_speed',     $FIELD_NUMBER],
    ['config.view.globe_animation.direction', 'view_globe_animation_direction'],

    // -------------------------------------------------------------------------
    // Layout
    // -------------------------------------------------------------------------
    ['config.layout.width',              'layout_width',                                       $FIELD_NUMBER],
    ['config.layout.width_units',        'layout_width_units'],
    ['config.layout.height',             'layout_height',                                      $FIELD_NUMBER],
    ['config.layout.height_units',       'layout_height_units'],
    ['config.layout.mobile.enabled',     'layout_add_mobile_breakpoints'],
    ['config.layout.mobile.breakpoint1.breakpoint',   'layout_breakpoints_breakpoint_1',       $FIELD_NUMBER],
    ['config.layout.mobile.breakpoint1.width',        'layout_breakpoints_breakpoint_1_map_width',       $FIELD_NUMBER],
    ['config.layout.mobile.breakpoint1.width_units',  'layout_breakpoints_breakpoint_1_map_width_units'],
    ['config.layout.mobile.breakpoint1.height',       'layout_breakpoints_breakpoint_1_map_height',      $FIELD_NUMBER],
    ['config.layout.mobile.breakpoint1.height_units', 'layout_breakpoints_breakpoint_1_map_height_units'],
    ['config.layout.mobile.breakpoint2.breakpoint',   'layout_breakpoints_breakpoint_2',       $FIELD_NUMBER],
    ['config.layout.mobile.breakpoint2.width',        'layout_breakpoints_breakpoint_2_map_width',       $FIELD_NUMBER],
    ['config.layout.mobile.breakpoint2.width_units',  'layout_breakpoints_breakpoint_2_map_width_units'],
    ['config.layout.mobile.breakpoint2.height',       'layout_breakpoints_breakpoint_2_map_height',      $FIELD_NUMBER],
    ['config.layout.mobile.breakpoint2.height_units', 'layout_breakpoints_breakpoint_2_map_height_units'],
    ['config.layout.full_page',          'layout_full_page'],
    ['config.layout.ignore_container',   'layout_ignore_container'],
    ['config.layout.map_only',           'layout_map_only'],

    // -------------------------------------------------------------------------
    // Interactivity
    // -------------------------------------------------------------------------
    ['config.interactivity.disable_all',   'interactivity',                $FIELD_INVERT],    // disable_all = !interactivity
    ['config.interactivity.scrollzoom',    'zoom_on_scroll'],
    ['config.interactivity.clicking',      null,                           $FIELD_COMPLEX],   // derived from clicking_disabled AND interactivity
    ['config.interactivity.coop_gestures', 'cooperative_gestures'],
    ['config.interactivity.rotation_pitch','allow_rotation_and_pitch'],
    ['config.interactivity.restrict_movement.enabled',              'restricted_movement_restrict_movement'],
    ['config.interactivity.restrict_movement.allowed_bounds.sw_lat','restricted_movement_allowed_bounds_southwest_latitude',  $FIELD_NUMBER],
    ['config.interactivity.restrict_movement.allowed_bounds.sw_lng','restricted_movement_allowed_bounds_southwest_longitude', $FIELD_NUMBER],
    ['config.interactivity.restrict_movement.allowed_bounds.ne_lat','restricted_movement_allowed_bounds_northeast_latitude',  $FIELD_NUMBER],
    ['config.interactivity.restrict_movement.allowed_bounds.ne_lng','restricted_movement_allowed_bounds_northeast_longitude', $FIELD_NUMBER],
    ['config.interactivity.restrict_movement.allowed_zoom.min',     'restricted_movement_allowed_zoom_min_zoom', $FIELD_NUMBER],
    ['config.interactivity.restrict_movement.allowed_zoom.max',     'restricted_movement_allowed_zoom_max_zoom', $FIELD_NUMBER],

    // -------------------------------------------------------------------------
    // Clusters
    // -------------------------------------------------------------------------
    ['config.clusters.enabled',          null,                             $FIELD_COMPLEX],   // OR of 5 separate clustering ACF fields
    ['config.clusters.types',            null,                             $FIELD_COMPLEX],   // returnClusterTypes — each type from its own ACF field
    ['config.clusters.by_category',      'cluster_options_cluster_by_category'],
    ['config.clusters.show_name',        'cluster_options_show_category_name'],
    ['config.clusters.image',            null,                             $FIELD_COMPLEX],
    ['config.clusters.categories',       null,                             $FIELD_COMPLEX],
    ['config.clusters.style.category',   null,                             $FIELD_COMPLEX],   // newline-delimited textarea
    ['config.clusters.style.small.color',        'cluster_options_small_cluster_color'],
    ['config.clusters.style.small.font_color',   'cluster_options_small_cluster_font_color'],
    ['config.clusters.style.small.radius',       'cluster_options_small_cluster_radius',  $FIELD_NUMBER],
    ['config.clusters.style.small.count',        'cluster_options_small_cluster_count',   $FIELD_NUMBER],
    ['config.clusters.style.small.border_color', 'cluster_options_small_cluster_border_color'],
    ['config.clusters.style.small.border_width', 'cluster_options_small_cluster_width',   $FIELD_NUMBER],
    ['config.clusters.style.medium.color',        'cluster_options_medium_cluster_color'],
    ['config.clusters.style.medium.font_color',   'cluster_options_medium_cluster_font_color'],
    ['config.clusters.style.medium.radius',       'cluster_options_medium_cluster_radius', $FIELD_NUMBER],
    ['config.clusters.style.medium.count',        'cluster_options_medium_cluster_count',  $FIELD_NUMBER],
    ['config.clusters.style.medium.border_color', 'cluster_options_medium_cluster_border_color'],
    ['config.clusters.style.medium.border_width', 'cluster_options_medium_cluster_width',  $FIELD_NUMBER],
    ['config.clusters.style.large.color',         'cluster_options_large_cluster_color'],
    ['config.clusters.style.large.font_color',    'cluster_options_large_cluster_font_color'],
    ['config.clusters.style.large.radius',        'cluster_options_large_cluster_radius',  $FIELD_NUMBER],
    ['config.clusters.style.large.border_color',  'cluster_options_large_cluster_border_color'],
    ['config.clusters.style.large.border_width',  'cluster_options_large_cluster_width',   $FIELD_NUMBER],

    // -------------------------------------------------------------------------
    // Controls — shared
    // -------------------------------------------------------------------------
    ['config.controls.control_order',    null,                             $FIELD_COMPLEX],   // returnArrayFromControlOrder — JSON string with slug extraction

    // zoom
    ['config.controls.zoom.enabled',     'zoom_control_enable'],
    ['config.controls.zoom.position',    'zoom_control_position'],

    // scale
    ['config.controls.scale.enabled',    'scale_control_enable'],
    ['config.controls.scale.position',   'scale_control_position'],

    // fullscreen
    ['config.controls.fullscreen.enabled',  'fullscreen_control_enable'],
    ['config.controls.fullscreen.position', 'fullscreen_control_position'],

    // attribution
    ['config.controls.attribution.enabled',  null,                         $FIELD_READONLY],  // hardcoded true
    ['config.controls.attribution.position', 'attribution_control_position'],                // default 'bottom-right' applied in read

    // logo
    ['config.controls.logo.enabled',     null,                             $FIELD_READONLY],  // hardcoded true
    ['config.controls.logo.position',    'logo_control_position'],

    // map_type
    ['config.controls.map_type.enabled',  'map_type_control_enable'],
    ['config.controls.map_type.position', 'map_type_control_position'],

    // street_view
    ['config.controls.street_view.enabled',  'street_view_control_enable'],
    ['config.controls.street_view.position', 'street_view_control_position'],

    // 3d
    ['config.controls.3d.enabled',       '3d_control_enable'],
    ['config.controls.3d.position',      '3d_control_position'],

    // print
    ['config.controls.print.enabled',    'print_control_enable'],
    ['config.controls.print.position',   'print_control_position'],

    // geocoder
    ['config.controls.geocoder.enabled',  'geocoder_control_enable'],
    ['config.controls.geocoder.position', 'geocoder_control_position'],
    ['config.controls.geocoder.options.placeholder',   'geocoder_control_placeholder'],
    ['config.controls.geocoder.options.marker_color',  'geocoder_control_marker_color'],
    ['config.controls.geocoder.options.accept_latlng', 'geocoder_control_accept_latlngs'],
    ['config.controls.geocoder.options.limit_results', 'geocoder_control_limit_results'],
    ['config.controls.geocoder.options.limit_by_bounds.enabled',       'geocoder_control_limit_by_bounds'],
    ['config.controls.geocoder.options.limit_by_bounds.bounds.sw_lat', 'geocoder_control_bounds_limit_southwest_latitude',  $FIELD_NUMBER],
    ['config.controls.geocoder.options.limit_by_bounds.bounds.sw_lng', 'geocoder_control_bounds_limit_southwest_longitude', $FIELD_NUMBER],
    ['config.controls.geocoder.options.limit_by_bounds.bounds.ne_lat', 'geocoder_control_bounds_limit_northeast_latitude',  $FIELD_NUMBER],
    ['config.controls.geocoder.options.limit_by_bounds.bounds.ne_lng', 'geocoder_control_bounds_limit_northeast_longitude', $FIELD_NUMBER],

    // geolocation
    ['config.controls.geolocation.enabled',  'geolocation_control_enable'],
    ['config.controls.geolocation.position', 'geolocation_control_position'],
    ['config.controls.geolocation.options.set_on_load',     'geolocation_control_set_on_load'],
    ['config.controls.geolocation.options.accuracy_circle', 'geolocation_control_show_accuracy_circle'],
    ['config.controls.geolocation.options.user_heading',    'geolocation_control_show_user_heading'],
    ['config.controls.geolocation.options.user_track',      'geolocation_control_track_user_location'],
    ['config.controls.geolocation.options.high_accuracy',   'geolocation_control_enable_high_accuracy'],

    // directions
    ['config.controls.directions.enabled',  'directions_control_enable'],
    ['config.controls.directions.position', 'directions_control_position'],
    ['config.controls.directions.options.units',        'directions_control_units'],
    ['config.controls.directions.options.default_type', 'directions_control_default_type'],
    ['config.controls.directions.options.placeholder',  'directions_control_placeholder_text'],

    // layers
    ['config.controls.layers.enabled',  'layer_control_enable'],
    ['config.controls.layers.position', 'layer_control_position'],
    ['config.controls.layers.options.toggleable_layers', null,             $FIELD_COMPLEX],   // returnArrayFromLayerControl — two parallel textarea fields
    ['config.controls.layers.options.checkbox_type',     'layer_control_checkbox_type'],

    // styles
    ['config.controls.styles.enabled',  'style_control_enable'],
    ['config.controls.styles.position', 'style_control_position'],
    ['config.controls.styles.options.toggleable_styles',   null,           $FIELD_COMPLEX],   // returnArrayFromStyleControl — two parallel textarea fields
    ['config.controls.styles.options.default_style_title', 'style_control_initial_style_title'],  // default 'Default Style' applied in read

    // menu
    ['config.controls.menu.enabled',  'control_menu_enable'],
    ['config.controls.menu.position', 'control_menu_position'],
    ['config.controls.menu.options.included_controls', null,               $FIELD_COMPLEX],   // returnArrayFromControlMenu — JSON string

    // download
    ['config.controls.download.enabled',  'download_control_enable'],
    ['config.controls.download.position', 'download_control_position'],
    ['config.controls.download.options.filter_interaction',  'download_control_interact_with_filters'],
    ['config.controls.download.options.notify_on_download',  null, $FIELD_COMPLEX],  // ACF user multiselect — formatted as [{value,label,email}]
    ['config.controls.download.options.included_properties', null,         $FIELD_COMPLEX],   // newline-delimited textarea
    ['config.controls.download.options.notification_url',    null,         $FIELD_READONLY],  // hardcoded ""

    // custom_html
    ['config.controls.custom_html.enabled',      'custom_html_control_enable'],
    ['config.controls.custom_html.position',     'custom_html_control_position'],
    ['config.controls.custom_html.options.html', 'custom_html_control_custom_html',  $FIELD_RAW],

    // elevation
    ['config.controls.elevation.enabled',  'elevation_line_chart_enable_elevation_chart'],
    ['config.controls.elevation.position', 'elevation_line_chart_control_position'],
    ['config.controls.elevation.options.single_line',  'elevation_line_chart_single_line'],
    ['config.controls.elevation.options.open_on_load', 'elevation_line_chart_open_on_load'],
    ['config.controls.elevation.options.profile_color','elevation_line_chart_profile_color'],
    ['config.controls.elevation.options.units',        'elevation_line_chart_units'],
    ['config.controls.elevation.options.line_length',  'elevation_line_chart_show_line_length'],

    // category_filter
    ['config.controls.category_filter.enabled',  'filter_category_filter_enable'],
    ['config.controls.category_filter.position', 'filter_category_filter_position'],
    ['config.controls.category_filter.options.category_display',        'filter_category_filter_category_display'],
    ['config.controls.category_filter.options.checkbox_display',        'filter_category_filter_checkbox_display'],
    ['config.controls.category_filter.options.show_number_of_features', 'filter_category_filter_show_number_of_features'],
    ['config.controls.category_filter.options.functionality',           'filter_category_filter_functionality'],
    ['config.controls.category_filter.options.independent_children',    'filter_category_filter_independent_children'],
    ['config.controls.category_filter.options.parent_cat_display',      'filter_category_filter_parent_cat_display'],
    ['config.controls.category_filter.options.excluded_categories',     null,                                         $FIELD_COMPLEX],  // term objects [{value,label,taxonomy}]
    ['config.controls.category_filter.options.initial_visibility',      'filter_category_filter_initial_visibility'],
    ['config.controls.category_filter.options.pre_selected_categories', 'filter_category_filter_pre_selected_categories'],
    ['config.controls.category_filter.options.category_order',          null,         $FIELD_COMPLEX],   // JSON-encoded array string
    ['config.controls.category_filter.options.reset_button',            'filter_category_filter_reset_button'],
    ['config.controls.category_filter.options.additional_filters',      null,                                         $FIELD_COMPLEX],  // textarea parsed to [{property,label}]
    ['config.controls.category_filter.options.accordion_layout',        'filter_category_filter_accordion_layout'],
    ['config.controls.category_filter.options.external_div.enabled',    'filter_category_filter_render_in_external_div', $FIELD_NONEMPTY_BOOL],
    ['config.controls.category_filter.options.external_div.id',         'filter_category_filter_render_in_external_div'],  // writeable side of the pair above

    // custom_search_filter
    ['config.controls.custom_search_filter.enabled',  'filter_custom_search_filter_enable'],
    ['config.controls.custom_search_filter.position', 'filter_custom_search_filter_position'],
    ['config.controls.custom_search_filter.options.result_number', 'filter_custom_search_filter_number_of_results'],
    ['config.controls.custom_search_filter.options.search_type',   'filter_custom_search_filter_search_type'],
    ['config.controls.custom_search_filter.options.placeholder',   'filter_custom_search_filter_placeholder'],
    ['config.controls.custom_search_filter.options.geocoder.enabled',       'filter_custom_search_filter_include_geocoder'],
    ['config.controls.custom_search_filter.options.geocoder.limit_results', 'filter_custom_search_filter_limit_results'],
    ['config.controls.custom_search_filter.options.limit_by_bounds.enabled',       'filter_custom_search_filter_limit_by_bounds'],
    ['config.controls.custom_search_filter.options.limit_by_bounds.bounds.sw_lat', 'filter_custom_search_filter_bounds_limit_southwest_latitude',  $FIELD_NUMBER],
    ['config.controls.custom_search_filter.options.limit_by_bounds.bounds.sw_lng', 'filter_custom_search_filter_bounds_limit_southwest_longitude', $FIELD_NUMBER],
    ['config.controls.custom_search_filter.options.limit_by_bounds.bounds.ne_lat', 'filter_custom_search_filter_bounds_limit_northeast_latitude',  $FIELD_NUMBER],
    ['config.controls.custom_search_filter.options.limit_by_bounds.bounds.ne_lng', 'filter_custom_search_filter_bounds_limit_northeast_longitude', $FIELD_NUMBER],
    ['config.controls.custom_search_filter.options.external_div.enabled', 'filter_custom_search_filter_render_in_external_div', $FIELD_NONEMPTY_BOOL],
    ['config.controls.custom_search_filter.options.external_div.id',      'filter_custom_search_filter_render_in_external_div'],

    // list
    ['config.controls.list.enabled',  'list_enable'],
    ['config.controls.list.position', 'list_position'],
    ['config.controls.list.options.sort_by_distance',    'list_sort_by_distance'],
    ['config.controls.list.options.show_distance',       'list_show_distance'],
    ['config.controls.list.options.units',               'list_units'],
    ['config.controls.list.options.listing_type',        'list_listing_type'],
    ['config.controls.list.options.number_of_locations', 'list_number_of_locations', $FIELD_NUMBER],
    ['config.controls.list.options.list_order',          'list_list_order'],
    ['config.controls.list.options.display_images',      'list_display_images'],
    ['config.controls.list.options.group_by_category',   'list_group_by_category'],
    ['config.controls.list.options.store_locator.enabled',          'list_store_locator_options_enable'],
    ['config.controls.list.options.store_locator.sort_hours_by_day','list_store_locator_options_sort_hours_by_day'],
    ['config.controls.list.options.store_locator.texts',             null,            $FIELD_READONLY],  // i18n strings, server-side only
    ['config.controls.list.options.external_div.enabled', 'list_render_in_external_div', $FIELD_NONEMPTY_BOOL],
    ['config.controls.list.options.external_div.id',      'list_render_in_external_div'],

    // filter_dropdown
    ['config.controls.filter_dropdown.enabled',  'filter_filter_dropdown_enable'],
    ['config.controls.filter_dropdown.position', 'filter_filter_dropdown_position'],
    ['config.controls.filter_dropdown.options.placeholder',    'filter_filter_dropdown_placeholder'],
    ['config.controls.filter_dropdown.options.display_images', 'filter_filter_dropdown_display_images'],

    // -------------------------------------------------------------------------
    // Popups
    // -------------------------------------------------------------------------
    ['config.popups.styles',            null,                              $FIELD_READONLY],  // getPopupStyles — derived from loaded features
    ['config.popups.shortcode_iframe',  null,                              $FIELD_READONLY],  // computed plugin URL
    ['config.popups.sidebar.enabled',   'open_popups_in_sidebar'],
    ['config.popups.sidebar.width_min', 'minimum_sidebar_width',          $FIELD_NUMBER],
    ['config.popups.sidebar.width_max', 'maximum_sidebar_width',          $FIELD_NUMBER],

    // -------------------------------------------------------------------------
    // Loading
    // -------------------------------------------------------------------------
    ['config.loading.enabled',    null,                                    $FIELD_READONLY],  // hardcoded false
    ['config.loading.graphic',    null,                      $FIELD_COMPLEX],  // SVG string normalised to slug
    ['config.loading.custom',     null,                      $FIELD_COMPLEX],  // image field — returned as {id, url}
    ['config.loading.background', 'loading_background_color'],
    ['config.loading.color',      'loading_loader_color'],

    // -------------------------------------------------------------------------
    // Advanced
    // -------------------------------------------------------------------------
    ['config.advanced.javascript',          'javascript',                        $FIELD_RAW],
    ['config.advanced.cache.enabled',       'cache_use_cache'],
    ['config.advanced.cache.cache_url',     null,                          $FIELD_READONLY],  // computed upload path
    ['config.advanced.embed.enabled',       'embed_allow_embed'],
    ['config.advanced.embed.allowed_origins', null,                        $FIELD_COMPLEX],   // getAllowedOrigins — derived from embed_protect_embed + embed_allowed_origins

    // -------------------------------------------------------------------------
    // Specialty — Compare
    // -------------------------------------------------------------------------
    ['config.specialty.compare.enabled',           'map_compare_enable_map_slider'],
    ['config.specialty.compare.enable_map_slider', 'map_compare_enable_map_slider'],  // intentional duplicate from original; same ACF field
    ['config.specialty.compare.other_map_id',      'map_compare_compared_map'],

    // -------------------------------------------------------------------------
    // Specialty — Submission
    // -------------------------------------------------------------------------
    ['config.specialty.submission.enabled', 'submission_enable_submission'],
    ['config.specialty.submission.options.texts.header',            null,             $FIELD_READONLY],  // hardcoded "Submit a Point"
    ['config.specialty.submission.options.texts.button',            null,             $FIELD_READONLY],  // hardcoded "Submit a Point"
    ['config.specialty.submission.options.texts.drag_zoom',         'submission_custom_texts_drag_zoom'],
    ['config.specialty.submission.options.texts.capture_point',     'submission_custom_texts_capture_point'],
    ['config.specialty.submission.options.texts.choose_how',        'submission_custom_texts_choose_how'],
    ['config.specialty.submission.options.texts.address_search',    'submission_custom_texts_address_search'],
    ['config.specialty.submission.options.texts.map_click',         'submission_custom_texts_map_click'],
    ['config.specialty.submission.options.texts.search_location',   'submission_custom_texts_search_location'],
    ['config.specialty.submission.options.texts.selection_saved',   'submission_custom_texts_selection_saved'],
    ['config.specialty.submission.options.texts.selection_error',   'submission_custom_texts_selection_error'],
    ['config.specialty.submission.options.texts.add_point_text',    'submission_custom_texts_add_point_text'],
    ['config.specialty.submission.options.texts.try_again',         'submission_custom_texts_try_again'],
    ['config.specialty.submission.options.texts.confirm',           'submission_custom_texts_confirm'],
    ['config.specialty.submission.options.texts.no_permissions',    'submission_custom_texts_no_permissions'],
    ['config.specialty.submission.options.texts.change_map_location','submission_custom_texts_change_map_location'],
    ['config.specialty.submission.options.texts.back',              'submission_custom_texts_back'],
    ['config.specialty.submission.options.texts.save',              'submission_custom_texts_save'],
    ['config.specialty.submission.options.texts.submit',            'submission_custom_texts_submit'],
    ['config.specialty.submission.options.texts.thanks',            'submission_custom_texts_thanks'],
    ['config.specialty.submission.options.geocoder.enabled',        'submission_submission_interface_include_address_search'],
    ['config.specialty.submission.options.method',                  null,             $FIELD_COMPLEX],   // derived from geocoder.enabled: "address" | "click"
    ['config.specialty.submission.options.size',                    null,             $FIELD_READONLY],  // hardcoded "lg"
    ['config.specialty.submission.options.fields_iframe',           null,             $FIELD_READONLY],  // getSubmissionURL
    ['config.specialty.submission.options.categories',              null,             $FIELD_COMPLEX],   // getSubmissionCategories — term objects
    ['config.specialty.submission.options.title_field',             'submission_submission_interface_title_field'],
    ['config.specialty.submission.administration.allowed_area',         'submission_administration_allowed_area'],
    ['config.specialty.submission.administration.template_posts',       null,         $FIELD_COMPLEX],   // getTemplatePosts — falls back to singular field
    ['config.specialty.submission.administration.notify_users',         null,         $FIELD_READONLY],  // hardcoded false
    ['config.specialty.submission.administration.editing_allowed',      null,         $FIELD_READONLY],  // hardcoded false
    ['config.specialty.submission.administration.publish_immediately',  'submission_administration_publish_immediately'],

    // -------------------------------------------------------------------------
    // Specialty — Heatmap
    // -------------------------------------------------------------------------
    ['config.specialty.heatmap.enabled',           'heatmap_enable_heatmap'],
    ['config.specialty.heatmap.layer',             'heatmap_heatmap_layer'],
    ['config.specialty.heatmap.visibility',        'heatmap_heatmap_layer_visibility'],
    ['config.specialty.heatmap.weighted_property', 'heatmap_heatmap_weighted_property'],
    ['config.specialty.heatmap.intensity',         'heatmap_heatmap_intensity'],
    ['config.specialty.heatmap.color_range',       null,                              $FIELD_COMPLEX],   // newline-delimited textarea
    ['config.specialty.heatmap.point_radius',      'heatmap_heatmap_point_radius'],
    ['config.specialty.heatmap.opacity',           'heatmap_heatmap_opacity'],

    // -------------------------------------------------------------------------
    // Specialty — Listings
    // -------------------------------------------------------------------------
    ['config.specialty.listings.enabled',            'listing_page_enable_listing_page'],
    ['config.specialty.listings.style',              'listing_page_options_listing_style'],
    ['config.specialty.listings.titles_on_images',   'listing_page_options_show_titles_on_images'],
    ['config.specialty.listings.container_class',    'listing_page_options_listing_container_class'],
    ['config.specialty.listings.custom_html',        'listing_page_options_custom_listing_html', $FIELD_RAW],
    ['config.specialty.listings.order',              'listing_page_options_listings_order'],
    ['config.specialty.listings.interaction',        'listing_page_options_interaction_event'],
    ['config.specialty.listings.center',             'listing_page_options_center_on_hover'],
    ['config.specialty.listings.popup',              'listing_page_options_popup_on_hover'],
    ['config.specialty.listings.lazy_load',          'listing_page_options_lazy_load'],
    ['config.specialty.listings.sticky_map',         'listing_page_options_sticky_map'],
    ['config.specialty.listings.link_target',        'listing_page_options_link_destination'],
    ['config.specialty.listings.link_blank',         'listing_page_options_links_in_new_window'],
    ['config.specialty.listings.reset_zoom_mouseout','listing_page_options_reset_zoom_on_mouseout'],

  );
}
