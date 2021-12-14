<?php

require_once dirname( __FILE__ ) . '/api/CnbAppRemote.php';
require_once dirname( __FILE__ ) . '/api/CnbAdminCloud.php';
require_once dirname( __FILE__ ) . '/partials/admin-functions.php';
require_once dirname( __FILE__ ) . '/partials/admin-header.php';
require_once dirname( __FILE__ ) . '/partials/admin-footer.php';
require_once dirname( __FILE__ ) . '/action-edit.php';

if(!class_exists('WP_List_Table')) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

function cnb_add_header_action_overview() {
    echo 'Actions ';
}

function cnb_action_after_header() {
// Only add the "Add new" action in the overview part
    $id = !empty($_GET['id']) ? sanitize_text_field($_GET['id']) : null;
    $action = !empty($_GET['action']) ? sanitize_text_field($_GET['action']) : null;
    $bid = !empty($_GET["bid"]) ? sanitize_text_field($_GET["bid"]) : null;
    if ($id === null || ($action != 'new' && $action != 'edit')) {
        // Create link
        $url = admin_url('admin.php');
        $new_link =
            add_query_arg(
                array(
                    'page' => 'call-now-button-actions',
                    'action' => 'new',
                    'id' => 'new',
                    'bid' => $bid),
                $url);
        $new_url = esc_url($new_link);

        echo '<a href="' . $new_url . '" class="page-title-action">Add New</a>';
    }
}

class Cnb_Action_List_Table extends WP_List_Table {

    /**
     * CallNowButton Button object
     *
     * @since v0.5.1
     * @var object
     */
    public $button;

    /**
     * Constructor, we override the parent to pass our own arguments
     * We usually focus on three parameters: singular and plural labels, as well as whether the class supports AJAX.
     * @param $args array (can contain at least a single key called 'button')
     */
    function __construct( $args = array() ) {
        if (isset($args['button'])) {
            $this->button = $args['button'];
        }

        parent::__construct(array(
            'singular' => 'cnb_list_action', //Singular label
            'plural' => 'cnb_list_actions', //plural label, also this well be one of the table css class
            'ajax' => true // We support it, see _js_vars()
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
            'actionType' => __('Type'),
            'actionValue' => __('Value'),
            'labelText' =>  __('Label'),
        );
        if ($this->button) { unset($columns['cb']); }
        return $columns;
    }

    function get_sortable_columns() {
        return array(
            'actionType' => array('actionType', false),
            'actionValue' => array('actionValue', false),
            'labelText' => array('labelText', false),
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
            $this->_column_headers = array($columns, $hidden_columns, $sortable_columns, 'actionType');

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
            case 'actionValue':
            case 'labelText':
                return !empty($item[$column_name]) ? esc_html($item[$column_name]) : '<em>No value</em>';
            // Handled by column_actionType
            case 'actionType':
                return null;
            default:
                return '<em>Unknown column ' .esc_html($column_name) . '</em>';
        }
    }

    private function get_data() {
        $actions = array();
        if ($this->button === null) {
            $actions = CnbAppRemote::cnb_remote_get_actions();
        } else {
            // Find ActionIDs for Button
            $button = $this->button;
            if ($button instanceof WP_Error) {
                return $button;
            }

            if ($button->actions != null) {
                $actions = $button->actions;
            }
        }

        if ($actions instanceof WP_Error) {
            return $actions;
        }

        $data = array();
        foreach ($actions as $action) {
            $data[] = array(
                'id' => $action->id,
                'actionType' => $action->actionType,
                'actionValue' => $action->actionValue,
                'labelText' => $action->labelText
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
        $orderby = !empty($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : 'actionValue';
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

    function column_actionType($item) {
        $column_name = 'actionType';
        $bid = $this->button !== null ? $this->button->id : null;
        $tab = $this->button !== null ? 'actions' : null;

        $actions = array();
        // Let's build a link
        $url = admin_url('admin.php');
        $edit_link =
            add_query_arg(
                array(
                    'page' => 'call-now-button-actions',
                    'action' => 'edit',
                    'id' => $item['id'],
                    'bid' => $bid,
                    'tab' => $tab),
                $url );
        $edit_url = esc_url( $edit_link );
        $actions['edit'] = '<a href="'.$edit_url.'">Edit</a>';

        if ($this->button) {
            $nonce = wp_create_nonce('cnb_delete_action');
            $screen = get_current_screen();
            $args = array(
                'action' => 'cnb_delete_action',
                'id' => $item['id'],
                'bid' => $bid,
                '_wpnonce' => $nonce,
                'refer' => $screen->parent_file
            );
            $delete_url = esc_url(add_query_arg($args, admin_url('admin-ajax.php')));
            $actions['delete'] = '<a data-ajax="true" data-id="' . $item['id'] . '" data-bid="' . $bid . '" data-wpnonce="' . $nonce . '" href="' . $delete_url . '">Delete</a>';
        } else {
            $delete_link = wp_nonce_url(
                add_query_arg(array(
                    'page' => 'call-now-button-actions',
                    'action' => 'delete',
                    'id' => $item['id'],
                    'bid' => $bid),
                    $url),
                'cnb_delete_action');
            $delete_url = esc_url($delete_link);
            $actions['delete'] = '<a href="' . $delete_url . '">Delete</a>';
        }

        $actionTypes = cnb_get_action_types();
        $value = !empty($item[$column_name]) ? esc_html($actionTypes[$item[$column_name]]) : '<em>No value</em>';
        return sprintf(
            '%1$s %2$s',
            '<strong><a class="row-title" href="'.$edit_url.'">'.$value . '</a></strong>',
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
                $actionIds = filter_input(INPUT_POST, 'cnb_list_action', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
                switch ($this->current_action()) {
                    case 'delete':
                        foreach ($actionIds as $actionId) {
                            $action = array('id' => $actionId);
                            CnbAppRemote::cnb_remote_delete_action( $action );
                        }
                        CnbAdminNotices::get_instance()->renderSuccess(count($actionIds) . ' Action(s) deleted.');
                        break;
                }
            }
        }
    }

    public function _js_vars() {
        $args = array(
            'class'  => get_class( $this ),
            'screen' => array(
                'id'   => $this->screen->id,
                'base' => $this->screen->base,
            ),
            'button' => $this->button,
            'data' => $this->get_data(),
        );

        printf( "<script type='text/javascript'>list_args = %s;</script>\n", wp_json_encode( $args ) );
    }

    function no_items() {
        _e( 'No actions found.' );
    }
}

function cnb_delete_action_real($action_id, $button_id = null, &$cnb_cloud_notifications = array()) {
    if (cnb_check_ajax_referer('cnb_delete_action')) {
        // If a button is set, remove this ID from the actions array
        if ($button_id !== null) {
            // Get the button
            $button = CnbAppRemote::cnb_remote_get_button($button_id);

            // Remove the current Action
            $pos = array_search($action_id, $button->actions);
            unset($button->actions[$pos]);

            // Convert to array and update
            $button_array = json_decode(json_encode($button), true);
            CnbAdminCloud::cnb_update_button($cnb_cloud_notifications, $button_array);
        }

        return CnbAdminCloud::cnb_delete_action($cnb_cloud_notifications, $action_id);
    }
    return false;
}

function cnb_delete_action() {
    // Action ID
    $id = !empty($_GET['id']) ? sanitize_text_field($_GET['id']) : null;
    $bid = !empty($_GET['bid']) ? sanitize_text_field($_GET['bid']) : null;

    $cnb_cloud_notifications = array();
    cnb_delete_action_real($id, $bid, $cnb_cloud_notifications);

    $adminNotices = CnbAdminNotices::get_instance();
    if ($bid !== null) {
        // Create link
        $url = admin_url('admin.php');
        $new_link =
            add_query_arg(
                array(
                    'page' => 'call-now-button',
                    'action' => 'edit',
                    'id' => $bid,
                ),
                $url);
        $new_url = esc_url_raw($new_link);

        $message = '<p>You will be redirected back to the Button overview in 1 second...</p><p>Or click here to go back immediately: <a href="'.$new_url.'">'.$new_url.'</a></p>';
        $message .= '<script type="text/javascript">setTimeout(function(){location.href="' . $new_url .'"} , 1000);   </script>';
        $notice = new CnbNotice('success', $message);
        CnbAdminNotices::get_instance()->renderNotice($notice);
    } else {
        foreach ($cnb_cloud_notifications as $cnb_cloud_notification) {
            $adminNotices->renderNotice($cnb_cloud_notification);
        }
    }
}

/**
 * Main entrypoint, used by `call-now-button.php`.
 */
function cnb_admin_page_action_overview_render() {
    $id = !empty($_GET['id']) ? sanitize_text_field($_GET['id']) : null;
    if ($id === null) {
        cnb_admin_page_action_overview_render_list();
        return;
    }

    $action = !empty($_GET['action']) ? sanitize_text_field($_GET['action']) : null;
    switch ($action) {
        case 'new':
        case 'edit':
            cnb_admin_page_action_edit_render();
            break;
        case 'delete':
            cnb_delete_action();
            cnb_admin_page_action_overview_render_list();
            break;
    }
}

function cnb_admin_page_action_overview_bid() {
    $bid = !empty($_GET['bid']) ? sanitize_text_field($_GET['bid']) : null;
    if ($bid !== null) {
        $button = CnbAppRemote::cnb_remote_get_button_full( $bid );

        if ( $button && ! ( $button instanceof WP_Error ) ) {
            CnbAdminNotices::get_instance()->renderInfo( 'Only actions for Button ID <code>' . esc_html( $button->id ) . '</code> (<strong>' . esc_html( $button->name ) . '</strong>) are shown.' );
            return array( 'button' => $button );
        }
    }
    return array();
}

function cnb_admin_page_action_overview_render_list() {
    add_action('cnb_header_name', 'cnb_add_header_action_overview');

    $args = cnb_admin_page_action_overview_bid();
    //Prepare Table of elements
    $wp_list_table = new Cnb_Action_List_Table($args);
    $data = $wp_list_table->prepare_items();

    if ($data instanceof WP_Error) {
        cnb_api_key_invalid_notice($data);
    } else {
        add_action('cnb_after_header', 'cnb_action_after_header');
    }

    do_action('cnb_header');

    echo '<form id="wp_list_event" method="post">';

    //Table of elements
    $wp_list_table->display();
    echo '</form>';
    do_action('cnb_footer');
}

/**
 * Used by button-overview
 *
 * @param $args
 */
function cnb_admin_page_action_overview_render_form($args) {
    //Prepare Table of elements
    $wp_list_table = new Cnb_Action_List_Table($args);
    $wp_list_table->prepare_items();

    //Table of elements
    $wp_list_table->display();
}
