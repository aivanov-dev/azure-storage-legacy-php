<?php

/**
 * LICENSE: The MIT License (the "License")
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * https://github.com/azure/azure-storage-php/LICENSE
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * PHP version 5
 *
 * @category  Microsoft
 * @package   MicrosoftAzureLegacy\Storage\Tests\Functional\Table
 * @author    Azure Storage PHP SDK <dmsh@microsoft.com>
 * @copyright 2016 Microsoft Corporation
 * @license   https://github.com/azure/azure-storage-php/LICENSE
 * @link      https://github.com/azure/azure-storage-php
 */

namespace MicrosoftAzureLegacy\Storage\Tests\Functional\Table;

use MicrosoftAzureLegacy\Storage\Table\TableRestProxy;
use MicrosoftAzureLegacy\Storage\Tests\Framework\TestResources;
use MicrosoftAzureLegacy\Storage\Tests\Functional\Table\Enums\ConcurType;
use MicrosoftAzureLegacy\Storage\Tests\Functional\Table\Enums\MutatePivot;
use MicrosoftAzureLegacy\Storage\Tests\Functional\Table\Enums\OpType;
use MicrosoftAzureLegacy\Storage\Tests\Functional\Table\Models\BatchWorkerConfig;
use MicrosoftAzureLegacy\Storage\Tests\Functional\Table\Models\FakeTableInfoEntry;
use MicrosoftAzureLegacy\Storage\Common\Internal\Utilities;
use MicrosoftAzureLegacy\Storage\Common\Exceptions\ServiceException;
use MicrosoftAzureLegacy\Storage\Table\Models\BatchOperations;
use MicrosoftAzureLegacy\Storage\Table\Models\DeleteEntityOptions;
use MicrosoftAzureLegacy\Storage\Table\Models\EdmType;
use MicrosoftAzureLegacy\Storage\Table\Models\Entity;
use MicrosoftAzureLegacy\Storage\Table\Models\InsertEntityResult;
use MicrosoftAzureLegacy\Storage\Table\Models\Property;
use MicrosoftAzureLegacy\Storage\Table\Models\QueryEntitiesOptions;
use MicrosoftAzureLegacy\Storage\Table\Models\GetTableOptions;
use MicrosoftAzureLegacy\Storage\Table\Models\GetEntityOptions;
use MicrosoftAzureLegacy\Storage\Table\Models\QueryTablesOptions;
use MicrosoftAzureLegacy\Storage\Table\Models\TableServiceOptions;
use MicrosoftAzureLegacy\Storage\Table\Models\TableServiceCreateOptions;
use MicrosoftAzureLegacy\Storage\Table\Models\UpdateEntityResult;
use MicrosoftAzureLegacy\Storage\Common\Middlewares\RetryMiddlewareFactory;
use MicrosoftAzureLegacy\Storage\Common\Middlewares\HistoryMiddleware;
use MicrosoftAzureLegacy\Storage\Common\LocationMode;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;

class TableServiceFunctionalTest extends FunctionalTestBase
{
    public function testGetServicePropertiesNoOptions()
    {
        $serviceProperties = TableServiceFunctionalTestData::getDefaultServiceProperties();

        $shouldReturn = false;
        try {
            $this->restProxy->setServiceProperties($serviceProperties);
            $this->assertFalse($this->isEmulated(), 'Should succeed when not running in emulator');
        } catch (ServiceException $e) {
            // Expect failure in emulator, as v1.6 doesn't support this method
            if ($this->isEmulated()) {
                $this->assertEquals(TestResources::STATUS_BAD_REQUEST, $e->getCode(), 'getCode');
                $shouldReturn = true;
            } else {
                throw $e;
            }
        }
        if ($shouldReturn) {
            return;
        }

        $this->getServicePropertiesWorker(null);
    }

    public function testGetServiceProperties()
    {
        $serviceProperties = TableServiceFunctionalTestData::getDefaultServiceProperties();

        try {
            $this->restProxy->setServiceProperties($serviceProperties);
            $this->assertFalse($this->isEmulated(), 'Should succeed when not running in emulator');
        } catch (ServiceException $e) {
            // Expect failure in emulator, as v1.6 doesn't support this method
            if ($this->isEmulated()) {
                $this->assertEquals(TestResources::STATUS_BAD_REQUEST, $e->getCode(), 'getCode');
            } else {
                throw $e;
            }
        }
    }

    private function getServicePropertiesWorker($options)
    {
        self::println('Trying $options: ' . self::tmptostring($options));
        $effOptions = (is_null($options) ? new TableServiceOptions() : $options);
        try {
            $ret = (is_null($options) ?
                $this->restProxy->getServiceProperties() :
                $this->restProxy->getServiceProperties($effOptions)
            );
            $this->assertFalse($this->isEmulated(), 'Should succeed when not running in emulator');
            $this->verifyServicePropertiesWorker($ret, null);
        } catch (ServiceException $e) {
            if ($this->isEmulated()) {
                // Expect failure in emulator, as v1.6 doesn't support this method
                $this->assertEquals(TestResources::STATUS_BAD_REQUEST, $e->getCode(), 'getCode');
            } else {
                $this->assertEquals(TestResources::STATUS_INTERNAL_SERVER_ERROR, $e->getCode(), 'getCode');
            }
        }
    }

    private function verifyServicePropertiesWorker($ret, $serviceProperties)
    {
        if (is_null($serviceProperties)) {
            $serviceProperties = TableServiceFunctionalTestData::getDefaultServiceProperties();
        }

        $sp = $ret->getValue();
        $this->assertNotNull($sp, 'getValue should be non-null');

        $l = $sp->getLogging();
        $this->assertNotNull($l, 'getValue()->getLogging() should be non-null');
        $this->assertEquals(
            $serviceProperties->getLogging()->getVersion(),
            $l->getVersion(),
            'getValue()->getLogging()->getVersion'
        );
        $this->assertEquals(
            $serviceProperties->getLogging()->getDelete(),
            $l->getDelete(),
            'getValue()->getLogging()->getDelete'
        );
        $this->assertEquals(
            $serviceProperties->getLogging()->getRead(),
            $l->getRead(),
            'getValue()->getLogging()->getRead'
        );
        $this->assertEquals(
            $serviceProperties->getLogging()->getWrite(),
            $l->getWrite(),
            'getValue()->getLogging()->getWrite'
        );

        $r = $l->getRetentionPolicy();
        $this->assertNotNull($r, 'getValue()->getLogging()->getRetentionPolicy should be non-null');
        $this->assertEquals(
            $serviceProperties->getLogging()->getRetentionPolicy()->getDays(),
            $r->getDays(),
            'getValue()->getLogging()->getRetentionPolicy()->getDays'
        );

        $m = $sp->getHourMetrics();
        $this->assertNotNull($m, 'getValue()->getHourMetrics() should be non-null');
        $this->assertEquals(
            $serviceProperties->getHourMetrics()->getVersion(),
            $m->getVersion(),
            'getValue()->getHourMetrics()->getVersion'
        );
        $this->assertEquals(
            $serviceProperties->getHourMetrics()->getEnabled(),
            $m->getEnabled(),
            'getValue()->getHourMetrics()->getEnabled'
        );
        $this->assertEquals(
            $serviceProperties->getHourMetrics()->getIncludeAPIs(),
            $m->getIncludeAPIs(),
            'getValue()->getHourMetrics()->getIncludeAPIs'
        );

        $r = $m->getRetentionPolicy();
        $this->assertNotNull($r, 'getValue()->getHourMetrics()->getRetentionPolicy should be non-null');
        $this->assertEquals(
            $serviceProperties->getHourMetrics()->getRetentionPolicy()->getDays(),
            $r->getDays(),
            'getValue()->getHourMetrics()->getRetentionPolicy()->getDays'
        );
    }

    public function testSetServicePropertiesNoOptions()
    {
        $serviceProperties = TableServiceFunctionalTestData::getDefaultServiceProperties();
        $this->setServicePropertiesWorker($serviceProperties, null);
    }

    public function testSetServiceProperties()
    {
        $interestingServiceProperties = TableServiceFunctionalTestData::getInterestingServiceProperties();
        foreach ($interestingServiceProperties as $serviceProperties) {
            $options = new TableServiceOptions();
            $this->setServicePropertiesWorker($serviceProperties, $options);
        }

        if (!$this->isEmulated()) {
            $serviceProperties = TableServiceFunctionalTestData::getDefaultServiceProperties();
            $this->restProxy->setServiceProperties($serviceProperties);
        }
    }

    private function setServicePropertiesWorker($serviceProperties, $options)
    {
        try {
            if (is_null($options)) {
                $this->restProxy->setServiceProperties($serviceProperties);
            } else {
                $this->restProxy->setServiceProperties($serviceProperties, $options);
            }

            $this->assertFalse($this->isEmulated(), 'Should succeed when not running in emulator');

            \sleep(10);

            $ret = (is_null($options) ?
                $this->restProxy->getServiceProperties() :
                $this->restProxy->getServiceProperties($options)
            );
            $this->verifyServicePropertiesWorker($ret, $serviceProperties);
        } catch (ServiceException $e) {
            if ($this->isEmulated()) {
                $this->assertEquals(TestResources::STATUS_BAD_REQUEST, $e->getCode(), 'getCode');
            } else {
                throw $e;
            }
        }
    }

    public function testQueryTablesNoOptions()
    {
        $this->queryTablesWorker(null);
    }

    public function testQueryTables()
    {
        $interestingqueryTablesOptions =
            TableServiceFunctionalTestData::getInterestingQueryTablesOptions($this->isEmulated());
        foreach ($interestingqueryTablesOptions as $options) {
            $this->queryTablesWorker($options);
        }
    }

    private function queryTablesWorker($options)
    {
        try {
            $ret = (is_null($options) ? $this->restProxy->queryTables() : $this->restProxy->queryTables($options));

            if (is_null($options)) {
                $options = new QueryTablesOptions();
            }

            if ((!is_null($options->getTop()) && $options->getTop() <= 0)) {
                if ($this->isEmulated()) {
                    $this->assertEquals(0, count($ret->getTables()), "should be no tables");
                } else {
                    $this->fail('Expect non-positive Top in $options to throw');
                }
            }

            $this->verifyqueryTablesWorker($ret, $options);
        } catch (ServiceException $e) {
            if ((!is_null($options->getTop()) && $options->getTop() <= 0) && !$this->isEmulated()) {
                $this->assertEquals(TestResources::STATUS_BAD_REQUEST, $e->getCode(), 'getCode');
            } else {
                throw $e;
            }
        }
    }

    private function verifyqueryTablesWorker($ret, $options)
    {
        $this->assertNotNull($ret->getTables(), 'getTables');

        $effectivePrefix = $options->getPrefix();
        if (is_null($effectivePrefix)) {
            $effectivePrefix = '';
        }

        $expectedFilter = $options->getFilter();
        if (TableServiceFunctionalTestUtils::isEqNotInTopLevel($expectedFilter)) {
            // This seems wrong, but appears to be a bug in the $service itself.
            // So working around the limitation.
            $expectedFilter = TableServiceFunctionalTestUtils::cloneRemoveEqNotInTopLevel($expectedFilter);
        }

        $expectedData = array();
        foreach (TableServiceFunctionalTestData::$testTableNames as $s) {
            if (substr($s, 0, strlen($effectivePrefix)) == $effectivePrefix) {
                $fte = new FakeTableInfoEntry();
                $fte->TableName = $s;
                array_push($expectedData, $fte);
            }
        }

        if (!is_null($options->getNextTableName())) {
            $tmpExpectedData = array();
            $foundNext = false;
            foreach ($expectedData as $s) {
                if ($s == $options->getNextTableName()) {
                    $foundNext = true;
                }

                if (!$foundNext) {
                    continue;
                }

                if (substr($s, 0, strlen($effectivePrefix)) == $effectivePrefix) {
                    $fte = new FakeTableInfoEntry();
                    $fte->TableName = $s;
                    array_push($expectedData, $fte);
                }
            }

            $expectedData = $tmpExpectedData;
        }


        $expectedData = TableServiceFunctionalTestUtils::filterList($expectedFilter, $expectedData);
        $effectiveTop = (is_null($options->getTop()) ? 100000 : $options->getTop());
        $expectedCount = min($effectiveTop, count($expectedData));

        $tables = $ret->getTables();
        for ($i = 0; $i < $expectedCount; $i++) {
            $expected = $expectedData[$i]->TableName;
            // Assume there are other tables. Make sure the expected ones are there.
            $foundNext = false;
            foreach ($tables as $actual) {
                if ($expected == $actual) {
                    $foundNext = true;
                    break;
                }
            }
            $this->assertTrue($foundNext, $expected . ' should be in getTables');
        }
    }

    public function testCreateTableNoOptions()
    {
        $this->createTableWorker(null);
    }

    public function testCreateTable()
    {
        $options = new TableServiceCreateOptions();
        $this->createTableWorker($options);
    }

    private function createTableWorker($options)
    {
        $table = TableServiceFunctionalTestData::getInterestingTableName();
        $created = false;

        // Make sure that the list of all applicable Tables is correctly updated.
        $qto = new QueryTablesOptions();
        if (!$this->isEmulated()) {
            // The emulator has problems with some queries,
            // but full Azure allow this to be more efficient:
            $qto->setPrefix(TableServiceFunctionalTestData::$testUniqueId);
        }
        $qsStart = $this->restProxy->queryTables($qto);

        if (is_null($options)) {
            $this->restProxy->createTable($table);
        } else {
            $this->restProxy->createTable($table, $options);
        }
        $created = true;

        if (is_null($options)) {
            $options = new TableServiceOptions();
        }

        // Make sure that the list of all applicable Tables is correctly updated.
        $qs = $this->restProxy->queryTables($qto);
        if ($created) {
            $this->restProxy->deleteTable($table);
        }

        $this->assertEquals(
            count($qsStart->getTables()) + 1,
            count($qs->getTables()),
            'After adding one, with Prefix=(\'' .
                TableServiceFunctionalTestData::$testUniqueId . '\'), then count(Tables)'
        );
    }

    public function testDeleteTableNoOptions()
    {
        $this->deleteTableWorker(null);
    }

    public function testDeleteTable()
    {
        $options = new TableServiceOptions();
        $this->deleteTableWorker($options);
    }

    private function deleteTableWorker($options)
    {
        $Table = TableServiceFunctionalTestData::getInterestingTableName();

        // Make sure that the list of all applicable Tables is correctly updated.
        $qto = new QueryTablesOptions();
        if (!$this->isEmulated()) {
            // The emulator has problems with some queries,
            // but full Azure allow this to be more efficient:
            $qto->setPrefix(TableServiceFunctionalTestData::$testUniqueId);
        }
        $qsStart = $this->restProxy->queryTables($qto);

        // Make sure there is something to delete.
        $this->restProxy->createTable($Table);

        // Make sure that the list of all applicable Tables is correctly updated.
        $qs = $this->restProxy->queryTables($qto);
        $this->assertEquals(
            count($qsStart->getTables()) + 1,
            count($qs->getTables()),
            'After adding one, with Prefix=(\'' .
                TableServiceFunctionalTestData::$testUniqueId . '\'), then count Tables'
        );

        $deleted = false;
        if (is_null($options)) {
            $this->restProxy->deleteTable($Table);
        } else {
            $this->restProxy->deleteTable($Table, $options);
        }

        $deleted = true;

        if (is_null($options)) {
            $options = new TableServiceOptions();
        }

        // Make sure that the list of all applicable Tables is correctly updated.
        $qs = $this->restProxy->queryTables($qto);

        if (!$deleted) {
            $this->println('Test didn\'t delete the $Table, so try again more simply');
            // Try again. If it doesn't work, not much else to try.
            $this->restProxy->deleteTable($Table);
        }

        $this->assertEquals(
            count($qsStart->getTables()),
            count($qs->getTables()),
            'After adding then deleting one, with Prefix=(\'' .
                TableServiceFunctionalTestData::$testUniqueId . '\'), then count(Tables)'
        );
    }

    public function testGetTableNoOptions()
    {
        $this->getTableWorker(null);
    }

    public function testGetTable()
    {
        $options = new GetTableOptions();
        $this->getTableWorker($options);
    }

    private function getTableWorker($options)
    {
        $table = TableServiceFunctionalTestData::getInterestingTableName();
        $created = false;

        $this->restProxy->createTable($table);
        $created = true;

        $ret = (is_null($options) ? $this->restProxy->getTable($table) : $this->restProxy->getTable($table, $options));

        if (is_null($options)) {
            $options = new GetTableOptions();
        }

        $this->verifygetTableWorker($ret, $table);

        if ($created) {
            $this->restProxy->deleteTable($table);
        }
    }

    private function verifygetTableWorker($ret, $tableName)
    {
        $this->assertNotNull($ret, 'getTableEntry');
        $this->assertEquals($tableName, $ret->getName(), 'getTableEntry->Name');
    }

    public function testGetEntity()
    {
        $ents = TableServiceFunctionalTestData::getInterestingEntities();
        foreach ($ents as $ent) {
            $options = new GetEntityOptions();
            $this->getEntityWorker($ent, true, $options);
        }
    }

    private function getEntityWorker($ent, $isGood, $options)
    {
        $table = $this->getCleanTable();
        try {
            // Upload the entity.
            $this->restProxy->insertEntity($table, $ent);
            $qer = (is_null($options) ?
                $this->restProxy->getEntity(
                    $table,
                    $ent->getPartitionKey(),
                    $ent->getRowKey()
                ) :
                $this->restProxy->getEntity(
                    $table,
                    $ent->getPartitionKey(),
                    $ent->getRowKey(),
                    $options
                )
            );

            if (is_null($options)) {
                $options = new GetEntityOptions();
            }

            $this->assertNotNull($qer->getEntity(), 'getEntity()');
            $this->verifygetEntityWorker($ent, $qer->getEntity());
        } catch (ServiceException $e) {
            if (!$isGood) {
                $this->assertEquals(TestResources::STATUS_BAD_REQUEST, $e->getCode(), 'getCode');
            } elseif (is_null($ent->getPartitionKey()) || is_null($ent->getRowKey())) {
                $this->assertEquals(TestResources::STATUS_BAD_REQUEST, $e->getCode(), 'getCode');
            } else {
                throw $e;
            }
        }
        $this->clearTable($table);
    }

    private function verifygetEntityWorker($ent, $entReturned)
    {
        $expectedProps = array();
        foreach ($ent->getProperties() as $pname => $actualProp) {
            if (is_null($actualProp) || !is_null($actualProp->getValue())) {
                $cloneProp = null;
                if (!is_null($actualProp)) {
                    $cloneProp = new Property();
                    $cloneProp->setEdmType($actualProp->getEdmType());
                    $cloneProp->setValue($actualProp->getValue());
                }
                $expectedProps[$pname] = $cloneProp;
            }
        }

        // Compare the entities to make sure they match.
        $this->assertEquals($ent->getPartitionKey(), $entReturned->getPartitionKey(), 'getPartitionKey');
        $this->assertEquals($ent->getRowKey(), $entReturned->getRowKey(), 'getRowKey');
        $this->assertNotNull($entReturned->getETag(), 'getETag');
        if (!is_null($ent->getETag())) {
            $this->assertEquals($ent->getETag(), $entReturned->getETag(), 'getETag');
        }
        $this->assertNotNull($entReturned->getTimestamp(), 'getTimestamp');
        if (is_null($ent->getTimestamp())) {
            // This property will come back, so need to account for it.
            $expectedProps['Timestamp'] = null;
        } else {
            $this->assertEquals($ent->getTimestamp(), $entReturned->getTimestamp(), 'getTimestamp');
        }
        $this->assertNotNull($ent->getProperties(), 'getProperties');

        $nullCount = 0;
        foreach ($entReturned->getProperties() as $pname => $actualProp) {
            if (is_null($actualProp->getValue())) {
                $nullCount++;
            }
        }

        // Need to skip null values from the count.
        $this->assertEquals(
            count($expectedProps) + $nullCount,
            count($entReturned->getProperties()),
            'getProperties()'
        );

        foreach ($entReturned->getProperties() as $pname => $actualProp) {
            $this->println($actualProp->getEdmType() . ':' . (is_null($actualProp->getValue()) ? 'NULL' :
                ($actualProp->getValue() instanceof \DateTime ? "date" : $actualProp->getValue())));
        }

        foreach ($entReturned->getProperties() as $pname => $actualProp) {
            $expectedProp = Utilities::tryGetValue($expectedProps, $pname, null);
            $this->assertNotNull($actualProp, 'getProperties[\'' . $pname . '\']');
            if (!is_null($expectedProp)) {
                $this->compareProperties($pname, $actualProp, $expectedProp);
            }

            $this->assertEquals(
                $entReturned->getProperty($pname),
                $actualProp,
                'getProperty(\'' . $pname . '\')'
            );
            $this->assertEquals(
                $entReturned->getPropertyValue($pname),
                $actualProp->getValue(),
                'getPropertyValue(\'' . $pname . '\')'
            );
        }
    }

    public function testDeleteEntity()
    {
        $ents = TableServiceFunctionalTestData::getSimpleEntities(3);
        for ($useETag = 0; $useETag <= 2; $useETag++) {
            foreach ($ents as $ent) {
                $options = new DeleteEntityOptions();
                $this->deleteEntityWorker($ent, $useETag, $options);
            }
        }
    }

    private function deleteEntityWorker($ent, $useETag, $options)
    {
        $table = $this->getCleanTable();
        try {
            // Upload the entity.
            $ier = $this->restProxy->insertEntity($table, $ent);
            if ($useETag == 1) {
                $options->setETag($ier->getEntity()->getETag());
            } elseif ($useETag == 2) {
                $options->setETag('W/"datetime\'2012-03-05T21%3A46%3A25->5385467Z\'"');
            }

            $this->restProxy->deleteEntity($table, $ent->getPartitionKey(), $ent->getRowKey(), $options);

            if ($useETag == 2) {
                $this->fail('Expect bad etag throws');
            }

            // Check that the entity really is gone

            $gotError = false;
            try {
                $this->restProxy->getEntity($table, $ent->getPartitionKey(), $ent->getRowKey());
            } catch (ServiceException $e2) {
                $gotError = ($e2->getCode() == TestResources::STATUS_NOT_FOUND);
            }
            $this->assertTrue($gotError, 'Expect error when entity is deleted');
        } catch (ServiceException $e) {
            if ($useETag == 2) {
                $this->assertEquals(TestResources::STATUS_PRECONDITION_FAILED, $e->getCode(), 'getCode');
            } else {
                throw $e;
            }
        }
        $this->clearTable($table);
    }

    public function testInsertEntity()
    {
        $ents = TableServiceFunctionalTestData::getInterestingEntities();
        foreach ($ents as $ent) {
            $options = new TableServiceCreateOptions();
            $this->insertEntityWorker($ent, true, $options);
        }
    }

    public function testInsertBadEntity()
    {
        $ents = TableServiceFunctionalTestData::getInterestingBadEntities();
        foreach ($ents as $ent) {
            $options = new TableServiceCreateOptions();
            try {
                $this->insertEntityWorker($ent, true, $options);
                $this->fail('this call should fail');
            } catch (\InvalidArgumentException $e) {
                $this->assertEquals(0, $e->getCode(), 'getCode');
                $this->assertTrue(true, 'got expected exception');
            }
        }
    }

    public function testInsertEntityBoolean()
    {
        foreach (TableServiceFunctionalTestData::getInterestingGoodBooleans() as $o) {
            $ent = new Entity();
            $ent->setPartitionKey(TableServiceFunctionalTestData::getNewKey());
            $ent->setRowKey(TableServiceFunctionalTestData::getNewKey());
            $ent->addProperty('BOOLEAN', EdmType::BOOLEAN, $o);
            $this->insertEntityWorker($ent, true, null, $o);
        }
    }

    public function testInsertEntityDate()
    {
        foreach (TableServiceFunctionalTestData::getInterestingGoodDates() as $o) {
            $ent = new Entity();
            $ent->setPartitionKey(TableServiceFunctionalTestData::getNewKey());
            $ent->setRowKey(TableServiceFunctionalTestData::getNewKey());
            $ent->addProperty('DATETIME', EdmType::DATETIME, $o);
            $this->insertEntityWorker($ent, true, null, $o);
        }
    }

    public function testInsertEntityDateNegative()
    {
        foreach (TableServiceFunctionalTestData::getInterestingBadDates() as $o) {
            $ent = new Entity();
            $ent->setPartitionKey(TableServiceFunctionalTestData::getNewKey());
            $ent->setRowKey(TableServiceFunctionalTestData::getNewKey());
            try {
                $ent->addProperty('DATETIME', EdmType::DATETIME, $o);
                $this->fail('Should get an exception when trying to parse this value');
                $this->insertEntityWorker($ent, false, null, $o);
            } catch (\Exception $e) {
                $this->assertEquals(0, $e->getCode(), 'getCode');
                $this->assertTrue(true, 'got expected exception');
            }
        }
    }

    public function testInsertEntityDouble()
    {
        foreach (TableServiceFunctionalTestData::getInterestingGoodDoubles() as $o) {
            $ent = new Entity();
            $ent->setPartitionKey(TableServiceFunctionalTestData::getNewKey());
            $ent->setRowKey(TableServiceFunctionalTestData::getNewKey());
            $ent->addProperty('DOUBLE', EdmType::DOUBLE, $o);
            $this->insertEntityWorker($ent, true, null, $o);
        }
    }

    public function testInsertEntityDoubleNegative()
    {
        foreach (TableServiceFunctionalTestData::getInterestingBadDoubles() as $o) {
            $ent = new Entity();
            $ent->setPartitionKey(TableServiceFunctionalTestData::getNewKey());
            $ent->setRowKey(TableServiceFunctionalTestData::getNewKey());
            try {
                $ent->addProperty('DOUBLE', EdmType::DOUBLE, $o);
                $this->fail('Should get an exception when trying to parse this value');
                $this->insertEntityWorker($ent, false, null, $o);
            } catch (\Exception $e) {
                $this->assertEquals(0, $e->getCode(), 'getCode');
                $this->assertTrue(true, 'got expected exception');
            }
        }
    }

    public function testInsertEntityGuid()
    {
        foreach (TableServiceFunctionalTestData::getInterestingGoodGuids() as $o) {
            $ent = new Entity();
            $ent->setPartitionKey(TableServiceFunctionalTestData::getNewKey());
            $ent->setRowKey(TableServiceFunctionalTestData::getNewKey());
            $ent->addProperty('GUID', EdmType::GUID, $o);
            $this->insertEntityWorker($ent, true, null, $o);
        }
    }

    public function testInsertEntityGuidNegative()
    {
        foreach (TableServiceFunctionalTestData::getInterestingBadGuids() as $o) {
            $ent = new Entity();
            $ent->setPartitionKey(TableServiceFunctionalTestData::getNewKey());
            $ent->setRowKey(TableServiceFunctionalTestData::getNewKey());
            try {
                $ent->addProperty('GUID', EdmType::GUID, $o);
                $this->fail('Should get an exception when trying to parse this value');
                $this->insertEntityWorker($ent, false, null, $o);
            } catch (\Exception $e) {
                $this->assertEquals(0, $e->getCode(), 'getCode');
                $this->assertTrue(true, 'got expected exception');
            }
        }
    }

    public function testInsertEntityInt()
    {
        foreach (TableServiceFunctionalTestData::getInterestingGoodInts() as $o) {
            $ent = new Entity();
            $ent->setPartitionKey(TableServiceFunctionalTestData::getNewKey());
            $ent->setRowKey(TableServiceFunctionalTestData::getNewKey());
            $ent->addProperty('INT32', EdmType::INT32, $o);
            $this->insertEntityWorker($ent, true, null, $o);
        }
    }

    public function testInsertEntityIntNegative()
    {
        foreach (TableServiceFunctionalTestData::getInterestingBadInts() as $o) {
            $ent = new Entity();
            $ent->setPartitionKey(TableServiceFunctionalTestData::getNewKey());
            $ent->setRowKey(TableServiceFunctionalTestData::getNewKey());
            try {
                $ent->addProperty('INT32', EdmType::INT32, $o);
                $this->fail('Should get an exception when trying to parse this value');
                $this->insertEntityWorker($ent, false, null, $o);
            } catch (\Exception $e) {
                $this->assertEquals(0, $e->getCode(), 'getCode');
                $this->assertTrue(true, 'got expected exception');
            }
        }
    }

    public function testInsertEntityLong()
    {
        foreach (TableServiceFunctionalTestData::getInterestingGoodLongs() as $o) {
            $ent = new Entity();
            $ent->setPartitionKey(TableServiceFunctionalTestData::getNewKey());
            $ent->setRowKey(TableServiceFunctionalTestData::getNewKey());
            $ent->addProperty('INT64', EdmType::INT64, $o);
            $this->insertEntityWorker($ent, true, null, $o);
        }
    }

    public function testInsertEntityLongNegative()
    {
        foreach (TableServiceFunctionalTestData::getInterestingBadLongs() as $o) {
            $ent = new Entity();
            $ent->setPartitionKey(TableServiceFunctionalTestData::getNewKey());
            $ent->setRowKey(TableServiceFunctionalTestData::getNewKey());
            try {
                $ent->addProperty('INT64', EdmType::INT64, $o);
                $this->fail('Should get an exception when trying to parse this value');
                $this->insertEntityWorker($ent, false, null, $o);
            } catch (\Exception $e) {
                $this->assertEquals(0, $e->getCode(), 'getCode');
                $this->assertTrue(true, 'got expected exception');
            }
        }
    }

    public function testInsertEntityBinary()
    {
        foreach (TableServiceFunctionalTestData::getInterestingGoodBinaries() as $o) {
            $ent = new Entity();
            $ent->setPartitionKey(TableServiceFunctionalTestData::getNewKey());
            $ent->setRowKey(TableServiceFunctionalTestData::getNewKey());
            $ent->addProperty('BINARY', EdmType::BINARY, $o);
            $this->insertEntityWorker($ent, true, null, $o);
        }
    }

    public function testInsertEntityBinaryNegative()
    {
        foreach (TableServiceFunctionalTestData::getInterestingBadBinaries() as $o) {
            $ent = new Entity();
            $ent->setPartitionKey(TableServiceFunctionalTestData::getNewKey());
            $ent->setRowKey(TableServiceFunctionalTestData::getNewKey());
            try {
                $ent->addProperty('BINARY', EdmType::BINARY, $o);
                $this->fail('Should get an exception when trying to parse this value');
                $this->insertEntityWorker($ent, false, null, $o);
            } catch (\Exception $e) {
                $this->assertEquals(0, $e->getCode(), 'getCode');
                $this->assertTrue(true, 'got expected exception');
            }
        }
    }

    public function testInsertEntityString()
    {
        foreach (TableServiceFunctionalTestData::getInterestingGoodStrings() as $o) {
            $ent = new Entity();
            $ent->setPartitionKey(TableServiceFunctionalTestData::getNewKey());
            $ent->setRowKey(TableServiceFunctionalTestData::getNewKey());
            $ent->addProperty('STRING', EdmType::STRING, $o);
            $this->insertEntityWorker($ent, true, null, $o);
        }
    }

    private function insertEntityWorker($ent, $isGood, $options, $specialValue = null)
    {
        $table = $this->getCleanTable();
        try {
            $ret = (is_null($options) ?
                $this->restProxy->insertEntity($table, $ent) :
                $this->restProxy->insertEntity($table, $ent, $options));

            if (is_null($options)) {
                $options = new TableServiceCreateOptions();
            }

            // Check that the message matches
            $this->assertNotNull($ret->getEntity(), 'getEntity()');
            $this->verifyinsertEntityWorker($ent, $ret->getEntity());

            if (is_null($ent->getPartitionKey()) || is_null($ent->getRowKey())) {
                $this->fail('Expect missing keys throw');
            }

            if (!$isGood) {
                $this->fail('Expect bad values to throw: ' . self::tmptostring($specialValue));
            }

            // Check that the message matches
            $qer = $this->restProxy->queryEntities($table);
            $this->assertNotNull($qer->getEntities(), 'getEntities()');
            $this->assertEquals(1, count($qer->getEntities()), 'getEntities() count');
            $entReturned = $qer->getEntities();
            $entReturned = $entReturned[0];
            $this->assertNotNull($entReturned, 'getEntities()[0]');

            $this->verifyinsertEntityWorker($ent, $entReturned);
        } catch (ServiceException $e) {
            if (!$isGood) {
                $this->assertEquals(TestResources::STATUS_BAD_REQUEST, $e->getCode(), 'getCode');
            } elseif (is_null($ent->getPartitionKey()) || is_null($ent->getRowKey())) {
                $this->assertEquals(TestResources::STATUS_BAD_REQUEST, $e->getCode(), 'getCode');
            } else {
                throw $e;
            }
        }
        $this->clearTable($table);
    }

    public function testUpdateEntity()
    {
        $ents = TableServiceFunctionalTestData::getSimpleEntities(2);
        foreach (MutatePivot::values() as $mutatePivot) {
            foreach ($ents as $initialEnt) {
                $options = new TableServiceOptions();
                $ent = TableServiceFunctionalTestUtils::cloneEntity($initialEnt);
                TableServiceFunctionalTestUtils::mutateEntity($ent, $mutatePivot);
                $this->updateEntityWorker($initialEnt, $ent, $options);
            }
        }
    }

    private function updateEntityWorker($initialEnt, $ent, $options)
    {
        $table = $this->getCleanTable();

        // Upload the entity.
        $this->restProxy->insertEntity($table, $initialEnt);

        if (is_null($options)) {
            $this->restProxy->updateEntity($table, $ent);
        } else {
            $this->restProxy->updateEntity($table, $ent, $options);
        }

        if (is_null($options)) {
            $options = new TableServiceOptions();
        }

        // Check that the message matches
        $qer = $this->restProxy->queryEntities($table);
        $this->assertNotNull($qer->getEntities(), 'getEntities()');
        $this->assertEquals(1, count($qer->getEntities()), 'getEntities()');
        $entReturned = $qer->getEntities();
        $entReturned = $entReturned[0];
        $this->assertNotNull($entReturned, 'getEntities()[0]');
        $this->verifyinsertEntityWorker($ent, $entReturned);
        $this->clearTable($table);
    }

    public function testMergeEntity()
    {
        $ents = TableServiceFunctionalTestData::getSimpleEntities(2);
        foreach (MutatePivot::values() as $mutatePivot) {
            foreach ($ents as $initialEnt) {
                $options = new TableServiceOptions();
                $ent = TableServiceFunctionalTestUtils::cloneEntity($initialEnt);
                TableServiceFunctionalTestUtils::mutateEntity($ent, $mutatePivot);
                $this->mergeEntityWorker($initialEnt, $ent, $options);
            }
        }
    }

    private function mergeEntityWorker($initialEnt, $ent, $options)
    {
        $table = $this->getCleanTable();

        // Upload the entity.
        $this->restProxy->insertEntity($table, $initialEnt);

        if (is_null($options)) {
            $this->restProxy->mergeEntity($table, $ent);
        } else {
            $this->restProxy->mergeEntity($table, $ent, $options);
        }

        if (is_null($options)) {
            $options = new TableServiceOptions();
        }

        // Check that the message matches
        $qer = $this->restProxy->queryEntities($table);
        $this->assertNotNull($qer->getEntities(), 'getEntities()');
        $this->assertEquals(1, count($qer->getEntities()), 'getEntities() count');
        $entReturned = $qer->getEntities();
        $entReturned = $entReturned[0];
        $this->assertNotNull($entReturned, 'getEntities()[0]');

        $this->verifymergeEntityWorker($initialEnt, $ent, $entReturned);
        $this->clearTable($table);
    }

    public function testInsertOrReplaceEntity()
    {
        $ents = TableServiceFunctionalTestData::getSimpleEntities(2);
        foreach (MutatePivot::values() as $mutatePivot) {
            foreach ($ents as $initialEnt) {
                $options = new TableServiceOptions();
                $ent = TableServiceFunctionalTestUtils::cloneEntity($initialEnt);
                TableServiceFunctionalTestUtils::mutateEntity($ent, $mutatePivot);
                try {
                    $this->insertOrReplaceEntityWorker($initialEnt, $ent, $options);
                    $this->assertFalse($this->isEmulated(), 'Should succeed when not running in emulator');
                } catch (ServiceException $e) {
                    // Expect failure in emulator, as v1.6 doesn't support this method
                    if ($this->isEmulated()) {
                        $this->assertEquals(TestResources::STATUS_BAD_REQUEST, $e->getCode(), 'getCode');
                    } else {
                        throw $e;
                    }
                }
            }
        }
    }

    private function insertOrReplaceEntityWorker($initialEnt, $ent, $options)
    {
        $table = $this->getCleanTable();

        // Upload the entity.
        $this->restProxy->insertEntity($table, $initialEnt);
        if (is_null($options)) {
            $this->restProxy->insertOrReplaceEntity($table, $ent);
        } else {
            $this->restProxy->insertOrReplaceEntity($table, $ent, $options);
        }

        if (is_null($options)) {
            $options = new TableServiceOptions();
        }

        // Check that the message matches
        $qer = $this->restProxy->queryEntities($table);
        $this->assertNotNull($qer->getEntities(), 'getEntities()');
        $this->assertEquals(1, count($qer->getEntities()), 'getEntities() count');
        $entReturned = $qer->getEntities();
        $entReturned = $entReturned[0];
        $this->assertNotNull($entReturned, 'getEntities()[0]');

        $this->verifyinsertEntityWorker($ent, $entReturned);
        $this->clearTable($table);
    }

    public function testInsertOrMergeEntity()
    {
        $ents = TableServiceFunctionalTestData::getSimpleEntities(2);
        foreach (MutatePivot::values() as $mutatePivot) {
            foreach ($ents as $initialEnt) {
                $options = new TableServiceOptions();
                $ent = TableServiceFunctionalTestUtils::cloneEntity($initialEnt);
                TableServiceFunctionalTestUtils::mutateEntity($ent, $mutatePivot);
                try {
                    $this->insertOrMergeEntityWorker($initialEnt, $ent, $options);
                    $this->assertFalse($this->isEmulated(), 'Should succeed when not running in emulator');
                } catch (ServiceException $e) {
                    // Expect failure in emulator, as v1.6 doesn't support this method
                    if ($this->isEmulated()) {
                        $this->assertEquals(TestResources::STATUS_BAD_REQUEST, $e->getCode(), 'getCode');
                    } else {
                        throw $e;
                    }
                }
            }
        }
    }

    private function insertOrMergeEntityWorker($initialEnt, $ent, $options)
    {
        $table = $this->getCleanTable();

        // Upload the entity.
        $this->restProxy->insertEntity($table, $initialEnt);

        if (is_null($options)) {
            $this->restProxy->insertOrMergeEntity($table, $ent);
        } else {
            $this->restProxy->insertOrMergeEntity($table, $ent, $options);
        }

        if (is_null($options)) {
            $options = new TableServiceOptions();
        }

        // Check that the message matches
        $qer = $this->restProxy->queryEntities($table);
        $this->assertNotNull($qer->getEntities(), 'getEntities()');
        $this->assertEquals(1, count($qer->getEntities()), 'getEntities() count');
        $entReturned = $qer->getEntities();
        $entReturned = $entReturned[0];
        $this->assertNotNull($entReturned, 'getEntities()[0]');

        $this->verifymergeEntityWorker($initialEnt, $ent, $entReturned);
        $this->clearTable($table);
    }

    public function testCRUDdeleteEntity()
    {
        foreach (ConcurType::values() as $concurType) {
            foreach (MutatePivot::values() as $mutatePivot) {
                for ($i = 0; $i <= 1; $i++) {
                    foreach (TableServiceFunctionalTestData::getSimpleEntities(2) as $ent) {
                        $options = ($i == 0 ? null : new TableServiceOptions());
                        $this->crudWorker(OpType::DELETE_ENTITY, $concurType, $mutatePivot, $ent, $options);
                    }
                }
            }
        }
    }

    public function testCRUDinsertEntity()
    {
        foreach (ConcurType::values() as $concurType) {
            foreach (MutatePivot::values() as $mutatePivot) {
                for ($i = 0; $i <= 1; $i++) {
                    foreach (TableServiceFunctionalTestData::getSimpleEntities(2) as $ent) {
                        $options = ($i == 0 ? null : new TableServiceCreateOptions());
                        $this->crudWorker(OpType::INSERT_ENTITY, $concurType, $mutatePivot, $ent, $options);
                    }
                }
            }
        }
    }

    public function testCRUDinsertOrMergeEntity()
    {
        $this->skipIfEmulated();

        foreach (ConcurType::values() as $concurType) {
            foreach (MutatePivot::values() as $mutatePivot) {
                for ($i = 0; $i <= 1; $i++) {
                    foreach (TableServiceFunctionalTestData::getSimpleEntities(2) as $ent) {
                        $options = ($i == 0 ? null : new TableServiceOptions());
                        $this->crudWorker(OpType::INSERT_OR_MERGE_ENTITY, $concurType, $mutatePivot, $ent, $options);
                    }
                }
            }
        }
    }

    public function testCRUDinsertOrReplaceEntity()
    {
        $this->skipIfEmulated();

        foreach (ConcurType::values() as $concurType) {
            foreach (MutatePivot::values() as $mutatePivot) {
                for ($i = 0; $i <= 1; $i++) {
                    foreach (TableServiceFunctionalTestData::getSimpleEntities(2) as $ent) {
                        $options = ($i == 0 ? null : new TableServiceOptions());
                        $this->crudWorker(OpType::INSERT_OR_REPLACE_ENTITY, $concurType, $mutatePivot, $ent, $options);
                    }
                }
            }
        }
    }

    public function testCRUDmergeEntity()
    {
        foreach (ConcurType::values() as $concurType) {
            foreach (MutatePivot::values() as $mutatePivot) {
                for ($i = 0; $i <= 1; $i++) {
                    foreach (TableServiceFunctionalTestData::getSimpleEntities(2) as $ent) {
                        $options = ($i == 0 ? null : new TableServiceOptions());
                        $this->crudWorker(OpType::MERGE_ENTITY, $concurType, $mutatePivot, $ent, $options);
                    }
                }
            }
        }
    }

    public function testCRUDupdateEntity()
    {
        foreach (ConcurType::values() as $concurType) {
            foreach (MutatePivot::values() as $mutatePivot) {
                for ($i = 0; $i <= 1; $i++) {
                    foreach (TableServiceFunctionalTestData::getSimpleEntities(2) as $ent) {
                        $options = ($i == 0 ? null : new TableServiceOptions());
                        $this->crudWorker(OpType::UPDATE_ENTITY, $concurType, $mutatePivot, $ent, $options);
                    }
                }
            }
        }
    }

    private function crudWorker($opType, $concurType, $mutatePivot, $ent, $options)
    {
        $exptErr = $this->expectConcurrencyFailure($opType, $concurType);
        $table = $this->getCleanTable();

        try {
            // Upload the entity.
            $initial = $this->restProxy->insertEntity($table, $ent);
            $targetEnt = $this->createTargetEntity($table, $initial->getEntity(), $concurType, $mutatePivot);

            $this->executeCrudMethod($table, $targetEnt, $opType, $concurType, $options);

            if (!is_null($exptErr)) {
                $this->fail(
                    'Expected a failure when opType=' . $opType .
                        ' and concurType=' . $concurType . ' :' .
                        $this->expectConcurrencyFailure($opType, $concurType)
                );
            }

            $this->verifyCrudWorker($opType, $table, $ent, $targetEnt, true);
        } catch (ServiceException $e) {
            if (!is_null($exptErr)) {
                $this->assertEquals($exptErr, $e->getCode(), 'getCode');
            } else {
                throw $e;
            }
        }
        $this->clearTable($table);
    }

    public function testBatchPositiveFirstNoKeyMatch()
    {
        $this->batchPositiveOuter(ConcurType::NO_KEY_MATCH, 123);
    }

    public function testBatchPositiveFirstKeyMatchNoETag()
    {
        $this->batchPositiveOuter(ConcurType::KEY_MATCH_NO_ETAG, 234);
    }

    public function testBatchPositiveFirstKeyMatchETagMismatch()
    {
        $this->skipIfEmulated();
        $this->batchPositiveOuter(ConcurType::KEY_MATCH_ETAG_MISMATCH, 345);
    }

    public function testBatchPositiveFirstKeyMatchETagMatch()
    {
        $this->batchPositiveOuter(ConcurType::KEY_MATCH_ETAG_MATCH, 456);
    }

    public function testBatchNegative()
    {
        $this->skipIfEmulated();

        // The random here is not to generate random values, but to
        // get a good mix of values in the table entities.

        mt_srand(456);
        $concurTypes = ConcurType::values();
        $mutatePivots = MutatePivot::values();
        $opTypes = OpType::values();

        for ($j = 0; $j < 10; $j++) {
            $configs = array();
            foreach (TableServiceFunctionalTestData::getSimpleEntities(6) as $ent) {
                $config = new BatchWorkerConfig();
                $config->concurType = $concurTypes[mt_rand(0, count($concurTypes) -1)];
                $config->opType = $opTypes[mt_rand(0, count($opTypes) -1)];
                $config->mutatePivot = $mutatePivots[mt_rand(0, count($mutatePivots) -1)];
                $config->ent = $ent;
                array_push($configs, $config);
            }

            for ($i = 0; $i <= 1; $i++) {
                $options = ($i == 0 ? null : new TableServiceOptions());
                $this->batchWorker($configs, $options);
            }
        }
    }

    private function verifyinsertEntityWorker($ent, $entReturned)
    {
        $this->verifyinsertOrMergeEntityWorker(null, $ent, $entReturned);
    }

    private function verifymergeEntityWorker($intitalEnt, $ent, $entReturned)
    {
        $this->verifyinsertOrMergeEntityWorker($intitalEnt, $ent, $entReturned);
    }

    private function verifyinsertOrMergeEntityWorker($initialEnt, $ent, $entReturned)
    {
        $expectedProps = array();
        if (!is_null($initialEnt) &&
            $initialEnt->getPartitionKey() == $ent->getPartitionKey() &&
            $initialEnt->getRowKey() == $ent->getRowKey()) {
            foreach ($initialEnt->getProperties() as $pname => $actualProp) {
                if (!is_null($actualProp) && !is_null($actualProp->getValue())) {
                    $cloneProp = null;
                    if (!is_null($actualProp)) {
                        $cloneProp = new Property();
                        $cloneProp->setEdmType($actualProp->getEdmType());
                        $cloneProp->setValue($actualProp->getValue());
                    }
                    $expectedProps[$pname] = $cloneProp;
                }
            }
        }
        foreach ($ent->getProperties() as $pname => $actualProp) {
            // Any properties with null values are ignored by the Merge Entity operation.
            // All other properties will be updated.
            if (!is_null($actualProp) && !is_null($actualProp->getValue())) {
                $cloneProp = new Property();
                $cloneProp->setEdmType($actualProp->getEdmType());
                $cloneProp->setValue($actualProp->getValue());
                $expectedProps[$pname] = $cloneProp;
            }
        }

        $effectiveProps = array();
        foreach ($entReturned->getProperties() as $pname => $actualProp) {
            // This is to work with Dev Storage, which returns items for all
            // columns, null valued or not.
            if (!is_null($actualProp) && !is_null($actualProp->getValue())) {
                $cloneProp = new Property();
                $cloneProp->setEdmType($actualProp->getEdmType());
                $cloneProp->setValue($actualProp->getValue());
                $effectiveProps[$pname] = $cloneProp;
            }
        }

        // Compare the entities to make sure they match.
        $this->assertEquals($ent->getPartitionKey(), $entReturned->getPartitionKey(), 'getPartitionKey');
        $this->assertEquals($ent->getRowKey(), $entReturned->getRowKey(), 'getRowKey');
        if (!is_null($ent->getETag())) {
            $this->assertTrue(
                $ent->getETag() != $entReturned->getETag(),
                'getETag should change after submit: initial \'' .
                    $ent->getETag() . '\', returned \'' . $entReturned->getETag() . '\''
            );
        }
        $this->assertNotNull($entReturned->getTimestamp(), 'getTimestamp');
        if (is_null($ent->getTimestamp())) {
            // This property will come back, so need to account for it.
            $expectedProps['Timestamp'] = null;
        } else {
            $this->assertEquals($ent->getTimestamp(), $entReturned->getTimestamp(), 'getTimestamp');
        }
        $this->assertNotNull($ent->getProperties(), 'getProperties');

        // Need to skip null values from the count.
        $this->assertEquals(count($expectedProps), count($effectiveProps), 'getProperties()');

        foreach ($expectedProps as $pname => $expectedProp) {
            $actualProp = $effectiveProps;
            $actualProp = $actualProp[$pname];

            $this->assertNotNull($actualProp, 'getProperties()[\'' . $pname . '\')');
            if (!is_null($expectedProp)) {
                $this->compareProperties($pname, $actualProp, $expectedProp);
            }

            $this->assertEquals(
                $entReturned->getProperty($pname)->getEdmType(),
                $actualProp->getEdmType(),
                'getProperty(\'' . $pname . '\')'
            );

            $this->assertEquals(
                $entReturned->getPropertyValue($pname),
                $actualProp->getValue(),
                'getPropertyValue(\'' . $pname . '\')'
            );
        }
    }

    private function batchPositiveOuter($firstConcurType, $seed)
    {
        // The random here is not to generate random values, but to
        // get a good mix of values in the table entities.
        mt_srand($seed);
        $concurTypes = ConcurType::values();
        $mutatePivots = MutatePivot::values();
        $opTypes = OpType::values();

        // Main loop.
        foreach ($opTypes as $firstOpType) {
            if (!is_null($this->expectConcurrencyFailure($firstOpType, $firstConcurType))) {
                // Want to know there is at least one part that does not fail.
                continue;
            }
            if ($this->isEmulated() && (
                    ($firstOpType == OpType::INSERT_OR_MERGE_ENTITY) ||
                    ($firstOpType == OpType::INSERT_OR_REPLACE_ENTITY))) {
                // Emulator does not support these operations.
                continue;
            }

            $simpleEntities = TableServiceFunctionalTestData::getSimpleEntities(6);
            $configs = array();
            $firstConfig = new BatchWorkerConfig();
            $firstConfig->concurType = $firstConcurType;
            $firstConfig->opType = $firstOpType;
            $firstConfig->ent = $simpleEntities[0];
            $firstConfig->mutatePivot = $mutatePivots[mt_rand(0, count($mutatePivots) -1)];
            array_push($configs, $firstConfig);

            for ($i = 1; $i < count($simpleEntities); $i++) {
                $config = new BatchWorkerConfig();
                while (!is_null($this->expectConcurrencyFailure($config->opType, $config->concurType))) {
                    $config->concurType = $concurTypes[mt_rand(0, count($concurTypes) -1)];
                    $config->opType = $opTypes[mt_rand(0, count($opTypes) -1)];
                    if ($this->isEmulated()) {
                        if ($config->opType == OpType::INSERT_OR_MERGE_ENTITY) {
                            $config->opType = OpType::MERGE_ENTITY;
                        }
                        if ($config->opType == OpType::INSERT_OR_REPLACE_ENTITY) {
                            $config->opType = OpType::UPDATE_ENTITY;
                        }
                    }
                }
                $config->mutatePivot = $mutatePivots[mt_rand(0, count($mutatePivots) -1)];
                $config->ent = $simpleEntities[$i];
                array_push($configs, $config);
            }

            for ($i = 0; $i <= 1; $i++) {
                $options = ($i == 0 ? null : new TableServiceOptions());
                if ($this->isEmulated()) {
                    // The emulator has trouble with some batches.
                    for ($j = 0; $j < count($configs); $j++) {
                        $tmpconfigs = array();
                        $tmpconfigs[] = $configs[$j];
                        $this->batchWorker($tmpconfigs, $options);
                    }
                } else {
                    $this->batchWorker($configs, $options);
                }
            }
        }
    }

    private function batchWorker($configs, $options)
    {
        $exptErrs = array();
        $expectedReturned = count($configs);
        $expectedError = false;
        $expectedErrorCount = 0;
        for ($i = 0; $i < count($configs); $i++) {
            $err = $this->expectConcurrencyFailure($configs[$i]->opType, $configs[$i]->concurType);
            if (!is_null($err)) {
                $expectedErrorCount++;
                $expectedError = true;
            }
            array_push($exptErrs, $err);
        }

        $table = $this->getCleanTable();

        try {
            // Upload the initial entities and get the target entities.
            $targetEnts = array();
            for ($i = 0; $i < count($configs); $i++) {
                $initial = $this->restProxy->insertEntity($table, $configs[$i]->ent);
                array_push(
                    $targetEnts,
                    $this->createTargetEntity(
                        $table,
                        $initial->getEntity(),
                        $configs[$i]->concurType,
                        $configs[$i]->mutatePivot
                    )
                );
            }

            // Build up the batch.
            $operations = new BatchOperations();
            for ($i = 0; $i < count($configs); $i++) {
                $this->buildBatchOperations(
                    $table,
                    $operations,
                    $targetEnts[$i],
                    $configs[$i]->opType,
                    $configs[$i]->concurType,
                    $configs[$i]->options
                );
            }

            // Verify results.
            if ($expectedError) {
                $exception = null;
                try {
                    // Execute the batch.
                    $ret = (is_null($options) ?
                        $this->restProxy->batch($operations) :
                        $this->restProxy->batch(
                            $operations,
                            $options
                        )
                    );
                } catch (ServiceException $e) {
                    $exception = $e;
                }

                $this->assertNotNull($exception, 'Caught exception should not be null');

                // No changes should have gone through.
                for ($i = 0; $i < count($configs); $i++) {
                    $this->verifyCrudWorker($configs[$i]->opType, $table, $configs[$i]->ent, $configs[$i]->ent, false);
                }
            } else {
                // Execute the batch.
                $ret = (is_null($options) ?
                    $this->restProxy->batch($operations) :
                    $this->restProxy->batch(
                        $operations,
                        $options
                    )
                );

                $this->assertEquals($expectedReturned, count($ret->getEntries()), 'count $ret->getEntries()');
                for ($i = 0; $i < count($ret->getEntries()); $i++) {
                    $opResult = $ret->getEntries()[$i];
                    $this->verifyBatchEntryType($configs[$i]->opType, $exptErrs[$i], $opResult);
                    $this->verifyEntryData($table, $exptErrs[$i], $targetEnts[$i], $opResult);
                    // Check out the entities.
                    $this->verifyCrudWorker($configs[$i]->opType, $table, $configs[$i]->ent, $targetEnts[$i], true);
                }
            }
        } catch (ServiceException $e) {
            if ($expectedError) {
                $this->assertEquals(TestResources::STATUS_BAD_REQUEST, $e->getCode(), 'getCode');
            } else {
                throw $e;
            }
        }

        $this->clearTable($table);
    }

    private function verifyEntryData($table, $exptErr, $targetEnt, $opResult)
    {
        if ($opResult instanceof InsertEntityResult) {
            $this->verifyinsertEntityWorker($targetEnt, $opResult->getEntity());
        } elseif ($opResult instanceof UpdateEntityResult) {
            $ger = $this->restProxy->getEntity($table, $targetEnt->getPartitionKey(), $targetEnt->getRowKey());
            $this->assertEquals($opResult->getETag(), $ger->getEntity()->getETag(), 'op->getETag');
        } elseif (is_string($opResult)) {
            // Nothing special to do.
        } else {
            $this->fail('opResult is of an unknown type');
        }
    }

    private function verifyBatchEntryType($opType, $exptErr, $opResult)
    {
        if (is_null($exptErr)) {
            switch ($opType) {
                case OpType::INSERT_ENTITY:
                    $this->assertTrue(
                        $opResult instanceof InsertEntityResult,
                        'When opType=' . $opType . ' expect opResult instanceof InsertEntityResult'
                    );
                    break;
                case OpType::DELETE_ENTITY:
                    $this->assertTrue(
                        is_string($opResult),
                        'When opType=' . $opType . ' expect opResult is a string'
                    );
                    break;
                case OpType::UPDATE_ENTITY:
                case OpType::INSERT_OR_REPLACE_ENTITY:
                case OpType::MERGE_ENTITY:
                case OpType::INSERT_OR_MERGE_ENTITY:
                    $this->assertTrue(
                        $opResult instanceof UpdateEntityResult,
                        'When opType=' . $opType . ' expect opResult instanceof UpdateEntityResult'
                    );
                    break;
            }
        }
    }

    private function buildBatchOperations($table, $operations, $targetEnt, $opType, $concurType, $options)
    {
        switch ($opType) {
            case OpType::DELETE_ENTITY:
                if (is_null($options) && $concurType != ConcurType::KEY_MATCH_ETAG_MISMATCH) {
                    $operations->addDeleteEntity($table, $targetEnt->getPartitionKey(), $targetEnt->getRowKey(), null);
                } else {
                    $operations->addDeleteEntity(
                        $table,
                        $targetEnt->getPartitionKey(),
                        $targetEnt->getRowKey(),
                        $targetEnt->getETag()
                    );
                }
                break;
            case OpType::INSERT_ENTITY:
                $operations->addInsertEntity($table, $targetEnt);
                break;
            case OpType::INSERT_OR_MERGE_ENTITY:
                $operations->addInsertOrMergeEntity($table, $targetEnt);
                break;
            case OpType::INSERT_OR_REPLACE_ENTITY:
                $operations->addInsertOrReplaceEntity($table, $targetEnt);
                break;
            case OpType::MERGE_ENTITY:
                $operations->addMergeEntity($table, $targetEnt);
                break;
            case OpType::UPDATE_ENTITY:
                $operations->addUpdateEntity($table, $targetEnt);
                break;
        }
    }

    private function executeCrudMethod($table, $targetEnt, $opType, $concurType, $options)
    {
        switch ($opType) {
            case OpType::DELETE_ENTITY:
                if (is_null($options) && $concurType != ConcurType::KEY_MATCH_ETAG_MISMATCH) {
                    $this->restProxy->deleteEntity($table, $targetEnt->getPartitionKey(), $targetEnt->getRowKey());
                } else {
                    $delOptions = new DeleteEntityOptions();
                    $delOptions->setETag($targetEnt->getETag());
                    $this->restProxy->deleteEntity(
                        $table,
                        $targetEnt->getPartitionKey(),
                        $targetEnt->getRowKey(),
                        $delOptions
                    );
                }
                break;
            case OpType::INSERT_ENTITY:
                if (is_null($options)) {
                    $this->restProxy->insertEntity($table, $targetEnt);
                } else {
                    $this->restProxy->insertEntity($table, $targetEnt, $options);
                }
                break;
            case OpType::INSERT_OR_MERGE_ENTITY:
                if (is_null($options)) {
                    $this->restProxy->insertOrMergeEntity($table, $targetEnt);
                } else {
                    $this->restProxy->insertOrMergeEntity($table, $targetEnt, $options);
                }
                break;
            case OpType::INSERT_OR_REPLACE_ENTITY:
                if (is_null($options)) {
                    $this->restProxy->insertOrReplaceEntity($table, $targetEnt);
                } else {
                    $this->restProxy->insertOrReplaceEntity($table, $targetEnt, $options);
                }
                break;
            case OpType::MERGE_ENTITY:
                if (is_null($options)) {
                    $this->restProxy->mergeEntity($table, $targetEnt);
                } else {
                    $this->restProxy->mergeEntity($table, $targetEnt, $options);
                }
                break;
            case OpType::UPDATE_ENTITY:
                if (is_null($options)) {
                    $this->restProxy->updateEntity($table, $targetEnt);
                } else {
                    $this->restProxy->updateEntity($table, $targetEnt, $options);
                }
                break;
        }
    }

    private function verifyCrudWorker($opType, $table, $initialEnt, $targetEnt, $expectedSuccess)
    {
        $entInTable = null;
        try {
            $ger = $this->restProxy->getEntity($table, $targetEnt->getPartitionKey(), $targetEnt->getRowKey());
            $entInTable = $ger->getEntity();
        } catch (ServiceException $e) {
            $this->assertTrue(
                ($opType == OpType::DELETE_ENTITY) &&
                    (TestResources::STATUS_NOT_FOUND == $e->getCode()),
                '404:NotFound is expected for deletes'
            );
        }

        switch ($opType) {
            case OpType::DELETE_ENTITY:
                // Check that the entity really is gone
                if ($expectedSuccess) {
                    $this->assertNull($entInTable, 'Entity from table');
                } else {
                    // Check that the message matches
                    $this->assertNotNull($entInTable, 'Entity from table');
                    $this->verifyinsertEntityWorker($targetEnt, $entInTable);
                }
                break;
            case OpType::INSERT_ENTITY:
                // Check that the message matches
                $this->assertNotNull($entInTable, 'Entity from table');
                $this->verifyinsertEntityWorker($targetEnt, $entInTable);
                break;
            case OpType::INSERT_OR_MERGE_ENTITY:
                $this->assertNotNull($entInTable, 'Entity from table');
                $this->verifymergeEntityWorker($initialEnt, $targetEnt, $entInTable);
                break;
            case OpType::INSERT_OR_REPLACE_ENTITY:
                // Check that the message matches
                $this->assertNotNull($entInTable, 'Entity from table');
                $this->verifyinsertEntityWorker($targetEnt, $entInTable);
                break;
            case OpType::MERGE_ENTITY:
                $this->assertNotNull($entInTable, 'Entity from table');
                $this->verifymergeEntityWorker($initialEnt, $targetEnt, $entInTable);
                break;
            case OpType::UPDATE_ENTITY:
                // Check that the message matches
                $this->assertNotNull($entInTable, 'Entity from table');
                $this->verifyinsertEntityWorker($targetEnt, $entInTable);
                break;
        }
    }

    private function createTargetEntity($table, $initialEnt, $concurType, $mutatePivot)
    {
        $targetEnt = TableServiceFunctionalTestUtils::cloneEntity($initialEnt);

        // Update the entity/table state to get the requested concurrency type error.
        switch ($concurType) {
            case ConcurType::NO_KEY_MATCH:
                // Mutate the keys to not match.
                $targetEnt->setRowKey(TableServiceFunctionalTestData::getNewKey());
                break;
            case ConcurType::KEY_MATCH_NO_ETAG:
                $targetEnt->setETag(null);
                break;
            case ConcurType::KEY_MATCH_ETAG_MISMATCH:
                $newETag =  $this->restProxy->updateEntity($table, $initialEnt)->getETag();
                $initialEnt->setETag($newETag);
                // Now the $targetEnt ETag will not match.
                $this->assertTrue(
                    $targetEnt->getETag() != $initialEnt->getETag(),
                    'targetEnt->ETag(\'' . $targetEnt->getETag() .
                        '\') !=  updated->ETag(\'' . $initialEnt->getETag() . '\')'
                );

                break;
            case ConcurType::KEY_MATCH_ETAG_MATCH:
                // Don't worry here.
                break;
        }

        // Mutate the properties.
        TableServiceFunctionalTestUtils::mutateEntity($targetEnt, $mutatePivot);
        return $targetEnt;
    }

    private static function expectConcurrencyFailure($opType, $concurType)
    {
        if (is_null($concurType) || is_null($opType)) {
            return -1;
        }

        switch ($concurType) {
            case ConcurType::NO_KEY_MATCH:
                if (($opType == OpType::DELETE_ENTITY) ||
                    ($opType == OpType::MERGE_ENTITY) ||
                    ($opType == OpType::UPDATE_ENTITY)) {
                    return TestResources::STATUS_NOT_FOUND;
                }
                break;
            case ConcurType::KEY_MATCH_NO_ETAG:
                if ($opType == OpType::INSERT_ENTITY) {
                    return TestResources::STATUS_CONFLICT;
                }
                break;
            case ConcurType::KEY_MATCH_ETAG_MATCH:
                if ($opType == OpType::INSERT_ENTITY) {
                    return TestResources::STATUS_CONFLICT;
                }
                break;
            case ConcurType::KEY_MATCH_ETAG_MISMATCH:
                if ($opType == OpType::INSERT_ENTITY) {
                    return TestResources::STATUS_CONFLICT;
                } elseif ($opType == OpType::INSERT_OR_REPLACE_ENTITY || $opType == OpType::INSERT_OR_MERGE_ENTITY) {
                    // If exists, just clobber.
                    return null;
                }
                return TestResources::STATUS_PRECONDITION_FAILED;
        }
        return null;
    }

    public function compareProperties($pname, $actualProp, $expectedProp)
    {
        $effectiveExpectedProp = (is_null($expectedProp->getEdmType()) ? EdmType::STRING : $expectedProp->getEdmType());
        $effectiveActualProp = (is_null($expectedProp->getEdmType()) ? EdmType::STRING : $expectedProp->getEdmType());

        $this->assertEquals(
            $effectiveExpectedProp,
            $effectiveActualProp,
            'getProperties()->get(\'' . $pname . '\')->getEdmType'
        );

        $effExp = $expectedProp->getValue();
        $effAct = $actualProp->getValue();

        if ($effExp instanceof \DateTime) {
            $effExp = $effExp->setTimezone(new \DateTimeZone('UTC'));
        }
        if ($effAct instanceof \DateTime) {
            $effAct = $effAct->setTimezone(new \DateTimeZone('UTC'));
        }

        $this->assertEquals(
            $expectedProp->getValue(),
            $actualProp->getValue(),
            'getProperties()->get(\'' . $pname . '\')->getValue [' . $effectiveExpectedProp . ']'
        );
    }

    public function testMiddlewares()
    {
        //setup middlewares.
        $historyMiddleware = new HistoryMiddleware();
        $retryMiddleware = RetryMiddlewareFactory::create(
            RetryMiddlewareFactory::GENERAL_RETRY_TYPE,
            3,
            1
        );

        //setup options for the first try.
        $options = new QueryTablesOptions();
        $options->setMiddlewares([$historyMiddleware]);
        //get the response of the server.
        $result = $this->restProxy->queryTables($options);
        $response = $historyMiddleware->getHistory()[0]['response'];
        $request = $historyMiddleware->getHistory()[0]['request'];

        //setup the mock handler
        $mock = MockHandler::createWithMiddleware([
            new RequestException(
                'mock 408 exception',
                $request,
                new Response(408, ['test_header' => 'test_header_value'])
            ),
            new Response(500, ['test_header' => 'test_header_value']),
            $response
        ]);
        $restOptions = ['http' => ['handler' => $mock]];
        $mockProxy = TableRestProxy::createTableService($this->connectionString, $restOptions);
        //test using mock handler.
        $options = new QueryTablesOptions();
        $options->setMiddlewares([$retryMiddleware, $historyMiddleware]);
        $newResult = $mockProxy->queryTables($options);
        $this->assertTrue(
            $result == $newResult,
            'Mock result does not match server behavior'
        );
        $this->assertTrue(
            $historyMiddleware->getHistory()[1]['reason']->getMessage() == 'mock 408 exception',
            'Mock handler does not gave the first 408 exception correctly'
        );
        $this->assertTrue(
            $historyMiddleware->getHistory()[2]['reason']->getCode() == 500,
            'Mock handler does not gave the second 500 response correctly'
        );
    }

    public function testRetryFromSecondary()
    {
        //setup middlewares.
        $historyMiddleware = new HistoryMiddleware();
        $retryMiddleware = RetryMiddlewareFactory::create(
            RetryMiddlewareFactory::GENERAL_RETRY_TYPE,
            3,
            1
        );

        //setup options for the first try.
        $options = new QueryTablesOptions();
        $options->setMiddlewares([$historyMiddleware]);
        //get the response of the server.
        $result = $this->restProxy->queryTables($options);
        $response = $historyMiddleware->getHistory()[0]['response'];
        $request = $historyMiddleware->getHistory()[0]['request'];

        //setup the mock handler
        $mock = MockHandler::createWithMiddleware([
            new Response(500, ['test_header' => 'test_header_value']),
            new RequestException(
                'mock 404 exception',
                $request,
                new Response(404, ['test_header' => 'test_header_value'])
            ),
            $response
        ]);
        $restOptions = ['http' => ['handler' => $mock]];
        $mockProxy = TableRestProxy::createTableService($this->connectionString, $restOptions);
        //test using mock handler.
        $options = new QueryTablesOptions();
        $options->setMiddlewares([$retryMiddleware, $historyMiddleware]);
        $options->setLocationMode(LocationMode::PRIMARY_THEN_SECONDARY);
        $newResult = $mockProxy->queryTables($options);
        $this->assertTrue(
            $result == $newResult,
            'Mock result does not match server behavior'
        );
        $this->assertTrue(
            $historyMiddleware->getHistory()[2]['reason']->getMessage() == 'mock 404 exception',
            'Mock handler does not gave the first 404 exception correctly'
        );
        $this->assertTrue(
            $historyMiddleware->getHistory()[1]['reason']->getCode() == 500,
            'Mock handler does not gave the second 500 response correctly'
        );

        $uri2 = (string)($historyMiddleware->getHistory()[2]['request']->getUri());
        $uri3 = (string)($historyMiddleware->getHistory()[3]['request']->getUri());

        $this->assertTrue(
            strpos($uri2, '-secondary') !== false,
            'Did not retry to secondary uri.'
        );
        $this->assertFalse(
            strpos($uri3, '-secondary'),
            'Did not switch back to primary uri.'
        );
    }
}
