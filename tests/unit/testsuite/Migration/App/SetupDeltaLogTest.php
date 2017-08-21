<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\App;

/**
 * Class ShellTest
 */
class SetupDeltaLogTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @return void
     */
    public function testPerform()
    {
        /** @var \Migration\ResourceModel\Source|\PHPUnit_Framework_MockObject_MockObject $source */
        $source = $this->getMock(\Migration\ResourceModel\Source::class, [], [], '', false);
        /** @var \Migration\ResourceModel\Document|\PHPUnit_Framework_MockObject_MockObject $source */
        $document = $this->getMock(\Migration\ResourceModel\Document::class, [], [], '', false);
        $source->expects($this->any())
            ->method('getDocument')
            ->willReturn($document);
        $source->expects($this->exactly(4))
            ->method('createDelta')
            ->withConsecutive(
                ['orders', 'order_id'],
                ['invoices', 'invoice_id'],
                ['reports', 'report_id'],
                ['shipments', 'shipment_id']
            );

        /** @var \Migration\Reader\Groups|\PHPUnit_Framework_MockObject_MockObject $readerGroups */
        $readerGroups = $this->getMock(\Migration\Reader\Groups::class, [], [], '', false);
        $readerGroups->expects($this->any())
            ->method('getGroups')
            ->with()
            ->willReturn(
                [
                    'firstGroup' => ['orders' => 'order_id', 'invoices' => 'invoice_id'],
                    'secondGroup' => ['reports' => 'report_id', 'shipments' => 'shipment_id']
                ]
            );

        /** @var \Migration\Reader\GroupsFactory|\PHPUnit_Framework_MockObject_MockObject $groupsFactory */
        $groupsFactory = $this->getMock(\Migration\Reader\GroupsFactory::class, [], [], '', false);
        $groupsFactory->expects($this->any())->method('create')->with('delta_document_groups_file')
            ->willReturn($readerGroups);

        /** @var \Migration\App\ProgressBar\LogLevelProcessor|\PHPUnit_Framework_MockObject_MockObject $progress */
        $progress = $this->getMock(\Migration\App\ProgressBar\LogLevelProcessor::class, [], [], '', false);
        $progress->expects($this->once())
            ->method('start')
            ->with(4);
        $progress->expects($this->exactly(4))
            ->method('advance');
        $progress->expects($this->once())
            ->method('finish');

        /** @var \Migration\Logger\Logger|\PHPUnit_Framework_MockObject_MockObject $logger */
        $logger = $this->getMock('\Migration\Logger\Logger', [], [], '', false);

        $deltaLog = new SetupDeltaLog($source, $groupsFactory, $progress, $logger);
        $this->assertTrue($deltaLog->perform());
    }
}
