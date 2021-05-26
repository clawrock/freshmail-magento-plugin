define([
    'jquery',
    'Magento_Ui/js/modal/alert',
    'jquery/ui'
], function ($, alert) {
    'use strict';

    $.widget('mage.forceFollowUpEmails', {
        options: {
            url: '',
            elementId: '',
            successText: '',
            failedText: '',
        },

        _create: function () {
            this._on({
                'click': $.proxy(this.onClick, this)
            });
        },

        onClick: function () {
            var element = $('#' + this.options.elementId),
                msg = '',
                self = this;
            element.removeClass('success').addClass('fail');

            $.ajax({
                url: this.options.url,
                showLoader: true,
                data: {form_key: window.FORM_KEY},
                type: 'POST',
                headers: this.options.headers || {}
            }).done(function (response) {

                if (response.success) {
                    element.removeClass('fail').addClass('success');
                    msg = self.options.successText;
                } else {
                    msg = response.errorMessage;
                    if (!msg) {
                        msg = self.options.failedText;
                    }
                }
            }).always(function () {
                alert({
                    content: msg
                });
            });
        },
    });

    return $.mage.forceFollowUpEmails;
});
