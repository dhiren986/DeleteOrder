<?php
/**
 * @author Khodal
 * @copyright Copyright (c) khodal
 * @package DeleteOrder for Magento 2
 */

declare(strict_types=1);

namespace Khodal\DeleteOrder\Cron;

use Psr\Log\LoggerInterface;

class FixInvoices
{
    public function __construct(
        LoggerInterface $logger,
        \Khodal\DeleteOrder\Helper\Data $data,
        \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository,
        \Magento\Sales\Model\Order $order,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $collectionFactory,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateTimeFactory
    ) {
        $this->logger = $logger;
        $this->data = $data;
        $this->invoiceRepository = $invoiceRepository;
        $this->order = $order;
        $this->collectionFactory = $collectionFactory;
        $this->dateTimeFactory = $dateTimeFactory;
    }

    /**
     * Execute the cron
     *
     * @return void
     */
    public function execute(): void
    {
        $this->logger->info("Fix Invoice cron - Start.");
        $startDate = $this->dateTimeFactory->create()->gmtDate('Y-m-d H:i:s');
        $startDate = date('Y-m-d H:i:s', strtotime($startDate. ' - 7 days'));
        $endDate = '2024-07-31 23:59:59';
        $statuses = ['processing','Preparing'];
        $curPage = 1;
        $pageSize = 6000;

        $collection = $this->collectionFactory->create()
                    ->addFieldToSelect('*')
                    ->addFieldToFilter('status',['in' => $statuses])
                    ->addFieldToFilter('created_at',['gteq' => $startDate])
                    ->addFieldToFilter('subtotal_invoiced',['null' => true])
                    // ->addFieldToFilter('created_at',['lteq' => $endDate])
                    ->setCurPage($curPage)
                    ->setPageSize($pageSize)
                    ->load();

        foreach($collection as $order){
            $this->logger->info("Fix Invoice - Order Id - ".$order->getId());
            $orderItems = $order->getAllItems();
            foreach ($order->getInvoiceCollection() as $invoice) {
                $invoiceItems = $invoice->getAllItems();
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
            }
            $order->save();
        }
        $this->logger->info("Fix Invoice cron - done.");
    }
}