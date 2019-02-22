<?php

/**
* Melis Technology (http://www.melistechnology.com)
*
* @copyright Copyright (c) 2016 Melis Technology (http://www.melistechnology.com)
*
*/

namespace MelisCommerceOrderInvoice\Model\Tables;

use MelisCommerce\Model\Tables\MelisEcomGenericTable;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Predicate\Predicate;
use Zend\Db\Sql\Expression;

class MelisCommerceOrderInvoiceTable extends MelisEcomGenericTable
{
    protected $tableGateway;
    protected $idField;

    public function __construct(TableGateway $tableGateway)
    {
        parent::__construct($tableGateway);
        $this->idField = 'ordin_id';
    }

    public function getOrderInvoiceList($orderId, $start, $limit, $order)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->where->equalTo('ordin_order_id', $orderId);

        if (!is_null($start)) {
            $select->offset($start);
        }

        if (!is_null($limit) && $limit != -1) {
            $select->limit($limit);
        }

        if (!is_null($order)) {
            $select->order('ordin_id ' . $order);
        }

        $resultData = $this->tableGateway->selectWith($select);

        return $resultData;
    }

    public function saveInvoice($set)
    {
        $insert = $this->tableGateway->getSql()->insert();
        $insert->values($set);

        $resultSet = $this->tableGateway->insertWith($insert);

        return $resultSet;
    }
}
