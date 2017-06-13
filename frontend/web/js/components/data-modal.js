$(document).ready(function(){
    $("#data-modal").on("shown.bs.modal",function(){
        //$(".data-modal .modal-content").slick({arrows:!1,dots:!1,swipe:!1,infinite:!1,adaptiveHeight:!0})
    });
    $("#data-modal").length>0&&$("#data-modal").modal("show");
});