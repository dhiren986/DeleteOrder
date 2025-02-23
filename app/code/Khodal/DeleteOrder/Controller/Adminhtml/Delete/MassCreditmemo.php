<?php
/**
 * @author Khodal
 * @copyright Copyright (c) khodal
 * @package DeleteOrder for Magento 2
 */

namespace Khodal\DeleteOrder\Controller\Adminhtml\Delete;

use Khodal\DeleteOrder\Model\Creditmemo\Delete;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Backend\App\Action\Context;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

class MassCreditmemo extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Creditmemo\CollectionFactory
     */
    protected $memoCollectionFactory;

    /**
     * @var \Magento\Sales\Api\CreditmemoRepositoryInterface
     */
    protected $creditmemoRepository;

    /**
     * @var \Khodal\DeleteOrder\Model\Creditmemo\Delete
     */
    protected $delete;

    /**
     * MassCreditmemo constructor.
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Creditmemo\CollectionFactory $memoCollectionFactory
     * @param CreditmemoRepositoryInterface $creditmemoRepository
     * @param Delete $delete
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\Creditmemo\CollectionFactory $memoCollectionFactory,
        \Magento\Sales\Api\CreditmemoRepositoryInterface $creditmemoRepository,
        \Khodal\DeleteOrder\Model\Creditmemo\Delete $delete
    ) {
        parent::__construct($context, $filter);
        $this->collectionFactory = $collectionFactory;
        $this->memoCollectionFactory = $memoCollectionFactory;
        $this->creditmemoRepository = $creditmemoRepository;
        $this->delete = $delete;
    }

    /**
     * Mass action
     *
     * @param AbstractCollection $collection
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function massAction(AbstractCollection $collection)
    {
        $params = $this->getRequest()->getParams();
        $selected = [];
        $collectionMemo = $this->filter->getCollection($this->memoCollectionFactory->create());
        foreach ($collectionMemo as $memo) {
            array_push($selected, $memo->getId());
        }

        if ($selected) {
            foreach ($selected as $creditmemoId) {
                $creditmemo = $this->creditmemoRepository->get($creditmemoId);
                try {
                    $order = $this->deleteCreditMemo($creditmemoId);
                    $this->messageManager->addSuccessMessage(
                        __(
                            'Successfully deleted credit memo #%1.',
                            $creditmemo->getIncrementId()
                        )
                    );
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage(
                        __(
                            'Error delete credit memo #%1.',
                            $creditmemo->getIncrementId()
                        )
                    );
                }
            }
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($params['namespace'] == 'sales_order_view_creditmemo_grid' && isset($order)) {
            $resultRedirect->setPath('sales/order/view', ['order_id' => $order->getId()]);
        } else {
            $resultRedirect->setPath('sales/creditmemo/');
        }
        return $resultRedirect;
    }

    /**
     * Check permission via ACL resource
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Khodal_DeleteOrder::delete_order');
    }

    /**
     * Delete credit memo
     *
     * @param int $creditmemoId
     * @return \Magento\Sales\Model\Order
     * @throws \Exception
     */
    protected function deleteCreditMemo($creditmemoId)
    {
        return $this->delete->deleteCreditmemo($creditmemoId);
    }
}
