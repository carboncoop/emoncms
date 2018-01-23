<?php
global $path;
?>
<script type="text/javascript" src="<?php echo $path; ?>Lib/tablejs/table.js"></script>
<script type="text/javascript" src="<?php echo $path; ?>Lib/tablejs/custom-table-fields.js"></script>

<h2>Users</h2>
<div id="table"></div>

<script>
    var path = "<?php echo $path; ?>";

    var admin = {
        'userlist': function ()
        {
            var result = {};
            $.ajax({url: path + "admin/userlist.json", dataType: 'json', async: false, success: function (data) {
                    result = data;
                }});
            return result;
        },
        'set': function (id, fields_to_update)
        {
            var result = {};
            $.ajax({url: path + "admin/setuserdata.json?id=" + id + "&fields=" + JSON.stringify(fields_to_update), dataType: 'json', async: false,success: function (data) {
                    console.log(data);
                    table.data = admin.userlist();
                    table.draw()
                } });
            return result;
        }
    }

    // Extend table library field types
    for (z in customtablefields)
        table.fieldtypes[z] = customtablefields[z];
    table.element = "#table";
    table.fields = {
        'id': {'title': "<?php echo _('Id'); ?>", 'type': "textlink", 'link': "setuser?id="},
        'username': {'title': "<?php echo _('Username'); ?>", 'type': "fixed"},
        'email': {'title': "<?php echo _('Email'); ?>", 'type': "fixed"},
        'admin': {'title': "<?php echo _('Admin'); ?>", 'type': "checkbox"},
        'edit-action': {'title': '', 'type': "edit"},
    }

    table.data = admin.userlist();
    table.draw();
    $("#table").bind("onSave", function (e, id, fields_to_update) {
        admin.set(id, fields_to_update);
    });
</script>