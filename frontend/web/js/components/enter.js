$(".form-slider").slick({arrows:!1,dots:!1,swipe:!1,infinite:!1,fade:!0,adaptiveHeight:!0});
$(".go-to-reg").on("click",function(e){
    e.preventDefault(),$(".form-slider").slick("slickNext")
});
$(".auth-sidebar__enter.reg a").on("click",function(e){
    e.preventDefault(),$(".form-slider").slick("slickPrev")
});
$(".call-menu-but").on("click",function(){
    $(".present-wrapper").addClass("active"),$("body").addClass("over_hidden")
});
$(".close-menu-but").on("click",function(){
    $(this).removeClass("active"),$(".present-wrapper").removeClass("active"),$("body").removeClass("over_hidden")
});
$(window).on("resize",function(){
    $("body").outerHeight()<758?$(".auth-sidebar__contacts").addClass("low-margin"):$(".auth-sidebar__contacts").removeClass("low-margin")
});