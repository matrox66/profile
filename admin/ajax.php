<?php
/**
 *  Common AJAX functions
 *
 *  @author     Lee Garner <lee@leegarner.com>
 *  @copyright  Copyright (c) 2009 Lee Garner <lee@leegarner.com>
 *  @package    profile
 *  @version    0.0.2
 *  @license    http://opensource.org/licenses/gpl-2.0.php 
 *  GNU Public License v2 or later
 *  @filesource
 */

/**
 *  Include required glFusion common functions
 */
require_once '../../../lib-common.php';

// This is for administrators only
if (!SEC_hasRights('profile.admin')) {
    COM_accessLog("User {$_USER['username']} tried to illegally access the profile AJAX functions.");
    exit;
}

switch ($_POST['action']) {
case 'toggleEnabled':
    $oldval = $_POST['oldval'] == 0 ? 0 : 1;
    $newval = $oldval == 0 ? 1 : 0;
    $type = DB_escapeString($_POST['type']);
    $id = (int)$_POST['id'];

    switch ($type) {
    case 'required':
    case 'enabled':
    case 'user_reg':
        // Toggle the is_origin flag between 0 and 1
COM_errorLog("UPDATE {$_TABLES['profile_def']}
                SET $type = '$newval'
                WHERE id='$id'");
        DB_query("UPDATE {$_TABLES['profile_def']}
                SET $type = '$newval'
                WHERE id='$id'", 1);
        break;

    case 'readonly':
        // Change the perm_owner to 2 if readonly is 1
        $db_val = $newval == 1 ? 2 : 3;
        DB_query("UPDATE {$_TABLES['profile_def']}
                SET perm_owner = $db_val
                WHERE id = '$id'", 1);
        break;

    case 'public':
        // Change the perm_owner to 2 if readonly is 1
        $db_val = $newval == 1 ? 2 : 0;
        DB_query("UPDATE {$_TABLES['profile_def']}
                SET perm_members = $db_val, perm_anon = $db_val
                WHERE id = '$id'", 1);
        break;

     default:
        exit;
    }
    if (DB_error()) {
        // Reset newval to flag that the update wasn't done.
        $newval = $oldval;
        COM_errorLog("Error: $sql");
    }

    $result = array(
        'statusMessage' => $newval == $oldval ? $LANG_PROFILE['toggle_failure'] :
            $LANG_PROFILE['toggle_success'],
        'newval' => $newval,
    );
    echo json_encode($result);
    exit;

default:
    exit;
}

?>
