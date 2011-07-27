<?php

class mp_Control {
    /*
     * Model Node
     * 
     * @var object <mp_model>
     * @access private
     */

    private $modelNode;

    /*
     * View Node
     * 
     * @var object <mp_view>
     * @access private
     */
    private $viewNode;

    /*
     * Constructor
     */

    function __construct() {
        $this->modelNode = mp_create_object('mp_Model');

        if (is_admin()) {
            $this->viewNode = mp_create_object('mp_View');
            $this->modelNode->setViewNode($this->viewNode);
            $this->viewNode->setModelNode($this->modelNode);
            add_action('wp_ajax_' . MP_PREFIX . 'ajax', array($this, 'ajax'));
        }
    }

    /*
     * Hange AJAX requests
     * 
     */

    public function ajax() {

        //check nonce
        check_ajax_referer($this->modelNode->getNonce(FALSE), MP_PREFIX . 'nonce');

        $this->modelNode->processAJAX();
    }

    /*
     * Get main custom post type id
     */

    public static function get_mainID() {

        return mp_Model::get_mainID();
    }

}

?>