<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit();
}

include_once 'includes/class-bc-tools-uninstaller.php';
BC_Tools_Uninstaller::uninstall();