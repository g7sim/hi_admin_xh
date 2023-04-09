<?php

/*
 * @version $Id: admin.php 7 2019-12-04 17:15:56Z hi $
 *
 */
 

if (!defined('XH_ADM')) {
	header('HTTP/1.0 403 Forbidden');
    return;
}

if (version_compare(CMSIMPLE_XH_VERSION, 'CMSimple_XH 1.6', '<') || defined('CMSIMPLE_RELEASE')) {
    $o .= XH_message('fail', $plugin_tx['hi_admin']['message_xh_version']);
    return;
}

/*
 * Register the plugin menu items.
 */
if (function_exists('XH_registerStandardPluginMenuItems')) {
    XH_registerStandardPluginMenuItems(false);
}

if (!include_once($pth['folder']['plugins'] . 'jquery/jquery.inc.php')) {
    $o .= XH_message('fail', $plugin_tx['hi_admin']['message_jQuery']);
    return;
}
include_jQuery();

if ($plugin_cf['hi_admin']['pluginmanager_activate'] ?? '' == 'true') {
    if (version_compare(CMSIMPLE_XH_VERSION, 'CMSimple_XH 1.6.2', '<')) {
        //deprecated, only for XH 1.6 - 1.6.1
        include_once $pth['folder']['plugins'] . 'hi_admin/pluginmanager_.inc.php';
    } else {
        include_once $pth['folder']['plugins'] . 'hi_admin/pluginmanager.inc.php';
    }
}

if ($plugin_cf['hi_admin']['usermenu_activate'] ?? '' == 'true')
    include_once $pth['folder']['plugins'] . 'hi_admin/usermenu.inc.php';

if ($plugin_cf['hi_admin']['admintemplate_activate'] ?? '' == 'true')
    include_once $pth['folder']['plugins'] . 'hi_admin/admintemplate.inc.php';

if ($plugin_cf['hi_admin']['templateeditor_activate'] ?? '' == 'true') {
    include_once $pth['folder']['plugins'] . 'hi_admin/templateeditor.inc.php';
    //Stylesheet or template - editor requested?
    if (isset($_REQUEST['admfile']) && is_readable($pth['folder']['templates'] . $_REQUEST['admfile'])) {
        $fileEditor = new HI_adm_TextFileEdit;
        if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'save') {
            XH_afterPluginLoading('hi_adm_saveTemplate');
            return;
        } else {
            $o .= '<h1>' . $plugin_tx['hi_admin']['heading_templateeditor_editfile'] . '</h1>';
            $o .= '<b>' . $pth['folder']['templates'] . XH_hsc($_REQUEST['admfile']) . '</b>';
            //fix for CodeEditor_XH with $file dropped by this plugin
            if (basename($_REQUEST['admfile']) == 'template.htm') {
                $o .= hi_adm_CodeEditor('php');
            } else {
                $o .= hi_adm_CodeEditor('css');
            }
            $o .= $fileEditor->form();
            return;
        }
    }
}

//Handle the plugin administration
if (function_exists('XH_wantsPluginAdministration') 
        && XH_wantsPluginAdministration('hi_admin') 
        || isset($hi_admin) && $hi_admin == 'true') {

    if ($admin != 'pluginmanager')
        $o .= print_plugin_admin('on');
    switch ($admin) {
        case '':
            $o .= hi_adm_Version();
            break;
        case 'plugin_main':
            $o .= hi_adm_Version();
            break;
        case 'pluginmanager':
            $o .= hi_adm_PluginManager();
            break;
        default:
            $o .= plugin_admin_common($action, $admin, $plugin);
    }
}

/**
 * Show plugin informations
 */
function hi_adm_Version() {
    global $pth;

    $o = '<h1>hi_Admin for CMSimple_XH</h1>' . "\n";
    $o .= tag('img style="float:left; margin:10px 20px 10px 0;" src="'.$pth['folder']['plugins'].'hi_admin/hi_admin_128.png" alt="Plugin Icon"');
    $o .= '<p><strong>Version: </strong> 1.0-beta.2 - 2019-12-04' . tag('br') . PHP_EOL;
    $o .= '&copy;2019 Holger Irmler - all rights reserved' . tag('br') . PHP_EOL;
    $o .= 'Email: <a href="mailto:CMSimple@HolgerIrmler.de">CMSimple@HolgerIrmler.de</a>' . tag('br') . PHP_EOL;
    $o .= 'Website: <a href="http://cmsimple.holgerirmler.de" target="_blank">http://CMSimple.HolgerIrmler.de</a></p>' . PHP_EOL;
    $o .= '<p>This program is free software:'
        . ' you can redistribute it and/or modify'
        . ' it under the terms of the GNU General Public License as published by'
        . ' the Free Software Foundation, either version 3 of the License, or'
        . ' (at your option) any later version.</p>' . PHP_EOL
        . '<p>This program is distributed'
        . ' in the hope that it will be useful,'
        . ' but WITHOUT ANY WARRANTY; without even the implied warranty of'
        . ' MERCHAN&shy;TABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the'
        . ' GNU General Public License for more details.</p>' . PHP_EOL
        . '<p class="coco_license"">You should have received a copy of the'
        . ' GNU General Public License along with this program.  If not, see'
        . ' <a href="http://www.gnu.org/licenses/">http://www.gnu.org/licenses/</a>'
        . '.</p>' . PHP_EOL;

    return $o;
}

/* * * Global functions, used in different modules ** */

/**
 * Returns the installed templates for $plugin_mcf
 */
function hi_adm_getTemplates() {
    global $pth;
    $templates = array('');
    $handle = opendir($pth['folder']['templates']);
    while (false !== ($entry = readdir($handle))) {
        if (strpos($entry, '.') === false
                && is_dir($pth['folder']['plugins'])) {
            $templates[] = $entry;
        }
    }
    closedir($handle);
    return $templates;
}

/**
 * returns all installed plugins 
 * param (bool) $excluded: whether plugins which match $plugin_cf['hi_admin']['admintemplate_exclude']
 * should removed from output
 */
function hi_adm_InstalledPlugins($excluded = FALSE) {
    global $plugin_cf, $pth;
    $installed_plugins = array();

    $dh = opendir($pth['folder']['plugins']);
    if ($dh) {
        while ($plugin = readdir($dh)) {
            if (strpos($plugin, '.') === false
                    && is_dir($pth['folder']['plugins'] . $plugin)) {
                $installed_plugins[] = strtolower($plugin);
            }
        }
        closedir($dh);
    }
    if ($excluded) {
        $excludedPlugins = explode(',', strtolower($plugin_cf['hi_admin']['admintemplate_exclude']));
        $excludedPlugins = array_map('trim', $excludedPlugins);
        return array_diff($installed_plugins, $excludedPlugins);
    }
    return $installed_plugins;
}
