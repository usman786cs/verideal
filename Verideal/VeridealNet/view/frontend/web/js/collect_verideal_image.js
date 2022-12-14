define([
    'uiComponent',
    'jquery',
    'ko'
], function (Component, $, ko) {
    $("#verideal-display-btn").on('click', function () {
        $("#main-verideal-image-container").toggle();
    })

    return Component.extend({
        initialize: function () {
            this._super();
        },

        getVeridealImage: function () {
            let token = this.token;
            let pid = this.pid;
            let requestUrl = this.request_url;
            if (undefined === token || undefined === pid || !token || !pid) {
                $('.verideal-button-container').hide();
                return '';
            }

            let params = {
                token: token,
                pid: pid
            };

            let selector = $('#verideal_image');

            $.ajax({
                url: requestUrl,
                // showLoader: true,
                data: params,
                type: "GET"
            }).done(function (data) {
                if (undefined !== data && undefined !== data.success && true === data.success) {
                    selector.append('<img src="data: image/png;base64,' + data.verideal_image_url + '" class="img_verideal_image" alt="Verideal Image"/>');
                    $("#main-verideal-image-container").hide();
                    $('#verideal-display-btn').prop('disabled', false);
                } else {
                    $('.verideal-button-container').hide();
                }
            });
        }
    });
});
