function afterRegSubmit(F){
    var F=$(F);F.hasClass("js-reg")&&$(".main-page-wrapper").addClass("success")
}
function afterLogSubmit(F){
    var F=$(F);F.hasClass("js-auth")&&F[0].submit()
}
function goToSecondStep(){
    $(".auth-sidebar__form.form-check.data .form-control").valid()&&$(".data-modal .modal-content").slick("slickNext")
}
function step_2(F){
    goToSecondStep()
}
$(".form-check").length>0&&($(".form-check").each(function(){
    $(this).validate({
        rules:{
            name:{required:!0},email:{required:!0,emailfull:!0},tel:{required:!0},password:{required:!0},fio:{required:!0},org:{required:!0},adress:{required:!0}
        },
        submitHandler:function(F){
            afterRegSubmit(F);
            afterLogSubmit(F);
            $(".data-modal").length>0&&step_2(F)}})}),$.validator.addMethod("emailfull",function(F,u){
                return this.optional(u)||/^([a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+(\.[a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+)*|"((([ \t]*\r\n)?[ \t]+)?([\x01-\x08\x0b\x0c\x0e-\x1f\x7f\x21\x23-\x5b\x5d-\x7e\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|\\[\x01-\x09\x0b\x0c\x0d-\x7f\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))*(([ \t]*\r\n)?[ \t]+)?")@(([a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.)+([a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.?$/i.test(F)
            },"Пожалуйста, введите корректный адрес электронной почты."));
$(".search-new").on("click",function(F){F.preventDefault(),$(this).closest(".modal-content").slick("slickNext"),$(".data-modal").find(".close").removeClass("hidden")});