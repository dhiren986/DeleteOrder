<?php
/**
 * @author Khodal
 * @copyright Copyright (c) khodal
 * @package DeleteOrder for Magento 2
 */

namespace Khodal\DeleteOrder\Model\Invoice;

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
     * @var \Magento\Sales\Api\InvoiceRepositoryInterface
     */
    protected $invoiceRepository;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $order;

    /**
     * Delete constructor.
     * @param ResourceConnection $resource
     * @param \Khodal\DeleteOrder\Helper\Data $data
     * @param \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository
     * @param \Magento\Sales\Model\Order $order
     */
    public function __construct(
        ResourceConnection $resource,
        \Khodal\DeleteOrder\Helper\Data $data,
        \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository,
        \Magento\Sales\Model\Order $order
    ) {
        $this->resource = $resource;
        $this->data = $data;
        $this->invoiceRepository = $invoiceRepository;
        $this->order = $order;
    }

    /**
     * @param $invoiceId
     * @return \Magento\Sales\Model\Order
     * @throws \Exception
     */
    public function deleteInvoice($invoiceId)
    {
        $connection = $this->resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);
        $invoiceGridTable = $connection->getTableName($this->data->getTableName('sales_invoice_grid'));
        $invoiceTable = $connection->getTableName($this->data->getTableName('sales_invoice'));
        $invoice = $this->invoiceRepository->get($invoiceId);
        $orderId = $invoice->getOrder()->getId();
        $order = $this->order->load($orderId);
        $orderItems = $order->getAllItems();
        $invoiceItems = $invoice->getAllItems();

        // revert item in order
        foreach ($orderItems as $item) {
            foreach ($invoiceItems as $invoiceItem) {
                if ($invoiceItem->getOrderItemId() == $item->getItemId()) {
                    $item->setQtyInvoiced($invoiceItem->getQty());
                    $item->setTaxInvoiced($invoiceItem->getTaxAmount());
                    $item->setBaseTaxInvoiced($invoiceItem->getBaseTaxAmount());
                    $item->setDiscountTaxCompensationInvoiced($invoiceItem->getDiscountTaxCompensationAmount());
                    $baseDiscountTaxItem = $item->getBaseDiscountTaxCompensationInvoiced();
                    $baseDiscountTaxInvoice = $invoiceItem->getBaseDiscountTaxCompensationAmount();
                    $item->setBaseDiscountTaxCompensationInvoiced($baseDiscountTaxInvoice);
                    $item->setDiscountInvoiced($invoiceItem->getDiscountAmount());
                    $item->setBaseDiscountInvoiced($invoiceItem->getBaseDiscountAmount());

                    $item->setRowInvoiced($invoiceItem->getRowTotal());
                    $item->setBaseRowInvoiced($invoiceItem->getBaseRowTotal());
                }
            }

        }
        // revert info in order
        $order->setTotalInvoiced($invoice->getGrandTotal());
        $order->setBaseTotalInvoiced($invoice->getBaseGrandTotal());

        $order->setSubtotalInvoiced($invoice->getSubtotal());
        $order->setBaseSubtotalInvoiced($invoice->getBaseSubtotal());

        $order->setTaxInvoiced($invoice->getTaxAmount());
        $order->setBaseTaxInvoiced($invoice->getBaseTaxAmount());

        $order->setDiscountTaxCompensationInvoiced($invoice->getDiscountTaxCompensationAmount());
        $order->setBaseDiscountTaxCompensationInvoiced($invoice->getBaseDiscountTaxCompensationAmount());
        $order->setShippingTaxInvoiced($invoice->getShippingTaxAmount());
        $order->setBaseShippingTaxInvoiced($invoice->getBaseShippingTaxAmount());

        $order->setShippingInvoiced($invoice->getShippingAmount());
        $order->setBaseShippingInvoiced($invoice->getBaseShippingAmount());

        $order->setDiscountInvoiced($invoice->getDiscountAmount());
        $order->setBaseDiscountInvoiced($invoice->getBaseDiscountAmount());
        $order->setBaseTotalInvoicedCost($invoice->getBaseCost());

        if ($invoice->getState() == \Magento\Sales\Model\Order\Invoice::STATE_PAID) {
            $order->setTotalPaid($invoice->getGrandTotal());
            $order->setBaseTotalPaid($invoice->getBaseGrandTotal());
        }
        // delete invoice info
        // $connection->rawQuery('DELETE FROM `'.$invoiceGridTable.'` WHERE entity_id='.$invoiceId);
        // $connection->rawQuery('DELETE FROM `'.$invoiceTable.'` WHERE entity_id='.$invoiceId);
        // if ($order->hasShipments() || $order->hasInvoices() || $order->hasCreditmemos()) {
        //     $order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING)
        //         ->setStatus($order->getConfig()->getStateDefaultStatus(\Magento\Sales\Model\Order::STATE_PROCESSING))
        //         ->save();
        // } else {
        //     $order->setState(\Magento\Sales\Model\Order::STATE_NEW)
        //         ->setStatus($order->getConfig()->getStateDefaultStatus(\Magento\Sales\Model\Order::STATE_NEW))
        //         ->save();
        // }
        $order->save();
        return $order;
    }

    /**
     * @param $invoiceId
     * @return \Magento\Sales\Model\Order
     * @throws \Exception
     */
    public function deleteInvoiceORIGINAL($invoiceId)
    {
        $connection = $this->resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);
        $invoiceGridTable = $connection->getTableName($this->data->getTableName('sales_invoice_grid'));
        $invoiceTable = $connection->getTableName($this->data->getTableName('sales_invoice'));
        $invoice = $this->invoiceRepository->get($invoiceId);
        $orderId = $invoice->getOrder()->getId();
        $order = $this->order->load($orderId);
        $orderItems = $order->getAllItems();
        $invoiceItems = $invoice->getAllItems();

        // revert item in order
        foreach ($orderItems as $item) {
            foreach ($invoiceItems as $invoiceItem) {
                if ($invoiceItem->getOrderItemId() == $item->getItemId()) {
                    $item->setQtyInvoiced($item->getQtyInvoiced() - $invoiceItem->getQty());
                    $item->setTaxInvoiced($item->getTaxInvoiced() - $invoiceItem->getTaxAmount());
                    $item->setBaseTaxInvoiced($item->getBaseTaxInvoiced() - $invoiceItem->getBaseTaxAmount());
                    $item->setDiscountTaxCompensationInvoiced(
                        $item->getDiscountTaxCompensationInvoiced() - $invoiceItem->getDiscountTaxCompensationAmount()
                    );
                    $baseDiscountTaxItem = $item->getBaseDiscountTaxCompensationInvoiced();
                    $baseDiscountTaxInvoice = $invoiceItem->getBaseDiscountTaxCompensationAmount();
                    $item->setBaseDiscountTaxCompensationInvoiced(
                        $baseDiscountTaxItem - $baseDiscountTaxInvoice
                    );

                    $item->setDiscountInvoiced($item->getDiscountInvoiced() - $invoiceItem->getDiscountAmount());
                    $item->setBaseDiscountInvoiced(
                        $item->getBaseDiscountInvoiced() - $invoiceItem->getBaseDiscountAmount()
                    );

                    $item->setRowInvoiced($item->getRowInvoiced() - $invoiceItem->getRowTotal());
                    $item->setBaseRowInvoiced($item->getBaseRowInvoiced() - $invoiceItem->getBaseRowTotal());
                }
            }
        }
        // revert info in order
        $order->setTotalInvoiced($order->getTotalInvoiced() - $invoice->getGrandTotal());
        $order->setBaseTotalInvoiced($order->getBaseTotalInvoiced() - $invoice->getBaseGrandTotal());

        $order->setSubtotalInvoiced($order->getSubtotalInvoiced() - $invoice->getSubtotal());
        $order->setBaseSubtotalInvoiced($order->getBaseSubtotalInvoiced() - $invoice->getBaseSubtotal());

        $order->setTaxInvoiced($order->getTaxInvoiced() - $invoice->getTaxAmount());
        $order->setBaseTaxInvoiced($order->getBaseTaxInvoiced() - $invoice->getBaseTaxAmount());

        $order->setDiscountTaxCompensationInvoiced(
            $order->getDiscountTaxCompensationInvoiced() - $invoice->getDiscountTaxCompensationAmount()
        );
        $order->setBaseDiscountTaxCompensationInvoiced(
            $order->getBaseDiscountTaxCompensationInvoiced() - $invoice->getBaseDiscountTaxCompensationAmount()
        );
        $order->setShippingTaxInvoiced($order->getShippingTaxInvoiced() - $invoice->getShippingTaxAmount());
        $order->setBaseShippingTaxInvoiced($order->getBaseShippingTaxInvoiced() - $invoice->getBaseShippingTaxAmount());

        $order->setShippingInvoiced($order->getShippingInvoiced() - $invoice->getShippingAmount());
        $order->setBaseShippingInvoiced($order->getBaseShippingInvoiced() - $invoice->getBaseShippingAmount());

        $order->setDiscountInvoiced($order->getDiscountInvoiced() - $invoice->getDiscountAmount());
        $order->setBaseDiscountInvoiced($order->getBaseDiscountInvoiced() - $invoice->getBaseDiscountAmount());
        $order->setBaseTotalInvoicedCost($order->getBaseTotalInvoicedCost() - $invoice->getBaseCost());

        if ($invoice->getState() == \Magento\Sales\Model\Order\Invoice::STATE_PAID) {
            $order->setTotalPaid($order->getTotalPaid() - $invoice->getGrandTotal());
            $order->setBaseTotalPaid($order->getBaseTotalPaid() - $invoice->getBaseGrandTotal());
        }
        // delete invoice info
        $connection->rawQuery('DELETE FROM `'.$invoiceGridTable.'` WHERE entity_id='.$invoiceId);
        $connection->rawQuery('DELETE FROM `'.$invoiceTable.'` WHERE entity_id='.$invoiceId);
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
