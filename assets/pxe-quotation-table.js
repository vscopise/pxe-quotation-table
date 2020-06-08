jQuery(document).ready(function ($) {
    $.ajax({
        type: 'POST',
        url: pxe_quotation_table_ajax_object.admin_url,
        data: {
            action: 'pxe_quotation_action',
        },
        success: function (result) {
           // console.log(result);
            var currencies = {
                'USD': 'Dólar',
                'ARS': 'Peso Argentino',
                'BRL': 'Real',
                'EUR': 'Euro',
            };
            $.each(currencies, function (index, currency) {
                //console.log(currency);
                //console.log(result.data.rates[index]);
                var currency_data = result.data.rates[index];
                var td_currency = '<td class="currency">' + currency + '</td>';
                var td_buy = '<td class="buy">' + currency_data.buy + '</td>';
                var td_sell = '<td class="sell">' + currency_data.sell + '</td>';
                var tr_currency = '<tr>' + td_currency + td_buy + td_sell + '</tr>';
                $('.pxe-quotation-table').append(tr_currency);
            });

            $('#top-navigation').append(result.data)
        }
    });
});