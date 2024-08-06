<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('Business_Manager_Review', false)) :

/**
 * Business_Manager_Review Class.
 */
class Business_Manager_Review
{
    public $post_type = 'bm-review';
    public $post_id = null;

    public $access_type = 'bm_access_reviews';
    public $bm_employee_id;
    public $bm_access;

    public function __construct()
    {
        $this->hooks();
    }

    /**
     * Hook in to actions & filters.
     *
     * @since 1.0.0
     */
    public function hooks()
    {
        add_action('plugins_loaded', [$this, 'bm_pluggable']);

        // Interface Changes
        add_filter('gettext', [$this, 'publish_metabox_rename'], 10, 2);
        add_filter('post_updated_messages', [$this, 'post_updated_message']);
        add_action('admin_menu', [$this, 'login_access_admin_menu']);
        add_filter('views_edit-'.$this->post_type, [$this, 'login_access_views_edit'], 1);
        add_filter('post_row_actions', [$this, 'login_access_post_row_actions'], 10, 1);
        add_filter('bulk_actions-edit-'.$this->post_type, [$this, 'login_access_bulk_actions']);

        add_action('cmb2_save_post_fields', [$this, 'on_save_update'], 10, 4);
    }

    /**
    *
    * Set Business Manager Variables that rely on pluggable functions.
    *
    * @since 1.4.1
    */
    public function bm_pluggable()
    {
        $this->bm_employee_id = business_manager_employee_id(get_current_user_id());
        $this->bm_access = business_manager_employee_access(get_current_user_id(), $this->access_type);
    }

    /**
    *
    * Rename Publish text.
    *
    * @since 1.4.4
    */
    public function publish_metabox_rename($translation, $text)
    {
        global $post_type;

        if ($post_type == $this->post_type && ($text == 'Publish' || $text == 'Update')) {
            return __('Save Review', 'business-manager');
        }

        return $translation;
    }

    /**
    *
    * Rename post updated message.
    *
    * @since 1.4.4
    */
    public function post_updated_message($messages)
    {
        global $post_type;

        if ($post_type == $this->post_type) {
            $messages['post'] = array_map(
                function ($string) {
                    return str_replace('Post', __('Review', 'business-manager'), str_replace('updated', __('saved', 'business-manager'), str_replace('published', __('saved', 'business-manager'), $string)));
                },
                $messages['post']
            );
        }

        return $messages;
    }

    /**
     *
     * Change interface based on Employee Login Access.
     *
     * @since 1.4.0
     */
    public function login_access_admin_menu()
    {
        global $pagenow;

        $this->post_id = (isset($_GET['post']) ? sanitize_text_field($_GET['post']) : null);
        $post_type = (isset($_GET['post_type']) ? sanitize_text_field($_GET['post_type']) : get_post_type($this->post_id));

        if (current_user_can('manage_options') || $post_type != $this->post_type) {
            return;
        }

        // Hide Publish Metabox
        if ($this->bm_access == 'limited' && $pagenow == 'post.php' && get_post_meta($this->post_id, '_bm_review_employee', true) == $this->bm_employee_id) {
            remove_meta_box('submitdiv', $this->post_type, 'side');
	        $object = get_post_type_object( $this->post_type );
	        // set get_post_type_labels()
	        $object->labels->edit_item          = sprintf('View %s', business_manager_label_review_single());
        }

        // Hide Buttons
        if ($this->bm_access == 'limited') {
            echo '<style type="text/css">';
            echo 'a.page-title-action { display: none; }';
            echo '.cmb-add-row { display: none; }';
            echo '.cmb2-upload-button, .cmb2-remove-file-button { display: none !important; }';
            echo '</style>';
        }
    }

    /**
     *
     * Change view options based on Employee Login Access.
     *
     * @since 1.4.0
     */
    public function login_access_views_edit($views)
    {
        global $pagenow;

        $post_type = (isset($_GET['post_type']) ? sanitize_text_field($_GET['post_type']) : null);

        if (current_user_can('manage_options') || $this->bm_access == 'full' || $post_type != $this->post_type) {
            return $views;
        }

        $remove_views = ['all', 'publish', 'future', 'sticky', 'draft', 'pending', 'trash', 'mine'];

        foreach ((array) $remove_views as $view) {
            if (isset($views[$view])) {
                unset($views[$view]);
            }
        }

        return $views;
    }

    /**
     *
     * Change row actions based on Employee Login Access.
     *
     * @since 1.4.0
     */
    public function login_access_post_row_actions($actions)
    {
        global $pagenow;

        $post_type = (isset($_GET['post_type']) ? sanitize_text_field($_GET['post_type']) : null);

        if (current_user_can('manage_options') || $this->bm_access == 'full' || $post_type != $this->post_type) {
            return $actions;
        }

        if ($this->bm_access == 'limited') {
            $actions['edit'] = str_replace(__('Edit'), __('View'), $actions['edit']);
            unset($actions['trash'], $actions['inline hide-if-no-js']);
        }

        return $actions;
    }

    /**
     *
     * Change bulks actions based on Employee Login Access.
     *
     * @since 1.4.0
     */
    public function login_access_bulk_actions($actions)
    {
        global $pagenow;

        $post_type = (isset($_GET['post_type']) ? sanitize_text_field($_GET['post_type']) : null);

        if (current_user_can('manage_options') || $this->bm_access == 'full' || $post_type != $this->post_type) {
            return $actions;
        }
    }

    /**
     * Run after saving post.
     *
     * @since 1.0.0
     */
    public function on_save_update($object_id, $cmb_id, $updated, $cmb)
    {
        if (get_post_type($object_id) != $this->post_type) {
            return;
        }

        $this->post_id = $object_id;
        $this->cmb = $cmb;

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (wp_is_post_revision($this->post_id)) {
            return;
        }

        remove_action('cmb2_save_post_fields', [$this, 'on_save_update'], 10, 4);
        $this->update_title();
        add_action('cmb2_save_post_fields', [$this, 'on_save_update'], 10, 4);
    }

    /**
     * Update title.
     *
     * @since 1.0.0
     */
    public function update_title()
    {
        $employee = get_post_meta($this->post_id, '_bm_review_employee', true);
        $date = get_post_meta($this->post_id, '_bm_review_date', true);
        $name = trim(business_manager_employee_full_name($employee));

        if (!$name) {
            $name = __('(no name)', 'business-manager');
        }

        if (!empty($date)) {
            $date = ' - '.business_manager_date_format($date);
        }

        $name = $name.$date;
        $post = [
            'ID' => $this->post_id,
            'post_title' => $name,
        ];
        wp_update_post($post);
    }
}

endif;

return new Business_Manager_Review();
