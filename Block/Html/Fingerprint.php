<?php

namespace Konduto\Antifraud\Block\Html;

use Magento\Framework\View\Element\Template;
use Magento\Cms\Api\Data\BlockInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Registry;
use Magento\Framework\App\Request\Http;
use Magento\Store\Model\ScopeInterface;
use Magento\Customer\Model\Session;
use Konduto\Antifraud\Helper\Data;

/**
 * Class Fingerprint
 * @package Konduto\Antifraud\Block\Html
 */
class Fingerprint extends Template
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * Fingerprint constructor.
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     * @param Registry $registry
     * @param Http $request
     * @param Data $helper
     * @param Session $customerSession
     * @param array $data
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        Registry $registry,
        Http $request,
        Data $helper,
        Session $customerSession,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->request = $request;
        $this->helper = $helper;
        $this->customerSession = $customerSession;
        parent::__construct(
            $context,
            $data
        );
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        if ($this->helper->isEnabled()) {
            return true;
        }
        return false;
    }

    /**
     * @return mixed
     */
    public function getPublicKey()
    {
        $environment = $this->helper->getEnvironment();
        return $this->_scopeConfig->getValue('konduto_antifraud/settings/'. $environment .'_public_key');
    }

    /**
     * @return mixed
     */
    public function getProductName()
    {
        $product = $this->registry->registry('current_product');
        return $product->getName();
    }

    /**
     * @return mixed
     */
    public function getProductSku()
    {
        $product = $this->registry->registry('current_product');

        return $product->getSku();
    }

    /**
     * @return bool
     */
    public function isCustomerLoggedIn()
    {
        return $this->customerSession->isLoggedIn();
    }

    /**
     * @return bool|mixed
     */
    public function getCustomerId()
    {
        if (!$this->isCustomerLoggedIn()) {
            return false;
        }
        return $this->customerSession->getCustomer()->getId();
    }
}