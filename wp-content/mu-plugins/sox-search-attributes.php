<?php
/**
 * Optica Simon: product search also matches attributes (color, lens, size, etc.), brand categories and gender tags.
 * Delete this file to undo.
 */
add_filter('posts_search', function ($search, $q) {
    global $wpdb;
    if (is_admin() && !wp_doing_ajax()) return $search;
    $s = trim((string) $q->get('s'));
    if ($s === '' || $search === '') return $search;
    $pt = $q->get('post_type');
    $is_product = ($pt === 'product') || (is_array($pt) && in_array('product', $pt, true));
    if (!$is_product) return $search;
    if (!preg_match('/^\s*AND\s*\((.*)\)\s*$/s', $search, $m)) return $search;
    $taxes = array_filter(get_object_taxonomies('product'), function ($t) {
        return strpos($t, 'pa_') === 0 || in_array($t, array('product_cat', 'product_tag'), true);
    });
    if (!$taxes) return $search;
    $in = "'" . implode("','", array_map('esc_sql', $taxes)) . "'";
    $like_start = $wpdb->esc_like($s) . '%';
    $like_word  = '% ' . $wpdb->esc_like($s) . '%';
    $exists = $wpdb->prepare("EXISTS (SELECT 1 FROM {$wpdb->term_relationships} tr INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id INNER JOIN {$wpdb->terms} t ON t.term_id = tt.term_id WHERE tr.object_id = {$wpdb->posts}.ID AND tt.taxonomy IN ($in) AND (t.name LIKE %s OR t.name LIKE %s))", $like_start, $like_word);
    return ' AND ((' . $m[1] . ') OR ' . $exists . ') ';
}, 20, 2);