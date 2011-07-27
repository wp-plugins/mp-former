<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class mp_model_ajax {
    /*
     * Hold current AJAX request name
     * 
     * @var string Current AJAX request
     * @access private
     */

    private $action;

    /*
     * View Node holder
     * 
     * @var object <mp_View>
     * @access private
     */
    private $viewNode;

    /*
     * Model Node holder
     * 
     * @var object <mp_Model>
     * @access private
     */
    private $modelNode;

    /*
     * Constructor
     */

    function __construct($viewNode, $modelNode) {
        global $post;

        $this->viewNode = $viewNode;
        $this->modelNode = $modelNode;
        //get current action
        $this->action = $_POST['subact'];
        //set current post type as object. This is for updateHTML in view Node
        $post->post_type = $this->modelNode->get_mainID();

        //switch proper method to process request
        switch ($this->action) {
            case 'getHTML_tab':
                $type = $_POST['type'];
                $doc = $this->viewNode->getHTML($type);

                $liClone = $doc['.mp-tabs ul > li']->clone();
                $liClone['a'] = '#{label}';
                $liClone['a']->attr('href', '#{href}');

                $divClone = $doc['.mp-tabs > div'];
                $id = $this->modelNode->renderUID();
                $divClone->attr('id', $type . '-' . $id);
                //update HTML and set all labels and default values
                $default[$type] = array(
                    $id => array(
                        'title' => __('New', MP_PLUGIN_NAME),
                        'label_count' => array(
                            'singular' => '',
                            'plural' => ''
                        ),
                        'show_in_admin_all' => 1,
                        'show_in_admin_all_list' => 1,
                        'show_in_admin_status_list' => 1,
                    )
                );
                $this->viewNode->updateHTML($divClone, $default);
                //update names
                foreach ($divClone["input,textarea,select"] as $el) {
                    $name = $el->getAttribute('name');
                    $el->setAttribute('name', str_replace('#n', $id, $name));
                }

                $result = array(
                    'status' => 'success',
                    'liHTML' => str_replace(array('%7B', '%7D'), array('{', '}'), $liClone->htmlOuter()),
                    'divHTML' => $divClone->html(),
                    'title' => __('New', MP_PLUGIN_NAME),
                    'id' => $id
                );
                break;

            case 'getHTML_sos':
                $type = $_POST['type'];
                $doc = $this->viewNode->getHTML($type);
                $clone = $doc['#object_args-4 table tbody tr:first']->clone();
                $id = $this->modelNode->renderUID();
                $opList = $this->modelNode->renderSoSFields();
                $clone['select[name*=[show_on_screen][#n][field]]']->html($opList);
                $clone['tr']->attr('id', $id);
                $act = "mpObj.deleteRow('#object_args-4 table #{$id}', true);";
                $clone['.delete-sos']->attr('onClick', $act);
                //update names
                foreach ($clone["input,textarea,select"] as $el) {
                    $name = $el->getAttribute('name');
                    $el->setAttribute('name', str_replace('#n', $id, $name));
                }

                $result = array(
                    'status' => 'success',
                    'html' => $clone->htmlOuter()
                );
                break;

            default:
                break;
        }

        $this->viewNode->responseAJAX($result);
    }

}

?>