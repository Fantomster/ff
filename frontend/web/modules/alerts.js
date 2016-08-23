function showAlert(message, type, closeDelay) {
  if ($("#alerts-container").length == 0) {
    $("body").append( $('<div id="alerts-container" style="position: fixed;width: 50%; right: 0; top: 0;z-index:99999">') );
  }
  type = type || "info";    
  var alert = $('<div class="alert alert-' + type + ' fade in" style="margin-bottom:1px;border-radius:0">').append($('<button type="button" class="close" data-dismiss="alert">').append("&times;")).append(message);
  $("#alerts-container").prepend(alert);
  if (closeDelay)
    window.setTimeout(function() { alert.alert("close") }, closeDelay);     
}