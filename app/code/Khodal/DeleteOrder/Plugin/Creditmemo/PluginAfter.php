<?php
/**
 * @author Khodal
 * @copyright Copyright (c) khodal
 * @package DeleteOrder for Magento 2
 */

namespace Khodal\DeleteOrder\Plugin\Creditmemo;

class PluginAfter extends \Khodal\DeleteOrder\Plugin\PluginAbstract
{
    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $data;

    /**
     * PluginAfter constructor.
     * @param \Magento\Authorization\Model\Acl\AclRetriever $aclRetriever
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Backend\Helper\Data $data
     */
    public function __construct(
        \Magento\Authorization\Model\Acl\AclRetriever $aclRetriever,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Backend\Helper\Data $data
    ) {
        parent::__construct($aclRetriever, $authSession);
        $this->data = $data;
    }

    /**
     * @param \Magento\Sales\Block\Adminhtml\Order\Creditmemo\View $subject
     * @param $result
     * @return mixed
     */
    public function afterGetBackUrl(\Magento\Sales\Block\Adminhtml\Order\Creditmemo\View $subject, $result)
    {
        if ($this->isAllowedResources()) {
            $params = $subject->getRequest()->getParams();
            $message = __('Are you sure you want to do this?');
            if ($subject->getRequest()->getFullActionName() == 'sales_order_creditmemo_view') {
                $subject->addButton(
                    'khodal-delete',
                    ['label' => __('Delete'), 'onclick' => 'confirmSetLocation(\'' . $message . '\',\'' . $this->getDeleteUrl($params['creditmemo_id']) . '\')', 'class' => 'khodal-delete'],
                    -1
                );
            }
        }

        return $result;
    }

    /**
     * @param string $creditmemoId
     * @return mixed
     */
    public function getDeleteUrl($creditmemoId)
    {
        return $this->data->getUrl(
            'deleteorder/delete/creditmemo',
            [
                'creditmemo_id' => $creditmemoId
            ]
        );
    }
}
