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

global $session, $path;
?>

<script type="text/javascript" src="<?php echo $path; ?>Modules/update/update.js"></script>

<style>
    .emoncms,.modules,.themes{
        display:none;
    }
    table, h2{margin-left: 25px}
    td:nth-child(1){width:50px;}
    td:nth-child(2){width:150px;}
</style>

<div id="page-container">
    <h1><?php echo _('System updates'); ?></h1>
    <div id="page">
        <h2 class="emoncms">EmonCMS</h2>
        <table class="emoncms table"></table>
        <h2 class="modules">Modules</h2>
        <table class="modules table"></table>
        <h2 class="themes">Themes</h2>
        <table class="themes table"></table>
    </div>
</div>

<script>
    var path = "<?php echo $path ?>";
    var available_updates = JSON.parse('<?php echo json_encode($available_updates) ?>');
    if (Object.keys(available_updates).length > 0) {
        if (available_updates.emoncms.length == 0 && available_updates.modules.length == 0 && available_updates.themes.length == 0)
            $('#page').html('<p><?php echo _('Everything up to date')?></p>');
        else{
            // EmonCMS
            if (available_updates.emoncms.length > 0) {
                var out = available_update_to_tr('emoncms', 0);
                $('table.emoncms').append(out);
                $('.emoncms').show();
            }
        // Modules 
        if (available_updates.modules.length > 0) {
            for (var mod in available_updates.modules) {
                var out = available_update_to_tr('modules', mod);
                $('table.modules').append(out);
            }
            $('.modules').show();
        }
        // Themes 
        if (available_updates.themes.length > 0) {
            for (var theme in available_updates.themes) {
                var out = available_update_to_tr('themes', theme);
                $('table.modules').append(out);
            }
            $('.themes').show();
        }
    }
    }


    // Actions
    $('#page').on('click', '.update_now', function () {
        $('#' + $(this).attr('source') + '-' + $(this).attr('item') + '-loader').show();
        update.update_item($(this).attr('source'), $(this).attr('item'), update_callback);
    });
    function update_callback(update_available, source, item) {
        $('#' + source + '-' + item + '-loader').hide();
        if (update_available === false) {
            $('#' + source + '-' + item + '-td').html('<?php echo _('Updated') ?>');
        }
        else {
            $('#' + source + '-' + item + '-td').html('<?php echo _('Update failed') ?>');
        }
    }

    // Functions
    function available_update_to_tr(type, index) {
        var name = available_updates[type][index].name;
        var out = '<tr>';
        if (available_updates[type][index].has_update_permissions === true)
            out += '<td><input class="update" source="' + type + '" item="' + name + '" type="checkbox" /></td>';
        else
            out += '<td></td>';
        out += '<td>' + name + '</td>';

        if (available_updates[type][index].has_update_permissions === false)
            out += '<td>' + available_updates[type][index].permissions_message + '</td>';
        if (type == 'emoncms' && available_updates[type][index].default_settings_changed === true)
            out += '<td><?php echo _('The default_settings.php file has been modified. EmonCMS update needs to be done manually in order to keep your current settings. ') ?><a href="https://github.com/emoncms/emoncms/blob/master/docs/RaspberryPi/general.md" ><?php echo _('See how to') ?> </a></td>';
        else {
            out += '<td id="' + type + '-' + name + '-td" style="position:relative"><div id="' + type + '-' + name + '-loader" class="ajax-loader" style="display:none;width:105px;margin-left:0px"></div><button class="update_now" source="modules" item="' + name + '" ><?php echo _('Update now') ?></button>';
            if (type == 'modules' && available_updates[type][index].db_update_required === true)
                out += '<br/><?php echo _('The schema of this module has changed. Remember to update database after finishing upudating the module') ?>';

            out += '</td>';

        }

        out += '</tr>';
        return out;
    }
    console.log(available_updates);
</script>
