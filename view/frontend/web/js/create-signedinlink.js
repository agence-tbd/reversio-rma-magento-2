define([
    'jquery'
], function ($) {
    return function (config, element) {
        $(element).click(function (event) {
            $.ajax({
                showLoader: true,
                url: config['callback_url'],
                data: {},
                type: "POST",
                dataType: 'json'
            }).done(function (data) {
                if (data['error']) {
                    // TODO
                } else {
                    window.location.href = data['link'];
                }
            });
        });
    }
})
