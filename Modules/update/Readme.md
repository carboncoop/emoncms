# Update module

The aim of the module is to provide a user interface for administrators to update emonCMS, modules and themes.

## Notes:

- Only items installed with git can be updated.
- If emonCMS' default settings file has been modified, a message is shown to the user asking to do the update manually and copy over the settings
- If the shema file of a module has changed, a message is shown to the user to remind that database update is required
- emonCMS will check if there are updates available everytime a page is loaded and show a message if there are updates available. The user only needs to close the message once per session
- It's very likely that the user will have permissions problems when updating an item. When installing via git on the command line the user is probably different than the one used by Apache. In this case a message is shown to the user with instructions about how to change the owner of the files.  Typical fix would be to run in the command line `sudo chown -R www-data:www-data your_emonCMS_path`. This assumes you apache user is www-data

