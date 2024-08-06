<?php

if ( ! function_exists('bm_get_template_part') ) {
    //Loading a template in a plugin, but allowing theme and child theme to override template
    function bm_get_template_part($slug, $name, $path= BUSINESSMANAGER_DIR . "templates/", $args = array())
    {
        /*
         * locate_template() returns path to file.
         * if either the child theme or the parent theme have overridden the template.
         */
        $template = locate_template("{$slug}-{$name}.php");

        if (!$template) {
            /*
             * If neither the child nor parent theme have overridden the template,
             * we load the template from the 'templates' sub-directory of the directory this file is in.
             */
            $template = $path . "{$slug}-{$name}.php";
        }

        $template =  apply_filters('bm_get_template_part', $template, $slug, $name, $args);

        if (!file_exists($template)) {
            /* translators: %s template */
            _doing_it_wrong(__FUNCTION__, sprintf(__('%s adf does not exist.', 'business-manager'), '<code>' . $template . '</code>'), '1.0.0');
            return;
        }
        load_template($template, false, $args);
    }
}

if ( ! function_exists('bm_get_template') ) {
    function bm_get_template($template_name, $args = array(), $teamplate_path = BUSINESSMANAGER_DIR . 'templates/')
    {
        if ($overridden_template = locate_template($template_name)) {
            $template = $overridden_template;
        } else {
            // Make sure that the absolute path to the template is resolved.
            $template = $teamplate_path . $template_name;
        }
        $template = apply_filters('bm_get_template', $template, $template_name, $teamplate_path);

        if (!file_exists($template)) {
            /* translators: %s template */
            _doing_it_wrong(__FUNCTION__, sprintf(__('%s does not exist.', 'business-manager'), '<code>' . $template . '</code>'), '1.0.0');
            return;
        }

        include $template;
    }
}