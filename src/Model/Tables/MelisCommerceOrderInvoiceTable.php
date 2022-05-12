<?php

/**
* Melis Technology (http://www.melistechnology.com)
*
* @copyright Copyright (c) 2016 Melis Technology (http://www.melistechnology.com)
*
*/

namespace MelisCommerceOrderInvoice\Model\Tables;

use MelisCommerce\Model\Tables\MelisEcomGenericTable;

class MelisCommerceOrderInvoiceTable extends MelisEcomGenericTable
{
    /**
     * Model table
     */
    const TABLE = 'melis_ecom_order_invoice';

    /**
     * Table primary key
     */
    const PRIMARY_KEY = 'ordin_id';

    public function __construct()
    {
        $this->idField = self::PRIMARY_KEY;
    }

    public function getOrderInvoiceList($orderId, $start, $limit, $order, $ordercol)
    {
        $select = $this->getTableGateway()->getSql()->select();
        $select->where->equalTo('ordin_order_id', $orderId);

        if (!is_null($start)) {
            $select->offset($start);
        }

        if (!is_null($limit) && $limit != -1) {
            $select->limit($limit);
        }

        if (!is_null($order) && !is_null($ordercol)) {
            $select->order($ordercol . ' ' .$order);
        } else {
            $select->order(self::PRIMARY_KEY . ' DESC');
        }

        $resultData = $this->getTableGateway()->selectWith($select);

        return $resultData;
    }

    public function saveInvoice($set)
    {
        $insert = $this->getTableGateway()->getSql()->insert();
        $insert->values($set);

        $resultSet = $this->getTableGateway()->insertWith($insert);

        return $resultSet;
    }
}
