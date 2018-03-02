<?php

/*
  All Emoncms code is released under the GNU Affero General Public License.
  See COPYRIGHT.txt and LICENSE.txt.

  ---------------------------------------------------------------------
  Emoncms - open source energy visualisation
  Part of the OpenEnergyMonitor project:
  http://openenergymonitor.org

  ---------------------------------------------------------------------
  Update module developed by Carbon Co-op
  http://carbon.coop/

 */

// no direct access
defined('EMONCMS_EXEC') or die('Restricted access');

/*
 * Create a new update
 * 
 *
 */

class Update {

    public $emoncms;
    public $modules;
    public $themes;
    private $log;

    public function __construct() {
        $this->emoncms = $this->ini('emoncms');
        $this->modules = $this->ini('modules');
        $this->themes = $this->ini('themes');
        $this->log = new EmonLogger(__FILE__);
    }

    private function ini($type) {
        if ($type == 'emoncms') {
            $temp = ['name' => 'emoncms', 'git' => file_exists(".git")];
            $temp['update_available'] = $this->update_available('emoncms', $temp);
            if ($temp['update_available'] === true) {
                if ($this->file_changed('default.settings.php', 'emoncms') || $this->file_changed('default.emonpi.settings.php', 'emoncms'))
                    $temp['default_settings_changed'] = true;
                else
                    $temp['default_settings_changed'] = false;
                $temp['has_update_permissions'] = $this->has_update_permissions('emoncms', $temp);
                if ($temp['has_update_permissions'] === false)
                    $temp['permissions_message'] = $this->permissions_message('emoncms', $temp);
            }
            return $temp;
        }
        else {
            $items = [];
            $dir_name = $this->get_dir($type);
            $dirs = scandir($dir_name);
            for ($i = 2; $i < count($dirs); $i++) {
                if (filetype($dir_name . $dirs[$i]) == 'dir' || filetype($dir_name . $dirs[$i]) == 'link') {
                    $item = ['name' => $dirs[$i], 'git' => false, 'update_available' => false];
                    if (file_exists($dir_name . $dirs[$i] . "/.git")) {
                        $item ['git'] = true;
                        $item['update_available'] = $this->update_available($type, $item);
                        $item['has_update_permissions'] = $this->has_update_permissions($type, $item);
                        if ($item['has_update_permissions'] === false)
                            $item['permissions_message'] = $this->permissions_message($type, $item);
                        else {
                            $schema_file = 'Modules/' . $item['name'] . '/' . $item['name'] . "_schema.php";
                            if (file_exists($schema_file) && $this->file_changed($item['name'] . "_schema.php", 'modules', $item)) {
                                $item['db_update_required'] = true;
                            }
                        }
                    }
                    array_push($items, $item);
                }
            }
            return $items;
        }
    }

    public function check_updates() {
        $updates_available = ['emoncms' => [], 'modules' => [], 'themes' => []];
        $module_status = [];

        // Check emonCMS
        if ($this->emoncms['update_available'] === true)
            array_push($updates_available['emoncms'], $this->emoncms);

        // Check modules
        foreach ($this->modules as $module) {
            if ($module['update_available'] === true)
                array_push($updates_available['modules'], $module);
        }

        // Check themes
        foreach ($this->themes as $theme) {
            if ($theme['update_available'] === true)
                array_push($updates_available['themes'], $theme);
        }

        return $updates_available;
    }

    public function update($source, $item) {
        $out = [];
        if ($source == 'emoncms')
            $out = exec('git pull', $out);
        else if ($source == 'modules') {
            chdir("Modules/" . $item);
            exec('git pull 2>&1', $out);
            chdir('../..');
        }
        else if ($source == 'themes') {
            chdir("Themes/" . $item);
            exec('git pull 2>&1', $out);
            chdir('../..');
        }
        $this->log->warn("Updated: $source -- $item");
        return $out;
    }

    public function get_warning_html() {
        global $path;
        $out = _('<strong>Updates</strong> available in <a href="' . $path . 'update/list" class="alert-link">updates manager</a>');
        return $out;
    }

    public function update_available($type, $item) {
        if ($item['git'] === false)
            return false;
        else {
            $result = false;
            $module_status = [];
            chdir($this->get_dir($type, $item));

            exec("git status 2>&1", $module_status);
            if (strpos($module_status[1], 'branch is behind') != false) {
                $result = true;
                //$result['status'] = str_replace(["'", '"'], "", implode(" -- ", $module_status));
            }

            if ($type != 'emoncms')
                chdir('../..');

            return $result;
        }
    }

    private function get_dir($type, $repo = ['name' => '']) {
        switch ($type) {
            case 'modules':
                $dir = 'Modules/' . $repo['name'];
                break;
            case 'themes':
                $dir = 'Theme/' . $repo['name'];
                break;
            case 'emoncms':
                $dir = './';
        }
        return $dir;
    }

    private function has_update_permissions($type, $item) {
        if ($item['git'] === false)
            return false;
        else {
            $result = true;
            $module_status = [];
            chdir($this->get_dir($type, $item));

            exec("git fetch 2>&1", $module_status);
            if (count($module_status) > 0 && strpos($module_status[0], 'Permission denied') != false) {
                $result = false;
                //$result['status'] = str_replace(["'", '"'], "", implode(" -- ", $module_status));
            }

            if ($type != 'emoncms')
                chdir('../..');

            return $result;
        }
    }

    private function permissions_message($type, $item) {
        chdir($this->get_dir($type, $item));
        $processUser = posix_getpwuid(posix_geteuid());
        $out = _('You have not got enough permissions to update<br/>In the command line run: ') . 'sudo chown -R ' . $processUser['name'] . ':' . $processUser['name'] . ' ' . getcwd() . '/';

        if ($type != 'emoncms')
            chdir('../..');

        return $out;
    }

    private function file_changed($file_name, $type, $repo = ['name' => '']) {
        $result = false;
        $diff = [];
        chdir($this->get_dir($type, $repo));

        exec("git diff --name-only origin 2>&1", $diff);
        if (in_array($file_name, $diff) != false) {
            $result = true;
        }

        if ($type != 'emoncms')
            chdir('../..');

        return $result;
    }

}
