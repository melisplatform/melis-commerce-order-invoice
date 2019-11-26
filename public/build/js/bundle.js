$(function() {
    var $body = $('body');

        /**
         * Regenerate Order
         */
        $body.on('click', '.regenerate-invoice', function(){
            var $this   = $(this),
                orderId = $this.val();

                melisCoreTool.pending(".regenerate-invoice");

                $.ajax({
                    type    : 'POST',
                    url     : '/melis/MelisCommerceOrderInvoice/MelisCommerceOrderInvoice/generateOrderInvoice',
                    data    : {'orderId': orderId},
                    dataType: 'json',
                    encode  : true,
                }).done(function(data) {
                    melisHelper.zoneReload(
                        orderId + '_id_meliscommerce_orders_content_tabs_content_order_invoice_details',
                        'meliscommerce_orders_content_tabs_content_order_invoice_details',
                        {
                            'orderId' : orderId
                        }
                    );
                    melisCoreTool.done(".regenerate-invoice");
                }).fail(function() {
                    melisCoreTool.done(".regenerate-invoice");
                    alert( translations.tr_meliscore_error_message );
                });
        });

        /**
         * Export Invoice in Invoice List Table
         */
        $body.on("click", '.export-invoice-pdf', function () {
            var $this       = $(this),
                invoiceId   = $this.closest('tr').data('invoiceid'),
                url         = '/melis/MelisCommerceOrderInvoice/MelisCommerceOrderInvoice/getOrderInvoice',
                params      = 'invoiceId=' + invoiceId;

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
            var $this   = $(this),
                orderId = $this.closest('tr').attr('id'),
                url     = '/melis/MelisCommerceOrderInvoice/MelisCommerceOrderInvoice/getOrderInvoice',
                params  = null;

                melisCoreTool.pending(".export-order-pdf");
                // check first if there is an invoice for the order
                $.ajax({
                    type    : 'POST',
                    url     : '/melis/MelisCommerceOrderInvoice/MelisCommerceOrderInvoice/getOrderLatestInvoiceId',
                    data    : {'orderId': orderId},
                    dataType: 'json',
                    encode  : true
                }).done(function(data) {
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
                }).fail(function() {
                    melisCoreTool.done('.export-order-pdf');
                    alert( translations.tr_meliscore_error_message );
                });
        });

        /**
         * Refresh Invoice Table
         */
        $body.on('click', '.orderInvoiceTableRefresh', function(){
            var $this   = $(this),
                id      = $this.closest('.container-level-a').attr('id'),
                orderId = isNaN(parseInt(id, 10)) ? '' : parseInt(id, 10);

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
                        var blob        = new Blob([this.response], {type:type}),
                            link        = document.createElement('a'),
                            downloadUrl = window.URL.createObjectURL(blob);

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