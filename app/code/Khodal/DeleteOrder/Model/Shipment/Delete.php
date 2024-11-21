<?php
/**
 * @author Khodal
 * @copyright Copyright (c) khodal
 * @package DeleteOrder for Magento 2
 */

namespace Khodal\DeleteOrder\Model\Shipment;

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
     * @var \Magento\Sales\Model\Order\Shipment
     */
    protected $shipment;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $order;

    /**
     * Delete constructor.
     * @param ResourceConnection $resource
     * @param \Khodal\DeleteOrder\Helper\Data $data
     * @param \Magento\Sales\Model\Order\Shipment $shipment
     * @param \Magento\Sales\Model\Order $order
     */
    public function __construct(
        ResourceConnection $resource,
        \Khodal\DeleteOrder\Helper\Data $data,
        \Magento\Sales\Model\Order\Shipment $shipment,
        \Magento\Sales\Model\Order $order
    ) {
        $this->resource = $resource;
        $this->data = $data;
        $this->shipment = $shipment;
        $this->order = $order;
    }

    /**
     * @param $shipmentId
     * @return \Magento\Sales\Model\Order
     * @throws \Exception
     */
    public function deleteShipment($shipmentId)
    {
        $connection = $this->resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);
        $shipmentTable = $connection->getTableName($this->data->getTableName('sales_shipment'));
        $shipmentGridTable = $connection->getTableName($this->data->getTableName('sales_shipment_grid'));
        $shipment = $this->shipment->load($shipmentId);
        $orderId = $shipment->getOrder()->getId();
        $order = $this->order->load($orderId);
        $orderItems = $order->getAllItems();
        $shipmentItems = $shipment->getAllItems();

        // revert item in order
        foreach ($orderItems as $item) {
            foreach ($shipmentItems as $shipmentItem) {
                if ($shipmentItem->getOrderItemId() == $item->getItemId()) {
                    $item->setQtyShipped($item->getQtyShipped() - $shipmentItem->getQty());
                }
            }
        }

        // delete shipment info
        $connection->rawQuery('DELETE FROM `'.$shipmentGridTable.'` WHERE entity_id='.$shipmentId);
        $connection->rawQuery('DELETE FROM `'.$shipmentTable.'` WHERE entity_id='.$shipmentId);
        if ($order->hasShipments() || $order->hasInvoices() || $order->hasCreditmemos()) {
            $order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING)
                ->setStatus($order->getConfig()->getStateDefaultStatus(\Magento\Sales\Model\Order::STATE_PROCESSING))
                ->save();
        } else {
            $order->setState(\Magento\Sales\Model\Order::STATE_NEW)
                ->setStatus($order->getConfig()->getStateDefaultStatus(\Magento\Sales\Model\Order::STATE_NEW))
                ->save();
        }

        return $order;
    }
}
