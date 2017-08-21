<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\UrlRewrite;

use Migration\Step\UrlRewrite\Model\Version11410to2000;

/**
 * Class UrlRewriteTest
 * @SuppressWarnings(PHPMD)
 */
class Version11410to2000Test extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Migration\Step\UrlRewrite\Version11410to2000
     */
    protected $version;

    /**
     * @var \Migration\App\ProgressBar\LogLevelProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $progress;

    /**
     * @var \Migration\Logger\Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    /**
     * @var \Migration\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $config;

    /**
     * @var \Migration\ResourceModel\Source|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $source;

    /**
     * @var \Migration\ResourceModel\Destination|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $destination;

    /**
     * @var \Migration\ResourceModel\Record\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $recordCollectionFactory;

    /**
     * @var \Migration\ResourceModel\RecordFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $recordFactory;

    /**
     * @var \Migration\Step\UrlRewrite\Helper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helper;

    /**
     * @var ProductRewritesWithoutCategories|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productRewritesWithoutCategories;

    /**
     * @var ProductRewritesIncludedIntoCategories|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productRewritesIncludedIntoCategories;

    /**
     * @var Suffix|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $suffix;

    /**
     * @var TemporaryTable|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $temporaryTable;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->progress = $this->getMock(
            \Migration\App\ProgressBar\LogLevelProcessor::class,
            ['start', 'finish', 'advance'],
            [],
            '',
            false
        );
        $this->logger = $this->getMock(\Migration\Logger\Logger::class, ['debug', 'error'], [], '', false);
        $this->config = $this->getMock(\Migration\Config::class, [], [], '', false);
        $this->config->expects($this->any())->method('getSource')->willReturn([
            'type' => 'database',
            'version' => '1.14.1.0'
        ]);
        $this->source = $this->getMock(\Migration\ResourceModel\Source::class, [], [], '', false);
        $this->destination = $this->getMock(\Migration\ResourceModel\Destination::class, [], [], '', false);
        $this->recordCollectionFactory = $this->getMock(
            \Migration\ResourceModel\Record\CollectionFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->recordFactory = $this->getMock(\Migration\ResourceModel\RecordFactory::class, ['create'], [], '', false);
        $this->helper = $this->getMock(\Migration\Step\UrlRewrite\Helper::class, [], ['processFields'], '', false);
        $this->productRewritesWithoutCategories = $this->getMock(
            \Migration\Step\UrlRewrite\Model\Version11410to2000\ProductRewritesWithoutCategories::class,
            [],
            [],
            '',
            false
        );
        $this->productRewritesIncludedIntoCategories = $this->getMock(
            \Migration\Step\UrlRewrite\Model\Version11410to2000\ProductRewritesIncludedIntoCategories::class,
            [],
            [],
            '',
            false
        );
        $this->suffix = $this->getMock(\Migration\Step\UrlRewrite\Model\Suffix::class, [], [], '', false);
        $this->temporaryTable = $this->getMock(\Migration\Step\UrlRewrite\Model\TemporaryTable::class, [], [], '', false);
    }

    /**
     * @return void
     */
    public function testIntegrity()
    {
        $this->helper->expects($this->any())->method('processFields')->willReturn([
            'array' => 'with_processed_fields'
        ]);
        $this->version = new \Migration\Step\UrlRewrite\Version11410to2000(
            $this->progress,
            $this->logger,
            $this->config,
            $this->source,
            $this->destination,
            $this->recordCollectionFactory,
            $this->recordFactory,
            $this->helper,
            $this->productRewritesWithoutCategories,
            $this->productRewritesIncludedIntoCategories,
            $this->suffix,
            $this->temporaryTable,
            'integrity'
        );
    }
}
