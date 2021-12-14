<?php

require_once dirname( __FILE__ ) . '/api/CnbAppRemote.php';
require_once dirname( __FILE__ ) . '/api/CnbAppRemotePayment.php';
require_once dirname( __FILE__ ) . '/api/CnbAdminCloud.php';
require_once dirname( __FILE__ ) . '/partials/admin-functions.php';
require_once dirname( __FILE__ ) . '/partials/admin-header.php';
require_once dirname( __FILE__ ) . '/partials/admin-footer.php';
require_once dirname( __FILE__ ) . '/domain-edit.php';
require_once dirname( __FILE__ ) . '/domain-upgrade.php';

if(!class_exists('WP_List_Table')) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

function cnb_add_header_domain_overview() {
    echo 'Domains ';
}

function cnb_add_new_domain_action() {
// Only add the "Add new" action in the overview part
    $id = !empty($_GET['id']) ? sanitize_text_field($_GET['id']) : null;
    $action = !empty($_GET['action']) ? sanitize_text_field($_GET['action']) : null;
    if ($id === null || ($action != 'new' && $action != 'edit')) {
        // Create link
        $url = admin_url('admin.php');
        $new_link =
            add_query_arg(
                array(
                    'page' => 'call-now-button-domains',
                    'action' => 'new',
                    'id' => 'new'),
                $url);
        $new_url = esc_url($new_link);

        echo '<a href="' . $new_url . '" class="page-title-action">Add New</a>';
    }
}

class Cnb_Domain_List_Table extends WP_List_Table {

    /**
     * Constructor, we override the parent to pass our own arguments
     * We usually focus on three parameters: singular and plural labels, as well as whether the class supports AJAX.
     */
    function __construct() {
        parent::__construct(array(
            'singular' => 'cnb_list_domain', //Singular label
            'plural' => 'cnb_list_domains', //plural label, also this well be one of the table css class
            'ajax' => false //We won't support Ajax for this table
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
            'type' => __('Type'),
            'expires' => __('Expires'),
            'renew' => __('Renew automatically'),
            'timezone' => __('Timezone'),
            'trackGA' => __('Google Analytics'),
            'trackConversion' => __('Google Ads')
        );
    }

    function get_sortable_columns() {
        return array(
            'name' => array('name', false),
            'type' => array('type', false),
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
            case 'type':
                switch ($item[ $column_name ]) {
                    case 'FREE':
                        return 'Free';
                    case 'PRO':
                        return 'Pro';
                    default:
                        return esc_html($item[$column_name]);
                }
            case 'renew':
            case 'trackGA':
            case 'trackConversion':
                return $item[ $column_name ] ? 'Enabled' : 'Disabled';
            case 'timezone':
            case 'expires':
                return esc_html($item[ $column_name ]);

            default:
                return '<em>Unknown column ' .esc_html($column_name) . '</em>';
        }
    }

    private function get_data() {
        $domains = CnbAppRemote::cnb_remote_get_domains();

        if ($domains instanceof WP_Error) {
            return $domains;
        }

        $data = array();
        foreach ($domains as $domain) {
            $data[] = array(
                'id' => $domain->id,
                'name' => $domain->name,
                'type' => $domain->type,
                'expires' => $domain->expires,
                'renew' => $domain->renew,
                'timezone' => $domain->timezone,
                'trackGA' => $domain->trackGA,
                'trackConversion' => $domain->trackConversion,
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
     *
     * @return string
     */
    function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            $this->_args['singular'],
            esc_attr($item['id'])
        );
    }

    function column_name($item) {
        $actions = array();
        // Let's build a link
        $url = admin_url('admin.php');
        $edit_link =
            add_query_arg(
                array( 'page' => 'call-now-button-domains', 'action' => 'edit', 'id' => $item['id'] ),
                $url );
        $edit_url = esc_url( $edit_link );
        $actions['edit'] = '<a href="'.$edit_url.'">Edit</a>';

        $delete_link = wp_nonce_url(
            add_query_arg( array(
                'page' => 'call-now-button-domains',
                'action' => 'delete',
                'id' => $item['id'] ),
                $url ),
            'cnb_delete_domain' );
        $delete_url = esc_url( $delete_link );
        $actions['delete'] = '<a href="'.$delete_url.'">Delete</a>';

        // If the type is not PRO, offer an upgrade
        if ($item['type'] !== 'PRO') {
            $upgrade_link =
                add_query_arg( array(
                    'page'   => 'call-now-button-domains',
                    'action' => 'upgrade',
                    'id'     => $item['id']
                ),
                $url );
            $upgrade_url        = esc_url( $upgrade_link );
            $actions['upgrade'] = '<a href="' . $upgrade_url . '" style="color: orange">Upgrade!</a>';
        }

        return sprintf(
            '%1$s %2$s',
            '<strong><a class="row-title" href="'.$edit_url.'">'.esc_html($item['name']) . '</a></strong>',
            $this->row_actions($actions)
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
                $domainIds = filter_input(INPUT_POST, 'cnb_list_domain', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
                switch ($this->current_action()) {
                    case 'delete':
                        foreach ($domainIds as $domainId) {
                            $domain = array('id' => $domainId);
                            CnbAppRemote::cnb_remote_delete_domain( $domain );
                        }
                        $notice = new CnbNotice('success', count($domainIds) . ' Domain(s) deleted');
                        CnbAdminNotices::get_instance()->renderNotice($notice);
                        break;
                }
            }
        }
    }

    function no_items() {
        _e( 'No domains found.' );
    }
}

function cnb_delete_domain() {
    if (isset($_REQUEST['_wpnonce']) && !empty($_REQUEST['_wpnonce'])) {
        $id = !empty($_GET['id']) ? sanitize_text_field($_GET['id']) : null;
        $nonce = filter_input(INPUT_GET, '_wpnonce', FILTER_SANITIZE_STRING);
        $action = 'cnb_delete_domain';

        if (wp_verify_nonce($nonce, $action)) {
            $adminNotices = CnbAdminNotices::get_instance();

            $cnb_cloud_notifications = array();
            CnbAdminCloud::cnb_delete_domain( $cnb_cloud_notifications, $id );
            foreach ($cnb_cloud_notifications as $cnb_cloud_notification) {
                $adminNotices->renderNotice($cnb_cloud_notification);
            }
        }
    }
}

/**
 * Main entrypoint, used by `call-now-button.php`.
 */
function cnb_admin_page_domain_overview_render() {
    $id = !empty($_GET['id']) ? sanitize_text_field($_GET['id']) : null;
    if ($id === null) {
        cnb_admin_page_domain_overview_render_list();
        return;
    }

    $action = !empty($_GET['action']) ? sanitize_text_field($_GET['action']) : null;
    switch ($action) {
        case 'new':
        case 'edit':
            cnb_admin_page_domain_edit_render();
            break;
        case 'upgrade':
            cnb_admin_page_domain_upgrade_render();
            break;
        case 'delete':
            cnb_delete_domain();
            cnb_admin_page_domain_overview_render_list();
            break;
    }
}

function cnb_admin_page_domain_overview_render_list() {
    //Prepare Table of elements
    $wp_list_table = new Cnb_Domain_List_Table();
    $data = $wp_list_table->prepare_items();

    add_action('cnb_header_name', 'cnb_add_header_domain_overview');

    if ($data instanceof WP_Error) {
        cnb_api_key_invalid_notice($data);
    } else {
        add_action('cnb_after_header', 'cnb_add_new_domain_action');
    }
    do_action('cnb_header');

    echo '<form id="wp_list_event" method="post">';

    //Table of elements
    $wp_list_table->display();
    echo '</form>';
    do_action('cnb_footer');
}
