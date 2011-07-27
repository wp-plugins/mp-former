<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of mp_post_submit_metabox
 *
 * @author vasyl
 */
class mp_model_coreSubmitDiv {
    /*
     * View Node refference
     * 
     * @var object <mp_View>
     * @access private
     */

    private $viewNode;

    /*
     * Model Node refference
     * 
     * @var object <mp_Model>
     * @access private
     */
    private $modelNode;

    /*
     * Constructor
     * 
     * @param array Array of setting from model Node
     */

    function __construct($viewNode, $modelNode) {

        $this->viewNode = $viewNode; //
        $this->modelNode = $modelNode; //
    }

    /*
     * Render Submit Metabox 
     * TODO : MAJOR - Rewrite this function. Probably next version of plugin
     */

    function post_submit_meta_box($post) {
        global $action, $wp_post_statuses;

        ob_start();
        post_submit_meta_box($post);
        $metabox = ob_get_contents();
        ob_clean();
        $doc = phpQuery::newDocumentHTML($metabox);
        if ($post->post_status != 'auto-draft') {
            $doc['#post-status-display']->html($wp_post_statuses[$post->post_status]->label);
        }
        if (is_array($wp_post_statuses)) {
            $select = $doc['select[name="post_status"]'];
            $select->empty(); //empty status list and add all statuses
            foreach ($wp_post_statuses as $id => $status) {
                if ($status->{MP_PREFIX . 'core'}) {
                    $option = '<option value="' . $id . '">' . $status->label . '</option>';
                    $select->append($option);
                }
            }
            $select->val($post->post_status);
            $doc['#minor-publishing-actions']->remove();
            $doc['#visibility']->remove();
            
            //change default Publish button to Save button
            $doc['#publish']->attr('name', 'save');
            $doc['#publish']->attr('value', esc_attr__('Update'));
            //update standart publish ID, so JS will not update it after changes
            $doc['#publish']->attr('id', 'mp-publish');
        }
        echo $doc->htmlOuter();
    }

}

?>
