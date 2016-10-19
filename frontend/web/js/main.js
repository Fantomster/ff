$(function () {
    function reposition() {
        var modal = $(this),
                dialog = modal.find('.modal-dialog');
        modal.css('display', 'block');

        dialog.css("margin-top", Math.max(0, ($(window).height() - dialog.height()) / 4));
    }

    $('.modal').on('show.bs.modal', reposition);

    $(window).on('resize', function () {
        $('.modal:visible').each(reposition);
        if (!window.matchMedia('(max-width: 767px)').matches) {
            if (!$('body').hasClass('sidebar-collapse')) {
                $('.invite-form').show();
            } else {
                $('.invite-form').hide();
            }
        }
    });

    $('body').on('click', '.sidebar-toggle', function () {
        if (window.matchMedia('(max-width: 767px)').matches) {
            if (!$('body').hasClass('sidebar-open')) {
                $('.invite-form').show();
            } else {
                $('.invite-form').hide();
            }
        } else {
            if ($('body').hasClass('sidebar-collapse')) {
                $('.invite-form').show();
            } else {
                $('.invite-form').hide();
            }
        }
    });
});