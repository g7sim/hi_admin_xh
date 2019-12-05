<?php

/*
 * @version $Id: templateeditor.inc.php 7 2019-12-04 17:15:56Z hi $
 *
 */

if (!defined('XH_ADM')) {
	header('HTTP/1.0 403 Forbidden');
    return;
}

if (in_array($file, array('template', 'stylesheet'))) {
    switch ($file) {
        case 'template':
            $file = false; //drop core-request
            $o .= hi_adm_tplLinkList();
            break;
        case 'stylesheet':
            $file = false; //drop core-request
            $o .= hi_adm_tplLinkList('stylesheet');
            break;
    }
}


include_once $pth['folder']['classes'] . 'FileEdit.php';

class HI_adm_TextFileEdit extends XH\TextFileEdit {
    
    protected $file = null;
    
    public function __construct() {

        global $pth;
        $file = $_REQUEST['admfile'];
        strpos($file, '.htm') ? $success = 'template' : $success = 'stylesheet';
        //read and write only in templates-folder
        $this->filename = $pth['folder']['templates'] . $file;
        $this->params = array('admfile' => $file, 'action' => 'save');
        $this->redir = "?hi_admin&admfile=" . $file . "&action=edit&xh_success=" . $success;
        $this->textareaName = 'hi_adm_textarea';
        parent::__construct();
    }
}

/**
 * Returns a list of links of files to edit
 */
function hi_adm_tplLinkList($type = 'template') {
    global $cf, $file, $sn, $su, $tx, $plugin_tx, $pth;

    $filename = '/template.htm';
    if ($type == 'stylesheet') {
        $filename = '/stylesheet.css';
    }

    $templates = hi_adm_GetTemplates();
    $type == 'stylesheet' ? $t = '<h1>' . $plugin_tx['hi_admin']['heading_templateeditor_stylesheet'] . '</h1>' : $t = '<h1>' . $plugin_tx['hi_admin']['heading_templateeditor_template'] . '</h1>';
    $t .= $plugin_tx['hi_admin']['heading_templateeditor_linklist'];
    $t .= '<ul>';
    foreach ($templates as $template) {
        if ($template != '') {
            $template == $cf['site']['template'] ? $name = '<b>' . XH_hsc($template) . $plugin_tx['hi_admin']['templateeditor_default-template'] . '</b>' : $name = XH_hsc($template);
            if (is_writable($pth['folder']['templates'] . $template . $filename )) {
				$t .= '<li><a href=' . $sn . '?hi_admin&admfile='
                   . $template . $filename . '>' . $name . '</a></li>' . PHP_EOL;
			} else {
				$t .= '<li>' . $tx['error']['cntwriteto'] . '&nbsp;' . $name . '</li>' . PHP_EOL;
			}
        }
    }
    $t .= '</ul>';
    return $t;
}

/**
 * enables CodeEditor_XH
 */
function hi_adm_CodeEditor($mode = '') {
    global $bjs, $plugin_cf, $pth;

    if (isset($plugin_cf['codeeditor']) && $plugin_cf['codeeditor']['enabled']) {
        include_once $pth['folder']['plugins'] . 'codeeditor/init.php';
		$classes = array('xh_file_edit');
		if ($mode == 'css') {
			init_codeeditor_css($classes);
		} else {
			init_codeeditor($classes);
		}
	}
}

/**
 * A basic syntax-check for php-code in the template.
 * Returns bool:false if no error is found,
 * otherwise the script dies and throws the php-errormessage
 */
function hi_adm_TplSyntaxError($code, $templatename) {
    global $pth, $plugin_cf;

    //only check template.htm if allowed by config
    if ($plugin_cf['hi_admin']['templateeditor_syntaxcheck'] == 'true'
            && strpos($templatename, '/template.htm')) {
        //save original template-path
        $tplpth = $pth['folder']['template'];
        //we need the correct path, if the template includes other files for example
        $pth['folder']['template'] = $pth['folder']['templates'] . str_replace('/template.htm', '', $templatename) . '/';
        $pattern = '/<\?php(.*)\?>/isU';
        $matches = NULL;
        if (preg_match_all($pattern, $code, $matches)) {
            $matches = array_map('trim', $matches[1]);
            foreach ($matches as $value) {
                ob_start();
                error_reporting(E_ERROR | E_PARSE);
                $t = eval($value . ';');
                ob_end_clean();
            }
        }
        //restore template-path
        $pth['folder']['template'] = $tplpth;
    }
    return false;
}

function hi_adm_saveTemplate()
{
    global $fileEditor, $o;
    
    //XH_after_Pluginloading (in admin.php) dazu
    if (!hi_adm_TplSyntaxError($_REQUEST['hi_adm_textarea'], $_REQUEST['admfile'])) {
        $o .= $fileEditor->submit();
    }
}