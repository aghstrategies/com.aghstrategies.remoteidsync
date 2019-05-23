CRM.$(function ($) {
  var $fieldInfo = CRM.vars.remoteidsync.info;
  var $contactIdInOtherDB = $('div#custom-set-content-' + $fieldInfo.custom_group_id + ' div.crm-content.crm-custom-data').html();
  $('div#custom-set-content-' + $fieldInfo.custom_group_id + ' div.crm-content.crm-custom-data').html(
    "<a href='" + $fieldInfo.base_url + $contactIdInOtherDB + "'>" + $contactIdInOtherDB + "</a>"
  );
});
