<?php

class mp_View {
    /*
     * Pointer to Model Node
     * 
     * @var object <mp_Model>
     */

    private $modelNode;

    /*
     * Global subparts holder
     * 
     * @var object <phpQuery>
     */
    private $globDoc;

    /*
     * Constructor
     * 
     */

    function __construct() {

        add_action('admin_print_scripts', array($this, 'admin_print_scripts'));
        add_action('admin_print_styles', array($this, 'admin_print_styles'));
        //create global subparts
        $this->globDoc = $this->getHTML('global_subparts');
    }

    /*
     * Print necessary JS scripts to header of HTML page
     */

    public function admin_print_scripts() {

        if ($this->modelNode->loadJS()) {
            wp_enqueue_script(MP_PREFIX . 'ui-all', MP_JS_DIR . 'ui/jquery-ui.js', array('jquery'));
            //TODO : URGENT - Change tooltip
            //wp_enqueue_script(MP_PREFIX . 'tooltip', MP_JS_DIR . 'ui/jquery.ui.tooltip.js');
            //wp_enqueue_script(MP_PREFIX . 'tooltip', MP_JS_DIR . 'jquery.tools.min.js');
            wp_enqueue_script(MP_PREFIX . 'admin-general', MP_JS_DIR . 'admin-general.js');
            wp_localize_script(MP_PREFIX . 'admin-general', MP_PREFIX . 'userSettings', array(
                'nonce' => $this->modelNode->getNonce(),
            ));
        }
    }

    /*
     * Print necessary CSS to header
     */

    public function admin_print_styles() {
        if ($this->modelNode->loadCSS()) {
            wp_enqueue_style(MP_PREFIX . 'ui', MP_CSS_DIR . 'ui/jquery.ui.all.css');
            wp_enqueue_style(MP_PREFIX . 'admin-general', MP_CSS_DIR . 'admin-general.css');
        }
    }

    /*
     * Set model Node
     * 
     * @param object Reference to mp_Model
     */

    public function setModelNode($modelNode) {

        $this->modelNode = $modelNode;
    }

    /*
     * Get HTML
     * 
     * @param string Data Type to render
     * @param bool Return rendered HTML or print it. Default - FALSE
     * @return mixed Depending on returnHTML, return string or none
     */

    public function renderHTML($dataType, $returnHTML = FALSE) {

        $doc = $this->getHTML($dataType);

        //get proper data
        $oID = $this->modelNode->getCurrentPostID();
        $data = $this->modelNode->getObjectProperties($oID, $dataType);

        switch ($dataType) {
            case 'object_args':
                //add field list select options
                $opList = $this->modelNode->renderSoSFields();
                $doc['select[name*=[show_on_screen][#n][field]]']->html($opList);
                $table = $doc['#object_args-4 table tbody'];
                $clone = $table['tr:first']->clone();
                //clean table and populate with list of options
                $table->empty();

                if (is_array($data->properties[$dataType]['show_on_screen'])){
                    foreach($data->properties[$dataType]['show_on_screen'] as $id => $dummy){
                        $table->append($clone->htmlOuter());
                        $table['tr:last']->attr('id', $id);
                        $act = "mpObj.deleteRow('#object_args-4 table #{$id}', true);";
                        $table['tr:last .delete-sos']->attr('onClick', $act);
                        //update names
                        foreach ($table["#{$id} input,#{$id} textarea,#{$id} select"] as $el) {
                            $name = $el->getAttribute('name');
                            $el->setAttribute('name', str_replace('#n', $id, $name));
                        }
                        
                    }
                }
                //update Add New Button
                $doc['#object_args-4 .mp_button a'] = __('Add New Column', MP_PLUGIN_NAME);
                $doc['#object_args-4 .mp_button a']->attr('href', 'javascript:void(0);');
                $doc['#object_args-4 .mp_button a']->attr('onClick', "mpObj.addNewSoS('{$dataType}');");
                $this->updateHTML($doc, $data->properties);
                break;

            case 'object_stats':
            case 'object_taxons':
                $liClone = $doc['ul > li']->clone();
                $divClone = $doc['.mp-tabs #' . $dataType]->clone();
                //clear li and div and start filling tabs
                $doc['ul']->empty();
                $doc['.mp-tabs #' . $dataType]->remove();

                if (count($data->properties[$dataType])) {
                    foreach ($data->properties[$dataType] as $id => $status) {
                        //add new status
                        $doc['ul']->append($liClone->htmlOuter());
                        $doc['.mp-tabs']->append($divClone->htmlOuter());
                        //add tab name
                        $doc['ul > li:last a'] = $status['title'];
                        //set proper attributes for ui-tabs
                        $hID = $dataType . '-' . $id;
                        $doc['ul > li:last a']->attr('href', '#' . $hID);
                        $doc['.mp-tabs > div:last']->attr('id', $hID);
                        //update names
                        foreach ($doc["#{$hID} input,#{$hID} textarea,#{$hID} select"] as $el) {
                            $name = $el->getAttribute('name');
                            $el->setAttribute('name', str_replace('#n', $id, $name));
                        }
                    }
                } else { //Notify user that list is Empty
                    $doc['.mp-tabs ul']->attr('style', 'display:none');
                    $notice = $this->globDoc['.notice-message']->clone();
                    $notice['p']->append(MP_NOTICE_EMPTYLIST);
                    $doc['.mp-tabs']->append($notice);
                }
                //update Add New Button
                $doc['.mp_button a'] = __('Add New', MP_PLUGIN_NAME);
                $doc['.mp_button a']->attr('href', 'javascript:void(0);');
                $doc['.mp_button a']->attr('onClick', "mpObj.addNewTab('{$dataType}');");
                $this->updateHTML($doc, $data->properties);
                break;

            default:
                break;
        }
        do_action(MP_PREFIX . $dataType, $this, $doc, $data, $dataType);

        $content = $doc->htmlOuter();
        unset($doc);


        if (!$returnHTML) {
            echo $content;
        } else {
            return $content;
        }
    }

    /*
     * Get HTML and prepare phpQuery object
     * 
     * @param string Data type
     * @param bool Return false result for AJAX request or throw exeption
     * @return object <phpQuery>
     */

    public function getHTML($dataType, $returnFALSE = FALSE) {

        $template = MP_PREFIX . $dataType . '.html';
        /*
         * Get HTML and create phpQuery object
         */
        $path = MP_HTML_DIR . $template;
        if (file_exists($path)) {
            $doc = phpQuery::newDocumentFileHTML($path);
        } else {
            if ($returnFALSE) {
                $this->responseAJAX($this->errorResponse(MP_ERROR_TEMPLATE), TRUE);
            } else {
                Throw new Exception(MP_ERROR_TEMPLATE);
            }
        }

        return $doc;
    }

    /*
     * Update HTML with data
     * 
     * @param object <phpQuery>
     * @param array Data
     * @param string Parent Level of Data
     */

    public function updateHTML($doc, &$data) {

        $curPT = $this->modelNode->getCurrentPostType();
        foreach ($doc['input, textarea, select'] as $el) {
            $name = $el->getAttribute('name');
            preg_match_all('/\[([a-z_0-9]{1,})\]/i', $name, $matches);
            if (is_array($matches[1])) {
                /*
                 * Exclude first element if it's current post type
                 * Expecting that field name is in next format:
                 * prefix[{post_type}][{data_level_1}]...[{data_level_n}]
                 * 
                 */
                if ($matches[1][0] == $curPT) {
                    unset($matches[1][0]);
                }
                $value = $data;
                foreach ($matches[1] as $key) {
                    if (isset($value[$key])) {
                        $value = $value[$key];
                    } else {
                        $value = NULL;
                        break;
                    }
                }

                switch ($el->tagName) {
                    case 'input':
                        $t = $el->getAttribute('type');
                        if (in_array($t, array('checkbox', 'radio')) && ($value !== NULL)) {
                            $value = (is_array($value) ? $value : array($value));
                        }
                        break;

                    default:
                        break;
                }

                $doc[$el->tagName . '[name="' . $name . '"]']->val($value);
                //update label
                $l = preg_replace('/(_mp_[0-9a-z]{1,}_)/', '_', 'l_' . implode('_', $matches[1]));
                $lu = strtoupper($l);

                if (defined($lu)) {
                    $label = constant($lu);
                } else {
                    $label = $this->modelNode->humanReadable(end($matches[1]));
                }
                $label = apply_filters(MP_PREFIX . 'field_label', $label, $matches[1]);
                $doc['#' . $l] = $label;
                unset($label);
                //update tooltip
                $l = preg_replace('/^l_/', 't_', $l);
                //check if this is multiplied field set and change if so
                $lu = strtoupper($l);

                if (defined($lu)) {
                    $tooltip = constant($lu);
                }
                $tooltip = apply_filters(MP_PREFIX . 'field_tooltip', $tooltip, $matches[1]);
                if (trim($tooltip)) {
                    $doc['#' . $l]->attr('title', $tooltip);
                } else {
                    $doc['#' . $l]->remove();
                }
                unset($tooltip);
            }
        }
    }

    /*
     * Get Select option
     * 
     * @param string Option value
     * @param string Option label
     * @param bol True if selected
     * @return string HTML
     */

    public function getSelectOptionHTML($value, $label, $selected = FALSE) {

        $selected = ($selected ? 'selected="selected"' : '');

        return "<option value='{$value}' {$selected}>{$label}</option>";
    }

    /*
     * Wrap list of select options into optgroup
     * 
     * @param string Option Group Title
     * @param string List of options
     * @return string Rendred Select group
     */

    public function wrapSelectOptions($title, $options) {

        return "<optgroup label='{$title}'>{$options}</optgroup>";
    }

    /*
     * Prepare Error Response array for AJAX response
     * 
     * @param string Error Message
     * @return array Error Response array
     * 
     */

    public function errorResponse($message) {

        $array = array(
            'status' => 'error',
            'message' => $message
        );

        return $array;
    }

    /*
     * Response AJAX request
     * 
     * @param mixed Response data
     * @param bool Response as JSON or plain text
     */

    public function responseAJAX($data, $json = TRUE) {

        $result = ($json ? json_encode($data) : $data);

        die($result);
    }

}

?>