<?php
/**
 * Optica Simon: on product search results, if the search term matches one of a product's colors,
 * show that color's photo in the grid and link to the product with that color preselected.
 * Delete this file to undo.
 */
function sox_sv_match($pid) {
    static $cache = array();
    if (isset($cache[$pid])) return $cache[$pid];
    $cache[$pid] = null;
    if (is_admin() || !is_search()) return null;
    global $wp_query;
    if (empty($wp_query->posts)) return null;
    $in_results = false;
    foreach ($wp_query->posts as $p) { if ((int) $p->ID === (int) $pid) { $in_results = true; break; } }
    if (!$in_results) return null;
    $s = strtolower(trim((string) get_search_query(false)));
    if ($s === '') return null;
    $terms = get_the_terms($pid, 'pa_color');
    if (!$terms || is_wp_error($terms)) return null;
    $slugs = array();
    foreach ($terms as $t) {
        $n = strtolower($t->name);
        if (strpos($n, $s) === 0 || strpos($n, ' ' . $s) !== false) $slugs[] = $t->slug;
    }
    if (!$slugs) return null;
    $vars = get_posts(array('post_type' => 'product_variation', 'post_parent' => $pid, 'post_status' => 'publish', 'numberposts' => -1, 'orderby' => 'menu_order ID', 'order' => 'ASC', 'fields' => 'ids'));
    foreach ($vars as $vid) {
        $c = get_post_meta($vid, 'attribute_pa_color', true);
        if (!in_array($c, $slugs, true)) continue;
        $img = (int) get_post_meta($vid, '_thumbnail_id', true);
        if ($img > 0) { $cache[$pid] = array('slug' => $c, 'image' => $img); return $cache[$pid]; }
    }
    return null;
}
add_filter('woocommerce_product_get_image_id', function ($id, $product) {
    $m = sox_sv_match($product->get_id());
    return $m ? $m['image'] : $id;
}, 20, 2);
add_filter('post_thumbnail_id', function ($id, $post = null) {
    $pid = $post ? (is_object($post) ? $post->ID : (int) $post) : get_the_ID();
    if (!$pid || get_post_type($pid) !== 'product') return $id;
    $m = sox_sv_match($pid);
    return $m ? $m['image'] : $id;
}, 20, 2);
add_filter('post_type_link', function ($url, $post) {
    if (!is_search() || $post->post_type !== 'product') return $url;
    $m = sox_sv_match($post->ID);
    return $m ? add_query_arg('attribute_pa_color', $m['slug'], $url) : $url;
}, 20, 2);