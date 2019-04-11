<?php

namespace Konduto\Antifraud\Controller\Webhook;

use Konduto\Antifraud\Helper\Data;
use Konduto\Antifraud\Model\KondutoService;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\Action;

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

    protected $response;

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
        $this->response = [];
        $success = false;

        try {
            $params = json_decode(utf8_encode(file_get_contents('php://input')), true);

            if ($params) {
                $success = $this->kondutoService->updateOrder($params);
            }

            if (!$success) {
                return $this->isNotSuccess();
            }

            return $this->isSuccess();
        } catch (\Exception $e) {
            echo json_encode($e->getMessage());
        }
    }

    protected function isSuccess()
    {
        $this->getResponse()->setHttpResponseCode(200);
        $this->response["status"] = "ok";
        echo json_encode($this->response);
    }

    protected function isNotSuccess()
    {
        $this->getResponse()->setHttpResponseCode(400);
        $this->response = json_encode($this->response);
        echo json_encode($this->response);
    }
}
