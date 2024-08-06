<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

if (! class_exists('Business_Manager_Tasks_Fields')) :

/**
 * The main class.
 *
 * @since 1.0.0
 */
class Business_Manager_Tasks_Fields {
    /**
     * Handles outputting an 'input' element.
     *
     * @since  1.0.0
     * @param  array $args Override arguments
     * @return string       Form input element
     */
    public function text($args = []) {
        $defaults = [
            'type' => 'text',
            'class' => 'regular-text',
            'name' => '',
            'id' => '',
            'desc' => '',
            'autocomplete' => 'off',
        ];

        $args = wp_parse_args($args, $defaults);

        return sprintf('<input%s/><span class="desc">%s</span>', $this->concat_attrs($args, ['desc']), $args['desc']);
    }

    /**
     * Handles outputting a 'wysiwyg' element.
     * @since  1.0.0
     * @return string Form wysiwyg element
     */
    public function wysiwyg($args = []) {
        $options = [
            'wpautop' => true, // use wpautop?
            'media_buttons' => false,
            'textarea_rows' => 2,
            'tabindex' => '',
            'editor_css' => '', // intended for extra styles for both visual and HTML editors buttons, needs to include the `<style>` tags, can use "scoped".
            'editor_class' => '', // add extra class(es) to the editor textarea
            'teeny' => true, // output the minimal editor config used in Press This
            'dfw' => false, // replace the default fullscreen with DFW (needs specific css)
            'tinymce' => true, // load TinyMCE, can be used to pass settings directly to TinyMCE using an array()
            'quicktags' => true, // load Quicktags, can be used to pass settings directly to Quicktags using an array()
        ];

        $defaults = [
            'class' => 'wysiwyg',
            'name' => '',
            'id' => '',
            'value' => '',
            'desc' => '',
            'options' => $options,
        ];

        $args = wp_parse_args($args, $defaults);

        return $this->get_wp_editor($args).'<span class="desc">'.$args['desc'].'</span>';
    }

    private function get_wp_editor($args) {
        ob_start();
        wp_editor($args['value'], $args['id'], $args['options']);
        return ob_get_clean();
    }

    /**
     * Handles outputting an 'textarea' element.
     *
     * @since  1.0.0
     * @param  array $args Override arguments
     * @return string       Form textarea element
     */
    public function textarea($args = []) {
        $options = [];

        $defaults = [
            'class' => 'textarea',
            'name' => '',
            'id' => '',
            'value' => '',
            'desc' => '',
            'cols' => 20,
            'rows' => 6,
        ];

        $args = wp_parse_args($args, $defaults);

        return sprintf('<textarea%s>%s</textarea><span class="desc">%s</span>', $this->concat_attrs($args, ['desc', 'value']), $args['value'], $args['desc']);
    }

    public function select($args = []) {
        $defaults = [
            'class' => 'select',
            'name' => '',
            'id' => '',
            'desc' => '',
            'options' => [],
        ];

        $args = wp_parse_args($args, $defaults);

        $attrs = $this->concat_attrs($args, ['desc', 'options']);
        $options = $this->concat_items($args['options']);

        return sprintf('<select%s>%s</select><span class="desc">%s</span>', $attrs, $options, $args['desc']);
    }

    public function colorpicker($args = []) {
        $defaults = [
            'class' => 'colorpicker',
            'desc' => '',
        ];

        $args = wp_parse_args($args, $defaults);

        return sprintf('<input%s/><span class="desc">%s</span>', $this->concat_attrs($args, ['desc']), $args['desc']);
    }

    /**
     * Generates html for concatenated items.
     *
     * @since  1.0.0
     * @param  array $args Optional arguments
     * @return string        Concatenated html items
     */
    public function concat_items($options = []) {
        $concatenated_items = '';
        $i = 1;
        foreach ($options as $opt_value => $opt_label) {
            $a['value'] = $opt_value;
            $a['label'] = $opt_label;

            $concatenated_items .= $this->select_option($a, $i++);
        }
        return $concatenated_items;
    }

    /**
     * Generates html for an option element.
     *
     * @since  1.0.0
     * @param  array $args Arguments array containing value, label, and checked boolean
     * @return string       Generated option element html
     */
    public function select_option($args = []) {
        return sprintf("\t".'<option value="%s">%s</option>', $args['value'], $args['label'])."\n";
    }

    /**
     * Combines attributes into a string for a form element.
     *
     * @since  1.0.0
     * @param  array $attrs        Attributes to concatenate.
     * @param  array $attr_exclude Attributes that should NOT be concatenated.
     * @return string               String of attributes for form element.
     */
    public function concat_attrs($attrs, $attr_exclude = []) {
        $attr_exclude[] = 'rendered';
        $attributes = '';
        foreach ($attrs as $attr => $val) {
            $excluded = in_array($attr, (array) $attr_exclude, true);
            $empty = false === $val && 'value' !== $attr;
            if (! $excluded && ! $empty) {
                // if data attribute, use single quote wraps, else double
                $quotes = $this->is_data_attribute($attr, 'data-') ? "'" : '"';
                $attributes .= sprintf(' %1$s=%3$s%2$s%3$s', $attr, $val, $quotes);
            }
        }
        return $attributes;
    }

    /**
     * Check if given attribute is a data attribute.
     *
     * @since  1.0.0
     * @param  string  $att HTML attribute
     * @return boolean
     */
    public function is_data_attribute($att) {
        return 0 === stripos($att, 'data-');
    }
}

endif;
