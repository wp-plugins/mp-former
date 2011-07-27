<?php

class mp_Model {
    /*
     * Main Custom Post Type ID
     * 
     * @var string
     * @access public
     */

    public static $mainID = 'object';

    /*
     * Custom Post Type List holder
     * 
     * @var array Array of CPT objects
     * @access private
     */
    private $objectList = array();

    /*
     * Default metabox IDs
     * 
     * @var array
     * @access private
     */
    private $defMList = array(
        'args' => 'metabox-object_args',
        'stats' => 'metabox-object_stats',
        'taxons' => 'metabox-object_taxons',
        'mboxes' => 'metabox-object_mboxes',
        'tools' => 'metabox-object_tools'
    );

    /*
     * Pointer to View Node
     * 
     * @var object <mp_View>
     */
    private $viewNode;

    /*
     * Original list of statuses in WP
     * 
     * @var array List of standart statuses
     * @access public
     */
    public $wp_status_list;

    /*
     * Constructor
     */

    function __construct() {

        //initiate core and register defined custom post types
        /*
         * 1. Initiate main custom post type
         */
        register_post_type(self::$mainID, array(
            'labels' => array(
                'name' => __('Former', MP_PLUGIN_NAME),
                'singular_name' => __('Former', MP_PLUGIN_NAME),
                'add_new' => __('Add New', MP_PLUGIN_NAME),
                'add_new_item' => __('Add New Object', MP_PLUGIN_NAME),
                'edit' => __('Edit', MP_PLUGIN_NAME),
                'edit_item' => __('Edit Object', MP_PLUGIN_NAME),
                'new_item' => __('New Object', MP_PLUGIN_NAME),
                'view' => __('View Object', MP_PLUGIN_NAME),
                'view_item' => __('View Object', MP_PLUGIN_NAME),
                'search_items' => __('Search Object', MP_PLUGIN_NAME),
                'not_found' => __('No Objects found', MP_PLUGIN_NAME),
                'not_found_in_trash' => __('No Objects found in Trash', MP_PLUGIN_NAME)
            ),
            'description' => __('List of Objects', MP_PLUGIN_NAME),
            'publicly_queryable' => FALSE,
            'exclude_from_search' => TRUE,
            'show_ui' => TRUE,
            'show_in_menu' => TRUE,
            // 'menu_icon' => '',  //TODO : MINOR - Add icon
            'capability_type' => 'post',
            'hierarchical' => TRUE,
            'can_export' => TRUE,
            'public' => FALSE,
            'menu_position' => 100,
            'register_meta_box_cb' => array($this, 'manage_custom_metaboxes'),
            'supports' => array('title')
        ));
        $this->registerCoreStatusList();

        //register existing custom post types
        $this->registerObjects();

        //setup admin environment
        if (is_admin()) {
            //add_action("manage_posts_custom_column", array($this, 'manage_custom_column'), 10, 2);
            add_action('save_post', array($this, 'save_post'), 10, 2);
            add_filter('manage_posts_columns', array($this, 'manage_posts_columns'), 10, 2);
            add_action('manage_pages_custom_column', array($this, 'manage_posts_custom_column'), 10, 2);
            add_action('manage_posts_custom_column', array($this, 'manage_posts_custom_column'), 10, 2);
        }
    }

    /*
     * Manage Post columns
     * 
     * @param array List of columns
     * @param string Current post type
     * @return array Filtered list of columns
     */

    public function manage_posts_columns($posts_columns, $post_type) {

        $obj = $this->getObjectByType($post_type);
        if (is_object($obj) && is_array($obj->properties['object_args']['show_on_screen'])) {
            $posts_columns = array();
            foreach ($obj->properties['object_args']['show_on_screen'] as $column) {
                $posts_columns[$column['field']] = $column['title'];
            }
        }

        return $posts_columns;
    }

    /*
     * Render custom defined Show on Screen column
     * 
     * @param string Column Name
     * @param int Post ID
     */

    public function manage_posts_custom_column($column_name, $postID) {
        global $post;

        if (!is_object($post) || ($post->ID != $postID)) {
            $tpost = get_post($postID);
        } else {
            $tpost = $post;
        }
        //Generally, if we are here, means that DB field is rendering.
        $content = $tpost->{$column_name};

        echo apply_filters(MP_PREFIX . 'custom_column', $content, $column_name, $postID);
    }

    /*
     * Get list of sortable columns
     * 
     * @param array List of pre-defined sortable columns
     * @return array Filtered list of sortable columns
     */

    public function manage_sortable_columns($sortable_columns) {

        $object = $this->getObjectByType($this->getCurrentPostType());

        if (is_object($object) && is_array($object->properties['object_args']['show_on_screen'])) {
            $tList = array();
            foreach ($object->properties['object_args']['show_on_screen'] as $column) {
                if ($column['sortable']) {
                    switch ($column['field']) {
                        case 'comments':
                            $tList[$column['field']] = 'comment_count';
                            break;

                        default:
                            $tList[$column['field']] = $column['field'];
                            break;
                    }
                }
            }
            if (count($tList)) {
                $sortable_columns = $tList;
            }
        }

        return $sortable_columns;
    }

    /*
     * Find the object by post type
     * 
     * @param string Post Type
     * @return mixed Return Object if current post type exists or empty if not
     */

    public function getObjectByType($post_type) {
        $result = '';

        if (is_array($this->objectList)) {
            foreach ($this->objectList as $object) {
                if ($object->properties['object_args']['post_type'] == $post_type) {
                    $result = $object;
                    break;
                }
            }
        }

        return $result;
    }

    /*
     * Get list of object fields for rendering Show on Screen
     * 
     * @return string rendered HTML string
     */

    public function renderSoSFields() {
        global $wpdb;

        $content = '';

        //render WP pre-defined list of columns
        $content = $this->renderDefaultSoSFields();
        //get list of DB fields of table posts
        $fields = $wpdb->get_results('SHOW COLUMNS FROM ' . $wpdb->posts);
        $oList = '';
        if (is_array($fields)) {
            foreach ($fields as $field) {
                $c = MP_PREFIX . 'SOS_DB_FIELD_' . strtoupper($field->Field);
                $label = (defined($c) ? constant($c) : $this->humanReadable($field->Field));
                $oList .= $this->viewNode->getSelectOptionHTML($field->Field, $label);
            }
            $content .= $this->viewNode->wrapSelectOptions(__('DB Fields', MP_PLUGIN_NAME), $oList);
        }

        return $content;
    }

    /*
     * If label is not defined for field, try to render human readable
     * label according to field name
     * 
     * @param string Field name
     * @return string Human Readable Label
     */

    public function humanReadable($label) {

        $parts = preg_split('/_/', $label);
        if (is_array($parts)) {
            foreach ($parts as &$part) {
                $part = ucfirst($part);
            }
        }

        return implode(' ', $parts);
    }

    /*
     * Get default WP column list
     * 
     * @return string HTML
     */

    public function renderDefaultSoSFields() {

        $defaultList = array(
            array(
                'value' => 'cb',
                'label' => __('Checkbox', MP_PLUGIN_NAME)
            ),
            array(
                'value' => 'title',
                'label' => __('Title', MP_PLUGIN_NAME)
            ),
            array(
                'value' => 'date',
                'label' => __('Date', MP_PLUGIN_NAME)
            ),
            array(
                'value' => 'categories',
                'label' => __('Categories', MP_PLUGIN_NAME)
            ),
            array(
                'value' => 'tags',
                'label' => __('Tags', MP_PLUGIN_NAME)
            ),
            array(
                'value' => 'comments',
                'label' => __('Comments', MP_PLUGIN_NAME)
            ),
            array(
                'value' => 'author',
                'label' => __('Author', MP_PLUGIN_NAME)
            )
        );
        $content = '';
        foreach ($defaultList as $item) {
            $content .= $this->viewNode->getSelectOptionHTML($item['value'], $item['label']);
        }

        $content = $this->viewNode->wrapSelectOptions(__('Pre-defined', MP_PLUGIN_NAME), $content);

        return $content;
    }

    /*
     * Register core object status list
     */

    private function registerCoreStatusList() {
        //add core object statuses
        register_post_status('active', array(
            'label' => __('Active', MP_PLUGIN_NAME),
            'label_count' => array(
                __('Active', MP_PLUGIN_NAME) . ' (%s)',
                __('Active', MP_PLUGIN_NAME) . ' (%s)',
                'plural' => __('Active', MP_PLUGIN_NAME) . ' (%s)',
                'singular' => __('Active', MP_PLUGIN_NAME) . ' (%s)',
            ),
            'exclude_from_search' => TRUE,
            MP_PREFIX . 'core' => TRUE,
            'protected' => TRUE,
            'show_in_admin_all' => TRUE,
            'publicly_queryable' => FALSE,
            'show_in_admin_status_list' => TRUE,
            'show_in_admin_all_list' => TRUE,
        ));

        register_post_status('inactive', array(
            'label' => __('Inactive', MP_PLUGIN_NAME),
            'label_count' => array(
                __('Inactive', MP_PLUGIN_NAME) . ' (%s)',
                __('Inactive', MP_PLUGIN_NAME) . ' (%s)',
                'plural' => __('Inactive', MP_PLUGIN_NAME) . ' (%s)',
                'singular' => __('Inactive', MP_PLUGIN_NAME) . ' (%s)',
            ),
            'exclude_from_search' => TRUE,
            MP_PREFIX . 'core' => TRUE,
            'protected' => TRUE,
            'show_in_admin_all' => TRUE,
            'publicly_queryable' => FALSE,
            'show_in_admin_status_list' => TRUE,
            'show_in_admin_all_list' => TRUE,
        ));
    }

    /*
     * Get ajax nonce
     * 
     * @param bool If true, return prepared nonce, else return only nonce name
     * @return string Nonce name
     */

    public function getNonce($compiled = TRUE) {

        $nonce = MP_PREFIX . 'ajax';
        if ($compiled) {
            $nonce = wp_create_nonce($nonce);
        }

        return $nonce;
    }

    /*
     * Process AJAX request
     * 
     */

    public function processAJAX() {

        $m = mp_create_object(MP_PREFIX . 'model_ajax', $this->viewNode, $this);
    }

    /*
     * Save additional post data
     * 
     * @param int
     * @param object
     */

    public function save_post($pID, $post) {

        if (is_array($_POST[MP_PREFIX])) {
            foreach ($_POST[MP_PREFIX] as $key => $data) {
                switch ($key) {
                    case self::$mainID:
                        //add post type
                        $data['object_args']['post_type'] = $this->makePostType($_POST['post_title']);
                        $this->insertMetaData($pID, MP_PREFIX . 'object_setup', $data);
                        break;
                }
            }
        }
    }

    /*
     * Prepare post type from post title
     * 
     * @param string post title
     * @return string post type
     */

    private function makePostType($pTitle) {

        return str_replace(' ', '_', strtolower($pTitle));
    }

    /*
     * Set view Node
     * 
     * @param object Reference to mp_View
     */

    public function setViewNode($viewNode) {

        $this->viewNode = $viewNode;
    }

    /*
     * Check if JS libraries related to current plugin should be loaded
     * 
     * @return bool TRUE to load JS
     */

    public function loadJS() {

        $postTP = $this->getCurrentPostType();
        $found = FALSE;
        foreach ($this->objectList as $id => $data) {
            if ($data->properties['object_args']['post_type'] == $postTP) {
                $found = TRUE;
                break;
            }
        }
        if ($found || ($postTP == self::get_mainID())) {
            $result = TRUE;
        } else {
            $result = FALSE;
        }

        return $result;
    }

    /*
     * Check if CSS related to current plugin should be loaded
     * 
     * @return bool TRUE to load CSS
     */

    public function loadCSS() {

        return $this->loadJS();
    }

    /*
     * Register CPTs
     * 
     */

    private function registerObjects() {
        global $wp_post_statuses;

        $currentPT = $this->getCurrentPostType();
        $this->getObjectList();
        //register CPTs and there's properties
        foreach ($this->objectList as $oID => $oData) {
            //do not register inactive posts
            if ($oData->post_status == 'inactive') {
                continue;
            }
            $gKey = 'object_args';
            $sKey = 'object_stats';
            $tKey = 'object_taxons';

            if (!empty($oData->properties[$gKey])) {
                //add handling custom metaboxes function
                $oData->properties[$gKey]['register_meta_box_cb'] = array($this, 'manage_custom_metaboxes');
                $postArgs = $this->normalizePostArgs($oData->properties[$gKey]);
                $postType = $oData->properties[$gKey]['post_type'];
                register_post_type($postType, $postArgs);
                if (($postType == $currentPT) && !empty($oData->properties[$sKey])) {
//                    $this->wp_status_list = array_keys($wp_post_statuses);
                    $wp_post_statuses = array();
                    foreach ($oData->properties[$sKey] as $status) {
                        $title = str_replace(' ', '_', strtolower($status['title']));
                        //normalize status args.
                        $statusArgs = $this->nomalizeStatusArgs($status);
                        register_post_status($title, $statusArgs);
                    }
                }
                //register filter for sortable field list
                add_filter("manage_edit-{$postType}_sortable_columns", array($this, 'manage_sortable_columns'));
                //register taxonomies
                if (!empty($oData->properties[$tKey])) {
                    foreach ($oData->properties[$tKey] as $taxonomy) {
                        $title = str_replace(' ', '_', strtolower($taxonomy['title']));
                        $taxArgs = $this->nomalizeTaxonomyArgs($taxonomy);
                        register_taxonomy($title, $postType, $taxArgs);
                    }
                }
            }
        }
    }

    /*
     * Normalize post arguments for setuping new custom post type
     * 
     * @param array Settings about CPT taken from DB
     * @return array Normalized array of arguments
     */

    private function normalizePostArgs($args) {

        return mb_normalize_arguments($args, 'post');
    }

    /*
     * Normalize status arguments for setuping new custom post status
     * 
     * @param array Settings about CPT taken from DB
     * @return array Normalized array of arguments
     */

    private function nomalizeStatusArgs($args) {

        return mb_normalize_arguments($args, 'status');
    }

    /*
     * Normalize taxonomy arguments for setuping new custom taxonomy
     * 
     * @param array Settings for Taxonomy taken from DB
     * @return array Normalized array of arguments
     */

    private function nomalizeTaxonomyArgs($args) {

        return mb_normalize_arguments($args, 'taxonomy');
    }

    /*
     * Manage Custom metaboxes
     * 
     * @param object <WP_Post>
     */

    public function manage_custom_metaboxes($post) {
        global $wp_meta_boxes;

        if ($post->post_type == self::$mainID) {
            //register standart main Object's metaboxes
            add_meta_box($this->defMList['args'], __('General', MP_PLUGIN_NAME), array($this, 'render_metabox'), $post->post_type, 'normal', 'default');
            add_meta_box($this->defMList['stats'], __('Statuses', MP_PLUGIN_NAME), array($this, 'render_metabox'), $post->post_type, 'normal', 'default');
            add_meta_box($this->defMList['taxons'], __('Taxonomies', MP_PLUGIN_NAME), array($this, 'render_metabox'), $post->post_type, 'normal', 'default');
        }

        //first of all rewrite current submit metabox
        $metabox = & $this->find_metabox('submitdiv', $wp_meta_boxes[$post->post_type]);
        if ($metabox != NULL) {
            $metabox['callback'] = array($this, 'submitdiv');
        }
    }

    /*
     * Render Unique ID
     * 
     * @return string unique ID
     */

    public function renderUID() {

        return uniqid(MP_PREFIX);
    }

    /*
     * Render custom submit div
     * 
     */

    public function submitdiv() {
        global $post;

        if ($post->post_type == self::$mainID) {
            $m = mp_create_object('mp_model_coreSubmitDiv', $this->viewNode, $this);
        } else {
            $m = mp_create_object('mp_model_submitDiv', $this->viewNode, $this);
        }
        $m->post_submit_meta_box($post);
    }

    /*
     * Find metabox and return pointer to it
     * 
     * @param string Metabox ID
     * @return ref Refference to the $wp_meta_boxes element with found metabox
     */

    private function &find_metabox($metabox, &$current) {

        $result = NULL;

        if (is_array($current)) {
            //  mp_debug($current);
            //  mp_debug(array_keys($current));
            foreach (array_keys($current) as $key) {
                if ($key == $metabox) {
                    $result = & $current[$key];
                } elseif (is_array($current[$key])) {
                    $result = & $this->find_metabox($metabox, $current[$key]);
                }
                //break loop if found
                if ($result != NULL) {
                    break;
                }
            }
        }

        return $result;
    }

    /*
     * Render Metabox
     * 
     * @param object <WP_Post>
     * @param array Information about metabox
     */

    function render_metabox($post, $config) {

        if (in_array($config['id'], $this->defMList)) {
            //render default metabox for main object
            $dataType = substr($config['id'], 8);
            $this->viewNode->renderHTML($dataType);
        } else {
            //render custom metabox for custom post type
        }
    }

    /*
     * Get Current Post Type
     * 
     * @return string Cost Type
     */

    public function getCurrentPostType() {
        global $post;

        if ($post->post_type) {
            $post_type = $post->post_type;
        } elseif (isset($_GET['post'])) {
            $post_type = get_post_field('post_type', $_GET['post']);
        } elseif (!isset($_GET['post_type'])) {
            $post_type = 'post';
        } else {
            $post_type = $_GET['post_type'];
        }

        return $post_type;
    }

    /*
     * Get Current Post ID
     * 
     * @return mixed Return current post ID or null if FALSE
     */

    public function getCurrentPostID() {
        global $post;

        return ($post->ID ? $post->ID : NULL);
    }

    /*
     * Get Object Properties
     * 
     * @param int Object's ID to get data from
     * @param string Data Type to merge with default WP settings
     * @return array Return Object properties or empty array if object doesn't exist
     */

    public function getObjectProperties($oID, $dataType) {

        $dummy->properties = array();
        $props = isset($this->objectList[$oID]) ? $this->objectList[$oID] : $dummy;
        mp_parse_args(&$props->properties[$dataType], $dataType);

        return $props;
    }

    /*
     * Get list of CPTs
     * 
     */

    private function getObjectList() {

        $posts = $this->getPosts(self::$mainID);
        if (is_array($posts) && count($posts) > 0) {
            foreach ($posts as $post) {
                $this->objectList[$post->ID] = $post;
                //get additional information about object
                $metaData = $this->getData($post->ID, MP_PREFIX . 'object_setup', 'ARRAY');
                $this->objectList[$post->ID]->properties = array();
                foreach ($metaData as $key => $data) {
                    $this->objectList[$post->ID]->properties[$key] = (is_array($data) ? $data : array());
                }
            }
        }
    }

    /*
     * Get Post List
     * 
     * @param string Post Type
     * @param array Array of arguments. Default is empty array
     */

    private function getPosts($post_type, $args = array()) {

        $default = array(
            'post_type' => $post_type,
            'post_status' => array('active', 'inactive'),
            'post_per_page' => -1
        );

        $sArgs = wp_parse_args($args, $default);

        return get_posts($sArgs);
    }

    /*
     * Insert Meta Data to DB
     * 
     * @param int Post ID to insert in
     * @param string Meta Key
     * @param mixed Data to insert. Objects and arrays will be serialized
     * @return bool Operation execution status
     */

    private function insertMetaData($pID, $key, $value) {

        if (!$value) {
            $result = delete_post_meta($pID, $key);
        } elseif (get_post_meta($pID, $key, true)) {
            $result = update_post_meta($pID, $key, $value);
        } else {
            $result = add_post_meta($pID, $key, $value);
        }


        return $result;
    }

    /*
     * Get Meta Data from DB
     * 
     * @param int Post ID to get from
     * @param string Meta Key
     * @param string Default return value if DB value is empty. Default STRING
     * @return 
     */

    private function getData($postID, $key, $defaultReturn = 'STRING') {

        $data = get_post_meta($postID, $key, TRUE);

        if (!$data) {
            switch ($defaultReturn) {
                case 'ARRAY':
                    $data = array();
                    break;

                default:
                    break;
            }
        }

        return $data;
    }

    /*
     * Get mainID
     */

    public static function get_mainID() {

        return self::$mainID;
    }

    /*
     * Return the list of main object view table columns
     * 
     */

    function main_object_columns() {
        
    }

    /*
     * Manage custom view Table column
     * 
     * @param string column Name
     * @param int Current Post ID
     */

    public function manage_custom_column($column, $pID) {
        global $post;

        //check if current post is needed
        if (is_object($post) && ($post->ID == $pID)) {
            $tPost = $post;
        } else {
            $tPost = get_post($pID);
        }

        //get column data and print it
    }

}

?>