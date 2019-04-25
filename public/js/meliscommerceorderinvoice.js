$(function() {
    var $body = $('body');

    /**
     * Regenerate Order
     */
    $body.on('click', '.regenerate-invoice', function(){
        var orderId = $(this).val();

        melisCoreTool.pending(".regenerate-invoice");

        $.ajax({
            type: 'POST',
            url: '/melis/MelisCommerceOrderInvoice/MelisCommerceOrderInvoice/generateOrderInvoice',
            data: {'orderId': orderId},
            dataType: 'json',
            encode: true,
        }).success(function (data) {
            melisHelper.zoneReload(
                orderId + '_id_meliscommerce_orders_content_tabs_content_order_invoice_details',
                'meliscommerce_orders_content_tabs_content_order_invoice_details',
                {
                    'orderId' : orderId
                }
            );
            melisCoreTool.done(".regenerate-invoice");
        }).error(function () {
            melisCoreTool.done(".regenerate-invoice");
        });
    });

    /**
     * Export Invoice in Invoice List Table
     */
    $body.on("click", '.export-invoice-pdf', function () {
        var invoiceId = $(this).closest('tr').data('invoiceid');
        var url = '/melis/MelisCommerceOrderInvoice/MelisCommerceOrderInvoice/getOrderInvoice';
        var params = 'invoiceId=' + invoiceId;

        melisCoreTool.pending('.export-invoice-pdf');

        downloadFile(
            'POST',
            url,
            params, 
            'application/pdf',
            function() {
                melisCoreTool.done('.export-invoice-pdf');
            },
            null
        );
    });

    /**
     * Export Order Invoice In Order List Table
     */
    $body.on("click", '.export-order-pdf', function () {
        var orderId = $(this).closest('tr').attr('id');
        var url = '/melis/MelisCommerceOrderInvoice/MelisCommerceOrderInvoice/getOrderInvoice';
        var params = null;

        melisCoreTool.pending(".export-order-pdf");
        // check first if there is an invoice for the order
        $.ajax({
            type: 'POST',
            url: '/melis/MelisCommerceOrderInvoice/MelisCommerceOrderInvoice/getOrderLatestInvoiceId',
            data: {'orderId': orderId},
            dataType: 'json',
            encode: true,
        }).success(function (data) {
            if (data.latestInvoiceId > 0) {
                params = 'invoiceId=' + data.latestInvoiceId;

                downloadFile(
                    'POST', 
                    url, 
                    params,
                    'application/pdf',
                    function() {
                        melisCoreTool.done('.export-order-pdf');
                    },
                    null
                );
            } else {
                melisHelper.melisKoNotification(
                    translations.tr_meliscommerce_order_invoice_export_prompt_title,
                    translations.tr_meliscommerce_order_invoice_export_no_invoice
                );
                melisCoreTool.done('.export-order-pdf');
            }
        }).error(function () {
            melisCoreTool.done('.export-order-pdf');
        });
    });

    /**
     * Refresh Invoice Table
     */
    $body.on('click', '.orderInvoiceTableRefresh', function(){
        var id = $(this).closest('.container-level-a').attr('id');
        var orderId = isNaN(parseInt(id, 10)) ? '' : parseInt(id, 10);

        melisHelper.zoneReload(
            orderId + '_id_meliscommerce_orders_content_tabs_content_order_invoice_details',
            'meliscommerce_orders_content_tabs_content_order_invoice_details',
            {
                'orderId' : orderId
            }
        );
    });

    /**
     * Download the file using XMLHttpRequest
     */
    function downloadFile(requestType, url, params, type, onLoadCallback, callback) {
        var xhr = new XMLHttpRequest();

        xhr.open(requestType, url);
        xhr.responseType = 'arraybuffer';

        if (params != null) {
            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhr.send(params);
        } else {
            xhr.send();
        }

        xhr.onload = function(e) {
            if (this.status == 200) {
                var blob = new Blob([this.response], {type:type});
                var link = document.createElement('a');
                var downloadUrl = window.URL.createObjectURL(blob);

                link.href = downloadUrl;
                link.download = xhr.getResponseHeader('fileName');
                link.dispatchEvent(new MouseEvent('click', {bubbles: true, cancelable: true, view: window}));

                if (onLoadCallback != undefined || onLoadCallback != null) {
                    onLoadCallback();
                }
            }
        };

        if (callback != undefined || callback != null) {
            callback();
        }
    }

    /**
     * Order Table Data
     */
    window.initOrderInvoiceTable = function(data, tblSettings) {
        var orderId = $("#" + tblSettings.sTableId ).data("orderid");

        data.orderId = orderId;
    };
});