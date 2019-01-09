<?php

namespace Konduto\Antifraud\Controller\Webhook;

use Magento\Framework\App\Action\Action;
use Magento\Backend\App\Action\Context;
use Konduto\Antifraud\Model\KondutoService;
use Konduto\Antifraud\Helper\Data;

/**
 * Class Index
 * @package Konduto\Antifraud\Controller\Webhook
 */
class Index extends Action
{
    /**
     * @var KondutoService
     */
    protected $kondutoService;

    protected $helper;

    /**
     * Index constructor.
     * @param KondutoService $kondutoService
     * @param Context $context
     */
    public function __construct(
        KondutoService $kondutoService,
        Data $helper,
        Context $context
    ) {
        $this->kondutoService = $kondutoService;
        $this->helper = $helper;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $response = [];
        $success = false;

        try {
            $params = json_decode(utf8_encode(file_get_contents('php://input')), true);

            if ($params) {
                $success = $this->kondutoService->updateOrder($params);
            }

            if (!$success) {
                $this->getResponse()->setHttpResponseCode(400);
            }

            if ($success) {
                $response["status"] = "ok";
                $this->getResponse()->setHttpResponseCode(200);
            }
            echo json_encode($response);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
