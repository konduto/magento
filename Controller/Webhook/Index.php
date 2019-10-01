<?php

namespace Konduto\Antifraud\Controller\Webhook;

use Konduto\Antifraud\Helper\Data;
use Konduto\Antifraud\Model\KondutoService;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Filesystem\Driver\File;

/**
 * Class Index
 * @package Konduto\Antifraud\Controller\Webhook
 */
class Index extends Action
{
    /**
     * @var KondutoService
     */
    public $kondutoService;

    public $helper;

    public $response;

    public $file;

    /**
     * Index constructor.
     * @param KondutoService $kondutoService
     * @param Context $context
     */
    public function __construct(
        KondutoService $kondutoService,
        Data $helper,
        Context $context,
        File $file
    ) {
        $this->kondutoService = $kondutoService;
        $this->helper = $helper;
        $this->file = $file;
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
            $postParams = $this->file->fileGetContents('php://input');
            $params = json_decode(utf8_encode($postParams), true);

            if (!array_key_exists('order_id', $params) && !array_key_exists('status', $params)) {
                return $this->isNotSuccess();
            }

            if (!array_key_exists('timestamp', $params) && !array_key_exists('signature', $params)) {
                return $this->isNotSuccess();
            }

            if ($params) {
                $success = $this->kondutoService->updateOrder($params);
            }

            if (!$success) {
                return $this->isNotSuccess();
            }

            return $this->isSuccess();
        } catch (\Exception $e) {
            $this->getResponse()->setHttpResponseCode(400);
            return $this->getResponse()->setBody(json_encode($e->getMessage()));
        }
    }

    private function isSuccess()
    {
        $this->getResponse()->setHttpResponseCode(200);
        $this->response["status"] = "ok";
        return $this->getResponse()->setBody(json_encode($this->response));
    }

    private function isNotSuccess()
    {
        $this->getResponse()->setHttpResponseCode(400);
        $this->response = json_encode($this->response);
        return $this->getResponse()->setBody(json_encode($this->response));
    }
}
