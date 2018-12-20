<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\SalesIncrement;

use Migration\ResourceModel\Source;
use Migration\ResourceModel\Destination;
use Migration\ResourceModel\Adapter\Mysql;

/**
 * Class Helper
 */
class Helper
{
    /**
     * @var Source
     */
    private $source;

    /**
     * @var Destination
     */
    private $destination;

    /**
     * @var string
     */
    private $eavEntityStoreTable = 'eav_entity_store';

    /**
     * @var string
     */
    private $storeTable = 'core_store';

    /**
     * @var array
     */
    private $sequenceMetaTable = [
        'name' => 'sales_sequence_meta',
        'structure' => [
            'meta_id',
            'entity_type',
            'store_id',
            'sequence_table'
        ]
    ];

    /**
     * @var array
     */
    private $sequenceProfileTable = [
        'name' => 'sales_sequence_profile',
        'structure' => [
            'profile_id',
            'meta_id',
            'prefix',
            'suffix',
            'start_value',
            'step',
            'max_value',
            'warning_value',
            'is_active'
        ]
    ];

    /**
     * @var array
     */
    private $entityTypeTablesMap = [
        [
            'entity_type_code' => 'order',
            'entity_type_table' => 'sequence_order',
            'column' => 'sequence_value'
        ], [
            'entity_type_code' => 'invoice',
            'entity_type_table' => 'sequence_invoice',
            'column' => 'sequence_value'
        ], [
            'entity_type_code' => 'creditmemo',
            'entity_type_table' => 'sequence_creditmemo',
            'column' => 'sequence_value'
        ], [
            'entity_type_code' => 'shipment',
            'entity_type_table' => 'sequence_shipment',
            'column' => 'sequence_value'
        ], [
            'entity_type_code' => 'rma_item',
            'entity_type_table' => 'sequence_rma_item',
            'column' => 'sequence_value'
        ]
    ];

    /**
     * @param Source $source
     * @param Destination $destination
     */
    public function __construct(
        Source $source,
        Destination $destination
    ) {
        $this->source = $source;
        $this->destination = $destination;
    }

    /**
     * @param int $entityTypeId
     * @param int $storeId
     * @return bool|int
     */
    public function getMaxIncrementForEntityType($entityTypeId, $storeId)
    {
        /** @var \Migration\ResourceModel\Adapter\Mysql $adapter */
        $adapter = $this->source->getAdapter();
        $query = $adapter->getSelect()->from(
            $this->source->addDocumentPrefix($this->eavEntityStoreTable),
            ['increment_prefix', 'increment_last_id']
        )->where('entity_type_id = ?', $entityTypeId
        )->where('store_id IN (?)', $this->getStoreIdsOfStoreGroup($storeId));
        $data = $query->getAdapter()->fetchAll($query);
        if (!$data) {
            return false;
        }
        $cutPrefixFunction = function (array $data) {
            return (int) substr($data['increment_last_id'], strlen($data['increment_prefix']));
        };
        $maxIncrement = max(array_map($cutPrefixFunction, $data));
        return $maxIncrement;
    }

    /**
     * Return store ids of store group
     *
     * @param $storeId
     * @return array
     */
    public function getStoreIdsOfStoreGroup($storeId)
    {
        /** @var \Migration\ResourceModel\Adapter\Mysql $adapter */
        $adapter = $this->source->getAdapter();
        $select = $adapter->getSelect()->from(
            ['cs' => $this->source->addDocumentPrefix($this->storeTable)],
            ['store_id']
        )->join(
            ['css' => $this->source->addDocumentPrefix($this->storeTable)],
            'css.group_id = cs.group_id',
            []
        )->where('css.store_id = ?', $storeId);
        return $select->getAdapter()->fetchCol($select);
    }

    /**
     * @return array
     */
    public function getStoreIds()
    {
        /** @var \Migration\ResourceModel\Adapter\Mysql $adapter */
        $adapter = $this->source->getAdapter();
        $query = $adapter->getSelect()->from($this->source->addDocumentPrefix($this->storeTable), ['store_id']);
        return $query->getAdapter()->fetchCol($query);
    }

    /**
     * @return array
     */
    public function getEntityTypeTablesMap()
    {
        $entityIds = $this->getEntityTypeIdByCode(array_column($this->entityTypeTablesMap, 'entity_type_code'));
        foreach ($this->entityTypeTablesMap as &$entityTypeTable) {
            $entityTypeTable['entity_type_id'] = isset($entityIds[$entityTypeTable['entity_type_code']])
                ? $entityIds[$entityTypeTable['entity_type_code']]
                : null;
        }
        return $this->entityTypeTablesMap;
    }

    /**
     * @param string $key
     * @param string $value
     * @return array
     */
    public function getEntityTypeData($key, $value)
    {
        foreach ($this->getEntityTypeTablesMap() as $entityType) {
            if (isset($entityType[$key]) && $entityType[$key] == $value) {
                return $entityType;
            }
        }
        return [];
    }

    /**
     * @param bool $structure
     * @return string|array
     */
    public function getSequenceMetaTable($structure = false)
    {
        return $structure ? $this->sequenceMetaTable['structure'] : $this->sequenceMetaTable['name'];
    }

    /**
     * @param bool $structure
     * @return string|array
     */
    public function getSequenceProfileTable($structure = false)
    {
        return $structure ? $this->sequenceProfileTable['structure'] : $this->sequenceProfileTable['name'];
    }

    /**
     * @param string $table
     * @param bool $storeId
     * @return string
     */
    public function getTableName($table, $storeId = false)
    {
        return ($storeId !== false)
            ? $this->destination->addDocumentPrefix($table) . '_' . $storeId
            : $this->destination->addDocumentPrefix($table);
    }

    /**
     * @param array $entityTypeCodes
     * @return array
     */
    private function getEntityTypeIdByCode($entityTypeCodes)
    {
        /** @var Mysql $adapter */
        $adapter = $this->destination->getAdapter();
        $query = $adapter->getSelect()
            ->from(
                ['et' => $this->destination->addDocumentPrefix('eav_entity_type')],
                ['entity_type_id', 'entity_type_code']
            )
            ->where('et.entity_type_code IN (?)', $entityTypeCodes);
        $entityTypeIds = [];
        foreach ($query->getAdapter()->fetchAll($query) as $record) {
            $entityTypeIds[$record['entity_type_code']] = $record['entity_type_id'];
        }
        return $entityTypeIds;
    }
}
