<?php

include_once plugin_dir_path( __FILE__ ) . 'field-map.php';
// ---------------------------------------------------------------------------
// Sentinel — returned to signal "key not present in array"
// ---------------------------------------------------------------------------
function mapster_missing() {
    static $s = null;
    if ( !$s ) {
        $s = new stdClass();
    }
    return $s;
}

// ---------------------------------------------------------------------------
// Loader graphic helpers
// ---------------------------------------------------------------------------
function mapster_loader_graphic_to_slug(  $value  ) {
    if ( !$value ) {
        return '';
    }
    if ( $value === 'custom' ) {
        return 'custom';
    }
    $fingerprints = [
        '20;45;57;80;64;32'           => 'audio',
        'cx="5" cy="50" r="5"'        => 'ball-triangle',
        'viewBox="0 0 135 140"'       => 'bars',
        'M67.447 58'                  => 'circles',
        'cx="92.5" cy="92.5"'         => 'grid',
        'stroke-opacity=".5" cx="18"' => 'oval',
        'keySplines="0.165'           => 'puff',
        'values="6;22"'               => 'rings',
        'cy="11.462"'                 => 'spinning-circles',
        'linearGradient'              => 'tail-spin',
        'cx="105"'                    => 'three-dots',
    ];
    foreach ( $fingerprints as $needle => $slug ) {
        if ( str_contains( (string) $value, $needle ) ) {
            return $slug;
        }
    }
    return (string) $value;
}

function mapster_loader_slug_to_graphic(  $slug, $post_id  ) {
    if ( $slug === 'custom' ) {
        return 'custom';
    }
    $field = get_field_object( 'loading_loading_graphic', $post_id );
    if ( !$field || empty( $field['choices'] ) ) {
        return $slug;
    }
    foreach ( array_keys( $field['choices'] ) as $svg_value ) {
        if ( mapster_loader_graphic_to_slug( $svg_value ) === $slug ) {
            return $svg_value;
        }
    }
    return $slug;
}

function mapster_cluster_type_map() {
    return [
        'circle'  => 'circle_clustering',
        'symbol'  => 'label_icon_clustering',
        'marker'  => 'marker_clustering',
        'polygon' => 'polygon_clustering',
        'line'    => 'line_clustering',
    ];
}

// ---------------------------------------------------------------------------
// READ HELPERS
// ---------------------------------------------------------------------------
function get_config_field(
    $key,
    $post_id,
    $override = [],
    $isNumeric = false
) {
    $value = ( array_key_exists( $key, $override ) ? $override[$key] : get_field( $key, $post_id ) );
    if ( $value === 1 || $value === '1' ) {
        return true;
    }
    if ( $value === 0 || $value === '0' ) {
        return false;
    }
    return $value;
}

// Mirrors get_field_objects($post_id) but returns only [$name => $value], without building
// or mutating the full field-definition array (choices, wrapper, conditional_logic, etc.)
// for every field. Calls the same acf_get_value()/acf_format_value() pipeline get_field()
// and get_field_objects() both use internally, so defaults and formatting are identical --
// only the wasted per-field array copy (triggered by get_field_objects()'s $field['value'] =
// ... mutation of a shared, cached field array) is skipped. This matters because it runs once
// per field per feature post, and features can number in the hundreds.
function mapster_get_field_values(  $post_id  ) {
    $post_id = acf_get_valid_post_id( $post_id );
    $meta = acf_get_meta( $post_id );
    if ( empty( $meta ) ) {
        return [];
    }
    $values = [];
    foreach ( $meta as $key => $raw ) {
        if ( !isset( $meta["_{$key}"] ) || !is_string( $meta["_{$key}"] ) && !is_numeric( $meta["_{$key}"] ) ) {
            continue;
        }
        $field = acf_get_field( $meta["_{$key}"] );
        if ( !$field || $field['name'] !== $key ) {
            continue;
        }
        $value = acf_get_value( $post_id, $field );
        $values[$key] = acf_format_value( $value, $post_id, $field );
    }
    return $values;
}

// Recursively flattens ACF field definitions into a compound-key => default_value map.
// e.g. a group 'label' containing subgroup 'text_properties' containing 'text-size'
// produces the key 'label_text_properties_text-size'.
function mapster_build_flat_defaults(  $fields, $prefix = ''  ) {
    $result = [];
    foreach ( $fields as $field ) {
        $key = ( $prefix !== '' ? $prefix . '_' . $field['name'] : $field['name'] );
        if ( !empty( $field['sub_fields'] ) ) {
            $result = array_merge( $result, mapster_build_flat_defaults( $field['sub_fields'], $key ) );
        } else {
            $result[$key] = $field['default_value'] ?? null;
        }
    }
    return $result;
}

// Like get_config_field but used for feature posts (locations/lines/polygons).
// When get_field() returns false and no value is saved, falls back to the ACF-registered
// default_value for that field — matching how the legacy template-based path handles
// posts imported without ACF meta being written (e.g. via import plugins).
function get_feature_field(  $key, $post_id  ) {
    static $defaults = null;
    if ( $defaults === null ) {
        $defaults = [];
        $groups = [
            'group_6163732e0426e',
            'group_616377d62836b',
            'group_616379566202f',
            'group_6163d357655f4',
            'group_626492b319912'
        ];
        foreach ( $groups as $group_id ) {
            $fields = acf_get_fields( $group_id );
            if ( $fields ) {
                $defaults = array_merge( $defaults, mapster_build_flat_defaults( $fields ) );
            }
        }
    }
    static $meta_cache = [];
    if ( !isset( $meta_cache[$post_id] ) ) {
        $meta_cache[$post_id] = get_post_meta( $post_id );
    }
    if ( array_key_exists( $key, $defaults ) && !array_key_exists( '_' . $key, $meta_cache[$post_id] ) ) {
        $value = $defaults[$key];
    } else {
        $value = get_field( $key, $post_id );
    }
    if ( $value === 1 || $value === '1' ) {
        return true;
    }
    if ( $value === 0 || $value === '0' ) {
        return false;
    }
    return $value;
}

function returnBool(  $value  ) {
    if ( is_bool( $value ) ) {
        return $value;
    }
    if ( $value === 1 || $value === '1' ) {
        return true;
    }
    if ( $value === 0 || $value === '0' ) {
        return false;
    }
    return (bool) $value;
}

function returnNumber(  $value, $default = 0  ) {
    // Reversing the stuff done in get_config_field for numbers
    if ( is_bool( $value ) ) {
        if ( $value ) {
            return 1;
        } else {
            return 0;
        }
    } else {
        return ( is_numeric( $value ) ? floatval( $value ) : $default );
    }
}

function returnStringOrJSON(  $string  ) {
    if ( str_contains( (string) $string, '{' ) ) {
        return json_decode( $string );
    }
    return $string;
}

function returnJsonStyle(  $url, $attribution  ) {
    return [
        'version' => 8,
        'glyphs'  => 'https://fonts.openmaptiles.org/{fontstack}/{range}.pbf',
        'sources' => [
            'raster-tiles' => [
                'type'        => 'raster',
                'tiles'       => [$url],
                'tileSize'    => 256,
                'attribution' => $attribution,
            ],
        ],
        'layers'  => [[
            'id'      => 'simple-tiles',
            'type'    => 'raster',
            'source'  => 'raster-tiles',
            'minzoom' => 0,
            'maxzoom' => 22,
        ]],
    ];
}

function returnStyleImage(  $post_id, $override  ) {
    $disabled = [
        'enabled' => false,
        'id'      => false,
        'width'   => 0,
        'height'  => 0,
        'url'     => false,
        'type'    => false,
    ];
    $provider = get_config_field( 'map_type_map_provider', $post_id, $override );
    $style_image = get_config_field( 'map_type_custom_image', $post_id, $override );
    if ( $provider !== 'custom-image' || !$style_image ) {
        return $disabled;
    }
    if ( is_array( $style_image ) ) {
        return [
            'enabled' => true,
            'id'      => intval( $style_image['ID'] ),
            'width'   => $style_image['width'],
            'height'  => $style_image['height'],
            'url'     => $style_image['url'],
            'type'    => $style_image['mime_type'],
        ];
    }
    if ( is_numeric( $style_image ) ) {
        $id = intval( $style_image );
        $src = wp_get_attachment_image_src( $id, 'full' );
        if ( !$src ) {
            return $disabled;
        }
        return [
            'enabled' => true,
            'id'      => $id,
            'width'   => $src[1],
            'height'  => $src[2],
            'url'     => $src[0],
            'type'    => get_post_mime_type( $id ),
        ];
    }
    return $disabled;
}

function returnStyleLighting(  $post_id, $override  ) {
    $custom_style = get_config_field( 'map_type_custom_mapbox_style', $post_id, $override );
    if ( $custom_style && !str_starts_with( $custom_style, 'mapbox://styles/mapbox/standard' ) ) {
        return false;
    }
    $custom_json = get_config_field( 'map_type_custom_style_json', $post_id, $override );
    if ( $custom_json && $custom_json !== '' ) {
        return false;
    }
    $provider = get_config_field( 'map_type_map_provider', $post_id, $override );
    $style_id = get_config_field( 'map_type_map_tile_style_no_access_token', $post_id, $override );
    if ( $provider === 'custom-image' ) {
        $style_id = 'empty';
    }
    if ( $provider === 'mapbox' && get_config_field( 'map_type_access_token', $post_id, $override ) !== '' ) {
        $style_id = get_config_field( 'map_type_map_tile_style_access_token', $post_id, $override );
    }
    $map = [
        'standard_day'             => 'day',
        'standard-night'           => 'night',
        'standard-dusk'            => 'dusk',
        'standard-dawn'            => 'dawn',
        'standard-satellite-day'   => 'day',
        'standard-satellite-night' => 'night',
    ];
    return $map[$style_id] ?? false;
}

function returnStyleJSON(  $post_id, $override  ) {
    $provider = get_config_field( 'map_type_map_provider', $post_id, $override );
    $style_id = get_config_field( 'map_type_map_tile_style_no_access_token', $post_id, $override );
    if ( $provider === 'custom-image' ) {
        $style_id = 'empty';
    }
    if ( $provider === 'mapbox' && get_config_field( 'map_type_access_token', $post_id, $override ) !== '' ) {
        $style_id = get_config_field( 'map_type_map_tile_style_access_token', $post_id, $override );
    }
    $custom_style = get_config_field( 'map_type_custom_mapbox_style', $post_id, $override );
    if ( $custom_style && $custom_style !== '' ) {
        return $custom_style;
    }
    $custom_json = get_config_field( 'map_type_custom_style_json', $post_id, $override );
    if ( $custom_json && is_object( returnStringOrJSON( $custom_json ) ) ) {
        return returnStringOrJSON( $custom_json );
    }
    $mapbox = [
        'standard-day'             => 'mapbox://styles/mapbox/standard',
        'standard-night'           => 'mapbox://styles/mapbox/standard',
        'standard-dusk'            => 'mapbox://styles/mapbox/standard',
        'standard-dawn'            => 'mapbox://styles/mapbox/standard',
        'standard-satellite-day'   => 'mapbox://styles/mapbox/standard-satellite',
        'standard-satellite-night' => 'mapbox://styles/mapbox/standard-satellite',
        'streets'                  => 'mapbox://styles/mapbox/streets-v12',
        'outdoors'                 => 'mapbox://styles/mapbox/outdoors-v12',
        'light'                    => 'mapbox://styles/mapbox/light-v11',
        'dark'                     => 'mapbox://styles/mapbox/dark-v11',
        'satellite'                => 'mapbox://styles/mapbox/satellite-v9',
        'satellite-streets'        => 'mapbox://styles/mapbox/satellite-streets-v12',
        'navigation-day'           => 'mapbox://styles/mapbox/navigation-day-v1',
        'navigation-night'         => 'mapbox://styles/mapbox/navigation-night-v1',
        'mapster-unicorn'          => 'mapbox://styles/mapstertech/cm10x5cvq02n401pwhdpncvrj',
        'mapster-minimo'           => 'mapbox://styles/mapstertech/cm12g9n3g00t501rbej0m74bv',
        'mapster-whaam'            => 'mapbox://styles/mapstertech/cm15kgcun033l01r793jh5b30',
        'mapster-standard-oil'     => 'mapbox://styles/mapstertech/cm12g8j4t03ao01pq9wm99p54',
        'mapster-blueprint'        => 'mapbox://styles/mapstertech/cm12g880g00t401rb9yc32icp',
        'mapster-frank'            => 'mapbox://styles/mapstertech/cm12g7tw303an01pqh7vr9sp4',
        'mapster-pencil'           => 'mapbox://styles/mapstertech/cm10xd4pn02g501rggzgifof7',
        'mapster-bubble'           => 'mapbox://styles/mapstertech/cm10xc01702g401rg1hmg4db8',
        'mapster-memory'           => 'mapbox://styles/mapstertech/cm10xaa8m020g01pq4ueh42z2',
        'mapster-neon-glow'        => 'mapbox://styles/mapstertech/cm10x86ma00l601r74d7a7rfw',
        'mapster-vintage'          => 'mapbox://styles/mapstertech/cm15klhbj033m01r70dwy5ap8',
        'mapster-swiss-ski'        => 'mapbox://styles/mapstertech/cm15klq59018k01q13nz0eo5k',
        'mapster-neon'             => 'mapbox://styles/mapstertech/cm15km0rx023b01rb6v7ieds5',
        'mapster-monochrome-blue'  => 'mapbox://styles/mapstertech/cm15km82m018l01q1aha7e04l',
        'mapster-camouflage'       => 'mapbox://styles/mapstertech/cm15l1x1g018n01q127v8g0pu',
        'mapster-bright'           => 'mapbox://styles/mapstertech/cm15l28wo023c01rb3ukd02j6',
    ];
    if ( isset( $mapbox[$style_id] ) ) {
        return $mapbox[$style_id];
    }
    $raster = [
        'open-street-map'   => ['https://a.tile.openstreetmap.org/{z}/{x}/{y}.png', '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'],
        'open-topo-map'     => ['https://a.tile.opentopomap.org/{z}/{x}/{y}.png', 'Map data: &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, <a href="https://viewfinderpanoramas.org">SRTM</a> | Map style: &copy; <a href="https://opentopomap.org">OpenTopoMap</a> (<a href="https://creativecommons.org/licenses/by-sa/3.0/">CC-BY-SA</a>)'],
        'hot-osm'           => ['https://a.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png', '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, Tiles style by <a href="https://www.hotosm.org/" target="_blank">Humanitarian OpenStreetMap Team</a> hosted by <a href="https://openstreetmap.fr/" target="_blank">OpenStreetMap France</a>'],
        'toner-stamen'      => ['https://tiles.stadiamaps.com/tiles/stamen_toner/{z}/{x}/{y}.png', 'Map tiles by <a href="https://stamen.com">Stamen Design</a>, <a href="https://creativecommons.org/licenses/by/3.0">CC BY 3.0</a> &mdash; Map data &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'],
        'watercolor-stamen' => ['https://tiles.stadiamaps.com/tiles/stamen_watercolor/{z}/{x}/{y}.jpg', 'Map tiles by <a href="https://stamen.com">Stamen Design</a>, <a href="https://creativecommons.org/licenses/by/3.0">CC BY 3.0</a> &mdash; Map data &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'],
        'terrain-stamen'    => ['https://tiles.stadiamaps.com/tiles/stamen_terrain/{z}/{x}/{y}.png', 'Map tiles by <a href="https://stamen.com">Stamen Design</a>, <a href="https://creativecommons.org/licenses/by/3.0">CC BY 3.0</a> &mdash; Map data &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'],
        'esri-satellite'    => ['https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community'],
        'esri-topo'         => ['https://server.arcgisonline.com/ArcGIS/rest/services/World_Topo_Map/MapServer/tile/{z}/{y}/{x}', 'Tiles &copy; Esri &mdash; Esri, DeLorme, NAVTEQ, TomTom, Intermap, iPC, USGS, FAO, NPS, NRCAN, GeoBase, Kadaster NL, Ordnance Survey, Esri Japan, METI, Esri China (Hong Kong), and the GIS User Community'],
        'blank-map'         => ['https://a.tile.openstreetmap.org/{z}/{x}/{y}.png', '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'],
    ];
    if ( isset( $raster[$style_id] ) ) {
        [$url, $attribution] = $raster[$style_id];
        return returnJsonStyle( $url, $attribution );
    }
    $urls = [
        'esri-hybrid'          => 'https://raw.githubusercontent.com/go2garret/maps/main/src/assets/json/arcgis_hybrid.json',
        'dark-matter'          => 'https://basemaps.cartocdn.com/gl/dark-matter-gl-style/style.json',
        'dark-matter-nolabels' => 'https://basemaps.cartocdn.com/gl/dark-matter-nolabels-gl-style/style.json',
        'positron'             => 'https://basemaps.cartocdn.com/gl/positron-gl-style/style.json',
        'positron-nolabels'    => 'https://basemaps.cartocdn.com/gl/positron-nolabels-gl-style/style.json',
        'voyager'              => 'https://basemaps.cartocdn.com/gl/voyager-gl-style/style.json',
        'voyager-nolabels'     => 'https://basemaps.cartocdn.com/gl/voyager-nolabels-gl-style/style.json',
        'icgc-main'            => 'https://geoserveis.icgc.cat/contextmaps/icgc.json',
        'icgc-dark'            => 'https://geoserveis.icgc.cat/contextmaps/icgc_mapa_base_fosc.json',
        'icgc-orthophoto'      => 'https://geoserveis.icgc.cat/contextmaps/icgc_orto_estandard.json',
    ];
    if ( isset( $urls[$style_id] ) ) {
        return $urls[$style_id];
    }
    if ( $style_id === 'custom-image' || $style_id === 'empty' ) {
        return [
            'version' => 8,
            'glyphs'  => 'https://fonts.openmaptiles.org/{fontstack}/{range}.pbf',
            'sources' => new stdClass(),
            'layers'  => [[
                'id'    => 'background',
                'type'  => 'background',
                'paint' => [
                    'background-color' => 'rgba(255,255,255,1)',
                ],
            ]],
        ];
    }
    return null;
}

function returnClusterTypes(  $post_id, $override  ) {
    $types = [];
    foreach ( mapster_cluster_type_map() as $type_key => $acf_key ) {
        if ( get_config_field( $acf_key, $post_id, $override ) ) {
            $types[] = $type_key;
        }
    }
    return $types;
}

function returnArrayFromClusterStyling(  $post_id, $override  ) {
    $text = get_config_field( 'cluster_options_category_cluster_styling', $post_id, $override );
    return ( $text && $text !== '' ? preg_split( '/\\R/', $text ) : [] );
}

function returnArrayFromColorRange(  $post_id, $override  ) {
    $text = get_config_field( 'heatmap_heatmap_color_range', $post_id, $override );
    return ( $text && $text !== '' ? preg_split( '/\\R/', $text ) : [] );
}

function returnArrayFromDownloadProperties(  $post_id, $override  ) {
    $text = get_config_field( 'download_control_included_fields', $post_id, $override );
    return ( $text && $text !== '' ? preg_split( '/\\R/', $text ) : [] );
}

function returnArrayFromStyleControl(  $post_id, $override  ) {
    $styles_text = get_config_field( 'style_control_toggleable_styles', $post_id, $override );
    $titles_text = get_config_field( 'style_control_toggleable_styles_titles', $post_id, $override );
    if ( !$styles_text ) {
        return [];
    }
    $styles = preg_split( '/\\R/', $styles_text );
    $names = ( $titles_text ? preg_split( '/\\R/', $titles_text ) : [] );
    $out = [];
    foreach ( $styles as $i => $style ) {
        if ( $style !== '' && ($names[$i] ?? '') !== '' ) {
            $out[] = [
                'style' => returnStringOrJSON( $style ),
                'name'  => $names[$i],
            ];
        }
    }
    return $out;
}

function returnArrayFromLayerControl(  $post_id, $override  ) {
    $layers_text = get_config_field( 'layer_control_toggleable_layers', $post_id, $override );
    $titles_text = get_config_field( 'layer_control_toggleable_layer_titles', $post_id, $override );
    if ( !$layers_text || !$titles_text ) {
        return [];
    }
    $rows = preg_split( '/\\R/', $layers_text );
    $names = preg_split( '/\\R/', $titles_text );
    $out = [];
    foreach ( $rows as $i => $row ) {
        if ( $row !== '' && ($names[$i] ?? '') !== '' ) {
            $out[] = [
                'layers' => explode( ',', $row ),
                'name'   => $names[$i],
            ];
        }
    }
    return $out;
}

function returnArrayFromControlMenu(  $post_id, $override  ) {
    $text = get_config_field( 'control_menu_included_controls', $post_id, $override );
    if ( !$text || $text === '' ) {
        return [];
    }
    return json_decode( $text ) ?? [];
}

function returnArrayFromControlOrder(  $post_id, $override  ) {
    $text = get_config_field( 'control_order', $post_id, $override );
    if ( !$text || $text === '' ) {
        return [];
    }
    $parsed = json_decode( $text );
    return ( $parsed ? array_map( fn( $item ) => $item->slug, $parsed ) : [] );
}

function returnArrayFromCategoryOrder(  $string  ) {
    if ( $string && str_contains( $string, '[' ) ) {
        return json_decode( $string );
    }
    return [];
}

function getAllowedOrigins(  $post_id, $override  ) {
    $protect = get_config_field( 'embed_protect_embed', $post_id, $override );
    $origins = get_config_field( 'embed_allowed_origins', $post_id, $override );
    return ( $protect ? explode( PHP_EOL, $origins ) : ['*'] );
}

// ---------------------------------------------------------------------------
// WRITE PATH
// ---------------------------------------------------------------------------
function mapster_write_config_to_acf(  $post_id, $config  ) {
    foreach ( mapster_get_field_map() as $entry ) {
        [$config_path, $acf_key, $flag] = array_pad( $entry, 3, null );
        if ( $flag === 'readonly' || $flag === 'nonempty_bool' ) {
            continue;
        }
        if ( $flag === 'complex' ) {
            mapster_write_complex_field( $post_id, $config_path, $config );
            continue;
        }
        $value = mapster_get_nested_config( $config, preg_replace( '/^config\\./', '', $config_path ) );
        if ( $value === mapster_missing() ) {
            continue;
        }
        if ( $flag === 'number' ) {
            update_field( $acf_key, floatval( $value ), $post_id );
        } elseif ( $flag === 'invert' ) {
            update_field( $acf_key, (int) (!$value), $post_id );
        } elseif ( $flag === 'concat_units' ) {
            mapster_write_concat_units( $post_id, $acf_key, $value );
        } elseif ( $flag === 'raw' ) {
            update_field( $acf_key, $value, $post_id );
        } else {
            if ( is_bool( $value ) ) {
                update_field( $acf_key, (int) $value, $post_id );
            } elseif ( is_string( $value ) ) {
                update_field( $acf_key, sanitize_text_field( $value ), $post_id );
            } else {
                update_field( $acf_key, $value, $post_id );
            }
        }
    }
}

function mapster_write_complex_field(  $post_id, $config_path, $config  ) {
    switch ( $config_path ) {
        case 'config.style.style':
            $style = mapster_get_nested_config( $config, 'style.style' );
            if ( $style === mapster_missing() || !$style ) {
                break;
            }
            if ( is_array( $style ) || is_object( $style ) || is_string( $style ) && ltrim( $style )[0] === '{' ) {
                update_field( 'map_type_custom_style_json', ( is_string( $style ) ? $style : json_encode( $style ) ), $post_id );
                update_field( 'map_type_custom_mapbox_style', '', $post_id );
            } else {
                update_field( 'map_type_custom_mapbox_style', esc_url_raw( $style ), $post_id );
                update_field( 'map_type_custom_style_json', '', $post_id );
            }
            break;
        case 'config.style.snazzy':
        case 'config.style.lighting':
            break;
        case 'config.style.image':
            $img = mapster_get_nested_config( $config, 'style.image' );
            if ( $img === mapster_missing() ) {
                break;
            }
            update_field( 'map_type_custom_image', ( !$img || !is_array( $img ) || empty( $img['id'] ) ? false : intval( $img['id'] ) ), $post_id );
            break;
        case 'config.interactivity.clicking':
            $v = mapster_get_nested_config( $config, 'interactivity.clicking' );
            if ( $v === mapster_missing() ) {
                break;
            }
            update_field( 'clicking_disabled', (int) (!$v), $post_id );
            break;
        case 'config.clusters.enabled':
            break;
        case 'config.clusters.types':
            $types = mapster_get_nested_config( $config, 'clusters.types' );
            if ( $types === mapster_missing() ) {
                break;
            }
            foreach ( mapster_cluster_type_map() as $type_key => $acf_key ) {
                update_field( $acf_key, (int) in_array( $type_key, $types ?? [] ), $post_id );
            }
            break;
        case 'config.clusters.categories':
            $terms = mapster_get_nested_config( $config, 'clusters.categories' );
            if ( $terms === mapster_missing() ) {
                break;
            }
            $ids = array_values( array_filter( array_map( fn( $t ) => ( is_array( $t ) ? intval( $t['value'] ?? 0 ) : intval( $t ) ), $terms ?? [] ) ) );
            update_field( 'cluster_options_categories_to_cluster', ( $ids ?: false ), $post_id );
            break;
        case 'config.clusters.image':
            $img = mapster_get_nested_config( $config, 'clusters.image' );
            if ( $img === mapster_missing() ) {
                break;
            }
            update_field( 'cluster_options_image_on_cluster', ( !$img || empty( $img['id'] ) ? false : intval( $img['id'] ) ), $post_id );
            break;
        case 'config.clusters.style.category':
            $v = mapster_get_nested_config( $config, 'clusters.style.category' );
            if ( $v === mapster_missing() ) {
                break;
            }
            update_field( 'cluster_options_category_cluster_styling', implode( "\n", $v ?? [] ), $post_id );
            break;
        case 'config.controls.control_order':
            $v = mapster_get_nested_config( $config, 'controls.control_order' );
            if ( $v === mapster_missing() ) {
                break;
            }
            update_field( 'control_order', json_encode( array_map( fn( $s ) => [
                'slug' => $s,
            ], $v ?? [] ) ), $post_id );
            break;
        case 'config.controls.layers.options.toggleable_layers':
            $groups = mapster_get_nested_config( $config, 'controls.layers.options.toggleable_layers' );
            if ( $groups === mapster_missing() ) {
                break;
            }
            $groups = $groups ?? [];
            update_field( 'layer_control_toggleable_layers', implode( "\n", array_map( fn( $g ) => implode( ',', $g['layers'] ), $groups ) ), $post_id );
            update_field( 'layer_control_toggleable_layer_titles', implode( "\n", array_map( fn( $g ) => $g['name'], $groups ) ), $post_id );
            break;
        case 'config.controls.styles.options.toggleable_styles':
            $items = mapster_get_nested_config( $config, 'controls.styles.options.toggleable_styles' );
            if ( $items === mapster_missing() ) {
                break;
            }
            $items = $items ?? [];
            update_field( 'style_control_toggleable_styles', implode( "\n", array_map( fn( $s ) => ( is_array( $s['style'] ) ? json_encode( $s['style'] ) : $s['style'] ), $items ) ), $post_id );
            update_field( 'style_control_toggleable_styles_titles', implode( "\n", array_map( fn( $s ) => $s['name'], $items ) ), $post_id );
            break;
        case 'config.controls.menu.options.included_controls':
            $v = mapster_get_nested_config( $config, 'controls.menu.options.included_controls' );
            if ( $v === mapster_missing() ) {
                break;
            }
            update_field( 'control_menu_included_controls', json_encode( $v ?? [] ), $post_id );
            break;
        case 'config.controls.download.options.notify_on_download':
            $users = mapster_get_nested_config( $config, 'controls.download.options.notify_on_download' );
            if ( $users === mapster_missing() ) {
                break;
            }
            $ids = ( is_array( $users ) ? array_values( array_filter( array_map( fn( $u ) => ( is_array( $u ) ? intval( $u['value'] ?? 0 ) : intval( $u ) ), $users ) ) ) : [] );
            update_field( 'download_control_notify_on_download', ( $ids ?: false ), $post_id );
            break;
        case 'config.controls.download.options.included_properties':
            $v = mapster_get_nested_config( $config, 'controls.download.options.included_properties' );
            if ( $v === mapster_missing() ) {
                break;
            }
            update_field( 'download_control_included_fields', implode( "\n", $v ?? [] ), $post_id );
            break;
        case 'config.controls.category_filter.options.excluded_categories':
            $terms = mapster_get_nested_config( $config, 'controls.category_filter.options.excluded_categories' );
            if ( $terms === mapster_missing() ) {
                break;
            }
            $ids = array_values( array_filter( array_map( fn( $t ) => ( is_array( $t ) ? intval( $t['value'] ?? 0 ) : intval( $t ) ), $terms ?? [] ) ) );
            update_field( 'filter_category_filter_excluded_categories', ( $ids ?: false ), $post_id );
            break;
        case 'config.controls.category_filter.options.additional_filters':
            $filters = mapster_get_nested_config( $config, 'controls.category_filter.options.additional_filters' );
            if ( $filters === mapster_missing() ) {
                break;
            }
            $lines = array_map( fn( $f ) => trim( $f['property'] ?? '' ) . ' : ' . trim( $f['label'] ?? '' ), $filters ?? [] );
            update_field( 'filter_category_filter_additional_filters', ( implode( "\n", array_filter( $lines ) ) ?: false ), $post_id );
            break;
        case 'config.controls.category_filter.options.category_order':
            $v = mapster_get_nested_config( $config, 'controls.category_filter.options.category_order' );
            if ( $v === mapster_missing() ) {
                break;
            }
            update_field( 'filter_category_filter_category_order', json_encode( $v ?? [] ), $post_id );
            break;
        case 'config.advanced.embed.allowed_origins':
            $origins = mapster_get_nested_config( $config, 'advanced.embed.allowed_origins' );
            if ( $origins === mapster_missing() ) {
                break;
            }
            $origins = $origins ?? ['*'];
            $origins = array_map( fn( $o ) => ( $o === '*' ? '*' : esc_url_raw( $o ) ), $origins );
            $is_open = count( $origins ) === 1 && $origins[0] === '*';
            update_field( 'embed_protect_embed', (int) (!$is_open), $post_id );
            if ( !$is_open ) {
                update_field( 'embed_allowed_origins', $origins, $post_id );
            }
            break;
        case 'config.loading.graphic':
            $slug = mapster_get_nested_config( $config, 'loading.graphic' );
            if ( $slug === mapster_missing() ) {
                break;
            }
            update_field( 'loading_loading_graphic', mapster_loader_slug_to_graphic( (string) ($slug ?? ''), $post_id ), $post_id );
            break;
        case 'config.loading.custom':
            $img = mapster_get_nested_config( $config, 'loading.custom' );
            if ( $img === mapster_missing() ) {
                break;
            }
            update_field( 'loading_custom_loader', ( !$img || empty( $img['id'] ) ? false : intval( $img['id'] ) ), $post_id );
            break;
        case 'config.specialty.heatmap.color_range':
            $v = mapster_get_nested_config( $config, 'specialty.heatmap.color_range' );
            if ( $v === mapster_missing() ) {
                break;
            }
            update_field( 'heatmap_heatmap_color_range', implode( "\n", $v ?? [] ), $post_id );
            break;
        case 'config.specialty.submission.options.method':
        case 'config.specialty.submission.options.categories':
        case 'config.specialty.submission.administration.template_posts':
            break;
    }
}

function mapster_get_nested_config(  $data, $path  ) {
    foreach ( explode( '.', $path ) as $key ) {
        if ( !is_array( $data ) || !array_key_exists( $key, $data ) ) {
            return mapster_missing();
        }
        $data = $data[$key];
    }
    return $data;
}

function mapster_write_concat_units(  $post_id, $keys, $value  ) {
    if ( preg_match( '/^(\\d+\\.?\\d*)(.+)$/', (string) $value, $m ) ) {
        update_field( $keys[0], $m[1], $post_id );
        update_field( $keys[1], $m[2], $post_id );
    }
}

function createSdkResponse(  $post_id, $feature_ids_to_load, $override = []  ) {
    $i8ln = new Mapster_Wordpress_Maps_i18n();
    $settings_page_id = get_option( 'mapster_settings_page' );
    return array(
        "config"   => array(
            "id"            => $post_id,
            "element"       => "mapster-wp-maps-" . $post_id,
            "mapbox_token"  => ( get_config_field( "map_type_access_token", $post_id, $override ) ?: get_field( "default_access_token", $settings_page_id ) ),
            "google_key"    => get_field( "google_maps_api_key", $settings_page_id ),
            "style"         => array(
                "style"                  => returnStyleJSON( $post_id, $override ),
                "snazzy"                 => ( get_config_field( "map_type_snazzy_map_style", $post_id, $override ) ? returnStringOrJSON( get_config_field( "map_type_snazzy_map_style", $post_id ) ) : false ),
                "image"                  => returnStyleImage( $post_id, $override ),
                "terrain"                => get_config_field( "map_type_terrain", $post_id, $override ),
                "buildings_3d"           => get_config_field( "map_type_buildings_3d", $post_id, $override ),
                "globe"                  => array(
                    "enabled"    => get_config_field( "map_type_globe", $post_id, $override ),
                    "background" => get_config_field( "map_type_globe_background", $post_id, $override ),
                ),
                "general_3d"             => array(
                    "enabled" => get_config_field( "load_3d_model_libraries", $post_id, $override ),
                ),
                "lighting"               => returnStyleLighting( $post_id, $override ),
                "projection"             => get_config_field( "map_type_projection", $post_id, $override ),
                "language"               => get_config_field( "map_type_map_language", $post_id, $override ),
                "duplicate_horizontally" => get_config_field( "layout_duplicate_horizontally", $post_id, $override ),
            ),
            "view"          => array(
                "on_load"         => get_config_field( 'view_initial_load', $post_id, $override ),
                "auto_bounds"     => false,
                "ip"              => ( $_SERVER && $_SERVER['REMOTE_ADDR'] ? $_SERVER['REMOTE_ADDR'] : false ),
                "manual"          => array(
                    "bounds"    => false,
                    "latitude"  => returnNumber( get_config_field( 'view_manual_latitude', $post_id, $override ) ),
                    "longitude" => returnNumber( get_config_field( 'view_manual_longitude', $post_id, $override ) ),
                    "zoom"      => returnNumber( get_config_field( 'view_manual_zoom', $post_id, $override ) ),
                    "pitch"     => returnNumber( get_config_field( 'view_manual_pitch', $post_id, $override ) ),
                    "bearing"   => returnNumber( get_config_field( 'view_manual_rotation', $post_id, $override ) ),
                ),
                "padding"         => returnNumber( get_config_field( 'view_padding_around_bounds', $post_id, $override ) ),
                "globe_animation" => array(
                    "enabled"   => get_config_field( 'view_globe_animation', $post_id, $override ),
                    "speed"     => returnNumber( get_config_field( 'view_globe_animation_speed', $post_id, $override ) ),
                    "direction" => get_config_field( 'view_globe_animation_direction', $post_id, $override ),
                ),
            ),
            "layout"        => array(
                "width"            => get_config_field( 'layout_width', $post_id, $override ),
                "width_units"      => get_config_field( 'layout_width_units', $post_id, $override ),
                "height"           => get_config_field( 'layout_height', $post_id, $override ),
                "height_units"     => get_config_field( 'layout_height_units', $post_id, $override ),
                "mobile"           => array(
                    "enabled"     => get_config_field( 'layout_add_mobile_breakpoints', $post_id, $override ),
                    "breakpoint1" => array(
                        "breakpoint"   => returnNumber( get_config_field( 'layout_breakpoints_breakpoint_1', $post_id, $override ) ),
                        "width"        => get_config_field( 'layout_breakpoints_breakpoint_1_map_width', $post_id, $override ),
                        "width_units"  => get_config_field( 'layout_breakpoints_breakpoint_1_map_width_units', $post_id, $override ),
                        "height"       => get_config_field( 'layout_breakpoints_breakpoint_1_map_height', $post_id, $override ),
                        "height_units" => get_config_field( 'layout_breakpoints_breakpoint_1_map_height_units', $post_id, $override ),
                    ),
                    "breakpoint2" => array(
                        "breakpoint"   => returnNumber( get_config_field( 'layout_breakpoints_breakpoint_2', $post_id, $override ) ),
                        "width"        => get_config_field( 'layout_breakpoints_breakpoint_2_map_width', $post_id, $override ),
                        "width_units"  => get_config_field( 'layout_breakpoints_breakpoint_2_map_width_units', $post_id, $override ),
                        "height"       => get_config_field( 'layout_breakpoints_breakpoint_2_map_height', $post_id, $override ),
                        "height_units" => get_config_field( 'layout_breakpoints_breakpoint_2_map_height_units', $post_id, $override ),
                    ),
                ),
                "full_page"        => get_config_field( 'layout_full_page', $post_id, $override ),
                "ignore_container" => get_config_field( 'layout_ignore_container', $post_id, $override ),
                "map_only"         => get_config_field( 'layout_map_only', $post_id, $override ),
            ),
            "interactivity" => array(
                "disable_all"       => ( get_config_field( 'interactivity', $post_id, $override ) == true ? false : true ),
                "scrollzoom"        => get_config_field( 'zoom_on_scroll', $post_id, $override ),
                "clicking"          => ( get_config_field( 'clicking_disabled', $post_id, $override ) == true && get_config_field( 'interactivity', $post_id, $override ) == false ? false : true ),
                "coop_gestures"     => get_config_field( 'cooperative_gestures', $post_id, $override ),
                "rotation_pitch"    => get_config_field( 'allow_rotation_and_pitch', $post_id, $override ),
                "restrict_movement" => array(
                    "enabled"        => get_config_field( 'restricted_movement_restrict_movement', $post_id, $override ),
                    "allowed_bounds" => array(
                        "sw_lat" => returnNumber( get_config_field( 'restricted_movement_allowed_bounds_southwest_latitude', $post_id, $override ) ),
                        "sw_lng" => returnNumber( get_config_field( 'restricted_movement_allowed_bounds_southwest_longitude', $post_id, $override ) ),
                        "ne_lat" => returnNumber( get_config_field( 'restricted_movement_allowed_bounds_northeast_latitude', $post_id, $override ) ),
                        "ne_lng" => returnNumber( get_config_field( 'restricted_movement_allowed_bounds_northeast_longitude', $post_id, $override ) ),
                    ),
                    "allowed_zoom"   => array(
                        "min" => returnNumber( get_config_field( 'restricted_movement_allowed_zoom_min_zoom', $post_id, $override ) ),
                        "max" => returnNumber( get_config_field( 'restricted_movement_allowed_zoom_max_zoom', $post_id, $override ), 22 ),
                    ),
                ),
            ),
            "clusters"      => array(
                "enabled"     => ( get_config_field( 'circle_clustering', $post_id, $override ) == true || get_config_field( 'label_icon_clustering', $post_id, $override ) == true || get_config_field( 'marker_clustering', $post_id, $override ) == true || get_config_field( 'polygon_clustering', $post_id, $override ) == true || get_config_field( 'line_clustering', $post_id, $override ) == true ? true : false ),
                "types"       => returnClusterTypes( $post_id, $override ),
                "by_category" => get_config_field( 'cluster_options_cluster_by_category', $post_id, $override ),
                "show_name"   => get_config_field( 'cluster_options_show_category_name', $post_id, $override ),
                "image"       => mapster_format_loader_custom( get_post_meta( $post_id, 'cluster_options_image_on_cluster', true ) ),
                "categories"  => mapster_resolve_category_terms( get_config_field( 'cluster_options_categories_to_cluster', $post_id, $override ) ),
                "style"       => array(
                    "category" => returnArrayFromClusterStyling( $post_id, $override ),
                    "small"    => array(
                        "color"        => get_config_field( 'cluster_options_small_cluster_color', $post_id, $override ),
                        "font_color"   => get_config_field( 'cluster_options_small_cluster_font_color', $post_id, $override ),
                        "radius"       => returnNumber( get_config_field( 'cluster_options_small_cluster_radius', $post_id, $override ) ),
                        "count"        => returnNumber( get_config_field( 'cluster_options_small_cluster_count', $post_id, $override ) ),
                        "border_color" => get_config_field( 'cluster_options_small_cluster_border_color', $post_id, $override ),
                        "border_width" => returnNumber( get_config_field( 'cluster_options_small_cluster_width', $post_id, $override ) ),
                    ),
                    "medium"   => array(
                        "color"        => get_config_field( 'cluster_options_medium_cluster_color', $post_id, $override ),
                        "font_color"   => get_config_field( 'cluster_options_medium_cluster_font_color', $post_id, $override ),
                        "radius"       => returnNumber( get_config_field( 'cluster_options_medium_cluster_radius', $post_id, $override ) ),
                        "count"        => returnNumber( get_config_field( 'cluster_options_medium_cluster_count', $post_id, $override ) ),
                        "border_color" => get_config_field( 'cluster_options_medium_cluster_border_color', $post_id, $override ),
                        "border_width" => returnNumber( get_config_field( 'cluster_options_medium_cluster_width', $post_id, $override ) ),
                    ),
                    "large"    => array(
                        "color"        => get_config_field( 'cluster_options_large_cluster_color', $post_id, $override ),
                        "font_color"   => get_config_field( 'cluster_options_large_cluster_font_color', $post_id, $override ),
                        "radius"       => returnNumber( get_config_field( 'cluster_options_large_cluster_radius', $post_id, $override ) ),
                        "border_color" => get_config_field( 'cluster_options_large_cluster_border_color', $post_id, $override ),
                        "border_width" => returnNumber( get_config_field( 'cluster_options_large_cluster_width', $post_id, $override ) ),
                    ),
                ),
            ),
            "controls"      => array(
                "control_order"        => returnArrayFromControlOrder( $post_id, $override ),
                "zoom"                 => array(
                    "enabled"  => get_config_field( "zoom_control_enable", $post_id, $override ),
                    "position" => get_config_field( "zoom_control_position", $post_id, $override ),
                ),
                "scale"                => array(
                    "enabled"  => get_config_field( "scale_control_enable", $post_id, $override ),
                    "position" => get_config_field( "scale_control_position", $post_id, $override ),
                ),
                "fullscreen"           => array(
                    "enabled"  => get_config_field( "fullscreen_control_enable", $post_id, $override ),
                    "position" => get_config_field( "fullscreen_control_position", $post_id, $override ),
                ),
                "attribution"          => array(
                    "enabled"  => true,
                    "position" => ( get_config_field( "attribution_control_position", $post_id, $override ) ? get_config_field( "attribution_control_position", $post_id, $override ) : "bottom-right" ),
                ),
                "logo"                 => array(
                    "enabled"  => true,
                    "position" => get_config_field( "logo_control_position", $post_id, $override ),
                ),
                "map_type"             => array(
                    "enabled"  => get_config_field( "map_type_control_enable", $post_id, $override ),
                    "position" => get_config_field( "map_type_control_position", $post_id, $override ),
                ),
                "street_view"          => array(
                    "enabled"  => get_config_field( "street_view_control_enable", $post_id, $override ),
                    "position" => get_config_field( "street_view_control_position", $post_id, $override ),
                ),
                "3d"                   => array(
                    "enabled"  => get_config_field( "3d_control_enable", $post_id, $override ),
                    "position" => get_config_field( "3d_control_position", $post_id, $override ),
                ),
                "print"                => array(
                    "enabled"  => get_config_field( "print_control_enable", $post_id, $override ),
                    "position" => get_config_field( "print_control_position", $post_id, $override ),
                ),
                "geocoder"             => array(
                    "enabled"  => get_config_field( "geocoder_control_enable", $post_id, $override ),
                    "position" => get_config_field( "geocoder_control_position", $post_id, $override ),
                    "options"  => array(
                        "placeholder"     => get_config_field( "geocoder_control_placeholder", $post_id, $override ),
                        "marker_color"    => get_config_field( "geocoder_control_marker_color", $post_id, $override ),
                        "accept_latlng"   => get_config_field( "geocoder_control_accept_latlngs", $post_id, $override ),
                        "limit_results"   => get_config_field( "geocoder_control_limit_results", $post_id, $override ),
                        "limit_by_bounds" => array(
                            "enabled" => get_config_field( "geocoder_control_limit_by_bounds", $post_id, $override ),
                            "bounds"  => array(
                                "sw_lat" => returnNumber( get_config_field( 'geocoder_control_bounds_limit_southwest_latitude', $post_id, $override ) ),
                                "sw_lng" => returnNumber( get_config_field( 'geocoder_control_bounds_limit_southwest_longitude', $post_id, $override ) ),
                                "ne_lat" => returnNumber( get_config_field( 'geocoder_control_bounds_limit_northeast_latitude', $post_id, $override ) ),
                                "ne_lng" => returnNumber( get_config_field( 'geocoder_control_bounds_limit_northeast_longitude', $post_id, $override ) ),
                            ),
                        ),
                    ),
                ),
                "geolocation"          => array(
                    "enabled"  => get_config_field( "geolocation_control_enable", $post_id, $override ),
                    "position" => get_config_field( "geolocation_control_position", $post_id, $override ),
                    "options"  => array(
                        "set_on_load"     => get_config_field( "geolocation_control_set_on_load", $post_id, $override ),
                        "accuracy_circle" => get_config_field( "geolocation_control_show_accuracy_circle", $post_id, $override ),
                        "user_heading"    => get_config_field( "geolocation_control_show_user_heading", $post_id, $override ),
                        "user_track"      => get_config_field( "geolocation_control_track_user_location", $post_id, $override ),
                        "high_accuracy"   => get_config_field( "geolocation_control_enable_high_accuracy", $post_id, $override ),
                    ),
                ),
                "directions"           => array(
                    "enabled"  => get_config_field( "directions_control_enable", $post_id, $override ),
                    "position" => get_config_field( "directions_control_position", $post_id, $override ),
                    "options"  => array(
                        "units"        => get_config_field( "directions_control_units", $post_id, $override ),
                        "default_type" => get_config_field( "directions_control_default_type", $post_id, $override ),
                        "placeholder"  => get_config_field( "directions_control_placeholder_text", $post_id, $override ),
                    ),
                ),
                "layers"               => array(
                    "enabled"  => get_config_field( "layer_control_enable", $post_id, $override ),
                    "position" => get_config_field( "layer_control_position", $post_id, $override ),
                    "options"  => array(
                        "toggleable_layers" => returnArrayFromLayerControl( $post_id, $override ),
                        "checkbox_type"     => get_config_field( "layer_control_checkbox_type", $post_id, $override ),
                    ),
                ),
                "styles"               => array(
                    "enabled"  => get_config_field( "style_control_enable", $post_id, $override ),
                    "position" => get_config_field( "style_control_position", $post_id, $override ),
                    "options"  => array(
                        "toggleable_styles"   => returnArrayFromStyleControl( $post_id, $override ),
                        "default_style_title" => ( get_config_field( "style_control_initial_style_title", $post_id, $override ) !== "" ? get_config_field( "style_control_initial_style_title", $post_id, $override ) : "Default Style" ),
                    ),
                ),
                "menu"                 => array(
                    "enabled"  => get_config_field( "control_menu_enable", $post_id, $override ),
                    "position" => get_config_field( "control_menu_position", $post_id, $override ),
                    "options"  => array(
                        "included_controls" => returnArrayFromControlMenu( $post_id, $override ),
                    ),
                ),
                "download"             => array(
                    "enabled"  => get_config_field( "download_control_enable", $post_id, $override ),
                    "position" => get_config_field( "download_control_position", $post_id, $override ),
                    "options"  => array(
                        "filter_interaction"  => get_config_field( "download_control_interact_with_filters", $post_id, $override ),
                        "notify_on_download"  => mapster_format_notify_users( get_config_field( "download_control_notify_on_download", $post_id, $override ) ),
                        "included_properties" => returnArrayFromDownloadProperties( $post_id, $override ),
                        "notification_url"    => get_rest_url() . "mapster-wp-maps/send-email",
                    ),
                ),
                "custom_html"          => array(
                    "enabled"  => get_config_field( "custom_html_control_enable", $post_id, $override ),
                    "position" => get_config_field( "custom_html_control_position", $post_id, $override ),
                    "options"  => array(
                        "html" => get_config_field( "custom_html_control_custom_html", $post_id, $override ),
                    ),
                ),
                "elevation"            => array(
                    "enabled"  => get_config_field( "elevation_line_chart_enable_elevation_chart", $post_id, $override ),
                    "position" => get_config_field( "elevation_line_chart_control_position", $post_id, $override ),
                    "options"  => array(
                        "single_line"   => get_config_field( "elevation_line_chart_single_line", $post_id, $override ),
                        "open_on_load"  => get_config_field( "elevation_line_chart_open_on_load", $post_id, $override ),
                        "profile_color" => get_config_field( "elevation_line_chart_profile_color", $post_id, $override ),
                        "units"         => get_config_field( "elevation_line_chart_units", $post_id, $override ),
                        "line_length"   => get_config_field( "elevation_line_chart_show_line_length", $post_id, $override ),
                    ),
                ),
                "category_filter"      => array(
                    "enabled"  => get_config_field( "filter_category_filter_enable", $post_id, $override ),
                    "position" => get_config_field( "filter_category_filter_position", $post_id, $override ),
                    "options"  => array(
                        "category_display"        => get_config_field( "filter_category_filter_category_display", $post_id, $override ),
                        "checkbox_display"        => get_config_field( "filter_category_filter_checkbox_display", $post_id, $override ),
                        "show_number_of_features" => get_config_field( "filter_category_filter_show_number_of_features", $post_id, $override ),
                        "functionality"           => get_config_field( "filter_category_filter_functionality", $post_id, $override ),
                        "independent_children"    => get_config_field( "filter_category_filter_independent_children", $post_id, $override ),
                        "parent_cat_display"      => get_config_field( "filter_category_filter_parent_cat_display", $post_id, $override ),
                        "excluded_categories"     => mapster_resolve_category_terms( get_config_field( "filter_category_filter_excluded_categories", $post_id, $override ) ),
                        "initial_visibility"      => get_config_field( "filter_category_filter_initial_visibility", $post_id, $override ),
                        "pre_selected_categories" => get_config_field( "filter_category_filter_pre_selected_categories", $post_id, $override ),
                        "category_order"          => returnArrayFromCategoryOrder( get_config_field( "filter_category_filter_category_order", $post_id, $override ) ),
                        "reset_button"            => get_config_field( "filter_category_filter_reset_button", $post_id, $override ),
                        "additional_filters"      => mapster_parse_additional_filters( get_config_field( "filter_category_filter_additional_filters", $post_id, $override ) ),
                        "accordion_layout"        => get_config_field( "filter_category_filter_accordion_layout", $post_id, $override ),
                        "external_div"            => array(
                            "enabled" => ( get_config_field( "filter_category_filter_render_in_external_div", $post_id, $override ) == "" ? false : true ),
                            "id"      => get_config_field( "filter_category_filter_render_in_external_div", $post_id, $override ),
                        ),
                    ),
                ),
                "custom_search_filter" => array(
                    "enabled"  => get_config_field( "filter_custom_search_filter_enable", $post_id, $override ),
                    "position" => get_config_field( "filter_custom_search_filter_position", $post_id, $override ),
                    "options"  => array(
                        "result_number"   => get_config_field( "filter_custom_search_filter_number_of_results", $post_id, $override ),
                        "search_type"     => get_config_field( "filter_custom_search_filter_search_type", $post_id, $override ),
                        "placeholder"     => get_config_field( "filter_custom_search_filter_placeholder", $post_id, $override ),
                        "geocoder"        => array(
                            "enabled"       => get_config_field( "filter_custom_search_filter_include_geocoder", $post_id, $override ),
                            "limit_results" => get_config_field( "filter_custom_search_filter_limit_results", $post_id, $override ),
                        ),
                        "limit_by_bounds" => array(
                            "enabled" => get_config_field( "filter_custom_search_filter_limit_by_bounds", $post_id, $override ),
                            "bounds"  => array(
                                "sw_lat" => returnNumber( get_config_field( "filter_custom_search_filter_bounds_limit_southwest_latitude", $post_id, $override ) ),
                                "sw_lng" => returnNumber( get_config_field( "filter_custom_search_filter_bounds_limit_southwest_longitude", $post_id, $override ) ),
                                "ne_lat" => returnNumber( get_config_field( "filter_custom_search_filter_bounds_limit_northeast_latitude", $post_id, $override ) ),
                                "ne_lng" => returnNumber( get_config_field( "filter_custom_search_filter_bounds_limit_northeast_longitude", $post_id, $override ) ),
                            ),
                        ),
                        "external_div"    => array(
                            "enabled" => ( get_config_field( "filter_custom_search_filter_render_in_external_div", $post_id, $override ) == "" ? false : true ),
                            "id"      => get_config_field( "filter_custom_search_filter_render_in_external_div", $post_id, $override ),
                        ),
                    ),
                ),
                "list"                 => array(
                    "enabled"  => get_config_field( "list_enable", $post_id, $override ),
                    "position" => get_config_field( "list_position", $post_id, $override ),
                    "options"  => array(
                        "sort_by_distance"    => get_config_field( "list_sort_by_distance", $post_id, $override ),
                        "show_distance"       => get_config_field( "list_show_distance", $post_id, $override ),
                        "units"               => get_config_field( "list_units", $post_id, $override ),
                        "listing_type"        => get_config_field( "list_listing_type", $post_id, $override ),
                        "number_of_locations" => returnNumber( get_config_field( "list_number_of_locations", $post_id, $override ) ),
                        "list_order"          => get_config_field( "list_list_order", $post_id, $override ),
                        "display_images"      => get_config_field( "list_display_images", $post_id, $override ),
                        "group_by_category"   => get_config_field( "list_group_by_category", $post_id, $override ),
                        "store_locator"       => array(
                            "enabled"           => get_config_field( "list_store_locator_options_enable", $post_id, $override ),
                            "sort_hours_by_day" => get_config_field( "list_store_locator_options_sort_hours_by_day", $post_id, $override ),
                            "texts"             => array(
                                "back"       => $i8ln->get_mapster_strings()['admin_js']['Back'],
                                "closed"     => $i8ln->get_mapster_strings()['admin_js']['Closed'],
                                "open_until" => $i8ln->get_mapster_strings()['admin_js']['Open Until'],
                                "hours"      => $i8ln->get_mapster_strings()['admin_js']['Hours'],
                                "sunday"     => $i8ln->get_mapster_strings()['admin_js']['Sunday'],
                                "monday"     => $i8ln->get_mapster_strings()['admin_js']['Monday'],
                                "tuesday"    => $i8ln->get_mapster_strings()['admin_js']['Tuesday'],
                                "wednesday"  => $i8ln->get_mapster_strings()['admin_js']['Wednesday'],
                                "thursday"   => $i8ln->get_mapster_strings()['admin_js']['Thursday'],
                                "friday"     => $i8ln->get_mapster_strings()['admin_js']['Friday'],
                                "saturday"   => $i8ln->get_mapster_strings()['admin_js']['Saturday'],
                                "today"      => $i8ln->get_mapster_strings()['admin_js']['Today'],
                                "tomorrow"   => $i8ln->get_mapster_strings()['admin_js']['Tomorrow'],
                                "miles"      => $i8ln->get_mapster_strings()['admin_js']['Miles'],
                                "directions" => $i8ln->get_mapster_strings()['admin_js']['Directions'],
                                "kilometers" => $i8ln->get_mapster_strings()['admin_js']['Kilometers'],
                            ),
                        ),
                        "external_div"        => array(
                            "enabled" => ( get_config_field( "list_render_in_external_div", $post_id, $override ) == "" ? false : true ),
                            "id"      => get_config_field( "list_render_in_external_div", $post_id, $override ),
                        ),
                    ),
                ),
                "filter_dropdown"      => array(
                    "enabled"  => get_config_field( "filter_filter_dropdown_enable", $post_id, $override ),
                    "position" => get_config_field( "filter_filter_dropdown_position", $post_id, $override ),
                    "options"  => array(
                        "placeholder"    => get_config_field( "filter_filter_dropdown_placeholder", $post_id, $override ),
                        "display_images" => get_config_field( "filter_filter_dropdown_display_images", $post_id, $override ),
                    ),
                ),
            ),
            "popups"        => array(
                "styles"           => getPopupStyles( $feature_ids_to_load ),
                "shortcode_iframe" => plugin_dir_url( __FILE__ ) . "../includes/mapster-modal-shortcode.php",
                "sidebar"          => array(
                    "enabled"   => get_config_field( "open_popups_in_sidebar", $post_id, $override ),
                    "width_min" => returnNumber( get_config_field( "minimum_sidebar_width", $post_id, $override ), 100 ),
                    "width_max" => returnNumber( get_config_field( "maximum_sidebar_width", $post_id, $override ), 250 ),
                ),
            ),
            "loading"       => array(
                "enabled"    => true,
                "graphic"    => mapster_loader_graphic_to_slug( get_config_field( "loading_loading_graphic", $post_id, $override ) ),
                "custom"     => mapster_format_loader_custom( get_post_meta( $post_id, 'loading_custom_loader', true ) ),
                "type"       => ( get_config_field( "loading_custom_loader", $post_id, $override ) ? "custom" : "svg" ),
                "background" => get_config_field( "loading_background_color", $post_id, $override ),
                "color"      => get_config_field( "loading_loader_color", $post_id, $override ),
            ),
            "advanced"      => array(
                "javascript" => get_config_field( "javascript", $post_id, $override ),
                "cache"      => array(
                    "enabled"    => get_config_field( "cache_use_cache", $post_id, $override ),
                    "cache_date" => mapster_get_cache_date( $post_id ),
                    "cache_url"  => trailingslashit( wp_upload_dir()['basedir'] ) . 'mapster/map-' . $post_id . '.json',
                ),
                "embed"      => array(
                    "enabled"         => get_config_field( "embed_allow_embed", $post_id, $override ),
                    "allowed_origins" => getAllowedOrigins( $post_id, $override ),
                ),
            ),
            "specialty"     => array(
                "compare"    => array(
                    "enabled"      => get_config_field( "map_compare_enable_map_slider", $post_id, $override ),
                    "other_map_id" => get_config_field( "map_compare_compared_map", $post_id, $override ),
                ),
                "submission" => array(
                    "enabled"        => get_config_field( "submission_enable_submission", $post_id, $override ),
                    "fields_iframe"  => getSubmissionURL(),
                    "options"        => array(
                        "texts"       => array(
                            "header"              => "Submit a Point",
                            "button"              => "Submit a Point",
                            "drag_zoom"           => get_config_field( "submission_custom_texts_drag_zoom", $post_id, $override ),
                            "capture_point"       => get_config_field( "submission_custom_texts_capture_point", $post_id, $override ),
                            "choose_how"          => get_config_field( "submission_custom_texts_choose_how", $post_id, $override ),
                            "address_search"      => get_config_field( "submission_custom_texts_address_search", $post_id, $override ),
                            "map_click"           => get_config_field( "submission_custom_texts_map_click", $post_id, $override ),
                            "search_location"     => get_config_field( "submission_custom_texts_search_location", $post_id, $override ),
                            "selection_saved"     => get_config_field( "submission_custom_texts_selection_saved", $post_id, $override ),
                            "selection_error"     => get_config_field( "submission_custom_texts_selection_error", $post_id, $override ),
                            "add_point_text"      => get_config_field( "submission_custom_texts_add_point_text", $post_id, $override ),
                            "try_again"           => get_config_field( "submission_custom_texts_try_again", $post_id, $override ),
                            "confirm"             => get_config_field( "submission_custom_texts_confirm", $post_id, $override ),
                            "no_permissions"      => get_config_field( "submission_custom_texts_no_permissions", $post_id, $override ),
                            "change_map_location" => get_config_field( "submission_custom_texts_change_map_location", $post_id, $override ),
                            "back"                => get_config_field( "submission_custom_texts_back", $post_id, $override ),
                            "save"                => get_config_field( "submission_custom_texts_save", $post_id, $override ),
                            "submit"              => get_config_field( "submission_custom_texts_submit", $post_id, $override ),
                            "thanks"              => get_config_field( "submission_custom_texts_thanks", $post_id, $override ),
                        ),
                        "geocoder"    => array(
                            "enabled" => get_config_field( "submission_submission_interface_include_address_search", $post_id, $override ),
                        ),
                        "method"      => ( get_config_field( "submission_submission_interface_include_address_search", $post_id, $override ) ? "address" : "click" ),
                        "size"        => "lg",
                        "categories"  => getSubmissionCategories( get_config_field( "submission_submission_interface_categories", $post_id, $override ) ),
                        "title_field" => get_config_field( "submission_submission_interface_title_field", $post_id, $override ),
                    ),
                    "administration" => array(
                        "allowed_area"        => getSubmissionAllowedArea( get_config_field( "submission_administration_allowed_area", $post_id, $override ) ),
                        "template_posts"      => getTemplatePosts( $post_id, $override ),
                        "publish_immediately" => get_config_field( "submission_administration_publish_immediately", $post_id, $override ),
                    ),
                ),
                "heatmap"    => array(
                    "enabled"           => get_config_field( "heatmap_enable_heatmap", $post_id, $override ),
                    "layer"             => get_config_field( "heatmap_heatmap_layer", $post_id, $override ),
                    "visibility"        => get_config_field( "heatmap_heatmap_layer_visibility", $post_id, $override ),
                    "weighted_property" => get_config_field( "heatmap_heatmap_weighted_property", $post_id, $override ),
                    "intensity"         => get_config_field( "heatmap_heatmap_intensity", $post_id, $override ),
                    "color_range"       => returnArrayFromColorRange( $post_id, $override ),
                    "point_radius"      => get_config_field( "heatmap_heatmap_point_radius", $post_id, $override ),
                    "opacity"           => get_config_field( "heatmap_heatmap_opacity", $post_id, $override ),
                ),
                "listings"   => array(
                    "enabled"             => get_config_field( "listing_page_enable_listing_page", $post_id, $override ),
                    "style"               => get_config_field( "listing_page_options_listing_style", $post_id, $override ),
                    "titles_on_images"    => get_config_field( "listing_page_options_show_titles_on_images", $post_id, $override ),
                    "container_class"     => get_config_field( "listing_page_options_listing_container_class", $post_id, $override ),
                    "custom_html"         => get_config_field( "listing_page_options_custom_listing_html", $post_id, $override ),
                    "order"               => get_config_field( "listing_page_options_listings_order", $post_id, $override ),
                    "interaction"         => get_config_field( "listing_page_options_interaction_event", $post_id, $override ),
                    "center"              => get_config_field( "listing_page_options_center_on_hover", $post_id, $override ),
                    "popup"               => get_config_field( "listing_page_options_popup_on_hover", $post_id, $override ),
                    "lazy_load"           => get_config_field( "listing_page_options_lazy_load", $post_id, $override ),
                    "sticky_map"          => get_config_field( "listing_page_options_sticky_map", $post_id, $override ),
                    "link_target"         => get_config_field( "listing_page_options_link_destination", $post_id, $override ),
                    "link_blank"          => get_config_field( "listing_page_options_links_in_new_window", $post_id, $override ),
                    "reset_zoom_mouseout" => get_config_field( "listing_page_options_reset_zoom_on_mouseout", $post_id, $override ),
                ),
            ),
        ),
        "features" => getFeatures( $feature_ids_to_load ),
    );
}

// Builds a single popup template in the same shape returned by getPopupStyles(),
// but reads directly from a mapster-wp-popup post rather than via a feature's
// popup_style relationship field. Used by the popup-templates endpoint.
function mapster_build_popup_template(  $popup_id  ) {
    return [
        'id'       => $popup_id,
        'title'    => get_the_title( $popup_id ),
        'sections' => [
            'header'  => get_config_field( 'enable_header', $popup_id ),
            'image'   => get_config_field( 'enable_image', $popup_id ),
            'body'    => get_config_field( 'enable_body', $popup_id ),
            'footer'  => get_config_field( 'enable_footer', $popup_id ),
            'pointer' => get_config_field( 'enable_pointer', $popup_id ),
        ],
        'style'    => [
            'background'           => get_config_field( 'background', $popup_id ),
            'header'               => get_config_field( 'header', $popup_id ),
            'header_text'          => get_config_field( 'header_text', $popup_id ),
            'image_height'         => get_config_field( 'image_height', $popup_id ),
            'image_min_width'      => ( get_config_field( 'image_min_width', $popup_id ) ?: 200 ),
            'image_thumbnail_size' => get_config_field( 'image_thumbnail_size', $popup_id ),
            'body'                 => get_config_field( 'body', $popup_id ),
            'body_text'            => get_config_field( 'body_text', $popup_id ),
            'footer'               => get_config_field( 'footer', $popup_id ),
            'button'               => get_config_field( 'button', $popup_id ),
            'button_text'          => get_config_field( 'button_text', $popup_id ),
            'pointer'              => get_config_field( 'pointer', $popup_id ),
            'align'                => get_config_field( 'align', $popup_id ),
            'max_width'            => get_config_field( 'max_width', $popup_id ),
            'custom_css'           => get_config_field( 'css_editor', $popup_id ),
            'custom_html'          => get_config_field( 'html_editor', $popup_id ),
        ],
        'options'  => [
            'class'          => get_config_field( 'popup_class', $popup_id ),
            'anchor'         => get_config_field( 'popup_anchor', $popup_id ),
            'custom_css'     => get_config_field( 'use_custom_css', $popup_id ),
            'custom_html'    => get_config_field( 'use_custom_html', $popup_id ),
            'close_button'   => get_config_field( 'close_button', $popup_id ),
            'close_on_click' => get_config_field( 'close_on_click', $popup_id ),
            'close_map_move' => get_config_field( 'close_on_map_move', $popup_id ),
            'center_on_open' => get_config_field( 'center_on_open', $popup_id ),
            'zoom_on_open'   => get_config_field( 'zoom_on_open', $popup_id ),
            'open_as_modal'  => [
                'enabled'    => get_config_field( 'open_as_modal_immediately', $popup_id ),
                'iframe_url' => '',
            ],
            'translate'      => [
                'enabled' => checkPopupTranslateEnabled( $popup_id ),
                'left'    => get_config_field( 'popup_translate_left_translate', $popup_id ),
                'right'   => get_config_field( 'popup_translate_right_translate', $popup_id ),
                'top'     => get_config_field( 'popup_translate_top_translate', $popup_id ),
                'bottom'  => get_config_field( 'popup_translate_bottom_translate', $popup_id ),
            ],
        ],
    ];
}

// Organizing responses to have minimal output
// Memoized by popup_id: maps commonly reuse a small number of popup styles across
// hundreds of features, so without this a shared style gets fully re-fetched per feature.
function mapster_returnPopupData(  $popup_id  ) {
    static $cache = [];
    if ( array_key_exists( $popup_id, $cache ) ) {
        return $cache[$popup_id];
    }
    $single_popup_style_data = mapster_get_field_values( $popup_id );
    $single_popup_style_data['id'] = $popup_id;
    return $cache[$popup_id] = $single_popup_style_data;
}

function mapster_getPropertyList(  $data  ) {
    $propertiesToReturn = array();
    return $propertiesToReturn;
}

function mapster_getTermList(  $object_id  ) {
    $terms = get_the_terms( $object_id, 'wp-map-category' );
    $termsToReturn = array();
    if ( mapster_can_be_looped( $terms ) ) {
        foreach ( $terms as $term ) {
            if ( metadata_exists( 'term', $term->term_id, 'term_order' ) ) {
                $term->term_order = get_term_meta( $term->term_id, 'term_order' );
            }
        }
        foreach ( $terms as $term ) {
            $translated_term = $term;
            if ( is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) {
                $term_id = apply_filters(
                    'wpml_object_id',
                    $term->term_id,
                    "wp-map-category",
                    true
                );
                $translated_term = get_term( $term_id, 'wp-map-category' );
            }
            $thisTerm = array(
                "id"         => $translated_term->term_id,
                "name"       => $translated_term->name,
                "slug"       => $translated_term->slug,
                "term_order" => $translated_term->term_order,
                "color"      => get_field( "color", 'wp-map-category_' . $translated_term->term_id ),
                "icon"       => get_field( "icon", 'wp-map-category_' . $translated_term->term_id ),
                "parent"     => $translated_term->parent,
            );
            array_push( $termsToReturn, $thisTerm );
        }
    }
    return $termsToReturn;
}

function mapster_getOnlyValues(  $object_id  ) {
    $field_object_data = mapster_get_field_values( $object_id );
    krsort( $field_object_data );
    $single_feature_data = array(
        "id"         => $object_id,
        "slug"       => get_post_field( 'post_name', $object_id ),
        "menu_order" => get_post_field( 'menu_order', $object_id ),
        "permalink"  => get_permalink( $object_id ),
        "title"      => get_the_title( $object_id ),
        "content"    => get_the_content( null, null, $object_id ),
        "categories" => mapster_getTermList( $object_id ),
        "data"       => $field_object_data,
    );
    if ( mapster_can_be_looped( $field_object_data ) ) {
        foreach ( $field_object_data as $key => $data ) {
            $thisValue = $data;
            if ( is_string( $data ) && strpos( $data, "FeatureCollection" ) !== false ) {
                ini_set( 'serialize_precision', '-1' );
                $thisGeoJSON = json_decode( $data );
                $thisValue = array(
                    'type'        => $thisGeoJSON->features[0]->geometry->type,
                    'coordinates' => mapster_encode_coordinates( $thisGeoJSON->features[0]->geometry->coordinates ),
                );
            }
            $single_feature_data['data'][$key] = $thisValue;
            if ( $key == 'popup_style' ) {
                if ( isset( $single_feature_data['data'][$key] ) && $single_feature_data['data'][$key] && $single_feature_data['data'][$key]->ID ) {
                    $single_feature_data['data'][$key] = mapster_returnPopupData( $single_feature_data['data'][$key]->ID );
                }
            }
            if ( $key == 'popup' ) {
                $single_feature_data['data'][$key]['permalink'] = get_permalink( $object_id );
                if ( $single_feature_data['data'][$key]['featured_image'] ) {
                    $newImageData = array();
                    $newImageData['id'] = $single_feature_data['data'][$key]['featured_image']['id'];
                    $image_thumbnail_size = ( isset( $single_feature_data['data']['popup_style']['image_thumbnail_size'] ) ? $single_feature_data['data']['popup_style']['image_thumbnail_size'] : 'medium' );
                    $newImageData['url'] = wp_get_attachment_image_url( $newImageData['id'], $image_thumbnail_size );
                    $single_feature_data['data'][$key]['featured_image'] = $newImageData;
                }
            }
            if ( $key == 'images' ) {
                foreach ( $field_object_data[$key] as $image ) {
                    $image_thumbnail_size = ( isset( $single_feature_data['data']['popup_style']['image_thumbnail_size'] ) ? $single_feature_data['data']['popup_style']['image_thumbnail_size'] : 'medium' );
                    $thisAttachment = wp_get_attachment_image_src( $image['id'], $image_thumbnail_size );
                    array_push( $single_feature_data['data']['popup']['images'], $thisAttachment[0] );
                }
            }
            if ( $key === 'icon' ) {
                $newImageData = array();
                if ( $single_feature_data['data'][$key]['icon_properties']['icon-image'] ) {
                    $newImageData['id'] = $single_feature_data['data'][$key]['icon_properties']['icon-image']['id'];
                    $newImageData['url'] = $single_feature_data['data'][$key]['icon_properties']['icon-image']['url'];
                    $newImageData['height'] = $single_feature_data['data'][$key]['icon_properties']['icon-image']['height'];
                    $newImageData['width'] = $single_feature_data['data'][$key]['icon_properties']['icon-image']['width'];
                    $single_feature_data['data'][$key]['icon_properties']['icon-image'] = $newImageData;
                }
                // $hoverImageData = array();
                // if($single_feature_data['data'][$key]['icon_properties']['hover_effects']['hover_image']) {
                //   $hoverImageData['id'] = $single_feature_data['data'][$key]['icon_properties']['hover_effects']['hover_image']['id'];
                //   $hoverImageData['url'] = $single_feature_data['data'][$key]['icon_properties']['hover_effects']['hover_image']['url'];
                //   $single_feature_data['data'][$key]['icon_properties']['hover_effects']['hover_image'] = $hoverImageData;
                // }
            }
        }
    }
    return $single_feature_data;
}

// Turning coordinates into encoded
function mapster_encode_coordinates(  $coordinates  ) {
    // $poly_encoder = new Polyline();
    // $coordinates_to_return = array();
    // if(is_numeric($coordinates[0])) { // It's a point, don't encode
    //   $coordinates_to_return = $coordinates;
    // } else if(is_array($coordinates[0])) { // Line, MultiLine, Poly, MultiPoly
    //   if(is_numeric($coordinates[0][0])) { // Line
    //     $coordinates_to_return = $poly_encoder->encode($coordinates);
    //   }
    //   if(is_array($coordinates[0][0])) { // Multiline, Poly, MultiPoly
    //     if(is_numeric($coordinates[0][0][0])) { // Multiline, Poly
    //       foreach($coordinates as $pointSet) {
    //         array_push($coordinates_to_return, $poly_encoder->encode($pointSet));
    //       }
    //     }
    //     if(is_array($coordinates[0][0][0])) { // MultiPoly
    //       foreach($coordinates as $polyOrHoleCollection) {
    //         $polyOrHoleHolder = array();
    //         foreach($polyOrHoleCollection as $polyOrHole) {
    //           array_push($polyOrHoleHolder, $poly_encoder->encode($polyOrHole));
    //         }
    //         array_push($coordinates_to_return, $polyOrHoleHolder);
    //       }
    //     }
    //   }
    // }
    // return $coordinates_to_return;
    return $coordinates;
}

// Organizing template fields
function mapster_arrange_fields(  $field_group, $isFeature  ) {
    $toReturn = array();
    if ( $isFeature ) {
        $toReturn['permalink'] = false;
        $toReturn['title'] = false;
        $toReturn['content'] = false;
        $toReturn['categories'] = false;
        $toReturn['slug'] = false;
        $toReturn['id'] = false;
        $toReturn['menu_order'] = false;
        $toReturn['data'] = array();
        if ( mapster_can_be_looped( $field_group ) ) {
            foreach ( $field_group as $field ) {
                if ( $field['name'] !== "" ) {
                    $toReturn['data'][$field['name']] = mapster_arrange_sub_fields( $field );
                }
            }
        }
        // Get popup stuff too
        $popup_fields = acf_get_fields( 'group_6163d357655f4' );
        if ( mapster_can_be_looped( $popup_fields ) ) {
            foreach ( $popup_fields as $field ) {
                $toReturn['data'][$field['name']] = mapster_arrange_sub_fields( $field );
            }
        }
        $toReturn['data']['popup']['permalink'] = false;
    } else {
        foreach ( $field_group as $field ) {
            if ( $field['name'] !== "" ) {
                $toReturn[$field['name']] = mapster_arrange_sub_fields( $field );
            }
        }
    }
    return $toReturn;
}

function mapster_arrange_sub_fields(  $field  ) {
    $toReturn = array();
    if ( isset( $field['sub_fields'] ) ) {
        if ( mapster_can_be_looped( $field['sub_fields'] ) ) {
            foreach ( $field['sub_fields'] as $sub_field ) {
                $value = mapster_arrange_sub_fields( $sub_field );
                $toReturn[$sub_field['name']] = $value;
            }
        }
        return $toReturn;
    } else {
        // Handler for true/false
        if ( $field['type'] == 'true_false' ) {
            return ( $field['default_value'] == 0 ? false : true );
        } else {
            if ( !isset( $field['default_value'] ) ) {
                return null;
            } else {
                return $field['default_value'];
            }
        }
    }
}

function mapster_can_be_looped(  $variable  ) {
    if ( is_array( $variable ) || is_object( $variable ) ) {
        return true;
    } else {
        return false;
    }
}

function getFeatures(  $feature_ids_to_load  ) {
    $toReturn = array();
    $acf_keys = getAllGroupFields(
        'group_616377d62836b',
        'group_6163732e0426e',
        'group_616379566202f',
        'group_6163d357655f4',
        'group_626492b319912'
    );
    foreach ( $feature_ids_to_load as $post_id ) {
        $geometry = getFeatureGeometry( $post_id );
        if ( $geometry ) {
            $associated_post = get_feature_field( "associated_post", $post_id );
            $popup_style = get_feature_field( "popup_style", $post_id );
            $popup_image_type = get_feature_field( "popup_image_type", $post_id );
            $popup_body_text = get_feature_field( "popup_body_text", $post_id );
            $popup_button_text = get_feature_field( "popup_button_text", $post_id );
            $popup_modal_details = get_feature_field( "popup_modal_details", $post_id );
            $social_media_facebook = get_feature_field( "social_media_facebook", $post_id );
            $type = false;
            $featureType = getFeatureType( $geometry );
            if ( $featureType == "point" ) {
                $location_style = get_feature_field( "location_style", $post_id );
                if ( $location_style == "circle" ) {
                    $type = "circle";
                } else {
                    if ( $location_style == "marker" ) {
                        $type = "marker";
                    } else {
                        if ( $location_style == "label" ) {
                            $type = "symbol";
                        } else {
                            if ( $location_style == "3d-model" ) {
                                $type = "3dObject";
                            }
                        }
                    }
                }
            } else {
                if ( $featureType == "polygon" ) {
                    $polygon_style = get_feature_field( "polygon_style", $post_id );
                    if ( $polygon_style == "fill" || !$polygon_style ) {
                        $type = "polygon";
                    } else {
                        if ( $polygon_style == "fill-extrusion" ) {
                            $type = "3d-polygon";
                        } else {
                            if ( $polygon_style == "fill-image" ) {
                                $type = "image-polygon";
                            } else {
                                if ( $polygon_style == "fill-pattern" ) {
                                    $type = "pattern-polygon";
                                }
                            }
                        }
                    }
                } else {
                    $type = $featureType;
                }
            }
            $feat_content = get_the_content( $post_id );
            $feat_categories = mapster_getTermList( $post_id );
            $feat_metadata = getAdditionalMetadata( $acf_keys, $post_id );
            $feat_carbon_props = null;
            if ( mwm_fs()->is__premium_only() && mwm_fs()->can_use_premium_code() ) {
                $feat_carbon_props = [];
                foreach ( carbon_get_post_meta( $post_id, 'mapster_custom_properties' ) as $property ) {
                    $feat_carbon_props[$property['property_name']] = $property['property_value'];
                }
            }
            $feat_popup_header = popupReplaceValues( get_feature_field( "popup_header_text", $post_id ), $post_id, $feat_carbon_props );
            $feat_popup_body = popupReplaceValues( $popup_body_text, $post_id, $feat_carbon_props );
            $feat_popup_button = ( $popup_button_text ? popupReplaceValues( $popup_button_text, $post_id, $feat_carbon_props ) : "" );
            $feat_popup_button_url = popupReplaceValues( get_feature_field( "popup_button_url", $post_id ), $post_id, $feat_carbon_props );
            $feat_popup_modal = popupReplaceValues( $popup_modal_details, $post_id, $feat_carbon_props );
            $thisFeature = array(
                "metadata"      => array(
                    "id"         => intval( $post_id ),
                    "slug"       => get_post_field( 'post_name', $post_id ),
                    "url"        => get_the_permalink( $post_id ),
                    "type"       => $type,
                    "title"      => get_the_title( $post_id ),
                    "content"    => $feat_content,
                    "categories" => $feat_categories,
                    "order"      => false,
                    "store"      => array(
                        "enabled"            => get_feature_field( "store_locator_fields", $post_id ),
                        "address"            => get_feature_field( "address", $post_id ),
                        "phone"              => get_feature_field( "phone_number", $post_id ),
                        "website"            => get_feature_field( "website", $post_id ),
                        "description"        => get_feature_field( "locator_description", $post_id ),
                        "use_custom_button"  => get_feature_field( "custom_button_show_custom_button", $post_id ),
                        "custom_button_icon" => get_feature_field( "custom_button_icon", $post_id ),
                        "custom_button_url"  => get_feature_field( "custom_button_url", $post_id ),
                        "show_hours"         => get_feature_field( "hours_show_hours", $post_id ),
                        "hours"              => array(
                            "monday"    => get_feature_field( "hours_monday", $post_id ),
                            "tuesday"   => get_feature_field( "hours_tuesday", $post_id ),
                            "wednesday" => get_feature_field( "hours_wednesday", $post_id ),
                            "thursday"  => get_feature_field( "hours_thursday", $post_id ),
                            "friday"    => get_feature_field( "hours_friday", $post_id ),
                            "saturday"  => get_feature_field( "hours_saturday", $post_id ),
                            "sunday"    => get_feature_field( "hours_sunday", $post_id ),
                        ),
                        "show_socials"       => get_feature_field( "social_media_show_social_media", $post_id ),
                        "socials"            => array(
                            "facebook"  => $social_media_facebook,
                            "twitter"   => get_feature_field( "social_media_twitter", $post_id ),
                            "linkedin"  => get_feature_field( "social_media_linkedin", $post_id ),
                            "instagram" => get_feature_field( "social_media_instagram", $post_id ),
                            "tiktok"    => get_feature_field( "social_media_tiktok", $post_id ),
                            "youtube"   => get_feature_field( "social_media_youtube", $post_id ),
                            "pinterest" => get_feature_field( "social_media_pinterest", $post_id ),
                        ),
                    ),
                    "listings"   => array(
                        "enabled" => $associated_post,
                        "date"    => ( $associated_post ? $associated_post->post_date : false ),
                        "image"   => ( $associated_post ? get_the_post_thumbnail_url( $associated_post->ID ) : false ),
                        "url"     => ( $associated_post ? get_the_permalink( $associated_post->ID ) : false ),
                    ),
                    "metadata"   => $feat_metadata,
                ),
                "interactivity" => array(
                    "interaction"  => get_feature_field( "interaction", $post_id ),
                    "click_mobile" => get_feature_field( "click_on_mobile", $post_id ),
                    "default_zoom" => get_feature_field( "default_zoom_level", $post_id ),
                    "direct_link"  => array(
                        "enabled" => get_feature_field( "open_link_on_click", $post_id ),
                        "url"     => get_feature_field( "click_link_url", $post_id ),
                        "target"  => ( get_feature_field( "click_link_open_in_new_tab", $post_id ) ? "_blank" : "_self" ),
                    ),
                    "popup"        => array(
                        "enabled"  => get_feature_field( 'enable_popup', $post_id ),
                        "style_id" => ( $popup_style ? $popup_style->ID : false ),
                        "content"  => array(
                            "header"         => $feat_popup_header,
                            "post_title"     => get_the_title( $post_id ),
                            "image_type"     => $popup_image_type,
                            "featured_image" => getFeaturedImage( get_feature_field( "popup_featured_image", $post_id ), $popup_style ),
                            "images"         => getGalleryImages( get_feature_field( "field_61dcb4a861391", $post_id ), $popup_style ),
                            "body"           => $feat_popup_body,
                            "body_raw"       => $popup_body_text,
                            "button"         => $feat_popup_button,
                            "button_raw"     => $popup_button_text,
                            "button_url"     => $feat_popup_button_url,
                            "modal"          => $feat_popup_modal,
                            "modal_raw"      => $popup_modal_details,
                        ),
                        "options"  => array(
                            "image_type"         => $popup_image_type,
                            "button_action"      => get_feature_field( "popup_button_action", $post_id ),
                            "render_iframe"      => array(
                                "enabled" => get_feature_field( "popup_render_shortcode", $post_id ),
                                "url"     => plugin_dir_url( __FILE__ ) . "../includes/mapster-popup-shortcode.php",
                            ),
                            "link_target"        => ( get_feature_field( "popup_open_link_in_new_tab", $post_id ) ? "_blank" : "_self" ),
                            "permalink"          => get_permalink( $post_id ),
                            "keep_open_on_hover" => get_feature_field( "keep_popup_open_on_hover", $post_id ),
                            "open_on_load"       => get_feature_field( "open_popup_on_load", $post_id ),
                            "permanent"          => get_feature_field( "popup_permanent", $post_id ),
                        ),
                    ),
                ),
                "geometry"      => $geometry,
            );
            if ( $type == "marker" ) {
                $thisFeature["properties"] = array(
                    "color"    => get_feature_field( "marker_color", $post_id ),
                    "scale"    => returnNumber( get_feature_field( "marker_scale", $post_id ) ),
                    "rotation" => returnNumber( get_feature_field( "marker_rotation", $post_id ) ),
                    "anchor"   => get_feature_field( "marker_anchor", $post_id ),
                    "hover"    => array(
                        "enabled"  => get_feature_field( "marker_hover_effects_hover_enabled", $post_id ),
                        "color"    => get_feature_field( "marker_hover_effects_hover_color", $post_id ),
                        "scale"    => returnNumber( get_feature_field( "marker_hover_effects_hover_scale", $post_id ) ),
                        "rotation" => returnNumber( get_feature_field( "marker_hover_effects_hover_rotation", $post_id ) ),
                    ),
                );
            }
            if ( $type == "circle" ) {
                $thisFeature["properties"] = array(
                    "color"          => get_feature_field( "circle_color", $post_id ),
                    "radius"         => returnNumber( get_feature_field( "circle_radius", $post_id ) ),
                    "opacity"        => returnNumber( get_feature_field( "circle_opacity", $post_id ) ),
                    "stroke-width"   => returnNumber( get_feature_field( "circle_stroke-width", $post_id ) ),
                    "stroke-color"   => get_feature_field( "circle_stroke-color", $post_id ),
                    "stroke-opacity" => returnNumber( get_feature_field( "circle_stroke-opacity", $post_id ) ),
                    "hover"          => array(
                        "enabled"        => get_feature_field( "circle_hover_effects_hover_enabled", $post_id ),
                        "color"          => get_feature_field( "circle_hover_effects_hover_color", $post_id ),
                        "radius"         => returnNumber( get_feature_field( "circle_hover_effects_hover_radius", $post_id ) ),
                        "opacity"        => returnNumber( get_feature_field( "circle_hover_effects_hover_opacity", $post_id ) ),
                        "stroke-width"   => returnNumber( get_feature_field( "circle_hover_effects_hover_stroke-width", $post_id ) ),
                        "stroke-color"   => get_feature_field( "circle_hover_effects_hover_stroke-color", $post_id ),
                        "stroke-opacity" => returnNumber( get_feature_field( "circle_hover_effects_hover_stroke-opacity", $post_id ) ),
                    ),
                    "options"        => array(
                        "static-size" => get_feature_field( "circle_circle-static-size", $post_id ),
                    ),
                );
            }
            if ( $type == "symbol" ) {
                $thisFeature["properties"] = array(
                    "text"  => array(
                        "enabled"    => get_feature_field( "label_label_on", $post_id ),
                        "field"      => popupReplaceValues( get_feature_field( "label_text_properties_text-field", $post_id ), $post_id, $feat_carbon_props ),
                        "font"       => get_feature_field( "label_text_properties_text-font", $post_id ),
                        "size"       => returnNumber( get_feature_field( "label_text_properties_text-size", $post_id ) ),
                        "color"      => get_feature_field( "label_text_properties_text-color", $post_id ),
                        "opacity"    => returnNumber( get_feature_field( "label_text_properties_text-opacity", $post_id ) ),
                        "rotate"     => returnNumber( get_feature_field( "label_text_properties_text-rotate", $post_id ) ),
                        "translate"  => array(returnNumber( get_feature_field( "label_text_properties_text-translate-x", $post_id ) ), returnNumber( get_feature_field( "label_text_properties_text-translate-y", $post_id ) )),
                        "halo-width" => returnNumber( get_feature_field( "label_text_properties_text-halo-width", $post_id ) ),
                        "halo-color" => get_feature_field( "label_text_properties_text-halo-color", $post_id ),
                        "halo-blur"  => returnNumber( get_feature_field( "label_text_properties_text-halo-blur", $post_id ) ),
                    ),
                    "icon"  => array(
                        "enabled"     => get_feature_field( "icon_icon_on", $post_id ),
                        "image"       => mapster_format_loader_custom( get_post_meta( $post_id, 'icon_icon_properties_icon-image', true ) ),
                        "size"        => returnNumber( get_feature_field( "icon_icon_properties_icon-size", $post_id ) ),
                        "opacity"     => returnNumber( get_feature_field( "icon_icon_properties_icon-opacity", $post_id ) ),
                        "rotate"      => returnNumber( get_feature_field( "icon_icon_properties_icon-rotate", $post_id ) ),
                        "translate"   => array(returnNumber( get_feature_field( "icon_icon_properties_icon-translate-x", $post_id ) ), returnNumber( get_feature_field( "icon_icon_properties_icon-translate-y", $post_id ) )),
                        "anchor"      => get_feature_field( "icon_icon_properties_icon-anchor", $post_id ),
                        "static-size" => get_feature_field( "icon_icon_properties_icon-static-size", $post_id ),
                    ),
                    "hover" => array(
                        "enabled" => get_feature_field( "icon_icon_properties_hover_effects_hover_enabled", $post_id ),
                        "icon"    => array(
                            "opacity" => returnNumber( get_feature_field( "icon_icon_properties_hover_effects_hover_opacity", $post_id ) ),
                        ),
                    ),
                );
            }
            if ( $type == "3dObject" ) {
                $thisFeature["properties"] = array(
                    "3d_model_file" => get_feature_field( "3d_model_3d_model_file", $post_id ),
                    "scale"         => returnNumber( get_feature_field( "3d_model_scale", $post_id ) ),
                    "rotation"      => array(
                        "x_rotation" => returnNumber( get_feature_field( "3d_model_rotation_x_rotation", $post_id ) ),
                        "y_rotation" => returnNumber( get_feature_field( "3d_model_rotation_y_rotation", $post_id ) ),
                        "z_rotation" => returnNumber( get_feature_field( "3d_model_rotation_z_rotation", $post_id ) ),
                    ),
                );
            }
            if ( $type == "line" ) {
                $thisFeature["properties"] = array(
                    "color"   => get_feature_field( "color", $post_id ),
                    "width"   => returnNumber( get_feature_field( "width", $post_id ) ),
                    "opacity" => returnNumber( get_feature_field( "opacity", $post_id ) ),
                    "dashed"  => array(
                        "enabled"   => get_feature_field( "dashed_line", $post_id ),
                        "dash_line" => array(returnNumber( get_feature_field( "dash_properties_dash_length", $post_id ) ), returnNumber( get_feature_field( "dash_properties_gap_length", $post_id ) )),
                    ),
                    "hover"   => array(
                        "enabled" => get_feature_field( "hover_effects_hover_enabled", $post_id ),
                        "color"   => get_feature_field( "hover_effects_hover_color", $post_id ),
                        "width"   => returnNumber( get_feature_field( "hover_effects_hover_width", $post_id ) ),
                        "opacity" => returnNumber( get_feature_field( "hover_effects_hover_opacity", $post_id ) ),
                    ),
                );
            }
            if ( $type == "polygon" ) {
                $thisFeature["properties"] = array(
                    "color"         => get_feature_field( "color", $post_id ),
                    "opacity"       => returnNumber( get_feature_field( "opacity", $post_id ) ),
                    "outline-color" => get_feature_field( "outline-color", $post_id ),
                    "hover"         => array(
                        "enabled"       => get_feature_field( "hover_effects_hover_enabled", $post_id ),
                        "color"         => get_feature_field( "hover_effects_hover_color", $post_id ),
                        "opacity"       => returnNumber( get_feature_field( "hover_effects_opacity", $post_id ) ),
                        "outline-color" => get_feature_field( "hover_effects_outline-color", $post_id ),
                    ),
                );
            }
            if ( $type == "3d-polygon" ) {
                $thisFeature["properties"] = array(
                    "color"         => get_feature_field( "color", $post_id ),
                    "opacity"       => returnNumber( get_feature_field( "opacity", $post_id ) ),
                    "outline-color" => get_feature_field( "outline-color", $post_id ),
                    "3d"            => array(
                        "enabled" => get_feature_field( "polygon_style", $post_id ) == "fill-extrusion",
                        "base"    => returnNumber( get_feature_field( "3d_polygon_base", $post_id ) ),
                        "height"  => returnNumber( get_feature_field( "3d_polygon_height", $post_id ) ),
                    ),
                    "hover"         => array(
                        "enabled"       => get_feature_field( "hover_effects_hover_enabled", $post_id ),
                        "color"         => get_feature_field( "hover_effects_hover_color", $post_id ),
                        "opacity"       => returnNumber( get_feature_field( "hover_effects_opacity", $post_id ) ),
                        "outline-color" => get_feature_field( "hover_effects_outline-color", $post_id ),
                        "3d"            => array(
                            "base"   => returnNumber( get_feature_field( "hover_effects_base", $post_id ) ),
                            "height" => returnNumber( get_feature_field( "hover_effects_height", $post_id ) ),
                        ),
                    ),
                );
            }
            if ( $type == "pattern-polygon" ) {
                $thisFeature["properties"] = array(
                    "pattern" => ( get_feature_field( "polygon_style", $post_id ) == "fill-pattern" ? get_feature_field( "pattern", $post_id ) : false ),
                );
            }
            if ( $type == "image-polygon" ) {
                $thisFeature["properties"] = array(
                    "image" => ( get_feature_field( "polygon_style", $post_id ) == "fill-image" ? get_feature_field( "polygon_image", $post_id ) : false ),
                );
            }
            array_push( $toReturn, $thisFeature );
        }
    }
    return $toReturn;
}

function popupReplaceValues(  $value, $post_id, $prefetched_properties = null  ) {
    return $value;
}

function getSubmissionAllowedArea(  $polygon_id  ) {
    if ( $polygon_id ) {
        $string_geom = get_field( 'polygon', $polygon_id );
        return json_decode( $string_geom )->features[0]->geometry;
    }
    return false;
}

function getSubmissionCategories(  $categoryIDs  ) {
    $toReturn = array();
    if ( $categoryIDs ) {
        foreach ( $categoryIDs as $categoryID ) {
            $term = get_term( $categoryID );
            array_push( $toReturn, array(
                "id"          => $categoryID,
                "icon"        => get_field( "icon", 'wp-map-category_' . $categoryID ),
                "name"        => $term->name,
                "description" => $term->description,
            ) );
        }
    }
    return $toReturn;
}

function getSubmissionURL() {
    $url = "";
    $query = new WP_Query(array(
        'posts_per_page' => 1,
        'post_type'      => 'page',
        'meta_key'       => '_wp_page_template',
        'meta_value'     => 'mapster-submission-template.php',
    ));
    if ( $query->have_posts() ) {
        $query->the_post();
        $url = get_permalink( get_the_ID() );
    }
    wp_reset_postdata();
    return $url;
}

function getAdditionalMetadata(  $acf_keys, $post_id  ) {
    $all_meta = get_post_meta( $post_id );
    $result = [];
    foreach ( $all_meta as $key => $value ) {
        if ( in_array( $key, $acf_keys, true ) ) {
            continue;
        }
        if ( preg_match( '/^field_[a-f0-9]+$/', $value[0] ) ) {
            continue;
        }
        if ( str_contains( $key, "_mapster_custom_properties" ) ) {
            continue;
        }
        $result[$key] = $value[0];
    }
    foreach ( $result as $key => $value ) {
        if ( str_starts_with( $key, '_' ) && array_key_exists( ltrim( $key, '_' ), $result ) ) {
            unset($result[$key]);
        }
    }
    foreach ( $result as $key => $raw_value ) {
        if ( isset( $all_meta['_' . $key] ) && preg_match( '/^field_[a-f0-9]+$/', $all_meta['_' . $key][0] ) ) {
            $result[$key] = get_field( $key, $post_id );
        }
    }
    return $result;
}

function getAllGroupFields(  ... $group_keys  ) {
    $keys = [];
    foreach ( $group_keys as $group_key ) {
        $fields = acf_get_fields( $group_key );
        // var_dump($fields);
        if ( !empty( $fields ) ) {
            recursiveOverGroups(
                $fields,
                '',
                '',
                $keys
            );
        }
    }
    return array_unique( $keys );
}

function recursiveOverGroups(
    $fields,
    $prefix,
    $immediate_prefix,
    &$keys
) {
    foreach ( $fields as $field ) {
        if ( empty( $field['name'] ) ) {
            continue;
        }
        $full_key = ( $prefix === '' ? $field['name'] : "{$prefix}_{$field['name']}" );
        $immediate_key = ( $immediate_prefix === '' ? $field['name'] : "{$immediate_prefix}_{$field['name']}" );
        $keys[] = $field['name'];
        $keys[] = "_{$field['name']}";
        $keys[] = $full_key;
        $keys[] = "_{$full_key}";
        if ( $immediate_key !== $full_key ) {
            $keys[] = $immediate_key;
            $keys[] = "_{$immediate_key}";
        }
        if ( $field['type'] === 'group' && !empty( $field['sub_fields'] ) ) {
            recursiveOverGroups(
                $field['sub_fields'],
                $full_key,
                $field['name'],
                $keys
            );
        }
    }
}

function getImageURLOnly(  $field, $post_id  ) {
    $image = get_field( $field, $post_id );
    if ( $image ) {
        if ( is_string( $image ) || is_numeric( $image ) ) {
            return $image;
        } else {
            if ( $image['ID'] ) {
                return $image['sizes']['large'];
            }
        }
    } else {
        return false;
    }
}

function getImageDimensions(  $field, $post_id  ) {
    $image = get_field( $field, $post_id );
    if ( $image ) {
        if ( is_string( $image ) || is_numeric( $image ) ) {
            return [100, 100];
        } else {
            if ( $image['ID'] ) {
                return [$image['width'], $image['height']];
            }
        }
    } else {
        return false;
    }
}

function getFeaturedImage(  $image, $popup_style  ) {
    if ( is_wp_error( $image ) || !$image ) {
        return false;
    }
    $image_id = false;
    if ( is_string( $image ) || is_numeric( $image ) ) {
        $image_id = $image;
    } else {
        if ( isset( $image['ID'] ) ) {
            $image_id = $image['ID'];
        }
    }
    if ( !$image_id ) {
        return false;
    }
    $thumb_size = ( isset( $popup_style->ID ) ? ( get_field( 'image_thumbnail_size', $popup_style->ID ) ?: 'medium' ) : 'medium' );
    $url = wp_get_attachment_image_url( intval( $image_id ), $thumb_size );
    return ( $url ? [
        'id'  => intval( $image_id ),
        'url' => $url,
    ] : false );
}

function getGalleryImages(  $images, $popup_style  ) {
    if ( !is_array( $images ) || count( $images ) === 0 ) {
        return [];
    }
    $thumb_size = ( isset( $popup_style->ID ) ? ( get_field( 'image_thumbnail_size', $popup_style->ID ) ?: 'medium' ) : 'medium' );
    $result = [];
    foreach ( $images as $image ) {
        $id = $image['id'] ?? $image['ID'] ?? null;
        $url = ( $id ? wp_get_attachment_image_url( intval( $id ), $thumb_size ) : null );
        if ( $id && $url ) {
            $result[] = [
                'id'  => intval( $id ),
                'url' => $url,
            ];
        }
    }
    return $result;
}

function getFeatureGeometry(  $post_id  ) {
    $post_type = get_post_type( $post_id );
    $geographic_data_key = false;
    if ( $post_type == "mapster-wp-location" ) {
        $geographic_data_key = "location";
    } else {
        if ( $post_type == "mapster-wp-line" ) {
            $geographic_data_key = "line";
        } else {
            if ( $post_type == "mapster-wp-polygon" ) {
                $geographic_data_key = "polygon";
            } else {
                $acf_fields = get_field_objects( $post_id );
                if ( mapster_can_be_looped( $acf_fields ) ) {
                    foreach ( $acf_fields as $key => $data_field ) {
                        if ( is_array( $data_field ) && array_key_exists( 'type', $data_field ) ) {
                            if ( isset( $data_field['type'] ) && isValidGeoJSON( $data_field['value'] ) ) {
                                $geographic_data_key = $key;
                            }
                        }
                    }
                }
            }
        }
    }
    if ( $geographic_data_key ) {
        $string_geom = get_field( $geographic_data_key, $post_id );
        return json_decode( $string_geom )->features[0]->geometry;
    }
    return false;
}

function getFeatureType(  $geometry  ) {
    if ( isset( $geometry ) ) {
        if ( str_contains( $geometry->type, "Point" ) ) {
            return 'point';
        }
        if ( str_contains( $geometry->type, "Line" ) ) {
            return 'line';
        }
        if ( str_contains( $geometry->type, "Polygon" ) ) {
            return 'polygon';
        }
    }
    return false;
}

function isValidGeoJSON(  $input  ) {
    if ( !is_string( $input ) ) {
        return false;
    }
    $input = trim( $input );
    if ( $input === '' ) {
        return false;
    }
    $data = json_decode( $input, true );
    if ( json_last_error() !== JSON_ERROR_NONE || !is_array( $data ) ) {
        return false;
    }
    if ( !isset( $data['type'] ) ) {
        return false;
    }
    return hasCoordinates( $data );
}

function hasCoordinates(  $data  ) {
    if ( isset( $data['coordinates'] ) ) {
        return true;
    }
    foreach ( $data as $value ) {
        if ( is_array( $value ) && hasCoordinates( $value ) ) {
            return true;
        }
    }
    return false;
}

function getPopupStyles(  $feature_ids_to_load  ) {
    $toReturn = array();
    foreach ( $feature_ids_to_load as $post_id ) {
        $popup_style = get_field( "popup_style", $post_id );
        if ( $popup_style ) {
            $popup_id = $popup_style->ID;
            array_push( $toReturn, array(
                "id"       => $popup_id,
                "sections" => array(
                    "header"  => get_field( 'enable_header', $popup_id ),
                    "image"   => get_field( 'enable_image', $popup_id ),
                    "body"    => get_field( 'enable_body', $popup_id ),
                    "footer"  => get_field( 'enable_footer', $popup_id ),
                    "pointer" => get_field( 'enable_pointer', $popup_id ),
                ),
                "style"    => array(
                    "background"           => get_field( 'background', $popup_id ),
                    "header"               => get_field( 'header', $popup_id ),
                    "header_text"          => get_field( 'header_text', $popup_id ),
                    "image_height"         => get_field( 'image_height', $popup_id ),
                    "image_min_width"      => ( get_field( 'image_min_width', $popup_id ) ? get_field( 'image_min_width', $popup_id ) : 200 ),
                    "image_thumbnail_size" => get_field( 'image_thumbnail_size', $popup_id ),
                    "body"                 => get_field( 'body', $popup_id ),
                    "body_text"            => get_field( 'body_text', $popup_id ),
                    "footer"               => get_field( 'footer', $popup_id ),
                    "button"               => get_field( 'button', $popup_id ),
                    "button_text"          => get_field( 'button_text', $popup_id ),
                    "pointer"              => get_field( 'pointer', $popup_id ),
                    "align"                => get_field( 'align', $popup_id ),
                    "max_width"            => get_field( 'max_width', $popup_id ),
                    "custom_css"           => get_field( 'css_editor', $popup_id ),
                    "custom_html"          => get_field( 'html_editor', $popup_id ),
                ),
                "options"  => array(
                    "class"          => get_field( 'popup_class', $popup_id ),
                    "anchor"         => get_field( 'popup_anchor', $popup_id ),
                    "custom_css"     => get_field( 'use_custom_css', $popup_id ),
                    "custom_html"    => get_field( 'use_custom_html', $popup_id ),
                    "close_button"   => get_field( 'close_button', $popup_id ),
                    "close_on_click" => get_field( 'close_on_click', $popup_id ),
                    "close_map_move" => get_field( 'close_on_map_move', $popup_id ),
                    "center_on_open" => get_field( 'center_on_open', $popup_id ),
                    "zoom_on_open"   => get_field( 'zoom_on_open', $popup_id ),
                    "open_as_modal"  => array(
                        "enabled"    => get_field( 'open_as_modal_immediately', $popup_id ),
                        "iframe_url" => "",
                    ),
                    "translate"      => array(
                        "enabled" => checkPopupTranslateEnabled( $popup_id ),
                        "left"    => get_field( 'popup_translate_left_translate', $popup_id ),
                        "right"   => get_field( 'popup_translate_right_translate', $popup_id ),
                        "top"     => get_field( 'popup_translate_top_translate', $popup_id ),
                        "bottom"  => get_field( 'popup_translate_bottom_translate', $popup_id ),
                    ),
                ),
            ) );
        }
    }
    return $toReturn;
}

function checkPopupTranslateEnabled(  $popup_id  ) {
    if ( get_field( 'popup_translate_left_translate', $popup_id ) ) {
        return true;
    }
    if ( get_field( 'popup_translate_right_translate', $popup_id ) ) {
        return true;
    }
    if ( get_field( 'popup_translate_top_translate', $popup_id ) ) {
        return true;
    }
    if ( get_field( 'popup_translate_bottom_translate', $popup_id ) ) {
        return true;
    }
    return false;
}

function getTemplatePosts(  $post_id, $override  ) {
    $template_posts = get_config_field( "submission_administration_template_posts", $post_id, $override );
    if ( $template_posts && count( $template_posts ) > 0 ) {
        return $template_posts;
    } else {
        return array(get_config_field( "submission_administration_template_post", $post_id, $override ));
    }
}
