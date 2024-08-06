<?php
/**
 * This list table in dashboard is not completed as we are using admin notices to show it in commit # 8ae811b0
 */
if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Create a new table class that will extend the WP_List_Table
 */
class BM_Dashboard_List_table extends WP_List_Table
{
    /**
     * @var string
     */
    var $post_type;
    /**
     * @var array
     */
    var $columns;
    /**
     * @var array|mixed
     */
    var $sortable_columns;
    /**
     * @var int|mixed
     */
    private $per_page;

    /**
     * Prepare the items for the table to process
     *
     * @return Void
     */
    public function prepare_items($post_type = 'post', $columns = array(), $sortable_columns = array(), $per_page = 10)
    {
        $this->post_type=$post_type;
        $this->columns=$columns;
        $this->sortable_columns = $sortable_columns;
        $this->per_page = $per_page;

        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();
        /** Process bulk action */
//        $this->process_bulk_action();

        $paged =  $this->get_pagenum();
        $args = array(
            'post_type' => $this->post_type,
            'post_status' => 'publish',
            'posts_per_page' =>$this->per_page,
            'orderby' => 'date',
            'order' => 'DSC',
            'paged' =>  $paged
        );

        $query_args = apply_filters("bm_dashboard_query_{$this->post_type}", $args);

        $this->_column_headers = array($columns, $hidden, $sortable);

        $this->set_pagination_args( [
            'total_items' => $this->total_posts(), //WE have to calculate the total number of items
            'per_page' => $this->per_page //WE have to determine how many items to show on a page
        ] );

        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = (new WP_Query( $query_args ))->get_posts();
    }

    public function total_posts() {
        $args = [
            'numberposts' => -1,
            'post_status' => 'publish',
            'post_type' => $this->post_type,
        ];
        return count(get_posts($args));
    }
    /**
     * Override the parent columns method. Defines the columns to use in your listing table
     *
     * @return Array
     */
    public function get_columns()
    {
        return $this->columns;
    }

    /**
     * Define which columns are hidden
     *
     * @return Array
     */
    public function get_hidden_columns()
    {
        return array();
    }

    /**
     * Define the sortable columns
     *
     * @return Array
     */
    /**
     * Columns to make sortable.
     *
     * @return array
     */
    public function get_sortable_columns() {
        return $this->sortable_columns;
    }

    /**
     * Define what data to show on each column of the table
     *
     * @param  Array $item        Data
     * @param  String $column_name - Current column name
     *
     * @return Mixed
     */
    public function column_default( $item, $column_name )
    {
        foreach ($this->columns as $key=>$value)
        {
            if($key == $column_name) return '<a href='.get_permalink($item).'>'.((array)$item)[$key].'</a>' ;
        }
        return print_r( $item, true );
    }
    /**
     * Define our bulk actions
     *
     * @since 1.2
     * @returns array() $actions Bulk actions
     */
    function get_bulk_actions() {
        $actions = array(
            'smc-bulk-trash' => __( 'Move to trash' , 'score-my-call'),
        );
        return $actions;
    }

    /**
     * Render the bulk edit checkbox
     *
     * @param array $item
     *
     * @return string
     */
    function column_cb( $item ) {
        return sprintf(
            '<input type="checkbox" name="smc-bulk-trash[]" value="%s" />', $item->ID
        );
    }

    /**
     * Method for name column
     *
     * @param array $item an array of DB data
     *
     * @return string
     */
    function column_post_title( $item ) {

        $link  =get_permalink($item);

        // create a nonce
        $nonce_url = wp_nonce_url(admin_url("admin.php?page=score-my-call&action=trash&entry=$item->id"), 'smc_trash_entry', '_wpnonce');

        $actions = [
            'trash' => sprintf( '<a href="%s" >Move to trash</a>', $nonce_url)
        ];

        return '<a href='.$link.'>'.get_the_title( $item ).'</a>' . $this->row_actions( $actions );
    }

    /**
     * Process our bulk actions
     *
     * @since 1.2
     */
    function trash_entry($id) {
        global $wpdb;
        $wpdb->query( "Update " . $wpdb->prefix . SMC_DB_Helper::$table_name . " SET status = 0 WHERE id = $id" );
    }

    public function process_bulk_action() {

        //Detect when a bulk action is being triggered...
        if ( 'trash' === $this->current_action() ) {
            // In our file that handles the request, verify the nonce.
            $nonce = esc_attr( $_REQUEST['_wpnonce'] );

            if ( ! wp_verify_nonce( $nonce, 'smc_trash_entry' ) ) {
                die( 'Not Allowed' );
            }
            else {
                self::trash_entry( $_REQUEST['entry'] );
            }
        }

        // If the trash bulk action is triggered
        if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'smc-bulk-trash' )
            || ( isset( $_POST['action2'] ) && $_POST['action2'] == 'smc-bulk-trash' )
        ) {

            $trash_ids = esc_sql( $_POST['smc-bulk-trash'] );
            // loop over the array of record IDs and trash them
            foreach ( $trash_ids as $id ) {
                self::trash_entry( $id );
                //wp_redirect( esc_url_raw(add_query_arg(array())) );
                //exit;
            }
        }
    }
}

