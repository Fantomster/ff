$(function () {
    function reposition() {
        var modal = $(this),
                dialog = modal.find('.modal-dialog');
        modal.css('display', 'block');

        dialog.css("margin-top", Math.max(0, ($(window).height() - dialog.height()) / 5));
    }

    $('.modal').on('show.bs.modal', reposition);
});