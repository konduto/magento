<?php

namespace Konduto\Antifraud\Helper;

use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Sales\Api\Data\TransactionSearchResultInterfaceFactory;
use Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\CollectionFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Stdlib\DateTime;
use Konduto\Exceptions\KondutoException;
use Konduto\Core\Konduto;
use Konduto\Antifraud\Logger\Logger;

/**
 * Class Data
 * @package Konduto\Antifraud\Helper
 */
class Data
{
    const ENABLED_PATH = 'konduto_antifraud/settings/enabled';
    const ENABLED_ANALYSIS_PATH = 'konduto_antifraud/settings/enabled_analysis';
    const DEBUG_PATH = 'konduto_antifraud/settings/debug';
    const PAYMENTS_PATH = 'konduto_antifraud/settings/payment_enabled';

    const STREET_ATTRIBUTE = 'konduto_antifraud/address_mapping/address_street_map';
    const NUMBER_ATTRIBUTE = 'konduto_antifraud/address_mapping/address_number_map';
    const COMPLEMENT_ATTRIBUTE = 'konduto_antifraud/address_mapping/address_complement_map';
    const NEIGHBORHOOD_ATTRIBUTE = 'konduto_antifraud/address_mapping/address_neighborhood_map';

    const KONDUTO_IDENTIFIER_ATTRIBUTE = 'konduto_antifraud/customer_mapping/konduto_identifier';
    const CPF_CNPJ_ATTRIBUTE = 'konduto_antifraud/customer_mapping/konduto_cpf_cnpj';
    const CUSTOMER_TAG_PATH = 'konduto_antifraud/customer_mapping/konduto_customer_tag';

    const PAYMENT_CREDIT_ATTRIBUTE = 'konduto_antifraud/payment_mapping/credit_map';
    const PAYMENT_DEBIT_ATTRIBUTE = 'konduto_antifraud/payment_mapping/debit_map';
    const PAYMENT_BOLETO_ATTRIBUTE = 'konduto_antifraud/payment_mapping/boleto_map';
    const PAYMENT_TRANSFER_ATTRIBUTE = 'konduto_antifraud/payment_mapping/transfer_map';

    const PAYMENT_VOUCHER_ATTRIBUTE = 'konduto_antifraud/payment_mapping/voucher_map';
    const FILTER_STATUS_PATH = 'konduto_antifraud/manage_status/filter_status';
    const AUTOMATIC_KONDUTO_UPDATE_PATH = 'konduto_antifraud/manage_status/automatic_konduto_update';
    const APPROVED_STATUS_PATH = 'konduto_antifraud/manage_status/approved_status';
    const DECLINED_STATUS_PATH = 'konduto_antifraud/manage_status/declined_status';
    const NOT_AUTHORIZED_STATUS_PATH = 'konduto_antifraud/manage_status/not_authorized_status';
    const CANCELED_STATUS_PATH = 'konduto_antifraud/manage_status/canceled_status';
    const FRAUD_STATUS_PATH = 'konduto_antifraud/manage_status/fraud_status';

    const ENABLED_ADVANCED_OPTIONS_PATH = 'konduto_antifraud/cron_config/enabled_advanced_options';
    const MASS_ORDER_QTY_PATH = 'konduto_antifraud/cron_config/mass_order_qty';
    const CRON_FREQUENCY_PATH = 'konduto_antifraud/cron_config/cron_frequency';

    const ORDER_STATUS_APPROVED = 'approved';
    const ORDER_STATUS_DECLINED = 'declined';
    const ORDER_STATUS_NOT_AUTHORIZED = 'not_authorized';
    const ORDER_STATUS_CANCELED = 'canceled';
    const ORDER_STATUS_FRAUD = 'fraud';

    public static $updateOrderStatusList = [
        self::ORDER_STATUS_APPROVED,
        self::ORDER_STATUS_DECLINED,
        self::ORDER_STATUS_NOT_AUTHORIZED,
        self::ORDER_STATUS_CANCELED,
        self::ORDER_STATUS_FRAUD,
    ];

    const CREDIT_METHOD = 'credit';
    const DEBIT_METHOD = 'debit';
    const BOLETO_METHOD = 'boleto';
    const TRANSFER_METHOD = 'transfer';
    const VOUCHER_METHOD = 'voucher';

    public $transactionSearchResultInterfaceFactory;
    public $collectionFactory;
    protected $customerRepositoryInterface;
    protected $orderRepository;
    protected $scopeConfig;
    protected $timezone;
    public $logger;

    /**
     * Data constructor.
     * @param OrderRepositoryInterface $orderRepository
     * @param ScopeConfigInterface $scopeConfig
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     * @param TransactionSearchResultInterfaceFactory $transactionSearchResultInterfaceFactory
     * @param CollectionFactory $collectionFactory
     * @param TimezoneInterface $timezone
     * @param LoggerInterface $logger
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        ScopeConfigInterface $scopeConfig,
        CustomerRepositoryInterface $customerRepositoryInterface,
        TransactionSearchResultInterfaceFactory $transactionSearchResultInterfaceFactory,
        CollectionFactory $collectionFactory,
        TimezoneInterface $timezone,
        Logger $logger
    ) {
        $this->orderRepository = $orderRepository;
        $this->scopeConfig = $scopeConfig;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->timezone = $timezone;
        $this->transactionSearchResultInterfaceFactory = $transactionSearchResultInterfaceFactory;
        $this->collectionFactory = $collectionFactory;
        $this->logger = $logger;
    }

    /**
     * @return string
     */
    public function getEnvironment()
    {
        if ($this->scopeConfig->getValue('konduto_antifraud/settings/environment') == 0) {
            return 'sandbox';
        }
        return 'production';
    }

    /**
     * @return mixed
     */
    public function getPrivateKey()
    {
        $environment = $this->getEnvironment();
        return $this->scopeConfig->getValue('konduto_antifraud/settings/'. $environment .'_private_key');
    }

    /**
     * @return int|mixed
     */
    public function getMassOrderQuantity()
    {
        $qty = 1;
        $configQty = $this->scopeConfig->getValue(
            self::MASS_ORDER_QTY_PATH, ScopeInterface::SCOPE_STORE);

        if ($configQty) {
            if ($configQty > $qty) {
                $qty = $configQty;
            }
        }

        return $qty;
    }

    public function isAdvancedOptions()
    {
        if (!$this->scopeConfig->getValue(self::ENABLED_ADVANCED_OPTIONS_PATH, ScopeInterface::SCOPE_STORE)) {
            return false;
        }
        return true;
    }

    public function isCustomerTag()
    {
        return $this->scopeConfig->getValue(self::CUSTOMER_TAG_PATH, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getConfigQty()
    {
        return $this->scopeConfig->getValue(self::MASS_ORDER_QTY_PATH, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        if (!$this->scopeConfig->getValue(self::ENABLED_PATH, ScopeInterface::SCOPE_STORE)) {
            return false;
        }
        return true;
    }

    public function isCronEnabled()
    {
        if (!$this->scopeConfig->getValue(self::ENABLED_ANALYSIS_PATH, ScopeInterface::SCOPE_STORE)) {
            return false;
        }
        return true;
    }

    /**
     * @throws KondutoException
     */
    public function apiKeyIsValid()
    {
        $privateKey = $this->getPrivateKey();

        if (!$privateKey) {
            $this->helper->log('The API private key was not filled', 'notice');
            return false;
        }

        try {
            $response = Konduto::setApiKey($privateKey);
            if ($response) {
                return true;
            }
        } catch (KondutoException $exception) {
            $this->log('ERROR: ' . $exception->getMessage(), 'error');
            return false;
        }
    }

    /**
     * @param $orderId
     * @return array
     */
    public function getFilterParams()
    {
        $params = $this->scopeConfig
                ->getValue(
                    self::PAYMENTS_PATH,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                );

        $params = explode(",", $params);
        return (array) $params;
    }

    public function getStatusCode($status)
    {
        switch ($status) {
            case self::ORDER_STATUS_APPROVED:
                return $this->scopeConfig->getValue(
                    self::APPROVED_STATUS_PATH,
                    ScopeInterface::SCOPE_STORE
                );

            case self::ORDER_STATUS_DECLINED:
                return $this->scopeConfig->getValue(
                    self::DECLINED_STATUS_PATH,
                    ScopeInterface::SCOPE_STORE
                );

            case self::ORDER_STATUS_NOT_AUTHORIZED:
                return $this->scopeConfig->getValue(
                    self::NOT_AUTHORIZED_STATUS_PATH,
                    ScopeInterface::SCOPE_STORE
                );
            case self::ORDER_STATUS_CANCELED:
                return $this->scopeConfig->getValue(
                    self::CANCELED_STATUS_PATH,
                    ScopeInterface::SCOPE_STORE
                );
            case self::ORDER_STATUS_FRAUD:
                return $this->scopeConfig->getValue(
                    self::FRAUD_STATUS_PATH,
                    ScopeInterface::SCOPE_STORE
                );
            default:
                return false;
        }
    }

    /**
     * @param $params
     * @return bool
     */
    public function validateSignature($params)
    {
        if (!isset($params["signature"])) {
            return false;
        }
        $signature = $params["signature"];
        unset($params["signature"]);
        $toHash = implode("#", $params);
        $myHash = hash_hmac("sha256", $toHash, $this->getPrivateKey());

        if ($myHash === $signature) {
            return true;
        }
        return false;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getNowDateTime()
    {
        $currentDateTimeUTC = (new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT);
        $localizedDateTimeISO = $this->timezone->date(
            new \DateTime($currentDateTimeUTC))->format(
            DateTime::DATETIME_PHP_FORMAT);

        $now = str_replace(' ', 'T', $localizedDateTimeISO);
        return $now;
    }

    /**
     * @param $date
     * @return false|string
     */
    public function getDate($date)
    {
        return date("Y-m-d", strtotime($date));
    }

    /**
     * @param $customerId
     * @return \Magento\Customer\Api\Data\CustomerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCustomer($customerId)
    {
        return $this->customerRepositoryInterface->getById($customerId);
    }

    /**
     * @param $method
     * @return bool|string
     */
    public function getPaymentType($method)
    {
        $defaults = $this->initPaymentMapping();

        switch ($method) {
            case in_array($method, $defaults['Cartao Credito']):
                return self::CREDIT_METHOD;

            case in_array($method, $defaults['Boleto']):
                return self::BOLETO_METHOD;

            case in_array($method, $defaults['Cartao Debito']):
                return self::DEBIT_METHOD;

            case in_array($method, $defaults['Transferencia']):
                return self::TRANSFER_METHOD;

            case in_array($method, $defaults['Voucher']):
                return self::VOUCHER_METHOD;

            default:
                return false;
        }
    }

    /**
     * @return array
     */
    protected function initPaymentMapping()
    {
        return array(
            'Cartao Credito' => $this->formatPaymentDefaults(self::PAYMENT_CREDIT_ATTRIBUTE),
            'Cartao Debito'	=> $this->formatPaymentDefaults(self::PAYMENT_DEBIT_ATTRIBUTE),
            'Boleto' => $this->formatPaymentDefaults(self::PAYMENT_BOLETO_ATTRIBUTE),
            'Transferencia'	=> $this->formatPaymentDefaults(self::PAYMENT_TRANSFER_ATTRIBUTE),
            'Voucher'	=> $this->formatPaymentDefaults(self::PAYMENT_VOUCHER_ATTRIBUTE)
        );
    }

    /**
     * @param $defaults
     * @return array
     */
    protected function formatPaymentDefaults($defaults)
    {
        $payments[] = $this->scopeConfig->getValue(
            $defaults,
            ScopeInterface::SCOPE_STORE
        );

        return $payments;
    }

    /**
     * @param $message
     * @param array $context
     */
    public function log($message, $level, array $context = [] )
    {
        if (!$this->scopeConfig->getValue(self::DEBUG_PATH, ScopeInterface::SCOPE_STORE)) {
            return;
        }
        $this->logger->log($level, $message, $context);
    }

    /**
     * @param $orderId
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function loadOrder($orderId)
    {
        return $this->orderRepository->get($orderId);
    }

    /**
     * @return mixed
     */
    public function getCpfCnpjAttribute()
    {
        return $this->scopeConfig->getValue(self::CPF_CNPJ_ATTRIBUTE, ScopeInterface::SCOPE_STORE);
    }

    public function getKondutoIdentifierPath()
    {
        $field = $this->scopeConfig->getValue(self::KONDUTO_IDENTIFIER_ATTRIBUTE, ScopeInterface::SCOPE_STORE);

        switch ($field) {
            case $field == '0':
                return 'CustomerId';

            case $field == '1':
                return 'TaxVat';

            case $field == '2':
                return 'Email';

            default:
                return false;
        }
    }

    public function getKondutoIdentifierData($customer)
    {
        $field = $this->getKondutoIdentifierPath();

        switch ($field) {
            case $field == 'CustomerId':
                return $customer->getId();

            case $field == 'TaxVat':
                return $this->getDocumentNumber($customer);

            case $field == 'Email':
                return $customer->getEmail();

            default:
                return false;
        }
    }

    public function getDocumentNumber($customer)
    {
        $field = $this->getCpfCnpjAttribute();
        $document = $customer->getCustomAttribute($field);

        if (!$document) {
            return $this->traitDocument($customer->getTaxvat());
        }

        return $this->traitDocument($document);
    }

    /**
     * @return mixed
     */
    public function getStreet()
    {
        return $this->scopeConfig->getValue(self::STREET_ATTRIBUTE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getNumber()
    {
        return $this->scopeConfig->getValue(self::NUMBER_ATTRIBUTE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getComplement()
    {
        return $this->scopeConfig->getValue(self::COMPLEMENT_ATTRIBUTE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getNeighborhood()
    {
        return $this->scopeConfig->getValue(self::NEIGHBORHOOD_ATTRIBUTE, ScopeInterface::SCOPE_STORE);
    }

    public function getAutomaticKondutoUpdate()
    {
        if (!$this->scopeConfig->getValue(self::AUTOMATIC_KONDUTO_UPDATE_PATH, ScopeInterface::SCOPE_STORE)) {
            return false;
        }
        return true;
    }

    /**
     * @param $orderId
     * @return \Magento\Sales\Api\Data\TransactionInterface[]
     */
    public function getTransactions($orderId)
    {
        return $this->collectionFactory->create()->addOrderIdFilter($orderId)->getItems();
    }

    public function treatCents($number)
    {
        return number_format($number, 2, '.', '');
    }

    public function traitDocument($document)
    {
        return preg_replace('/[^0-9]+/', '', $document);
    }
}