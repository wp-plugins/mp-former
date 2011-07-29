<?php

/*
 * Create a new class
 * I'm not using __autoload to prevent from conflicts with other autoload
 * functions
 */

function mp_create_object($name) {

    //give ability to rewrite standart class
    //this class should be allready included during wp init
    $name = apply_filters(MP_PREFIX . 'rewrite_core_class', $name);
    //if there are some paramters for class constructor, get them
    $params = func_get_args();
    $pcount = func_num_args();
    $pList = array();
    for ($i = 1; $i < $pcount; $i++) {
        $pList[] = '$params[' . $i . ']';
    }

    if (class_exists($name)) {
        eval('$obj = new $name(' . implode(',', $pList) . ');');
    } else { //try to find proper file and include it
        $lName = strtolower($name);
        $parts = preg_split('/_/', $lName);
        $fPath = MP_BASE_DIR . $parts[1] . '/' . $lName . '.php';
        if ($parts[0] . '_' != MP_PREFIX) {
            Throw new Exeption('Class does not named proparly');
        } elseif (!file_exists($fPath)) {

            Throw new Exception($fPath);
        } else {
            require($fPath);
            eval('$obj = new $name(' . implode(',', $pList) . ');');
        }
    }

    return $obj;
}

/*
 * Initiate main plugin class
 */

function mp_init() {
    $mainObj = mp_create_object('mb_Former');
}

/*
 * Debuggin function
 */

function mp_debug($what, $die = FALSE) {
    echo '<pre>';
    print_r($what);
    echo '</pre>';

    if ($die) {
        die();
    }
}

/*
 * Merge custom post type's settings with default WP settings
 * 
 * @param array Array of settings
 * @param string Data type
 */

function mp_parse_args($args, $dataType) {

    if (!is_array($args)) {
        $args = array();
    }

    switch ($dataType) {
        case 'object_args':
            $default = array(
                'labels' => array(), 'description' => '',
                'publicly_queryable' => null, 'exclude_from_search' => null,
                'capability_type' => 'post', 'capabilities' => array(),
                'map_meta_cap' => null, '_builtin' => false,
                '_edit_link' => 'post.php?post=%d', 'hierarchical' => false,
                'public' => false, 'rewrite' => null,
                'has_archive' => false, 'query_var' => null, 'supports' => array(),
                'register_meta_box_cb' => null, 'taxonomies' => array(),
                'show_ui' => null, 'menu_position' => null, 'menu_icon' => null,
                'permalink_epmask' => EP_PERMALINK, 'can_export' => null,
                'show_in_nav_menus' => null, 'show_in_menu' => null,
                'show_in_admin_bar' => null,
            );
            break;

        case 'object_stats':
        case 'object_taxons':
            $default = array();
            break;

        default:
            $default = array();
            break;
    }

    $args = wp_parse_args($args, $default);
}

/*
 * Just getting out some sh..t from model node
 * Normalize post arguments
 */

function mb_normalize_arguments($args, $type) {

    switch ($type) {
        case 'post':
            //prepare required list of parameters
            $checkboxes = array('public', 'exclude_from_search', 'rewrite', 'publicly_queryable',
                'show_ui', 'permalink_epmask', 'show_in_menu', 'map_meta_cap', 'hierarchical',
                'can_export', 'show_in_nav_menus', 'query_var', 'has_archive');

            foreach ($checkboxes as $check) {
                $args[$check] = (isset($args[$check]) ? TRUE : FALSE);
            }

            foreach ($args as $key => $value) {
                if (empty($value)) {
                    unset($args[$key]);
                }
            }
            //clear labels
            if (is_array($args['labels'])) {
                foreach ($args['labels'] as $key => $value) {
                    if (empty($value)) {
                        unset($args['labels'][$key]);
                    }
                }
            }
            //convert string to intval for menu_position
            $args['menu_position'] = intval($args['menu_position']);
            //set unexisting support element
            if (!isset($args['supports'])) {
                $args['supports'] = array('dummy');
            }
            //permalink_epmask normalization
            if (defined($args['permalink_epmask'])) {
                $args['permalink_epmask'] = constant($args['permalink_epmask']);
            } else {
                unset($args['permalink_epmask']);
            }
            break;

        case 'status':
            foreach ($args['label_count'] as $key => &$value) {
                if (!trim($value)) {
                    $value = $args['title'];
                }
                if (strpos($value, '(%s)') === FALSE) {
                    $value .= ' (%s)';
                }
            }
            array_unshift($args['label_count'], $args['label_count']['plural']);
            array_unshift($args['label_count'], $args['label_count']['singular']);
            $args['label'] = $args['title'];
            $args[$args['visibility']] = TRUE;
            break;

        case 'taxonomy':
            $args['label'] = $args['title'];
            //clear labels
            if (is_array($args['labels'])) {
                foreach ($args['labels'] as $key => $value) {
                    if (empty($value)) {
                        unset($args['labels'][$key]);
                    }
                }
            }
            break;
    }

    return $args;
}

?>