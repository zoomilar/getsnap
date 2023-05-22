<?php

class GetSnap_Admin
{

    public function __construct()
    {
        // Registruojame nustatymus, pridedame puslapį, filtruojame aktyvacijos kodą, indeksuojame duomenis ir pridedame stilius bei skriptus
        add_action('admin_init', array($this, 'register_activation_code_setting'));
        add_action('admin_menu', array($this, 'add_plugin_page'));
        add_filter('pre_update_option_getsnap_activation_code', array($this, 'check_activation_code'), 10, 2);
        add_action('admin_post_index_products', array($this, 'index_data'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_styles'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_scripts'));
        add_action('rest_api_init', array($this, 'add_api_routes'));
    }

    public function add_api_routes() {
        register_rest_route('getsnap/v1', '/activate', array(
            'methods' => 'POST',
            'callback' => [$this, 'handle_activation_request'],
        ));

        register_rest_route('getsnap/v1', '/index', array(
            'methods' => 'GET',
            'callback' => [$this, 'handle_indexing_request'],
        ));
    }

    public function handle_activation_request($request) {
        $site_name = $request['site_name'];
        $activation_code = $request['activation_code'];

        // Check activation code
        // In real case, you would make a call to your API or your database to check the activation code
        // I will assume that the activation code is always valid
        // Note that this is a mock, you would have to implement this logic yourself

        if ($activation_code == '123456') {
            return array(
                'success' => true,
                'message' => 'Activation was successful.'
            );
        } else {
            return array(
                'success' => false,
                'message' => 'Invalid activation code.'
            );
        }
    }

    public function handle_indexing_request($request) {
        $products = $request['products'];

        // Index products
        // In real case, you would make a call to your API or your database to index the products
        // I will assume that all products are successfully indexed
        // Note that this is a mock, you would have to implement this logic yourself

        return array(
            'success' => true,
            'message' => 'Indexing was successful.'
        );
    }

    public function getsnap_index_callback(WP_REST_Request $request) {
        // Implement your API logic here
    }

    public function register_activation_code_setting()
    {
        // Registruojame aktyvacijos kodo nustatymą
        register_setting('getsnap-settings-group', 'getsnap_activation_code');
    }

    public function add_plugin_page()
    {
        // Pridedame nustatymų puslapį
        add_options_page(
            'GetSnap Activation',
            'GetSnap',
            'manage_options',
            'getsnap-activation',
            array($this, 'display_activation_form')
        );
    }

    public function display_activation_form()
    {
        // Atvaizduojame aktyvacijos formą ir atsakymą iš API, jei toks yra
        ?>
        <div class="getsnap-container">
            <div class="logo-block">
                <a href="https://getsnap.eu/" target="_blank">
                    <img width="250" src="https://getsnap.eu/wp-content/themes/getsnap/assets/images/logo.svg" alt="">
                </a>
            </div>
            <h2>Visual search suite for your online store</h2>
            <div class="descr">
                Enhance your ecommerce business with visual search features to increase conversion, customer
                satisfaction and average order value. Get competitive advantage with GetSnap
            </div>
            <form method="post" action="options.php">
                <?php settings_fields('getsnap-settings-group'); ?>
                <div class="getsnap_activation-cotnainer">
                    <div>
                        <label for="">Enter access KEY:</label>
                        <input type="text" name="getsnap_activation_code"
                               value="<?php echo esc_attr(get_option('getsnap_activation_code')); ?>"/>

                    </div>
                    <?php submit_button('Save Changes'); ?>
                </div>
            </form>

            <?php
            // Jei aktyvinimo kodas yra, parodyti produkto indeksavimo formą
            if (get_option('getsnap_activation_code')): ?>
                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                    <input type="hidden" name="action" value="index_products">
                    <?php submit_button('Products indexing'); ?>
                </form>
            <?php endif; ?>

            <?php if (!get_option('getsnap_activation_code')): ?>
                <h3>
                    Do not have access KEY?
                    <br>
                    <a href="https://getsnap.eu/my-account/?registration" target="_blank">
                        Click Here
                    </a>
                </h3>
            <?php endif; ?>
        </div>
        <div class="getsnap-container-media">
            <img src="https://getsnap.eu/wp-content/themes/getsnap/assets/images/misc/visual10.svg" alt="">
        </div>
        <?php
        // Gaunam ir išvedam atsakymą iš API, jei jis egzistuoja
        $response = get_option('getsnap_api_response');
        if ($response) {
            echo '<div class="notice notice-info is-dismissible"><p>' . $response . '</p></div>';
            delete_option('getsnap_api_response');
        }
        ?>
        <?php
    }

    public function check_activation_code($new_value, $old_value)
    {
        // Tikriname aktyvacijos kodą su API ir tvarkome atsakymą
        $url = 'https://getsnap.bugzag.lt/postme.php';

        $args = array(
            'method' => 'POST',
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
            'body' => json_encode(array(
                'site_name' => sanitize_text_field(get_bloginfo('name')),
                'activation_code' => sanitize_text_field($new_value), // используйте новое значение
            )),
        );

        $response = wp_remote_post($url, $args);

        // Patikrinam, ar nėra klaidų
        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();

            if ($error_message != 'Request received successfully') {
                add_settings_error('getsnap_activation_code', 'getsnap_activation_code_error', "Ошибка: $error_message", 'error');
                return false; // grąžina false, kad nebūtų atnaujintas nustatymas
            }
        }

        // Atsakymo iš API tvarkymas
        $body = wp_remote_retrieve_body($response);

        $data = json_decode($body);

        if ($data->success) {
            return sanitize_text_field($new_value); // grąžina naują reikšmę, kad atnaujintumėte nustatymą
        } else {
            add_settings_error('getsnap_activation_code', 'getsnap_activation_code_error', 'Aktyvacijos klaida: ' . sanitize_text_field($data->message), 'error');
            return false; // grąžina false, kad nebūtų atnaujintas nustatymas
        }

        if ($data->success) {
            update_option('getsnap_activation_code ', true);
        } else {
            update_option('getsnap_activation_code ', false);
        }

    }

    public function index_data()
    {
        // Tikriname, ar WooCommerce yra aktyvuotas, gauname visus produktus, peržiūrime juos, gauname produktų duomenis ir siunčiame juos į API
        if (!class_exists('WooCommerce')) {
            return;
        }

        // Gaunam visus produktus
        $args = array(
            'post_type' => 'product',
            'posts_per_page' => -1
        );

        $products = get_posts($args);
        $products_data = array();

        foreach ($products as $product_post) {
            $product = wc_get_product($product_post->ID);

            $product_data = array(
                'id' => $product->get_id(),
                'name' => $product->get_name(),
                'url' => $product->get_permalink(),
                'images' => $this->get_product_images($product),
                'category' => $this->get_product_category($product),
                'price' => $product->get_price(),
            );

            $products_data[] = $product_data;
        }

        // Siųsti duomenis į API serverį ir grąžinti atsakymą
        $response = $this->send_products_to_api($products_data);

        // Jei yra klaida, pridėkite klaidos pranešimą
        if (isset($response->error)) {
            add_settings_error('getsnap_indexing', 'getsnap_indexing_error', $response->error, 'error');
            // Индексация не была успешной, удаляем опцию
            delete_option('getsnap_indexing_success');
        } else {
            add_settings_error('getsnap_indexing', 'getsnap_indexing_success', 'Индексация успешно завершена', 'updated');
            //update_option('getsnap_indexing_success', true);
        }

        // Peradresuokite atgal į nustatymų puslapį
        wp_redirect(admin_url('options-general.php?page=getsnap-activation'));
        exit;
    }

    public function get_product_images($product)
    {
        // Gauname produkto paveikslėlius
        $images = array();
        $attachment_ids = $product->get_gallery_image_ids();

        // Pridedamas pagrindinis produkto vaizdas
        $images[] = esc_url_raw(wp_get_attachment_url($product->get_image_id()));

        // Pridedam likusius vaizdus
        foreach ($attachment_ids as $attachment_id) {
            $images[] = esc_url_raw(wp_get_attachment_url($attachment_id));
        }

        // Vaizdų skaičius iki 5
        return array_slice($images, 0, 5);
    }

    public function get_product_category($product)
    {
        // Gauname produkto kategoriją
        $terms = get_the_terms($product->get_id(), 'product_cat');

        if (!empty($terms)) {
            return sanitize_text_field($terms[0]->name);
        } else {
            return '';
        }
    }

    public function send_products_to_api($products_data)
    {
        // Siunčiame produktų duomenis į API ir tvarkome atsakymą
        $products_data = [
            'products' => $products_data
        ];

        // API URL
        $url = 'https://getsnap.bugzag.lt/postme.php';

        // POST užklausos parametrai
        $args = array(
            'method' => 'POST',
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
            'body' => json_encode($products_data)
        );

        // POST užklausos siuntimas
        $response = wp_remote_post($url, $args);

        // Patikrinkite, ar nėra klaidų
        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            update_option('getsnap_api_response', "Ошибка при индексации данных: $error_message");
            delete_option('getsnap_indexing_success');
        } else {
            // Atsakymo iš API tvarkymas
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body);

            if (!$data->success) {
                update_option('getsnap_api_response', 'Ошибка при индексации данных: ' . $data->message);
                delete_option('getsnap_indexing_success');
            } else {
                update_option('getsnap_api_response', $data->message);
                update_option('getsnap_indexing_success', true);

                if ($data->message == 'Database is being indexed, please wait.') {
                    delete_option('getsnap_indexing_success');
                }
            }
        }

        wp_redirect(admin_url('options-general.php?page=getsnap-activation'));
        exit;
    }

    public function enqueue_styles()
    {
        // Įtraukiame stilius
        global $pagenow;
        $screen = get_current_screen();
        if ($pagenow == 'options-general.php' && $screen->id == 'settings_page_getsnap-activation') {
            wp_enqueue_style('getsnap-admin-styles', plugin_dir_url(__FILE__) . 'css/getsnap-admin.css');
        }
    }

    public function enqueue_scripts()
    {
        // Įtraukiame skriptus
        global $pagenow;
        $screen = get_current_screen();
        if ($pagenow == 'options-general.php' && $screen->id == 'settings_page_getsnap-activation') {
            wp_enqueue_script('getsnap-admin-scripts', plugin_dir_url(__FILE__) . 'js/getsnap-admin.js', array('jquery'), '1.0.0', true);
        }
    }

    public function enqueue_frontend_scripts()
    {
        // Tikriname, ar aktyvacijos kodas yra ir ar buvo atliktas sėkmingas produktų indeksavimas, tada įtraukiame skriptus
        if (get_option('getsnap_activation_code') && get_option('getsnap_indexing_success')) {
            wp_enqueue_script('getsnap-frontend-scripts', 'https://getsnap.eu/api?key=' . get_option('getsnap_activation_code'), array('jquery'), '1.0.0', true);
        }
    }

}