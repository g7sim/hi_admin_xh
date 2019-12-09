<?php

/*
 * @version $Id: pluginmanager_.inc.php 7 2019-12-04 17:15:56Z hi $
 *
 * for CMSimple_XH 1.6 & 1.6.1
 * 
 */

if (!defined('XH_ADM')) {
	header('HTTP/1.0 403 Forbidden');
    return;
}

function hi_adm_PluginManager() {
    global $cf, $plugin_cf, $plugin_tx, $sn, $su, $tx, $pth;
    global $_XH_csrfProtection;

    $o = '<div id="hi_adm_pluginmanager">' . PHP_EOL;
    $o .= '<h1>Pluginmanager</h1>'. PHP_EOL;

    if (isset($_REQUEST['pm_error'])) {
        $error = $_REQUEST['pm_error'];
        switch ($error) {
            case '1':
                $o .= XH_message('fail', $plugin_tx['hi_admin']['message_not_saved']);
                break;

            case '0':
                $o .= XH_message('success', $plugin_tx['hi_admin']['message_saved']);
                break;

            default:
                break;
        }
    }

    $o .= XH_message('warning', $plugin_tx['hi_admin']['message_pluginmanager_deactivate']);

    $ignore = explode(',', $plugin_cf['hi_admin']['pluginmanager_ignore']);
    $ignore = array_map('trim', $ignore);

    $plugins = hi_adm_InstalledPlugins();
    $plugins = array_map('strtolower', $plugins);
    $plugins = array_diff($plugins, $ignore);

    $inactivePlugins = array();
    $inactivePlugins = explode(',', $cf['plugins']['disabled']);
    $inactivePlugins = array_map('trim', $inactivePlugins);

    $hiddenPlugins = array();
    $hiddenPlugins = explode(',', $plugin_cf['hi_admin']['pluginmanager_hide']);
    $hiddenPlugins = array_map('trim', $hiddenPlugins);


    if (isset($_POST['action']) && $_POST['action'] == 'pm_save') {

        $_XH_csrfProtection->check();
        $toHide = array();
        $toDeactivate = array();

        foreach ($_POST as $key => $value) {
            switch ($value) {
                case 'pm_hidden':
                    $toHide[] = substr($key, 3);
                    break;

                case 'pm_inactive':
                    $toDeactivate[] = substr($key, 3);
                    break;

                default:
                    break;
            }
        }

        $error = FALSE;
        $config = XH_includeVar($pth['file']['config'], 'cf');
        if (!$config) {
            $error = TRUE;
        }
        if (!$error) {
            $config['plugins']['disabled'] = implode(',', $toDeactivate);
            if (!XH_writeFile($pth['file']['config'], hi_adm_asString($config))) {
                $error = TRUE;
            }
        }
        $config = NULL;
        if (!$error) {
            $file = $pth['folder']['plugins'] . 'hi_admin/config/config.php';
            $config = XH_includeVar($file, 'plugin_cf');
            if (!$config) {
                $error = TRUE;
            }
            if (!$error) {
                $config['hi_admin']['pluginmanager_hide'] = implode(',', $toHide);
                if (!XH_writeFile($file, hi_adm_asString($config, 'plugin_cf'))) {
                    $error = TRUE;
                }
            }
        }
        $errormsg = $error ? '&pm_error=1' : '&pm_error=0';
        return header("Location: " . $sn . "?" . $su . '&admin=pluginmanager' . $errormsg);
    }

    $o .= '<form method="post" action="' . $sn . '?' . $su . '">' . PHP_EOL;
    $o .= $_XH_csrfProtection->tokenInput(). PHP_EOL;
    $o .= tag('input type="hidden" value="pluginmanager" name="admin"') . PHP_EOL
            . tag('input type="hidden" value="pm_save" name="action"') . PHP_EOL
            . tag('input type="submit" value="' . ucfirst($tx['action']['save'])
                    . '" style="margin: 0 0 10px 0;"') . PHP_EOL
    ;
    $o .= tag('br');
    $o .= '<table>' . PHP_EOL;
    $o .= '<tr>' . PHP_EOL;
    $o .= '<th>' . $plugin_tx['hi_admin']['heading_plugintable_plugins'] . '</th>' .
            '<th colspan="2">' . $plugin_tx['hi_admin']['heading_plugintable_active'] . '</th>' .
            '<th>' . $plugin_tx['hi_admin']['heading_plugintable_deactivated'] . '</th>' . PHP_EOL;
    foreach ($plugins as $plugin) {
        if (in_array($plugin, $inactivePlugins))
            $mode = 'disabled';
        elseif (in_array($plugin, $hiddenPlugins))
            $mode = 'hidden';
        else
            $mode = 'active';
        $o .= '<tr>' . PHP_EOL;
        $o .= '<td>';
        $o .= $mode == 'disabled' ? '<span class="hi_adm_' . $mode . '">' . $plugin . '</span>' : '<a href="' . $sn . '?' . $plugin . '&normal"><span class="hi_adm_' . $mode . '">' . $plugin . '</span></a>';
        $o .= '</td>' . PHP_EOL;
        $selected = $mode == 'active' ? ' checked="checked"' : '';
        $o .= '<td>' . tag('input type="radio" id="active_' . $plugin . '" name="pm_' . $plugin . '" value="pm_active"' . $selected) . PHP_EOL;
        $o .= '<label for="active_' . $plugin . '">' . $plugin_tx['hi_admin']['plugintable_visible'] . '</label></td>' . PHP_EOL;
        $selected = $mode == 'hidden' ? ' checked="checked"' : '';
        $o .= '<td>' . tag('input type="radio" id="hidden_' . $plugin . '" name="pm_' . $plugin . '" value="pm_hidden"' . $selected) . PHP_EOL;
        $o .= '<label for="hidden_' . $plugin . '">' . $plugin_tx['hi_admin']['plugintable_hidden'] . '</label></td>' . PHP_EOL;
        $selected = $mode == 'disabled' ? ' checked="checked"' : '';
        $o .= '<td>' . tag('input type="radio" id="inactive_' . $plugin . '" name="pm_' . $plugin . '" value="pm_inactive"' . $selected) . PHP_EOL;
        $o .= '<label for="inactive_' . $plugin . '">' . $plugin_tx['hi_admin']['plugintable_deactivated'] . '</label></td>' . PHP_EOL;
        $o .= '</tr>' . PHP_EOL;
    }
    $o .= '</table>';
    $o .= tag('input type="submit" value="' . ucfirst($tx['action']['save'])
                    . '" style="margin: 10px 0 0 0;"') . PHP_EOL;
    $o .= '</form>' . PHP_EOL;

    $o .= '</div>' . PHP_EOL;
    return $o;
}

/**
 * returns a string of plugins defined in $plugin_cf['hi_admin']['pluginmanager_hide']
 * in JS array format
 */
function hi_adm_PluginsToHide() {
    global $plugin_cf;
    $hidden_plugins = '';

    if ($plugin_cf['hi_admin']['pluginmanager_hide'] != '') {
        $hidden_plugins = explode(',', $plugin_cf['hi_admin']['pluginmanager_hide']);
        $hidden_plugins = array_map('trim', $hidden_plugins);
        $hidden_plugins = "'" . implode("','", $hidden_plugins) . "'";
    }
    return "[" . $hidden_plugins . "]";
}

function hi_adm_asString($arr, $varName = 'cf') {
    $o = "<?php\n\n";
    foreach ($arr as $cat => $opts) {
        foreach ($opts as $name => $opt) {
            $opt = addcslashes($opt, "\0..\37\"\$\\");
            $o .= "$" . $varName . "['$cat']['$name']=\"$opt\";\n";
        }
    }
    $o .= "\n?>\n";
    return $o;
}

/******************************************************************************* */


$hi_admHide = hi_adm_PluginsToHide();
$hi_admTxPlugins = $tx['editmenu']['plugins'];

$hi_admJS = <<<EOS
<script>
jQuery(document).ready(function($){
    $("#xh_adminmenu ul li span:contains($hi_admTxPlugins)").replaceWith("<a href=\"$sn?hi_admin&amp;admin=pluginmanager&amp;action=pm_edit\">$hi_admTxPlugins</a>");
    var toHide = $hi_admHide;
    if (toHide !== '[]') {
        $.each(toHide , function(index, value){
          $("#xh_adminmenu ul li ul li:has(a[href*='"+value+"'])").remove();
        });
    };
});
</script>
EOS;

$bjs .= $hi_admJS;