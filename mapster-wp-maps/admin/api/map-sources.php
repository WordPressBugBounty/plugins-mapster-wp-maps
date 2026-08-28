<?php

class Mapster_Map_Sources {

  private const MAPSTER_POST_TYPES = [
    'mapster-wp-location',
    'mapster-wp-line',
    'mapster-wp-polygon',
  ];

  // Taxonomies that have a dedicated source field — excluded from custom_categories search
  private const EXCLUDED_TAXONOMIES = [
    'wp-map-category',
    'nav_menu',
    'link_category',
    'post_format',
  ];

  // ===========================================================================
  // Public API
  // ===========================================================================

  public static function get(int $map_id): array {
    return [
      'locations'         => self::posts_to_options(get_field('locations', $map_id)              ?: []),
      'lines'             => self::posts_to_options(get_field('lines', $map_id)                  ?: []),
      'polygons'          => self::posts_to_options(get_field('polygons', $map_id)               ?: []),
      'categories'        => self::resolve_terms(get_field('add_by_category', $map_id)           ?: []),
      'custom_posts'      => self::posts_to_options(get_field('add_custom_posts', $map_id)       ?: []),
      'custom_categories' => self::resolve_terms(get_field('add_by_custom_category', $map_id)   ?: []),
    ];
  }

  public static function save(int $map_id, array $sources): array {
    update_field('locations',              array_map('intval', $sources['locations']         ?? []), $map_id);
    update_field('lines',                  array_map('intval', $sources['lines']             ?? []), $map_id);
    update_field('polygons',               array_map('intval', $sources['polygons']          ?? []), $map_id);
    update_field('add_by_category',        array_map('intval', $sources['categories']        ?? []), $map_id);
    update_field('add_custom_posts',       array_map('intval', $sources['custom_posts']      ?? []), $map_id);
    update_field('add_by_custom_category', array_map('intval', $sources['custom_categories'] ?? []), $map_id);
    return self::get($map_id);
  }

  public static function search(string $type, string $search): array {
    switch ($type) {
      case 'locations':        return self::search_posts('mapster-wp-location', $search);
      case 'lines':            return self::search_posts('mapster-wp-line',     $search);
      case 'polygons':         return self::search_posts('mapster-wp-polygon',  $search);
      case 'categories':       return self::search_terms(['wp-map-category'],   $search);
      case 'custom_posts':     return self::search_custom_posts($search);
      case 'custom_categories':return self::search_custom_categories($search);
      case 'users':            return self::search_users($search);
      default:                 return [];
    }
  }

  // ===========================================================================
  // Search helpers
  // ===========================================================================

  private static function search_posts(string $post_type, string $search): array {
    $query = new WP_Query([
      'post_type'      => $post_type,
      's'              => $search,
      'posts_per_page' => 5,
      'post_status'    => 'publish',
    ]);
    return self::posts_to_options($query->posts);
  }

  private static function search_custom_posts(string $search): array {
    $all   = array_keys(get_post_types(['public' => true]));
    $types = array_values(array_diff($all, self::MAPSTER_POST_TYPES));
    if (empty($types)) return [];
    $query = new WP_Query([
      'post_type'      => $types,
      's'              => $search,
      'posts_per_page' => 5,
      'post_status'    => 'publish',
    ]);
    return self::posts_to_options($query->posts);
  }

  private static function search_terms(array $taxonomies, string $search): array {
    $terms = get_terms([
      'taxonomy'   => $taxonomies,
      'search'     => $search,
      'number'     => 5,
      'hide_empty' => false,
    ]);
    if (is_wp_error($terms)) return [];
    return self::terms_to_options($terms);
  }

  private static function search_users(string $search): array {
    $users = get_users([
      'search'         => "*{$search}*",
      'search_columns' => ['display_name', 'user_login', 'user_email'],
      'number'         => 5,
    ]);
    return array_values(array_map(fn($u) => [
      'value' => $u->ID,
      'label' => $u->display_name,
      'email' => $u->user_email,
    ], $users));
  }

  private static function search_custom_categories(string $search): array {
    $all        = array_keys(get_taxonomies(['public' => true]));
    $taxonomies = array_values(array_diff($all, self::EXCLUDED_TAXONOMIES));
    if (empty($taxonomies)) return [];
    return self::search_terms($taxonomies, $search);
  }

  // ===========================================================================
  // Formatters — convert WP objects to {value, label, meta} for React Select
  // ===========================================================================

  private static function posts_to_options(array $posts): array {
    return array_values(array_map(fn($p) => [
      'value'          => $p->ID,
      'label'          => $p->post_title,
      'post_type'      => $p->post_type,
      'post_type_name' => get_post_type_object($p->post_type)->labels->singular_name ?? $p->post_type,
    ], $posts));
  }

  private static function terms_to_options(array $terms): array {
    return array_values(array_map(fn($t) => [
      'value'    => $t->term_id,
      'label'    => $t->name,
      'taxonomy' => $t->taxonomy,
    ], $terms));
  }

  // add_by_category and add_by_custom_category store raw term IDs in ACF,
  // so we resolve each to a full term object for display.
  private static function resolve_terms(array $term_ids): array {
    $result = [];
    foreach ($term_ids as $id) {
      $term = get_term(intval($id));
      if ($term && !is_wp_error($term)) {
        $result[] = [
          'value'    => $term->term_id,
          'label'    => $term->name,
          'taxonomy' => $term->taxonomy,
        ];
      }
    }
    return $result;
  }

}
