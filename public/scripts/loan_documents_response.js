/**
 * Created by benjaminmanford on 3/1/17.
 */

(function () {
    "use strict";

    $(function () {
        setupLoanDocumentsResponsePageListeners();
    });

    function setupLoanDocumentsResponsePageListeners() {
        /**
         * Handle click of respond button for requested documents
         */
        $(document).on('click', '.respond-button', function () {

            var target = $(this).data('target'),
                documentId = $(this).data('document-id'),
                oldAction = $(target + ' form').data('action'),
                newAction = oldAction ? oldAction.replace(':document', documentId) : null;

            // Append document id to the form action
            if(newAction) {
                $(target + ' form').attr('action', newAction);
            }
        });
    }

})(window.jQuery);