# com.aghstrategies.remoteidsync

This extension creates a remote id field.

If two civicrm instances have this extension installed with the settings configured the Remote ID field created by this extension will be linked to the remote id field in the site it is configured to talk to.

If a remote id is deleted the other site will be updated.
If a remote id is changed the other site will be updated.

Example settings for drupal ({site}//civicrm/remoteidsync/settings):
Endpoint for API: http://d514.localhost/sites/all/modules/civicrm/extern/rest.php
Base URL: http://d514.localhost/civicrm/contact/view?reset=1&cid=

Example settings for wordpress ({site}/wp-admin/admin.php?page=CiviCRM&q=civicrm%2Fremoteidsync%2Fsettings):
Endpoint for API: http://wp5.localhost/wp-content/plugins/civicrm/civicrm/extern/rest.php
Base URL: http://wp5.localhost/wp-admin/admin.php?page=CiviCRM&q=civicrm%2Fcontact%2Fview&cid=


Strips out white space in the Remote ID Custom Field
