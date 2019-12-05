<?php

/*
 * @version $Id: usermenu.inc.php 7 2019-12-04 17:15:56Z hi $
 *
 */

if (!defined('XH_ADM')) {
	header('HTTP/1.0 403 Forbidden');
    return;
}

if ($plugin_tx['hi_admin']['usermenu'] != '') {
    $hi_admUserMenu = "'" . rmanl($plugin_tx['hi_admin']['usermenu']) . "'";

    $hi_admJS = <<<EOS
<script type="text/javascript">
/* <![CDATA[ */
jQuery(document).ready(function($){
    $("#xh_adminmenu ul li:has(a[href*='&logout'])").before($hi_admUserMenu);
});
/* ]]> */
</script>
EOS;

    $bjs .= $hi_admJS;
}