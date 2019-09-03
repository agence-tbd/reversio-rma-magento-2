define([
    'jquery',
    'mage/translate'
], function ($) {
    return function (config, element) {
        $('.order-information-table tbody')
            .append('<tr><th>'+$.mage.__('Revers.io Sync Status')+'</th><td>'+config['syncstatus']+'</td></tr>');
    }
})
