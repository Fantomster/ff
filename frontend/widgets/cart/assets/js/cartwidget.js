$(".basket_a").on('click', function (e) {
    var p = $(".block_right_basket").css("right");
    if (p == "0px") {
        e.preventDefault();
        $('.maska1').fadeOut(300);
        $('.block_right_basket').animate({"right": "-100%"}, 400);
    }
    else {
        e.preventDefault();
        $('.block_right_basket').animate({"right": "0"}, 400);
        $('.maska1').fadeIn(300);
    }
});
$(".maska1,.hide_basket").click(function () {
    $('.maska1').fadeOut(300);
    $('.block_right_basket').animate({"right": "-100%"}, 400);
});
$(document).on("click", ".cart-delete-position", function(e) {
    //$("#loader-show").showLoading();
    e.preventDefault();
    clicked = $(this);
    $.post(
        clicked.data("url")
    )
    .done(function (result) {
//                    if (result) {
//                        $.pjax.reload({container: "#cart"});
//                    }
      //  $("#loader-show").hideLoading();
    });
    return false;
});
