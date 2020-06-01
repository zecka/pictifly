<?php
add_filter('rocket_page_has_hebp_files', 'bo_rocket_imgix_webp_support', 10, 2);
function bo_rocket_imgix_webp_support($has_webp, $html) {
    if ($has_webp) {
        return $has_webp;
    }
    return (strpos($html, 'fm=webp') !== false || strpos($html, '.webp')  !== false );
}
