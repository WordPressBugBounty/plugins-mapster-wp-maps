(async function ($) {

  let data = null;
  let map = null;

  let totalSelect2Fields = 6;
  let select2Ready = 0;
  acf.addAction('select2_init', ( $select, args, settings, field ) => {
    select2Ready += 1;
    if (totalSelect2Fields === select2Ready) {
      init();
    }
  });

  async function init() {
    data = await fetchConfig();
    map = await startMap();
    setListeners();
  }


  async function startMap() {
    const mapProvider = $('.acf-field[data-name="map_provider"] select').val();

    let mapster = false;
    if (window.mapster_params.is_dev === "true") {
      const base_url = "http://localhost:5173";
      if (mapProvider === "maplibre" || mapProvider === "custom-image") {
        mapster = await import(`${base_url}/src/index-maplibre.js`);
      }
      if (mapProvider === "mapbox") {
        mapster = await import(`${base_url}/src/index-mapbox.js`);
      }
      if (mapProvider === "google-maps") {
        mapster = await import(`${base_url}/src/index-google.js`);
      }
    } else {
      const base_url = window.mapster_params.sdk_base_url;
      const free = window.mapster_params.is_pro === "true" ? "pro" : "free";
      if (mapProvider === "maplibre" || mapProvider === "custom-image") {
        mapster = await import(`${base_url}/${free}/mapster-maplibre-${free}.js`);
      }
      if (mapProvider === "mapbox") {
        mapster = await import(`${base_url}/${free}/mapster-mapbox-${free}.js`);
      }
      if (mapProvider === "google-maps") {
        mapster = await import(`${base_url}/${free}/mapster-google-${free}.js`);
      }
    }

    const map = new mapster.MapInstance();

    data.config.layout.ignore_container = false;
    data.config.layout.height = '400';
    data.config.layout.height_units = 'px';
    data.config.layout.full_page = false;

    map.init({
      config: data.config,
      features: data.features,
      paid : window.mapster_params.is_pro === "true"
    });

    return map;
  }

  async function fetchConfig() {
    const response = await fetch(`${window.mapster_params.rest_url}mapster-wp-maps/map-live`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        id: document.getElementById('post_ID').value,
        features: getFeatures(),
        overrides : getConfig()
      })
    }).then(resp => resp.json())
    return response;
  }

  function getFeatures() {
    const locations = $('.acf-field[data-name="locations"] select').select2('data').map(item => item.id)
    const lines = $('.acf-field[data-name="lines"] select').select2('data').map(item => item.id)
    const polygons = $('.acf-field[data-name="polygons"] select').select2('data').map(item => item.id)
    const categories = $('.acf-field[data-name="add_by_category"] select').select2('data').map(item => item.id)
    const custom = $('.acf-field[data-name="add_custom_posts"] select').select2('data').map(item => item.id)
    const customCats = $('.acf-field[data-name="add_by_custom_category"] select').select2('data').map(item => item.id)
    return {
      ids: locations.concat(lines.concat(polygons)),
      categories,
      custom,
      custom_cats : customCats
    }
  }

  function getConfig() {
    const field_map = returnFieldMap();
    let override = {};
    for (const prop in field_map) {
      let value = "";
      if(field_map[prop].type === 'checked') {
        value = $(field_map[prop].selector).is(':checked')
      }
      if(field_map[prop].type === 'val') {
        value = $(field_map[prop].selector).val();
      }
      if(field_map[prop].type === 'select2') {
        value = $(field_map[prop].selector).select2('data').map(item => item.id);
      }
      override[prop] = value
    }
    return override;
  }

  function setListeners() {
    const field_map = returnFieldMap();
    for (const prop in field_map) {
      registerListener(field_map[prop]);
    }
    const features_map = returnFeaturesMap();
    for (const prop in features_map) {
      registerListener(features_map[prop]);
    }
  }

  async function runUpdate() {
    data = await fetchConfig();
    map.updateConfig(data.config);
    map.updateFeatures(data.features);
  }

  function registerListener(field) {
    let jQueryElement = field.selector;
    if (field.type !== 'select2') {
      $(document).on('change', jQueryElement.replace('select', ':input'), () => {
        runUpdate()
      })
    } else {
      $(document).on('change', jQueryElement, () => {
        runUpdate()
      })
    }
  }

  function returnFeaturesMap() {
    return {
      "locations": { selector: '.acf-field[data-name="locations"] select', type: 'select2' },
      "lines": { selector: '.acf-field[data-name="lines"] select', type: 'select2' },
      "polygons": { selector: '.acf-field[data-name="polygons"] select', type: 'select2' },
      "add_by_category": { selector: '.acf-field[data-name="add_by_category"] select', type: 'select2' },
      "add_custom_posts": { selector: '.acf-field[data-name="add_custom_posts"] select', type: 'select2' },
      "add_by_custom_category": { selector: '.acf-field[data-name="add_by_custom_category"] select', type: 'select2' }
    }
  }

  function returnFieldMap() {
    return {
      "3d_control_enable": { selector: '.acf-field[data-name="3d_control"] .acf-field[data-name="enable"] :checkbox', type: 'checked' },
      "3d_control_position": { selector: '.acf-field[data-name="3d_control"] .acf-field[data-name="position"] select', type: 'val' },
      "allow_rotation_and_pitch": { selector: '.acf-field[data-name="allow_rotation_and_pitch"] :checkbox', type: 'checked' },
      "attribution_control_position": { selector: '.acf-field[data-name="attribution_control"] .acf-field[data-name="position"] select', type: 'val' },
      "cache_use_cache": { selector: '.acf-field[data-name="cache"] .acf-field[data-name="use_cache"] :checkbox', type: 'checked' },
      "circle_clustering": { selector: '.acf-field[data-name="circle_clustering"] :checkbox', type: 'checked' },
      "clicking_disabled": { selector: '.acf-field[data-name="clicking_disabled"] :checkbox', type: 'checked' },
      "cluster_options_categories_to_cluster": { selector: '.acf-field[data-name="cluster_options"] .acf-field[data-name="categories_to_cluster"] :input', type: 'val' },
      "cluster_options_category_cluster_styling": { selector: '.acf-field[data-name="cluster_options"] .acf-field[data-name="category_cluster_styling"] :input', type: 'val' },
      "cluster_options_cluster_by_category": { selector: '.acf-field[data-name="cluster_options"] .acf-field[data-name="cluster_by_category"] :checkbox', type: 'checked' },
      "cluster_options_image_on_cluster": { selector: '.acf-field[data-name="cluster_options"] .acf-field[data-name="image_on_cluster"] :input', type: 'val' },
      "cluster_options_large_cluster_border_color": { selector: '.acf-field[data-name="cluster_options"] .acf-field[data-name="large_cluster_border_color"] :input', type: 'val' },
      "cluster_options_large_cluster_color": { selector: '.acf-field[data-name="cluster_options"] .acf-field[data-name="large_cluster_color"] :input', type: 'val' },
      "cluster_options_large_cluster_font_color": { selector: '.acf-field[data-name="cluster_options"] .acf-field[data-name="large_cluster_font_color"] :input', type: 'val' },
      "cluster_options_large_cluster_radius": { selector: '.acf-field[data-name="cluster_options"] .acf-field[data-name="large_cluster_radius"] :input', type: 'val' },
      "cluster_options_large_cluster_width": { selector: '.acf-field[data-name="cluster_options"] .acf-field[data-name="large_cluster_width"] :input', type: 'val' },
      "cluster_options_medium_cluster_border_color": { selector: '.acf-field[data-name="cluster_options"] .acf-field[data-name="medium_cluster_border_color"] :input', type: 'val' },
      "cluster_options_medium_cluster_color": { selector: '.acf-field[data-name="cluster_options"] .acf-field[data-name="medium_cluster_color"] :input', type: 'val' },
      "cluster_options_medium_cluster_count": { selector: '.acf-field[data-name="cluster_options"] .acf-field[data-name="medium_cluster_count"] :input', type: 'val' },
      "cluster_options_medium_cluster_font_color": { selector: '.acf-field[data-name="cluster_options"] .acf-field[data-name="medium_cluster_font_color"] :input', type: 'val' },
      "cluster_options_medium_cluster_radius": { selector: '.acf-field[data-name="cluster_options"] .acf-field[data-name="medium_cluster_radius"] :input', type: 'val' },
      "cluster_options_medium_cluster_width": { selector: '.acf-field[data-name="cluster_options"] .acf-field[data-name="medium_cluster_width"] :input', type: 'val' },
      "cluster_options_show_category_name": { selector: '.acf-field[data-name="cluster_options"] .acf-field[data-name="show_category_name"] :checkbox', type: 'checked' },
      "cluster_options_small_cluster_border_color": { selector: '.acf-field[data-name="cluster_options"] .acf-field[data-name="small_cluster_border_color"] :input', type: 'val' },
      "cluster_options_small_cluster_color": { selector: '.acf-field[data-name="cluster_options"] .acf-field[data-name="small_cluster_color"] :input', type: 'val' },
      "cluster_options_small_cluster_count": { selector: '.acf-field[data-name="cluster_options"] .acf-field[data-name="small_cluster_count"] :input', type: 'val' },
      "cluster_options_small_cluster_font_color": { selector: '.acf-field[data-name="cluster_options"] .acf-field[data-name="small_cluster_font_color"] :input', type: 'val' },
      "cluster_options_small_cluster_radius": { selector: '.acf-field[data-name="cluster_options"] .acf-field[data-name="small_cluster_radius"] :input', type: 'val' },
      "cluster_options_small_cluster_width": { selector: '.acf-field[data-name="cluster_options"] .acf-field[data-name="small_cluster_width"] :input', type: 'val' },
      "control_menu_enable": { selector: '.acf-field[data-name="control_menu"] .acf-field[data-name="enable"] :checkbox', type: 'checked' },
      "control_menu_included_controls": { selector: '.acf-field[data-name="control_menu"] .acf-field[data-name="included_controls"] :input', type: 'val' },
      "control_menu_position": { selector: '.acf-field[data-name="control_menu"] .acf-field[data-name="position"] select', type: 'val' },
      "control_order": { selector: '.acf-field[data-name="control_order"] :input', type: 'val' },
      "cooperative_gestures": { selector: '.acf-field[data-name="cooperative_gestures"] :checkbox', type: 'checked' },
      "custom_html_control_custom_html": { selector: '.acf-field[data-name="custom_html_control"] .acf-field[data-name="custom_html"] :input', type: 'val' },
      "custom_html_control_enable": { selector: '.acf-field[data-name="custom_html_control"] .acf-field[data-name="enable"] :checkbox', type: 'checked' },
      "custom_html_control_position": { selector: '.acf-field[data-name="custom_html_control"] .acf-field[data-name="position"] select', type: 'val' },
      "directions_control_default_type": { selector: '.acf-field[data-name="directions_control"] .acf-field[data-name="default_type"] select', type: 'val' },
      "directions_control_enable": { selector: '.acf-field[data-name="directions_control"] .acf-field[data-name="enable"] :checkbox', type: 'checked' },
      "directions_control_placeholder_text": { selector: '.acf-field[data-name="directions_control"] .acf-field[data-name="placeholder_text"] :input', type: 'val' },
      "directions_control_position": { selector: '.acf-field[data-name="directions_control"] .acf-field[data-name="position"] select', type: 'val' },
      "directions_control_units": { selector: '.acf-field[data-name="directions_control"] .acf-field[data-name="units"] select', type: 'val' },
      "download_control_enable": { selector: '.acf-field[data-name="download_control"] .acf-field[data-name="enable"] :checkbox', type: 'checked' },
      "download_control_included_fields": { selector: '.acf-field[data-name="download_control"] .acf-field[data-name="included_fields"] :input', type: 'val' },
      "download_control_interact_with_filters": { selector: '.acf-field[data-name="download_control"] .acf-field[data-name="interact_with_filters"] :checkbox', type: 'checked' },
      "download_control_notify_on_download": { selector: '.acf-field[data-name="download_control"] .acf-field[data-name="notify_on_download"] :input', type: 'val' },
      "download_control_position": { selector: '.acf-field[data-name="download_control"] .acf-field[data-name="position"] select', type: 'val' },
      "elevation_line_chart_control_position": { selector: '.acf-field[data-name="elevation_line_chart"] .acf-field[data-name="control_position"] select', type: 'val' },
      "elevation_line_chart_enable_elevation_chart": { selector: '.acf-field[data-name="elevation_line_chart"] .acf-field[data-name="enable_elevation_chart"] :checkbox', type: 'checked' },
      "elevation_line_chart_open_on_load": { selector: '.acf-field[data-name="elevation_line_chart"] .acf-field[data-name="open_on_load"] :checkbox', type: 'checked' },
      "elevation_line_chart_profile_color": { selector: '.acf-field[data-name="elevation_line_chart"] .acf-field[data-name="profile_color"] :input', type: 'val' },
      "elevation_line_chart_show_line_length": { selector: '.acf-field[data-name="elevation_line_chart"] .acf-field[data-name="show_line_length"] :checkbox', type: 'checked' },
      "elevation_line_chart_single_line": { selector: '.acf-field[data-name="elevation_line_chart"] .acf-field[data-name="single_line"] :checkbox', type: 'checked' },
      "elevation_line_chart_units": { selector: '.acf-field[data-name="elevation_line_chart"] .acf-field[data-name="units"] select', type: 'val' },
      "embed_allow_embed": { selector: '.acf-field[data-name="embed"] .acf-field[data-name="allow_embed"] :checkbox', type: 'checked' },
      "filter_category_filter_accordion_layout": { selector: '.acf-field[data-name="filter"] .acf-field[data-name="category_filter"] .acf-field[data-name="accordion_layout"] :checkbox', type: 'checked' },
      "filter_category_filter_additional_filters": { selector: '.acf-field[data-name="filter"] .acf-field[data-name="category_filter"] .acf-field[data-name="additional_filters"] :input', type: 'val' },
      "filter_category_filter_category_display": { selector: '.acf-field[data-name="filter"] .acf-field[data-name="category_filter"] .acf-field[data-name="category_display"] select', type: 'val' },
      "filter_category_filter_category_order": { selector: '.acf-field[data-name="filter"] .acf-field[data-name="category_filter"] .acf-field[data-name="category_order"] :input', type: 'val' },
      "filter_category_filter_checkbox_display": { selector: '.acf-field[data-name="filter"] .acf-field[data-name="category_filter"] .acf-field[data-name="checkbox_display"] select', type: 'val' },
      "filter_category_filter_enable": { selector: '.acf-field[data-name="filter"] .acf-field[data-name="category_filter"] .acf-field[data-name="enable"] :checkbox', type: 'checked' },
      "filter_category_filter_excluded_categories": { selector: '.acf-field[data-name="filter"] .acf-field[data-name="category_filter"] .acf-field[data-name="excluded_categories"] :input', type: 'val' },
      "filter_category_filter_functionality": { selector: '.acf-field[data-name="filter"] .acf-field[data-name="category_filter"] .acf-field[data-name="functionality"] select', type: 'val' },
      "filter_category_filter_independent_children": { selector: '.acf-field[data-name="filter"] .acf-field[data-name="category_filter"] .acf-field[data-name="independent_children"] :checkbox', type: 'checked' },
      "filter_category_filter_initial_visibility": { selector: '.acf-field[data-name="filter"] .acf-field[data-name="category_filter"] .acf-field[data-name="initial_visibility"] :checkbox', type: 'checked' },
      "filter_category_filter_parent_cat_display": { selector: '.acf-field[data-name="filter"] .acf-field[data-name="category_filter"] .acf-field[data-name="parent_cat_display"] select', type: 'val' },
      "filter_category_filter_position": { selector: '.acf-field[data-name="filter"] .acf-field[data-name="category_filter"] .acf-field[data-name="position"] select', type: 'val' },
      "filter_category_filter_pre_selected_categories": { selector: '.acf-field[data-name="filter"] .acf-field[data-name="category_filter"] .acf-field[data-name="pre_selected_categories"] :input', type: 'val' },
      "filter_category_filter_render_in_external_div": { selector: '.acf-field[data-name="filter"] .acf-field[data-name="category_filter"] .acf-field[data-name="render_in_external_div"] :input', type: 'val' },
      "filter_category_filter_reset_button": { selector: '.acf-field[data-name="filter"] .acf-field[data-name="category_filter"] .acf-field[data-name="reset_button"] :checkbox', type: 'checked' },
      "filter_category_filter_show_number_of_features": { selector: '.acf-field[data-name="filter"] .acf-field[data-name="category_filter"] .acf-field[data-name="show_number_of_features"] :checkbox', type: 'checked' },
      "filter_custom_search_filter_bounds_limit_northeast_latitude": { selector: '.acf-field[data-name="filter"] .acf-field[data-name="custom_search_filter"] .acf-field[data-name="bounds_limit"] .acf-field[data-name="northeast_latitude"] :input', type: 'val' },
      "filter_custom_search_filter_bounds_limit_northeast_longitude": { selector: '.acf-field[data-name="filter"] .acf-field[data-name="custom_search_filter"] .acf-field[data-name="bounds_limit"] .acf-field[data-name="northeast_longitude"] :input', type: 'val' },
      "filter_custom_search_filter_bounds_limit_southwest_latitude": { selector: '.acf-field[data-name="filter"] .acf-field[data-name="custom_search_filter"] .acf-field[data-name="bounds_limit"] .acf-field[data-name="southwest_latitude"] :input', type: 'val' },
      "filter_custom_search_filter_bounds_limit_southwest_longitude": { selector: '.acf-field[data-name="filter"] .acf-field[data-name="custom_search_filter"] .acf-field[data-name="bounds_limit"] .acf-field[data-name="southwest_longitude"] :input', type: 'val' },
      "filter_custom_search_filter_enable": { selector: '.acf-field[data-name="filter"] .acf-field[data-name="custom_search_filter"] .acf-field[data-name="enable"] :checkbox', type: 'checked' },
      "filter_custom_search_filter_include_geocoder": { selector: '.acf-field[data-name="filter"] .acf-field[data-name="custom_search_filter"] .acf-field[data-name="include_geocoder"] :checkbox', type: 'checked' },
      "filter_custom_search_filter_limit_by_bounds": { selector: '.acf-field[data-name="filter"] .acf-field[data-name="custom_search_filter"] .acf-field[data-name="limit_by_bounds"] :checkbox', type: 'checked' },
      "filter_custom_search_filter_limit_results": { selector: '.acf-field[data-name="filter"] .acf-field[data-name="custom_search_filter"] .acf-field[data-name="limit_results"] :input', type: 'val' },
      "filter_custom_search_filter_number_of_results": { selector: '.acf-field[data-name="filter"] .acf-field[data-name="custom_search_filter"] .acf-field[data-name="number_of_results"] :input', type: 'val' },
      "filter_custom_search_filter_placeholder": { selector: '.acf-field[data-name="filter"] .acf-field[data-name="custom_search_filter"] .acf-field[data-name="placeholder"] :input', type: 'val' },
      "filter_custom_search_filter_position": { selector: '.acf-field[data-name="filter"] .acf-field[data-name="custom_search_filter"] .acf-field[data-name="position"] select', type: 'val' },
      "filter_custom_search_filter_render_in_external_div": { selector: '.acf-field[data-name="filter"] .acf-field[data-name="custom_search_filter"] .acf-field[data-name="render_in_external_div"] :input', type: 'val' },
      "filter_custom_search_filter_search_type": { selector: '.acf-field[data-name="filter"] .acf-field[data-name="custom_search_filter"] .acf-field[data-name="search_type"] select', type: 'val' },
      "filter_filter_dropdown_display_images": { selector: '.acf-field[data-name="filter"] .acf-field[data-name="filter_dropdown"] .acf-field[data-name="display_images"] :checkbox', type: 'checked' },
      "filter_filter_dropdown_enable": { selector: '.acf-field[data-name="filter"] .acf-field[data-name="filter_dropdown"] .acf-field[data-name="enable"] :checkbox', type: 'checked' },
      "filter_filter_dropdown_placeholder": { selector: '.acf-field[data-name="filter"] .acf-field[data-name="filter_dropdown"] .acf-field[data-name="placeholder"] :input', type: 'val' },
      "filter_filter_dropdown_position": { selector: '.acf-field[data-name="filter"] .acf-field[data-name="filter_dropdown"] .acf-field[data-name="position"] select', type: 'val' },
      "fullscreen_control_enable": { selector: '.acf-field[data-name="fullscreen_control"] .acf-field[data-name="enable"] :checkbox', type: 'checked' },
      "fullscreen_control_position": { selector: '.acf-field[data-name="fullscreen_control"] .acf-field[data-name="position"] select', type: 'val' },
      "geocoder_control_accept_latlngs": { selector: '.acf-field[data-name="geocoder_control"] .acf-field[data-name="accept_latlngs"] :checkbox', type: 'checked' },
      "geocoder_control_bounds_limit_northeast_latitude": { selector: '.acf-field[data-name="geocoder_control"] .acf-field[data-name="bounds_limit"] .acf-field[data-name="northeast_latitude"] :input', type: 'val' },
      "geocoder_control_bounds_limit_northeast_longitude": { selector: '.acf-field[data-name="geocoder_control"] .acf-field[data-name="bounds_limit"] .acf-field[data-name="northeast_longitude"] :input', type: 'val' },
      "geocoder_control_bounds_limit_southwest_latitude": { selector: '.acf-field[data-name="geocoder_control"] .acf-field[data-name="bounds_limit"] .acf-field[data-name="southwest_latitude"] :input', type: 'val' },
      "geocoder_control_bounds_limit_southwest_longitude": { selector: '.acf-field[data-name="geocoder_control"] .acf-field[data-name="bounds_limit"] .acf-field[data-name="southwest_longitude"] :input', type: 'val' },
      "geocoder_control_enable": { selector: '.acf-field[data-name="geocoder_control"] .acf-field[data-name="enable"] :checkbox', type: 'checked' },
      "geocoder_control_limit_by_bounds": { selector: '.acf-field[data-name="geocoder_control"] .acf-field[data-name="limit_by_bounds"] :checkbox', type: 'checked' },
      "geocoder_control_limit_results": { selector: '.acf-field[data-name="geocoder_control"] .acf-field[data-name="limit_results"] :input', type: 'val' },
      "geocoder_control_marker_color": { selector: '.acf-field[data-name="geocoder_control"] .acf-field[data-name="marker_color"] :input', type: 'val' },
      "geocoder_control_placeholder": { selector: '.acf-field[data-name="geocoder_control"] .acf-field[data-name="placeholder"] :input', type: 'val' },
      "geocoder_control_position": { selector: '.acf-field[data-name="geocoder_control"] .acf-field[data-name="position"] select', type: 'val' },
      "geolocation_control_enable": { selector: '.acf-field[data-name="geolocation_control"] .acf-field[data-name="enable"] :checkbox', type: 'checked' },
      "geolocation_control_enable_high_accuracy": { selector: '.acf-field[data-name="geolocation_control"] .acf-field[data-name="enable_high_accuracy"] :checkbox', type: 'checked' },
      "geolocation_control_position": { selector: '.acf-field[data-name="geolocation_control"] .acf-field[data-name="position"] select', type: 'val' },
      "geolocation_control_set_on_load": { selector: '.acf-field[data-name="geolocation_control"] .acf-field[data-name="set_on_load"] :checkbox', type: 'checked' },
      "geolocation_control_show_accuracy_circle": { selector: '.acf-field[data-name="geolocation_control"] .acf-field[data-name="show_accuracy_circle"] :checkbox', type: 'checked' },
      "geolocation_control_show_user_heading": { selector: '.acf-field[data-name="geolocation_control"] .acf-field[data-name="show_user_heading"] :checkbox', type: 'checked' },
      "geolocation_control_track_user_location": { selector: '.acf-field[data-name="geolocation_control"] .acf-field[data-name="track_user_location"] :checkbox', type: 'checked' },
      "heatmap_enable_heatmap": { selector: '.acf-field[data-name="heatmap"] .acf-field[data-name="enable_heatmap"] :checkbox', type: 'checked' },
      "heatmap_heatmap_color_range": { selector: '.acf-field[data-name="heatmap"] .acf-field[data-name="heatmap_color_range"] :input', type: 'val' },
      "heatmap_heatmap_intensity": { selector: '.acf-field[data-name="heatmap"] .acf-field[data-name="heatmap_intensity"] :input', type: 'val' },
      "heatmap_heatmap_layer": { selector: '.acf-field[data-name="heatmap"] .acf-field[data-name="heatmap_layer"] select', type: 'val' },
      "heatmap_heatmap_layer_visibility": { selector: '.acf-field[data-name="heatmap"] .acf-field[data-name="heatmap_layer_visibility"] :input', type: 'val' },
      "heatmap_heatmap_opacity": { selector: '.acf-field[data-name="heatmap"] .acf-field[data-name="heatmap_opacity"] :input', type: 'val' },
      "heatmap_heatmap_point_radius": { selector: '.acf-field[data-name="heatmap"] .acf-field[data-name="heatmap_point_radius"] :input', type: 'val' },
      "heatmap_heatmap_weighted_property": { selector: '.acf-field[data-name="heatmap"] .acf-field[data-name="heatmap_weighted_property"] :input', type: 'val' },
      "interactivity": { selector: '.acf-field[data-name="interactivity"] :checkbox', type: 'checked' },
      "javascript": { selector: '.acf-field[data-name="javascript"] :input', type: 'val' },
      "label_icon_clustering": { selector: '.acf-field[data-name="label_icon_clustering"] :checkbox', type: 'checked' },
      "layer_control_checkbox_type": { selector: '.acf-field[data-name="layer_control"] .acf-field[data-name="checkbox_type"] select', type: 'val' },
      "layer_control_enable": { selector: '.acf-field[data-name="layer_control"] .acf-field[data-name="enable"] :checkbox', type: 'checked' },
      "layer_control_position": { selector: '.acf-field[data-name="layer_control"] .acf-field[data-name="position"] select', type: 'val' },
      "layer_control_toggleable_layer_titles": { selector: '.acf-field[data-name="layer_control"] .acf-field[data-name="toggleable_layer_titles"] :input', type: 'val' },
      "layer_control_toggleable_layers": { selector: '.acf-field[data-name="layer_control"] .acf-field[data-name="toggleable_layers"] :input', type: 'val' },
      "layout_add_mobile_breakpoints": { selector: '.acf-field[data-name="layout"] .acf-field[data-name="add_mobile_breakpoints"] :checkbox', type: 'checked' },
      "layout_breakpoints_breakpoint_1": { selector: '.acf-field[data-name="layout"] .acf-field[data-name="breakpoints"] .acf-field[data-name="breakpoint_1"] :input', type: 'val' },
      "layout_breakpoints_breakpoint_1_map_height": { selector: '.acf-field[data-name="layout"] .acf-field[data-name="breakpoints"] .acf-field[data-name="breakpoint_1_map_height"] :input', type: 'val' },
      "layout_breakpoints_breakpoint_1_map_height_units": { selector: '.acf-field[data-name="layout"] .acf-field[data-name="breakpoints"] .acf-field[data-name="breakpoint_1_map_height_units"] select', type: 'val' },
      "layout_breakpoints_breakpoint_1_map_width": { selector: '.acf-field[data-name="layout"] .acf-field[data-name="breakpoints"] .acf-field[data-name="breakpoint_1_map_width"] :input', type: 'val' },
      "layout_breakpoints_breakpoint_1_map_width_units": { selector: '.acf-field[data-name="layout"] .acf-field[data-name="breakpoints"] .acf-field[data-name="breakpoint_1_map_width_units"] select', type: 'val' },
      "layout_breakpoints_breakpoint_2": { selector: '.acf-field[data-name="layout"] .acf-field[data-name="breakpoints"] .acf-field[data-name="breakpoint_2"] :input', type: 'val' },
      "layout_breakpoints_breakpoint_2_map_height": { selector: '.acf-field[data-name="layout"] .acf-field[data-name="breakpoints"] .acf-field[data-name="breakpoint_2_map_height"] :input', type: 'val' },
      "layout_breakpoints_breakpoint_2_map_height_units": { selector: '.acf-field[data-name="layout"] .acf-field[data-name="breakpoints"] .acf-field[data-name="breakpoint_2_map_height_units"] select', type: 'val' },
      "layout_breakpoints_breakpoint_2_map_width": { selector: '.acf-field[data-name="layout"] .acf-field[data-name="breakpoints"] .acf-field[data-name="breakpoint_2_map_width"] :input', type: 'val' },
      "layout_breakpoints_breakpoint_2_map_width_units": { selector: '.acf-field[data-name="layout"] .acf-field[data-name="breakpoints"] .acf-field[data-name="breakpoint_2_map_width_units"] select', type: 'val' },
      "layout_full_page": { selector: '.acf-field[data-name="layout"] .acf-field[data-name="full_page"] :checkbox', type: 'checked' },
      "layout_height": { selector: '.acf-field[data-name="layout"] .acf-field[data-name="height"] :input', type: 'val' },
      "layout_height_units": { selector: '.acf-field[data-name="layout"] .acf-field[data-name="height_units"] select', type: 'val' },
      "layout_ignore_container": { selector: '.acf-field[data-name="layout"] .acf-field[data-name="ignore_container"] :checkbox', type: 'checked' },
      "layout_map_only": { selector: '.acf-field[data-name="layout"] .acf-field[data-name="map_only"] :checkbox', type: 'checked' },
      "layout_width": { selector: '.acf-field[data-name="layout"] .acf-field[data-name="width"] :input', type: 'val' },
      "layout_width_units": { selector: '.acf-field[data-name="layout"] .acf-field[data-name="width_units"] select', type: 'val' },
      "line_clustering": { selector: '.acf-field[data-name="line_clustering"] :checkbox', type: 'checked' },
      "list_display_images": { selector: '.acf-field[data-name="list"] .acf-field[data-name="display_images"] :checkbox', type: 'checked' },
      "list_enable": { selector: '.acf-field[data-name="list"] .acf-field[data-name="enable"] :checkbox', type: 'checked' },
      "list_group_by_category": { selector: '.acf-field[data-name="list"] .acf-field[data-name="group_by_category"] :checkbox', type: 'checked' },
      "list_list_order": { selector: '.acf-field[data-name="list"] .acf-field[data-name="list_order"] select', type: 'val' },
      "list_listing_type": { selector: '.acf-field[data-name="list"] .acf-field[data-name="listing_type"] select', type: 'val' },
      "list_number_of_locations": { selector: '.acf-field[data-name="list"] .acf-field[data-name="number_of_locations"] :input', type: 'val' },
      "list_position": { selector: '.acf-field[data-name="list"] .acf-field[data-name="position"] select', type: 'val' },
      "list_render_in_external_div": { selector: '.acf-field[data-name="list"] .acf-field[data-name="render_in_external_div"] :input', type: 'val' },
      "list_show_distance": { selector: '.acf-field[data-name="list"] .acf-field[data-name="show_distance"] :checkbox', type: 'checked' },
      "list_sort_by_distance": { selector: '.acf-field[data-name="list"] .acf-field[data-name="sort_by_distance"] :checkbox', type: 'checked' },
      "list_store_locator_options_enable": { selector: '.acf-field[data-name="list"] .acf-field[data-name="store_locator_options"] .acf-field[data-name="enable"] :checkbox', type: 'checked' },
      "list_store_locator_options_sort_hours_by_day": { selector: '.acf-field[data-name="list"] .acf-field[data-name="store_locator_options"] .acf-field[data-name="sort_hours_by_day"] :checkbox', type: 'checked' },
      "list_units": { selector: '.acf-field[data-name="list"] .acf-field[data-name="units"] select', type: 'val' },
      "listing_page_enable_listing_page": { selector: '.acf-field[data-name="listing_page"] .acf-field[data-name="enable_listing_page"] :checkbox', type: 'checked' },
      "listing_page_options_center_on_hover": { selector: '.acf-field[data-name="listing_page"] .acf-field[data-name="options"] .acf-field[data-name="center_on_hover"] :checkbox', type: 'checked' },
      "listing_page_options_custom_listing_html": { selector: '.acf-field[data-name="listing_page"] .acf-field[data-name="options"] .acf-field[data-name="custom_listing_html"] :input', type: 'val' },
      "listing_page_options_interaction_event": { selector: '.acf-field[data-name="listing_page"] .acf-field[data-name="options"] .acf-field[data-name="interaction_event"] select', type: 'val' },
      "listing_page_options_lazy_load": { selector: '.acf-field[data-name="listing_page"] .acf-field[data-name="options"] .acf-field[data-name="lazy_load"] :checkbox', type: 'checked' },
      "listing_page_options_link_destination": { selector: '.acf-field[data-name="listing_page"] .acf-field[data-name="options"] .acf-field[data-name="link_destination"] select', type: 'val' },
      "listing_page_options_links_in_new_window": { selector: '.acf-field[data-name="listing_page"] .acf-field[data-name="options"] .acf-field[data-name="links_in_new_window"] :checkbox', type: 'checked' },
      "listing_page_options_listing_container_class": { selector: '.acf-field[data-name="listing_page"] .acf-field[data-name="options"] .acf-field[data-name="listing_container_class"] :input', type: 'val' },
      "listing_page_options_listing_style": { selector: '.acf-field[data-name="listing_page"] .acf-field[data-name="options"] .acf-field[data-name="listing_style"] select', type: 'val' },
      "listing_page_options_listings_order": { selector: '.acf-field[data-name="listing_page"] .acf-field[data-name="options"] .acf-field[data-name="listings_order"] select', type: 'val' },
      "listing_page_options_popup_on_hover": { selector: '.acf-field[data-name="listing_page"] .acf-field[data-name="options"] .acf-field[data-name="popup_on_hover"] :checkbox', type: 'checked' },
      "listing_page_options_reset_zoom_on_mouseout": { selector: '.acf-field[data-name="listing_page"] .acf-field[data-name="options"] .acf-field[data-name="reset_zoom_on_mouseout"] :checkbox', type: 'checked' },
      "listing_page_options_show_titles_on_images": { selector: '.acf-field[data-name="listing_page"] .acf-field[data-name="options"] .acf-field[data-name="show_titles_on_images"] :checkbox', type: 'checked' },
      "listing_page_options_sticky_map": { selector: '.acf-field[data-name="listing_page"] .acf-field[data-name="options"] .acf-field[data-name="sticky_map"] :checkbox', type: 'checked' },
      "load_3d_model_libraries": { selector: '.acf-field[data-name="load_3d_model_libraries"] :checkbox', type: 'checked' },
      "loading_background_color": { selector: '.acf-field[data-name="loading"] .acf-field[data-name="background_color"] :input', type: 'val' },
      "loading_loader_color": { selector: '.acf-field[data-name="loading"] .acf-field[data-name="loader_color"] :input', type: 'val' },
      "loading_loading_graphic": { selector: '.acf-field[data-name="loading"] .acf-field[data-name="loading_graphic"] select', type: 'val' },
      "logo_control_position": { selector: '.acf-field[data-name="logo_control"] .acf-field[data-name="position"] select', type: 'val' },
      "map_compare_compared_map": { selector: '.acf-field[data-name="map_compare"] .acf-field[data-name="compared_map"] :input', type: 'val' },
      "map_compare_enable_map_slider": { selector: '.acf-field[data-name="map_compare"] .acf-field[data-name="enable_map_slider"] :checkbox', type: 'checked' },
      "map_type_access_token": { selector: '.acf-field[data-name="map_type"] .acf-field[data-name="access_token"] :input', type: 'val' },
      "map_type_buildings_3d": { selector: '.acf-field[data-name="map_type"] .acf-field[data-name="buildings_3d"] :checkbox', type: 'checked' },
      "map_type_control_enable": { selector: '.acf-field[data-name="map_type_control"] .acf-field[data-name="enable"] :checkbox', type: 'checked' },
      "map_type_control_position": { selector: '.acf-field[data-name="map_type_control"] .acf-field[data-name="position"] select', type: 'val' },
      "map_type_custom_image": { selector: '.acf-field[data-name="map_type"] .acf-field[data-name="custom_image"] :input', type: 'val' },
      "map_type_custom_mapbox_style": { selector: '.acf-field[data-name="map_type"] .acf-field[data-name="custom_mapbox_style"] :input', type: 'val' },
      "map_type_custom_style_json": { selector: '.acf-field[data-name="map_type"] .acf-field[data-name="custom_style_json"] :input', type: 'val' },
      "view_duplicate_horizontally": { selector: '.acf-field[data-name="duplicate_horizontally_copy"] :checkbox', type: 'checked' },
      "map_type_globe": { selector: '.acf-field[data-name="map_type"] .acf-field[data-name="globe"] :checkbox', type: 'checked' },
      "map_type_globe_background": { selector: '.acf-field[data-name="map_type"] .acf-field[data-name="globe_background"] select', type: 'val' },
      "map_type_map_language": { selector: '.acf-field[data-name="map_type"] .acf-field[data-name="map_language"] select', type: 'val' },
      "map_type_map_provider": { selector: '.acf-field[data-name="map_type"] .acf-field[data-name="map_provider"] select', type: 'val' },
      "map_type_map_tile_style_access_token": { selector: '.acf-field[data-name="map_type"] .acf-field[data-name="map_tile_style_access_token"] select', type: 'val' },
      "map_type_map_tile_style_no_access_token": { selector: '.acf-field[data-name="map_type"] .acf-field[data-name="map_tile_style_no_access_token"] select', type: 'val' },
      "map_type_projection": { selector: '.acf-field[data-name="map_type"] .acf-field[data-name="projection"] select', type: 'val' },
      "map_type_snazzy_map_style": { selector: '.acf-field[data-name="map_type"] .acf-field[data-name="snazzy_map_style"] :input', type: 'val' },
      "map_type_terrain": { selector: '.acf-field[data-name="map_type"] .acf-field[data-name="terrain"] :checkbox', type: 'checked' },
      "marker_clustering": { selector: '.acf-field[data-name="marker_clustering"] :checkbox', type: 'checked' },
      "maximum_sidebar_width": { selector: '.acf-field[data-name="maximum_sidebar_width"] :input', type: 'val' },
      "minimum_sidebar_width": { selector: '.acf-field[data-name="minimum_sidebar_width"] :input', type: 'val' },
      "open_popups_in_sidebar": { selector: '.acf-field[data-name="open_popups_in_sidebar"] :checkbox', type: 'checked' },
      "polygon_clustering": { selector: '.acf-field[data-name="polygon_clustering"] :checkbox', type: 'checked' },
      "print_control_enable": { selector: '.acf-field[data-name="print_control"] .acf-field[data-name="enable"] :checkbox', type: 'checked' },
      "print_control_position": { selector: '.acf-field[data-name="print_control"] .acf-field[data-name="position"] select', type: 'val' },
      "restricted_movement_allowed_bounds_northeast_latitude": { selector: '.acf-field[data-name="restricted_movement"] .acf-field[data-name="allowed_bounds"] .acf-field[data-name="northeast_latitude"] :input', type: 'val' },
      "restricted_movement_allowed_bounds_northeast_longitude": { selector: '.acf-field[data-name="restricted_movement"] .acf-field[data-name="allowed_bounds"] .acf-field[data-name="northeast_longitude"] :input', type: 'val' },
      "restricted_movement_allowed_bounds_southwest_latitude": { selector: '.acf-field[data-name="restricted_movement"] .acf-field[data-name="allowed_bounds"] .acf-field[data-name="southwest_latitude"] :input', type: 'val' },
      "restricted_movement_allowed_bounds_southwest_longitude": { selector: '.acf-field[data-name="restricted_movement"] .acf-field[data-name="allowed_bounds"] .acf-field[data-name="southwest_longitude"] :input', type: 'val' },
      "restricted_movement_allowed_zoom_max_zoom": { selector: '.acf-field[data-name="restricted_movement"] .acf-field[data-name="allowed_zoom"] .acf-field[data-name="max_zoom"] :input', type: 'val' },
      "restricted_movement_allowed_zoom_min_zoom": { selector: '.acf-field[data-name="restricted_movement"] .acf-field[data-name="allowed_zoom"] .acf-field[data-name="min_zoom"] :input', type: 'val' },
      "restricted_movement_restrict_movement": { selector: '.acf-field[data-name="restricted_movement"] .acf-field[data-name="restrict_movement"] :checkbox', type: 'checked' },
      "scale_control_enable": { selector: '.acf-field[data-name="scale_control"] .acf-field[data-name="enable"] :checkbox', type: 'checked' },
      "scale_control_position": { selector: '.acf-field[data-name="scale_control"] .acf-field[data-name="position"] select', type: 'val' },
      "street_view_control_enable": { selector: '.acf-field[data-name="street_view_control"] .acf-field[data-name="enable"] :checkbox', type: 'checked' },
      "street_view_control_position": { selector: '.acf-field[data-name="street_view_control"] .acf-field[data-name="position"] select', type: 'val' },
      "style_control_enable": { selector: '.acf-field[data-name="style_control"] .acf-field[data-name="enable"] :checkbox', type: 'checked' },
      "style_control_initial_style_title": { selector: '.acf-field[data-name="style_control"] .acf-field[data-name="initial_style_title"] :input', type: 'val' },
      "style_control_position": { selector: '.acf-field[data-name="style_control"] .acf-field[data-name="position"] select', type: 'val' },
      "style_control_toggleable_styles": { selector: '.acf-field[data-name="style_control"] .acf-field[data-name="toggleable_styles"] :input', type: 'val' },
      "style_control_toggleable_styles_titles": { selector: '.acf-field[data-name="style_control"] .acf-field[data-name="toggleable_styles_titles"] :input', type: 'val' },
      "submission_administration_allowed_area": { selector: '.acf-field[data-name="submission"] .acf-field[data-name="administration"] .acf-field[data-name="allowed_area"] :input', type: 'val' },
      "submission_administration_publish_immediately": { selector: '.acf-field[data-name="submission"] .acf-field[data-name="administration"] .acf-field[data-name="publish_immediately"] :checkbox', type: 'checked' },
      "submission_administration_template_post": { selector: '.acf-field[data-name="submission"] .acf-field[data-name="administration"] .acf-field[data-name="template_post"] :input', type: 'val' },
      "submission_administration_template_posts": { selector: '.acf-field[data-name="submission"] .acf-field[data-name="administration"] .acf-field[data-name="template_posts"] :input', type: 'val' },
      "submission_custom_texts_add_point_text": { selector: '.acf-field[data-name="submission"] .acf-field[data-name="custom_texts"] .acf-field[data-name="add_point_text"] :input', type: 'val' },
      "submission_custom_texts_address_search": { selector: '.acf-field[data-name="submission"] .acf-field[data-name="custom_texts"] .acf-field[data-name="address_search"] :input', type: 'val' },
      "submission_custom_texts_back": { selector: '.acf-field[data-name="submission"] .acf-field[data-name="custom_texts"] .acf-field[data-name="back"] :input', type: 'val' },
      "submission_custom_texts_capture_point": { selector: '.acf-field[data-name="submission"] .acf-field[data-name="custom_texts"] .acf-field[data-name="capture_point"] :input', type: 'val' },
      "submission_custom_texts_change_map_location": { selector: '.acf-field[data-name="submission"] .acf-field[data-name="custom_texts"] .acf-field[data-name="change_map_location"] :input', type: 'val' },
      "submission_custom_texts_choose_how": { selector: '.acf-field[data-name="submission"] .acf-field[data-name="custom_texts"] .acf-field[data-name="choose_how"] :input', type: 'val' },
      "submission_custom_texts_confirm": { selector: '.acf-field[data-name="submission"] .acf-field[data-name="custom_texts"] .acf-field[data-name="confirm"] :input', type: 'val' },
      "submission_custom_texts_drag_zoom": { selector: '.acf-field[data-name="submission"] .acf-field[data-name="custom_texts"] .acf-field[data-name="drag_zoom"] :input', type: 'val' },
      "submission_custom_texts_map_click": { selector: '.acf-field[data-name="submission"] .acf-field[data-name="custom_texts"] .acf-field[data-name="map_click"] :input', type: 'val' },
      "submission_custom_texts_no_permissions": { selector: '.acf-field[data-name="submission"] .acf-field[data-name="custom_texts"] .acf-field[data-name="no_permissions"] :input', type: 'val' },
      "submission_custom_texts_save": { selector: '.acf-field[data-name="submission"] .acf-field[data-name="custom_texts"] .acf-field[data-name="save"] :input', type: 'val' },
      "submission_custom_texts_search_location": { selector: '.acf-field[data-name="submission"] .acf-field[data-name="custom_texts"] .acf-field[data-name="search_location"] :input', type: 'val' },
      "submission_custom_texts_selection_error": { selector: '.acf-field[data-name="submission"] .acf-field[data-name="custom_texts"] .acf-field[data-name="selection_error"] :input', type: 'val' },
      "submission_custom_texts_selection_saved": { selector: '.acf-field[data-name="submission"] .acf-field[data-name="custom_texts"] .acf-field[data-name="selection_saved"] :input', type: 'val' },
      "submission_custom_texts_submit": { selector: '.acf-field[data-name="submission"] .acf-field[data-name="custom_texts"] .acf-field[data-name="submit"] :input', type: 'val' },
      "submission_custom_texts_thanks": { selector: '.acf-field[data-name="submission"] .acf-field[data-name="custom_texts"] .acf-field[data-name="thanks"] :input', type: 'val' },
      "submission_custom_texts_try_again": { selector: '.acf-field[data-name="submission"] .acf-field[data-name="custom_texts"] .acf-field[data-name="try_again"] :input', type: 'val' },
      "submission_enable_submission": { selector: '.acf-field[data-name="submission"] .acf-field[data-name="enable_submission"] :checkbox', type: 'checked' },
      "submission_submission_interface_categories": { selector: '.acf-field[data-name="submission"] .acf-field[data-name="submission_interface"] .acf-field[data-name="categories"] :input', type: 'val' },
      "submission_submission_interface_include_address_search": { selector: '.acf-field[data-name="submission"] .acf-field[data-name="submission_interface"] .acf-field[data-name="include_address_search"] :checkbox', type: 'checked' },
      "submission_submission_interface_title_field": { selector: '.acf-field[data-name="submission"] .acf-field[data-name="submission_interface"] .acf-field[data-name="title_field"] :input', type: 'val' },
      "view_globe_animation": { selector: '.acf-field[data-name="view"] .acf-field[data-name="globe_animation"] :checkbox', type: 'checked' },
      "view_globe_animation_direction": { selector: '.acf-field[data-name="view"] .acf-field[data-name="globe_animation_direction"] select', type: 'val' },
      "view_globe_animation_speed": { selector: '.acf-field[data-name="view"] .acf-field[data-name="globe_animation_speed"] :input', type: 'val' },
      "view_initial_load": { selector: '.acf-field[data-name="view"] .acf-field[data-name="initial_load"] select', type: 'val' },
      "view_manual_latitude": { selector: '.acf-field[data-name="view"] .acf-field[data-name="manual_latitude"] :input', type: 'val' },
      "view_manual_longitude": { selector: '.acf-field[data-name="view"] .acf-field[data-name="manual_longitude"] :input', type: 'val' },
      "view_manual_pitch": { selector: '.acf-field[data-name="view"] .acf-field[data-name="manual_pitch"] :input', type: 'val' },
      "view_manual_rotation": { selector: '.acf-field[data-name="view"] .acf-field[data-name="manual_rotation"] :input', type: 'val' },
      "view_manual_zoom": { selector: '.acf-field[data-name="view"] .acf-field[data-name="manual_zoom"] :input', type: 'val' },
      "view_padding_around_bounds": { selector: '.acf-field[data-name="view"] .acf-field[data-name="padding_around_bounds"] :input', type: 'val' },
      "zoom_control_enable": { selector: '.acf-field[data-name="zoom_control"] .acf-field[data-name="enable"] :checkbox', type: 'checked' },
      "zoom_control_position": { selector: '.acf-field[data-name="zoom_control"] .acf-field[data-name="position"] select', type: 'val' },
      "zoom_on_scroll": { selector: '.acf-field[data-name="zoom_on_scroll"] :checkbox', type: 'checked' },
    }
  }


})(jQuery);
