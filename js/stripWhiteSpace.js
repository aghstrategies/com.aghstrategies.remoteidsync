CRM.$(function ($) {
  var $fieldInfo = CRM.vars.remoteidsync.info;
  // Strip out white space in the remote id custom field
  $("input[id^='custom_" + $fieldInfo.custom_field_id + "']").change(function() {
    var $value = $("input[id^='custom_" + $fieldInfo.custom_field_id + "']").val().trim();
    $("input[id^='custom_" + $fieldInfo.custom_field_id + "']").val($value)
  });
  console.log($("input[id^='custom_" + $fieldInfo.custom_field_id + "']").val());
});
