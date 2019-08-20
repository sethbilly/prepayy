/**
 * Created by kwabena on 10/11/17.
 */
$(document).ready(function () {
    var selectors = {
        editLink: '.edit-loan-type',
        editModal: '#edit-loan-type-modal'
    };

    $(document.body)
        .on('click', selectors.editLink, function () {
            $(selectors.editModal + ' input[name="name"]').val($(this).data('name'));

            $(selectors.editModal + ' form').attr('action', $(this).data('href'));

            $(selectors.editModal).modal();
        });
});