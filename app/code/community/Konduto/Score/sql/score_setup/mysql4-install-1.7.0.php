<?php

$installer = $this;

$installer->startSetup();

$tableScore = $installer->getTable('score');

$installer->getConnection()->dropTable($tableScore);

$table = $installer->getConnection()
    ->newTable($tableScore)
    ->addColumn('score_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned' => true,
        'nullable' => false,
        'primary' => true,
        'auto_increment' => true,
    ), 'score_id')
    ->addColumn('order_no', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => true,
    ), 'order_no')
    ->addColumn('request', Varien_Db_Ddl_Table::TYPE_LONGVARCHAR, null, array(
        'nullable' => true,
    ), 'request')
    ->addColumn('response', Varien_Db_Ddl_Table::TYPE_LONGVARCHAR, null, array(
        'nullable' => true,
    ), 'response')
    ->addColumn('score', Varien_Db_Ddl_Table::TYPE_VARCHAR, 128, array(
        'nullable' => true,
    ), 'score')
    ->addColumn('recommendation', Varien_Db_Ddl_Table::TYPE_VARCHAR, 128, array(
        'nullable' => true,
    ), 'recommendation')
    ->addColumn('visitor_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 128, array(
        'nullable' => true,
    ), 'visitor_id')
    ->addColumn('created_time', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
        'nullable' => TRUE,
    ), 'created_time')
    ->addColumn('update_time', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
        'nullable' => TRUE,
    ), 'update_time')
    ->addIndex($installer->getIdxName('score_order_no_idx', array('score_id'), Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE), array('score_id'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->setComment('Konduto');
$installer->getConnection()->createTable($table);

$installer->endSetup(); 