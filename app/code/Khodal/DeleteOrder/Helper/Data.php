<?php
/**
 * @author Khodal
 * @copyright Copyright (c) khodal
 * @package DeleteOrder for Magento 2
 */

namespace Khodal\DeleteOrder\Helper;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\ConfigOptionsListConstants;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var DeploymentConfig
     */
    protected $deploymentConfig;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param DeploymentConfig $deploymentConfig
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        DeploymentConfig $deploymentConfig
    ) {
        parent::__construct($context);
        $this->deploymentConfig = $deploymentConfig;
    }

    /**
     * @param null $name
     * @return bool|string|null
     */
    public function getTableName($name = null)
    {
        if ($name == null) {
            return false;
        }
        $tableName = $name;
        $tablePrefix = (string)$this->deploymentConfig->get(
            ConfigOptionsListConstants::CONFIG_PATH_DB_PREFIX
        );
        if ($tablePrefix) {
            $tableName = $tablePrefix . $name;
        }
        return $tableName;
    }
}
