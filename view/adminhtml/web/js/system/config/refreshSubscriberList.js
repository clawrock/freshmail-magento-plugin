define([
    'jquery',
    'Magento_Ui/js/modal/alert',
    'jquery/ui'
], function ($, alert) {
    'use strict';

    $.widget('mage.freshMailRefreshSubscriptionList', {
        options: {
            url: '',
            elementId: '',
            selectId: ''
        },

        /**
         * Bind handlers to events
         */
        _create: function () {
            this._on({
                'click': $.proxy(this._refreshList, this)
            });
        },

        /**
         * Method triggers an AJAX request to refresh subscriber list
         * @private
         */
        _refreshList: function () {
            var self = this;
            var params = {};

            params['form_key'] = window.FORM_KEY;
            $.ajax({
                url: this.options.url,
                showLoader: true,
                data: params,
                headers: this.options.headers || {}
            }).done(function (response) {
                var options, index, option;
                if (response.success === false) {
                    alert({
                        content: response.errorMessage
                    });

                    return;
                }

                var select = document.getElementById(self.options.selectId);
                var selectedValue = select.options[select.selectedIndex].value;
                var selectedValueExists = false;
                select.options.length = 0;
                options = response.options;

                for (index = 0; index < options.length; ++index) {
                    option = options[index];
                    select.options.add(new Option(option.label, option.value));
                    if (option.value === selectedValue) {
                        selectedValueExists = true;
                    }
                }

                if (selectedValueExists) {
                    select.value = selectedValue;
                }
            }).always(function () {});
        }
    });

    return $.mage.freshMailRefreshSubscriptionList;
});
