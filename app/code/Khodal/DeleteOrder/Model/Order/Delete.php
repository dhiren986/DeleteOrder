<?php
/**
 * @author Khodal
 * @copyright Copyright (c) khodal
 * @package DeleteOrder for Magento 2
 */

namespace Khodal\DeleteOrder\Model\Order;

use Magento\Framework\App\ResourceConnection;

class Delete
{
    /**
     * @var ResourceConnection
     */
    protected $resource;
    /**
     * @var \Khodal\DeleteOrder\Helper\Data
     */
    protected $data;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $order;

    /**
     * Delete constructor.
     * @param ResourceConnection $resource
     * @param \Khodal\DeleteOrder\Helper\Data $data
     * @param \Magento\Sales\Model\Order $order
     */
    public function __construct(
        ResourceConnection $resource,
        \Khodal\DeleteOrder\Helper\Data $data,
        \Magento\Sales\Model\Order $order
    ) {
        $this->resource = $resource;
        $this->data = $data;
        $this->order = $order;
    }

    /**
     * @param $orderId
     * @throws \Exception
     */
    public function deleteOrder($orderId)
    {
        $connection = $this->resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);

        $invoiceGridTable = $connection->getTableName($this->data->getTableName('sales_invoice_grid'));
        $shippmentGridTable = $connection->getTableName($this->data->getTableName('sales_shipment_grid'));
        $creditmemoGridTable = $connection->getTableName($this->data->getTableName('sales_creditmemo_grid'));

        $order = $this->order->load($orderId);
        $order->delete();
        $connection->rawQuery('DELETE FROM `'.$invoiceGridTable.'` WHERE order_id='.$orderId);
        $connection->rawQuery('DELETE FROM `'.$shippmentGridTable.'` WHERE order_id='.$orderId);
        $connection->rawQuery('DELETE FROM `'.$creditmemoGridTable.'` WHERE order_id='.$orderId);
    }
}
