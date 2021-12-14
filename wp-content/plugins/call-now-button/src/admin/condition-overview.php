<?php

require_once dirname( __FILE__ ) . '/api/CnbAppRemote.php';
require_once dirname( __FILE__ ) . '/api/CnbAdminCloud.php';
require_once dirname( __FILE__ ) . '/partials/admin-functions.php';
require_once dirname( __FILE__ ) . '/partials/admin-header.php';
require_once dirname( __FILE__ ) . '/partials/admin-footer.php';
require_once dirname( __FILE__ ) . '/condition-edit.php';

if(!class_exists('WP_List_Table')) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

function cnb_add_header_condition_overview() {
    echo 'Conditions ';
}

function cnb_conditions_after_header() {
// Only add the "Add new" action in the overview part
    $id = !empty($_GET['id']) ? sanitize_text_field($_GET['id']) : null;
    $action = !empty($_GET['action']) ? sanitize_text_field($_GET['action']) : null;
    $bid = !empty($_GET['bid']) ? sanitize_text_field($_GET['bid']) : null;
    if ($id === null || ($action != 'new' && $action != 'edit')) {
        // Create link
        $url = admin_url('admin.php');
        $new_link =
            add_query_arg(
                array(
                    'page' => 'call-now-button-conditions',
                    'action' => 'new',
                    'id' => 'new',
                    'bid' => $bid),
                $url);
        $new_url = esc_url($new_link);

        echo '<a href="' . $new_url . '" class="page-title-action">Add New</a>';
    }
}

class Cnb_Condition_List_Table extends WP_List_Table {

    /**
     * CallNowButton Condition object
     *
     * @since v0.5.5
     * @var object
     */
    public $button;

    /**
     * Constructor, we override the parent to pass our own arguments
     * We usually focus on three parameters: singular and plural labels, as well as whether the class supports AJAX.
     * @param array $args
     */
    function __construct( $args = array() ) {
        if (isset($args['button'])) {
            $this->button = $args['button'];
        }

        parent::__construct(array(
            'singular' => 'cnb_list_condition', //Singular label
            'plural' => 'cnb_list_conditions', //plural label, also this well be one of the table css class
            'ajax' => false //We won't support Ajax for this table
        ));
    }

    /**
     * Define the columns that are going to be used in the table
     * @return array $columns, the array of columns to use with the table
     */
    function get_columns() {
        $columns = array(
            'cb' => '<input type="checkbox">',
            'id' => __('ID'),
            'conditionType' => __('Type'),
            'filterType' => __('Filter type'),
            'matchType' =>  __('Match type'),
            'matchValue' => __('Match value'),
        );
        if ($this->button) { unset($columns['cb']); }
        return $columns;
    }

    function get_sortable_columns() {
        return array(
            'conditionType' => array('conditionType', false),
            'filterType' => array('filterType', false),
            'matchType' => array('matchType', false),
            'matchValue' => array('matchValue', false),
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
            $this->_column_headers = array($columns, $hidden_columns, $sortable_columns, 'conditionType');

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
            case 'conditionType':
            case 'filterType':
            case 'matchType':
                return '<code>' . esc_html($item[ $column_name ]) . '</code>';
            case 'matchValue':
                return !empty($item[$column_name]) ? esc_html($item[$column_name]) : '<em>No value</em>';
            default:
                return '<em>Unknown column ' .esc_html($column_name) . '</em>';
        }
    }

    private function get_data() {
        $entities = array();
        if ($this->button === null) {
            $entities = CnbAppRemote::cnb_remote_get_conditions();
        } else {
            // Find ConditionIDs for Button
            $button = CnbAppRemote::cnb_remote_get_button( $this->button->id );
            if ($button instanceof WP_Error) {
                return $button;
            }

            foreach ($button->conditions as $entityId) {
                $entities[] = CnbAppRemote::cnb_remote_get_condition( $entityId );
            }
        }

        if ($entities instanceof WP_Error) {
            return $entities;
        }

        $data = array();
        foreach ($entities as $entity) {
            $data[] = array(
                'id' => $entity->id,
                'conditionType' => $entity->conditionType,
                'filterType' => $entity->filterType,
                'matchType' => $entity->matchType,
                'matchValue' => $entity->matchValue,
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
        $orderby = !empty($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : 'conditionType';
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
            esc_attr($item['id'])
        );
    }

    function column_conditionType($item) {
        $bid = $this->button !== null ? $this->button->id : null;
        $tab = $this->button !== null ? 'visibility' : null;

        // Let's build a link
        $url = admin_url('admin.php');
        $edit_link =
            add_query_arg(
                array(
                    'page' => 'call-now-button-conditions',
                    'action' => 'edit',
                    'id' => $item['id'],
                    'bid' => $bid,
                    'tab' => $tab),
                $url );
        $edit_url = esc_url( $edit_link );
        $actions = array(
            'edit' => '<a href="'.$edit_url.'">Edit</a>',
        );

        $delete_link = wp_nonce_url(
            add_query_arg( array(
                'page' => 'call-now-button-conditions',
                'action' => 'delete',
                'id' => $item['id'],
                'bid' => $bid ),
                $url ),
            'cnb_delete_condition' );
        $delete_url = esc_url( $delete_link );
        $actions['delete'] = '<a href="'.$delete_url.'">Delete</a>';

        $value = !empty($item['conditionType']) ? $item['conditionType'] : '<em>No value</em>';
        return sprintf(
            '%1$s %2$s',
            '<strong><a class="row-title" href="'.$edit_url.'">'.esc_html($value) . '</a></strong>',
            $this->row_actions($actions)
        );
    }

    function get_bulk_actions() {
        // Hide Bulk Actions if we're on the Button edit page
        if ($this->button) { return array(); }
        return array(
            'delete'    => 'Delete',
        );
    }

    function process_bulk_action() {
        if ( isset( $_REQUEST['_wpnonce'] ) && ! empty( $_REQUEST['_wpnonce'] ) ) {
            $nonce  = filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING );
            $action = 'bulk-' . $this->_args['plural'];

            if ( wp_verify_nonce( $nonce, $action ) ) {
                $entityIds = filter_input(INPUT_POST, 'cnb_list_condition', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
                switch ($this->current_action()) {
                    case 'delete':
                        foreach ($entityIds as $entityId) {
                            $entity = array('id' => $entityId);
                            CnbAppRemote::cnb_remote_delete_condition( $entity );
                        }
                        CnbAdminNotices::get_instance()->renderSuccess(count($entityIds) . ' Condition(s) deleted.');
                        break;
                }
            }
        }
    }
}

function cnb_delete_condition() {
    if (isset($_REQUEST['_wpnonce']) && !empty($_REQUEST['_wpnonce'])) {
        $id = !empty($_GET['id']) ? sanitize_text_field($_GET['id']) : null;
        $nonce = filter_input(INPUT_GET, '_wpnonce', FILTER_SANITIZE_STRING);
        $action = 'cnb_delete_condition';

        if (wp_verify_nonce($nonce, $action)) {
            $cnb_cloud_notifications = array();
            // If a button is set, remove this ID from the conditions array
            $bid = !empty($_GET['bid']) ? sanitize_text_field($_GET['bid']) : null;
            if ($bid !== null) {
                // Get the button
                $button = CnbAppRemote::cnb_remote_get_button( $bid );

                // Remove the current Condition
                $pos = array_search($id, $button->conditions);
                unset($button->conditions[$pos]);

                // Convert to array and update
                $button_array = json_decode(json_encode($button), true);
                CnbAdminCloud::cnb_update_button( $cnb_cloud_notifications, $button_array );
            }

            CnbAdminCloud::cnb_delete_condition( $cnb_cloud_notifications, $id );
            $adminNotices = CnbAdminNotices::get_instance();

            foreach ($cnb_cloud_notifications as $cnb_cloud_notification) {
                $adminNotices->renderNotice($cnb_cloud_notification);
            }
        }
    }

    function no_items() {
        _e( 'No conditions found.' );
    }
}

/**
 * Main entrypoint, used by `call-now-button.php`.
 */
function cnb_admin_page_condition_overview_render() {
    $id = !empty($_GET['id']) ? sanitize_text_field($_GET['id']) : null;
    if ($id === null) {
        cnb_admin_page_condition_overview_render_list();
        return;
    }

    $action = !empty($_GET['action']) ? sanitize_text_field($_GET['action']) : null;
    switch ($action) {
        case 'new':
        case 'edit':
            cnb_admin_page_condition_edit_render();
            break;
        case 'delete':
            cnb_delete_condition();
            cnb_admin_page_condition_overview_render_list();
            break;
    }
}

/**
 * @param $cnb_notices CnbNotice[]
 *
 * @return array
 */
function cnb_admin_page_condition_overview_bid(&$cnb_notices) {
    $bid = !empty($_GET['bid']) ? sanitize_text_field($_GET['bid']) : null;
    $args = array();
    $button = null;
    if ($bid !== null) {
        $button = CnbAppRemote::cnb_remote_get_button( $bid );
    }

    if ($button && !($button instanceof WP_Error)) {
        $args['button'] = $button;
        $notice = new CnbNotice('info', 'Only conditions for Button ID <code>'.esc_html($button->id).'</code> (<strong>'.esc_html($button->name).'</strong>) are shown.');
        $cnb_notices[] = $notice;
    }
    return $args;
}

function cnb_admin_page_condition_overview_render_list() {
    $cnb_notices = cnb_get_notices();

    $args = cnb_admin_page_condition_overview_bid($cnb_notices);
    //Prepare Table of elements
    $wp_list_table = new Cnb_Condition_List_Table($args);
    $data = $wp_list_table->prepare_items();

    add_action('cnb_header_name', 'cnb_add_header_condition_overview');

    if ($data instanceof WP_Error) {
        cnb_api_key_invalid_notice($data);
    } else {
        add_action('cnb_after_header', 'cnb_conditions_after_header');
    }
    do_action('cnb_header');

    echo '<form id="wp_list_event" method="post">';

    //Table of elements
    $wp_list_table->display();
    echo '</form>';
    do_action('cnb_footer');
}
