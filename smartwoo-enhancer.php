<?php
/*
Plugin Name: SmartWoo Enhancer
Description: WooCommerce mağazalarınızı daha verimli ve kullanıcı dostu hale getiren gelişmiş özellikler.
Version: 1.0
Author: [Senin İsmin veya Marka Adın]
Text Domain: smartwoo-enhancer
Domain Path: /languages
*/

// Eklenti etkinleştirildiğinde çalışacak fonksiyonlar
function swe_activate() {
    // Gerekli ayarları oluşturabiliriz.
}
register_activation_hook(__FILE__, 'swe_activate');

// Eklenti devre dışı bırakıldığında çalışacak fonksiyonlar
function swe_deactivate() {
    // Geçici verileri temizleyebiliriz.
}
register_deactivation_hook(__FILE__, 'swe_deactivate');

// Temel dosya ve fonksiyonların yüklenmesi
function swe_init() {
    // Eklentiye özel işlemler buraya.
}
add_action('plugins_loaded', 'swe_init');

// WooCommerce Ayarlarına Yeni Sekme Ekliyoruz
add_filter('woocommerce_get_settings_products', 'swe_add_similar_products_setting', 10, 2);
function swe_add_similar_products_setting($settings, $current_section) {
    if ($current_section === '') {
        $settings[] = array(
            'title' => __('Benzer Ürünler Ayarları', 'smartwoo-enhancer'),
            'type'  => 'title',
            'desc'  => __('Benzer ürünlerde gösterilecek maksimum ürün sayısını belirleyin.', 'smartwoo-enhancer'),
            'id'    => 'swe_similar_products_settings'
        );

        $settings[] = array(
            'title'    => __('Benzer Ürün Sayısı', 'smartwoo-enhancer'),
            'id'       => 'swe_similar_products_count',
            'type'     => 'number',
            'desc'     => __('Kaç adet benzer ürün gösterilsin?', 'smartwoo-enhancer'),
            'desc_tip' => true,
            'default'  => 4,
            'css'      => 'min-width:300px;',
        );

        $settings[] = array(
            'type' => 'sectionend',
            'id'   => 'swe_similar_products_settings'
        );
    }

    return $settings;
}

// Benzer Ürün Sayısını Uygula
add_filter('woocommerce_output_related_products_args', 'swe_modify_related_products_args');
function swe_modify_related_products_args($args) {
    $count = get_option('swe_similar_products_count', 4); // Varsayılan 4
    $args['posts_per_page'] = (int) $count;
    return $args;
}

// Admin Menüsüne Ayarlar Sayfası Ekliyoruz
add_action('admin_menu', 'swe_add_admin_menu');
function swe_add_admin_menu() {
    add_menu_page(
        __('SmartWoo Enhancer Ayarları', 'smartwoo-enhancer'),
        __('SmartWoo Enhancer', 'smartwoo-enhancer'),
        'manage_options',
        'smartwoo-enhancer',
        'swe_settings_page',
        'dashicons-admin-generic',
        81
    );
}

// Ayarlar Sayfası İçeriği
function swe_settings_page() {
    ?>
    <div class="wrap">
        <h1><?php _e('SmartWoo Enhancer Ayarları', 'smartwoo-enhancer'); ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('swe_settings_group');
            do_settings_sections('smartwoo-enhancer');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Ayarlar ve Alanlar Kayıt
add_action('admin_init', 'swe_register_settings');
function swe_register_settings() {
    register_setting('swe_settings_group', 'swe_exclude_out_of_stock');

    add_settings_section(
        'swe_general_settings',
        __('Genel Ayarlar', 'smartwoo-enhancer'),
        null,
        'smartwoo-enhancer'
    );

    add_settings_field(
        'swe_exclude_out_of_stock',
        __('Stok Biten Ürünleri Gösterme', 'smartwoo-enhancer'),
        'swe_exclude_out_of_stock_field',
        'smartwoo-enhancer',
        'swe_general_settings'
    );
}

function swe_exclude_out_of_stock_field() {
    $value = get_option('swe_exclude_out_of_stock', 'no');
    ?>
    <input type="checkbox" name="swe_exclude_out_of_stock" value="yes" <?php checked($value, 'yes'); ?> />
    <?php
}

// Stok Biten Ürünleri Gösterme Özelliğini Kontrol Et
add_filter('woocommerce_related_products_args', 'swe_control_out_of_stock_products');
function swe_control_out_of_stock_products($args) {
    $exclude_out_of_stock = get_option('swe_exclude_out_of_stock', 'no');
    if ($exclude_out_of_stock === 'yes') {
        $args['meta_query'] = isset($args['meta_query']) ? $args['meta_query'] : array();
        $args['meta_query'][] = array(
            'key'     => '_stock_status',
            'value'   => 'instock',
            'compare' => '='
        );
    }
    return $args;
}
