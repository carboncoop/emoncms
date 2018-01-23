var update = {
    'update_item': function (source, item, callback) {
        $.ajax({url: path + "update/update.json?source=" + source + "&item=" + item, async: true, success: function (data) {
                update.get_status(source, item, callback);
            }
        });
    },
    'get_status': function (source, item, callback) {
        $.ajax({url: path + "update/updateavailable.json?source=" + source + "&item=" + item, async: true, success: function (data) {
                callback(data, source, item);
            }
        })
    }
}