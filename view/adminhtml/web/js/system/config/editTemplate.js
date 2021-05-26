define([
    'jquery',
    'Magento_Ui/js/modal/alert',
    'jquery/ui'
], function ($, alert) {
    'use strict';

    $.widget('mage.freshMailEditTemplate', {
        options: {
            url: '',
            elementId: '',
            successText: '',
            failedText: '',
        },

        /**
         * Bind handlers to events
         */
        _create: function () {
            this._super();
            this._bind();
        },

        _bind: function () {
            this._on({
                'click': 'onClick'
            });
        },

        onClick: function () {
            var self = this;
            var templateId = $(self.element).parents('tr').prev('tr').find("select.email-template").val();

            if (typeof templateId === 'undefined' || templateId === '0') {
                alert({
                    content: 'Please select template.'
                });
                return;
            }
            $.ajax({
                url: this.options.url,
                showLoader: true,
                data: {template_id: templateId},
                headers: this.options.headers || {}
            }).done(function (response) {

                if(response.success === true) {
                    if(response.new_window === true) {
                        window.open(response.url, '_blank');
                    } else {
                        window.location.replace(response.url);
                    }
                } else {
                    alert({
                        content: response.message
                    });
                }
            }).error(function () {
                alert({
                    content: "Something went wrong, please try again later."
                });
            });
        },
    });

    return $.mage.freshMailTestConnection;
});
