<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\Step\Map;

/**
 * Volume step test class
 */
class VolumeTest extends \PHPUnit_Framework_TestCase
{

    public function testPerform()
    {
        $objectManager = \Migration\TestFramework\Helper::getInstance()->getObjectManager();
        $objectManager->get('\Migration\Config')->init(dirname(__DIR__) . '/../_files/config.xml');
        $logManager = $objectManager->create('\Migration\Logger\Manager');
        $logger = $objectManager->create('\Migration\Logger\Logger');
        $mapReader = $objectManager->create('\Migration\MapReader\MapReaderMain');
        $config = $objectManager->get('\Migration\Config');
        /** @var \Migration\Logger\Manager $logManager */
        $logManager->process(\Migration\Logger\Manager::LOG_LEVEL_NONE);

        $data = $objectManager->create(
            '\Migration\Step\Map\Data',
            [
                'logger' => $logger,
                'map' => $mapReader,
                'config' => $config
            ]
        );
        $volume = $objectManager->create(
            '\Migration\Step\Map\Volume',
            [
                'logger' => $logger,
                'map' => $mapReader,
                'config' => $config
            ]
        );
        ob_start();
        $data->perform();
        $isSuccess = $volume->perform();
        ob_end_clean();

        $this->assertTrue($isSuccess);
    }
}
