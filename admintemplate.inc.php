<?php

/*
 * @version $Id: admintemplate.inc.php 7 2019-12-04 17:15:56Z hi $
 *
 */

if (!defined('XH_ADM')) {
	header('HTTP/1.0 403 Forbidden');
    return;
}

/**
 * Checks if admin-template is requested
 */
function hi_adm_CheckRequest() {
    global $cf, $edit, $file, $o, $plugin_cf, $pth;
    $template = $plugin_cf['hi_admin']['admintemplate_template'];
    if ($template == '')
        return;
    if (!is_readable($pth['folder']['templates'] . $template . '/template.htm'))
        $template = FALSE;

    //do not read all plugins again and again...
    if (!isset($_SESSION['hi_admGetVars'])) {
        $_SESSION['hi_admGetVars'] = array();
        if ($plugin_cf['hi_admin']['admintemplate_plugins'] == 'true') {
            $_SESSION['hi_admGetVars'] = array_merge($_SESSION['hi_admGetVars'], hi_adm_InstalledPlugins(TRUE));
        }
        if ($plugin_cf['hi_admin']['admintemplate_pagemanager'] == 'true') {
            $_SESSION['hi_admGetVars'][] = 'xhpages';
        }
        if ($plugin_cf['hi_admin']['admintemplate_filebrowser'] == 'true') {
            $req = array('images', 'downloads', 'media', 'userfiles');
            $_SESSION['hi_admGetVars'] = array_merge($_SESSION['hi_admGetVars'], $req);
        }
        if ($plugin_cf['hi_admin']['admintemplate_configuration'] == 'true') {
            $req = array('settings', 'validate', 'sysinfo', 'xh_backups', 'xh_pagedata');
            $_SESSION['hi_admGetVars'] = array_merge($_SESSION['hi_admGetVars'], $req);
        }
    }
    if ($plugin_cf['hi_admin']['admintemplate_configuration'] == 'true') {
        $files = array('language', 'config', 'template', 'stylesheet', 'log', 'content');
    }

    if (isset($files) && in_array($file, $files))
        return(hi_adm_SwitchTpl($template));

    foreach ($_SESSION['hi_admGetVars'] as $param) {
        if (isset($_GET[$param])) {
            return(hi_adm_SwitchTpl($template));
        }
    }

    if ($plugin_cf['hi_admin']['admintemplate_contenteditor'] == 'true'
            && $edit) {
        return(hi_adm_SwitchTpl($template));
    }
}

/**
 * Template-switch
 */
function hi_adm_SwitchTpl($template = FALSE) {
    global $o, $plugin_tx, $pth;

    if ($template === FALSE) {
        $o .= XH_message('fail', $plugin_tx['hi_admin']['message_admintemplate_not_found']);
        return;
    }
    $pth['folder']['template'] = $pth['folder']['templates'] . $template . '/';
    $pth['file']['template'] = $pth['folder']['template'] . 'template.htm';
    $pth['file']['stylesheet'] = $pth['folder']['template'] . 'stylesheet.css';
    $pth['folder']['menubuttons'] = $pth['folder']['template'] . 'menu';
    $pth['folder']['templateimages'] = $pth['folder']['template'] . 'images';
}

/* * *************************************************************************** */

//initvar('xh_success');
if (isset($_GET['xh_success']) && $_GET['xh_success'] === 'config') {
    //reset only when configuration has changed
    unset($_SESSION['hi_admGetVars']);
}

hi_adm_CheckRequest();