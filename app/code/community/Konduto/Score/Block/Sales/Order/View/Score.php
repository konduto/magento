<?php

class Konduto_Score_Block_Sales_Order_View_Score extends Mage_Adminhtml_Block_Sales_Order_View_Info {

  protected $score = '-';
  protected $recomend = '-';

  protected function getScore() {
    $order_id = $this->getOrder()->getId();
    $data = Mage::getModel('score/score')->getCollection()->addFieldToFilter('order_no', $order_id);
    $response = $data->getData();
    $req = $response[0]['response'];
    $resp_arr = json_decode($req, true);
    if (isset($resp_arr['order']['score'])) {
      $this->score = $resp_arr['order']['score'];
    }
    if (isset($resp_arr['order']['recommendation'])) {
      $this->recomend = $resp_arr['order']['recommendation'];
    }
  }

}