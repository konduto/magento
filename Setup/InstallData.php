<?php

namespace Konduto\Antifraud\Setup;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Sales\Setup\SalesSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * Class InstallData
 * @package Konduto\Antifraud\Setup
 */
class InstallData implements InstallDataInterface
{
    /**
     * @var SalesSetupFactory
     */
    private $salesSetupFactory;

    /**
     * InstallData constructor.
     * @param SalesSetupFactory $salesSetupFactory
     */
    public function __construct(
        SalesSetupFactory $salesSetupFactory
    )
    {
        $this->salesSetupFactory = $salesSetupFactory;

    }

    /**
     * Function install
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        /** @var \Magento\Sales\Setup\SalesSetup $salesInstaller */
        $salesInstaller = $this->salesSetupFactory->create(['resourceName' => 'sales_setup', 'setup' => $setup]);

        $salesInstaller
            ->addAttribute('order', 'konduto_status', [
                'type' => Table::TYPE_TEXT,
                'length'=> 100,
                'visible' => false,
                'nullable' => true,
            ])->addAttribute('order', 'visitor_id', [
                'type' => Table::TYPE_TEXT,
                'length'=> 100,
                'visible' => false,
                'nullable' => true,
            ]);

        $setup->endSetup();
    }
}