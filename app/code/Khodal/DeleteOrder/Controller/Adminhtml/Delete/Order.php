<?php
/**
 * @author Khodal
 * @copyright Copyright (c) khodal
 * @package DeleteOrder for Magento 2
 */

namespace Khodal\DeleteOrder\Controller\Adminhtml\Delete;

use Magento\Backend\App\Action;

class Order extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $order;

    /**
     * @var \Khodal\DeleteOrder\Model\Order\Delete
     */
    protected $delete;

    /**
     * Order constructor.
     * @param Action\Context $context
     * @param \Magento\Sales\Model\Order $order
     * @param \Khodal\DeleteOrder\Model\Order\Delete $delete
     */
    public function __construct(
        Action\Context $context,
        \Magento\Sales\Model\Order $order,
        \Khodal\DeleteOrder\Model\Order\Delete $delete
    ) {
        $this->order = $order;
        $this->delete = $delete;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $order = $this->order->load($orderId);
        $incrementId = $order->getIncrementId();
        try {
            $this->delete->deleteOrder($orderId);
            $this->messageManager->addSuccessMessage(__('Successfully deleted order #%1.', $incrementId));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Error delete order #%1.', $incrementId));
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('sales/order/');
        return $resultRedirect;
    }

    /*
     * Check permission via ACL resource
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Khodal_DeleteOrder::delete_order');
    }
}
