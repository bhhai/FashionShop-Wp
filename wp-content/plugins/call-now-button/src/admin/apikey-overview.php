<?php

require_once dirname( __FILE__ ) . '/api/CnbAppRemote.php';
require_once dirname( __FILE__ ) . '/api/CnbAdminCloud.php';
require_once dirname( __FILE__ ) . '/partials/admin-functions.php';
require_once dirname( __FILE__ ) . '/partials/admin-header.php';
require_once dirname( __FILE__ ) . '/partials/admin-footer.php';

if(!class_exists('WP_List_Table')) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

function cnb_add_header_apikey_overview() {
    echo 'API keys ';
}

function cnb_add_new_apikey_modal_action() {
    $url = admin_url('admin.php');
    $new_link =
        add_query_arg(
            array(
                'TB_inline' => 'true',
                'inlineId' => 'cnb-add-new-apikey-modal',
                'height' => '150',
                'page' => 'call-now-button-apikeys',
                'action' => 'new',
                'id' => 'new' ),
            $url );
    printf(
        '<a href="%s" title="%s" class="thickbox open-plugin-details-modal page-title-action" data-title="%s">%s</a>',
        $new_link,
        __('Create new API key', CNB_NAME),
        __('Create new API key', CNB_NAME),
        __('Add New', CNB_NAME)
    );
}

/**
 * This is called to create the Domain
 */
function cnb_admin_page_apikey_create_process() {
    global $cnb_slug_base;
    $nonce  = filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING );
    if( isset( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( $nonce, 'cnb_create_apikey') ) {

        // sanitize the input
        $apikey_data = filter_input(
            INPUT_POST,
            'apikey',
            FILTER_SANITIZE_STRING,
            FILTER_REQUIRE_ARRAY);

        $apikey = array();
        $apikey['name'] = sanitize_text_field( $apikey_data['name'] );

        // do the processing
        $cnb_cloud_notifications = array();
        $new_apikey = CnbAdminCloud::cnb_create_apikey( $cnb_cloud_notifications, $apikey );

        // redirect the user to the appropriate page
        $transient_id = 'cnb-' . wp_generate_uuid4();
        set_transient($transient_id, $cnb_cloud_notifications, HOUR_IN_SECONDS);

        // Create link
        $url = admin_url('admin.php');
        $redirect_link =
            add_query_arg(
                array(
                    'page' => 'call-now-button-apikeys',
                    'id' => $new_apikey->id,
                    'tid' => $transient_id),
                $url );
        $redirect_url = esc_url_raw( $redirect_link );
        wp_safe_redirect($redirect_url);
        exit;
    }
    else {
        wp_die( __( 'Invalid nonce specified', CNB_NAME), __( 'Error', CNB_NAME), array(
            'response' 	=> 403,
            'back_link' => 'admin.php?page=' . $cnb_slug_base,
        ) );
    }
}

class Cnb_Apikey_List_Table extends WP_List_Table {

    /**
     * Constructor, we override the parent to pass our own arguments
     * We usually focus on three parameters: singular and plural labels, as well as whether the class supports AJAX.
     */
    function __construct() {
        parent::__construct(array(
            'singular' => 'cnb_list_apikey', //Singular label
            'plural' => 'cnb_list_apikeys', //plural label, also this well be one of the table css class
            'ajax' => false //We don't support Ajax for this table
        ));
    }

    /**
     * Define the columns that are going to be used in the table
     * @return array $columns, the array of columns to use with the table
     */
    function get_columns() {
        return array(
            'cb' => '<input type="checkbox">',
            'id' => __('ID'),
            'name' => __('Name'),
            'created' => __('Created'),
            'lastUsed' => __('Last used'),
            'updateTime' => __('Last updated'),
        );
    }

    function get_sortable_columns() {
        return array(
            'name' => array('name', false),
            'created' => array('created', false),
            'lastUsed' => array('lastUsed', false),
            'updateTime' => array('updateTime', false),
        );
    }

    function get_hidden_columns() {
        return array('id');
    }

    function prepare_items() {
        // Process any Bulk actions before gathering data
        $this->process_bulk_action();

        /* -- Preparing your query -- */
        $data = $this->get_data();

        if ($data instanceof WP_Error) {
            return $data;
        }

        /* -- Ordering parameters -- */
        //Parameters that are going to be used to order the result
        usort( $data, array( &$this, 'sort_data' ) );

        /* -- Pagination parameters -- */
        //Number of elements in your table?
        $totalitems = count($data); //return the total number of affected rows
        $per_page = 20; //How many to display per page?
        //Which page is this?
        $current_page = !empty($_GET['paged']) ? (int)sanitize_text_field($_GET['paged']) : 1;

        //Page Number
        if (empty($current_page) || !is_numeric($current_page) || $current_page <= 0) {
            $current_page = 1;
        }

        //How many pages do we have in total?
        $totalpages = ceil($totalitems / $per_page); //adjust the query to take pagination into account
        if (!empty($current_page) && !empty($per_page)) {
            $offset = ($current_page - 1) * $per_page;

            /* -- Register the pagination -- */
            $this->set_pagination_args(array(
                'total_items' => $totalitems,
                'total_pages' => $totalpages,
                'per_page' => $per_page,
            ));
            //The pagination links are automatically built according to those parameters

            /* -- Register the Columns -- */
            $columns = $this->get_columns();
            $hidden_columns = $this->get_hidden_columns();
            $sortable_columns = $this->get_sortable_columns();
            $this->_column_headers = array($columns, $hidden_columns, $sortable_columns, 'name');

            /* -- Register the items -- */
            $data = array_slice($data,$offset,$per_page);
            $this->items = $data;
        }
        return null;
    }

    function column_default( $item, $column_name )
    {
        switch( $column_name ) {
            case 'id':
                return '<code>' . esc_html($item[ $column_name ]) . '</code>';
            case 'name':
                return '<strong>' . esc_html($item[ $column_name ]) . '</strong>';
            case 'created':
            case 'lastUsed':
            case 'updateTime':
                return esc_html(cnb_timestamp_to_string($item[ $column_name ]));
            default:
                return '<em>Unknown column ' .esc_html($column_name) . '</em>';
        }
    }

    private function get_data() {
        $apikeys = CnbAppRemote::cnb_remote_get_apikeys();

        if ($apikeys instanceof WP_Error) {
            return $apikeys;
        }

        $data = array();
        foreach ($apikeys as $apikey) {
            $data[] = array(
                'id' => $apikey->id,
                'name' => $apikey->name,
                'created' => $apikey->created,
                'lastUsed' => $apikey->lastUsed,
                'updateTime' => $apikey->updateTime,
            );
        }
        return $data;
    }

    /**
     * Allows you to sort the data by the variables set in the $_GET
     *
     * @return Mixed
     */
    private function sort_data( $a, $b ) {
        // If orderby is set, use this as the sort column
        $orderby = !empty($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : 'name';
        // If order is set use this as the order
        $order = !empty($_GET['order']) ? sanitize_text_field($_GET['order']) : 'asc';

        $result = strcmp( $a[$orderby], $b[$orderby] );

        if($order === 'asc') {
            return $result;
        }
        return -$result;
    }

    /**
     * Custom action for `cb` columns (checkboxes)
     *
     * @param array|object $item
     * @return string|void
     */
    function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            $this->_args['singular'],
            esc_html($item['id'])
        );
    }

    function get_bulk_actions() {
        return array(
            'delete'    => 'Delete',
        );
    }

    function process_bulk_action() {
        if ( isset( $_REQUEST['_wpnonce'] ) && ! empty( $_REQUEST['_wpnonce'] ) ) {
            $nonce  = filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING );
            $action = 'bulk-' . $this->_args['plural'];

            if ( wp_verify_nonce( $nonce, $action ) ) {
                $apikeyIds = filter_input(INPUT_POST, 'cnb_list_apikey', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
                switch ($this->current_action()) {
                    case 'delete':
                        foreach ($apikeyIds as $apikeyId) {
                            $apikey = array('id' => $apikeyId);
                            CnbAppRemote::cnb_remote_delete_apikey( $apikey );
                        }
                        CnbAdminNotices::get_instance()->renderSuccess('<p>' . count($apikeyIds) . ' Api key(s) deleted.</p>');
                        break;
                }
            }
        }
    }

    function no_items() {
        _e( 'No API keys found.' );
    }
}

function cnb_delete_apikey() {
    if (isset($_REQUEST['_wpnonce']) && !empty($_REQUEST['_wpnonce'])) {
        $id = !empty($_GET['id']) ? sanitize_text_field($_GET['id']) : null;
        $nonce = filter_input(INPUT_GET, '_wpnonce', FILTER_SANITIZE_STRING);
        $action = 'cnb_delete_apikey';

        if (wp_verify_nonce($nonce, $action)) {
            $adminNotices = CnbAdminNotices::get_instance();
            $cnb_cloud_notifications = array();
            CnbAdminCloud::cnb_delete_apikey( $cnb_cloud_notifications, $id );
            foreach ($cnb_cloud_notifications as $cnb_cloud_notification) {
                $adminNotices->renderNotice($cnb_cloud_notification);
            }
        }
    }
}
/**
 * Main entrypoint, used by `call-now-button.php`.
 */
function cnb_admin_page_apikey_overview_render() {
    $id = !empty($_GET['id']) ? sanitize_text_field($_GET['id']) : null;
    $action = !empty($_GET['action']) ? sanitize_text_field($_GET['action']) : null;

    if ($id === null || $action === null) {
        cnb_admin_page_apikey_overview_render_list();
        return;
    }

    switch ($action) {
        case 'delete':
            cnb_delete_apikey();
            cnb_admin_page_apikey_overview_render_list();
            break;
    }
}

function cnb_admin_page_apikey_overview_render_list() {
    //Prepare Table of elements
    $wp_list_table = new Cnb_Apikey_List_Table();
    $data = $wp_list_table->prepare_items();

    add_action('cnb_header_name', 'cnb_add_header_apikey_overview');

    if ($data instanceof WP_Error) {
        cnb_api_key_invalid_notice($data);
    } else {
        add_action('cnb_after_header', 'cnb_add_new_apikey_modal_action');
    }

    do_action('cnb_header');

    echo '<form id="wp_list_event" method="post">';

    //Table of elements
    $wp_list_table->display();
    echo '</form>';
    cnb_admin_page_render_thickbox();

    do_action('cnb_footer');
}

function cnb_admin_page_render_thickbox() {
    add_thickbox();
    ?>
    <div id="cnb-add-new-apikey-modal" style="display:none;"><div>
    <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post">
        <input type="hidden" name="page" value="call-now-button-apikeys" />
        <input type="hidden" name="action" value="cnb_create_apikey" />
        <input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('cnb_create_apikey')?>" />

        <label>Name
        <input type="text" name="apikey[name]" />
        </label>
        <?php submit_button(); ?>
    </div></div>
<?php }
