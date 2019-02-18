$(function() {
    let $body = $('body');

    /**
     * Regenerate Order
     */
    $body.on('click', '.regenerate-invoice', function(){
        let orderId = $(this).val();

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

        });
    });

    /**
     * Export Invoice in Invoice List Table
     */
    $body.on("click", '.export-invoice-pdf', function () {
        let invoiceId = $(this).closest('tr').data('invoiceid');
        let url = '/melis/MelisCommerceOrderInvoice/MelisCommerceOrderInvoice/exportInvoice?invoiceId=' + invoiceId;

        melisCoreTool.pending('.export-invoice-pdf');

        downloadFile(url, null, 'invoice.pdf',function() {
            console.log('test');
        });

        melisCoreTool.done('.export-invoice-pdf');
    });

    /**
     * Export Order Invoice In Order List Table
     */
    $body.on("click", '.export-order-pdf', function () {
        let orderId = $(this).closest('tr').attr('id');
        let url = '/melis/MelisCommerceOrderInvoice/MelisCommerceOrderInvoice/exportOrderInvoice?orderId=' + orderId;

        melisCoreTool.pending(".export-order-pdf");
        // check first if there is an invoice for the order
        $.ajax({
            type: 'POST',
            url: '/melis/MelisCommerceOrderInvoice/MelisCommerceOrderInvoice/checkForInvoice',
            data: {'orderId': orderId},
            dataType: 'json',
            encode: true,
        }).success(function (data) {
            if (data.hasInvoice === true) {
                downloadFile(url, null, 'invoice.pdf',function() {
                    melisCoreTool.done('.export-order-pdf');
                });
            } else {
                melisHelper.melisKoNotification(
                    translations.tr_meliscommerce_order_invoice_export_prompt_title,
                    translations.tr_meliscommerce_order_invoice_export_no_invoice
                );
            }
        }).error(function () {

        });
    });

    /**
     * Refresh Invoice Table
     */
    $body.on('click', '.orderInvoiceTableRefresh', function(){
        let id = $(this).closest('.container-level-a').attr('id');
        let orderId = isNaN(parseInt(id, 10)) ? '' : parseInt(id, 10);

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
    function downloadFile(url, type = 'application/pdf', fileName, callback) {
        let xhr = new XMLHttpRequest();

        xhr.open('GET', url);
        xhr.responseType = 'arraybuffer';
        xhr.onload = function(e) {
            if (this.status === 200) {
                let blob = new Blob([this.response], {type:type});
                let link = document.createElement('a');

                link.href = window.URL.createObjectURL(blob);
                link.download = fileName;
                link.click();
            }
        };
        xhr.send();

        callback();
    }

    /**
     * Order Table Data
     */
    window.initOrderInvoiceTable = function(data, tblSettings) {
        let orderId = $("#" + tblSettings.sTableId ).data("orderid");

        data.orderId = orderId;
    };
});