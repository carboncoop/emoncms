<?php

/*
  All Emoncms code is released under the GNU Affero General Public License.
  See COPYRIGHT.txt and LICENSE.txt.

  ---------------------------------------------------------------------
  Emoncms - open source energy visualisation
  Part of the OpenEnergyMonitor project:
  http://openenergymonitor.org
 * 
  ---------------------------------------------------------------------
  Update module developed by Carbon Co-op
  http://carbon.coop/

 */

defined('EMONCMS_EXEC') or die('Restricted access');

function update_controller() {
    global $mysqli, $session, $route, $path;

    if ($session['admin'] != 1)
        return array('content' => _('<h1 style="margin: 50px">You haven\' got enough permissions</h1>'));
    require_once "Modules/update/update_model.php";
    $update = new Update();
    if ($session) {
        if ($route->format == 'html') {
            if ($route->action == "list" && $session['write']) {
                $available_updates = $update->check_updates();
                $result = view("Modules/update/Views/update_view.php", array('available_updates' => $available_updates));
            }
        }
        else if ($route->format == 'json' && $session['write']) {
            if ($route->action == 'list') {
                $result = $update->check_updates();
            }
            else if ($route->action == 'update')
                $result = $update->update(get('source'), get('item'));
            else if ($route->action == 'updateavailable') {
                $item = ['git' => true, 'name' => get('item')];
                $result = $update->update_available(get('source'), $item);
            }
            else if ($route->action == 'closeupdatemessage')
                $_SESSION['update_message_closed'] = true;
        }
    }

    return array('content' => $result);
}
