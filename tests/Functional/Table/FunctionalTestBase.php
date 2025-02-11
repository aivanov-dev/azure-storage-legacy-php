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

use MicrosoftAzureLegacy\Storage\Common\Internal\Utilities;
use MicrosoftAzureLegacy\Storage\Table\Models\Entity;
use MicrosoftAzureLegacy\Storage\Table\Models\Filters\Filter;

class FunctionalTestBase extends IntegrationTestBase
{
    private static $isOneTimeSetup = false;

    public function setUp()
    {
        parent::setUp();
        if (!self::$isOneTimeSetup) {
            $this->doOneTimeSetup();
            self::$isOneTimeSetup = true;
        }
    }

    private function doOneTimeSetup()
    {
        TableServiceFunctionalTestData::setupData();

        foreach (TableServiceFunctionalTestData::$testTableNames as $name) {
            // self::println('Creating Table: ' . $name);
            $this->restProxy->createTable($name);
        }
    }

    public static function tearDownAfterClass()
    {
        if (self::$isOneTimeSetup) {
            $testBase = new FunctionalTestBase();
            $testBase->setUp();
            foreach (TableServiceFunctionalTestData::$testTableNames as $name) {
                $testBase->safeDeleteTable($name);
            }
            self::$isOneTimeSetup = false;
        }
        parent::tearDownAfterClass();
    }

    protected function clearTable($table)
    {
        $index = array_search($table, TableServiceFunctionalTestData::$testTableNames);
        if ($index !== false) {
            // This is a well-known table, so need to create a new one to replace it.
            TableServiceFunctionalTestData::$testTableNames[$index] = TableServiceFunctionalTestData::getInterestingTableName();
            $this->restProxy->createTable(TableServiceFunctionalTestData::$testTableNames[$index]);
        }

        $this->restProxy->deleteTable($table);
    }

    protected function getCleanTable()
    {
        $this->clearTable(TableServiceFunctionalTestData::$testTableNames[0]);
        return TableServiceFunctionalTestData::$testTableNames[0];
    }

    public static function println($msg)
    {
        //error_log($msg);
    }

    public static function tmptostring($value)
    {
        if (is_null($value)) {
            return 'null';
        } elseif (is_bool($value)) {
            return ($value == true ? 'true' : 'false');
        } elseif ($value instanceof \DateTime) {
            return Utilities::convertToEdmDateTime($value);
        } elseif ($value instanceof Entity) {
            return self::entityToString($value);
        } elseif (is_array($value)) {
            return self::entityPropsToString($value);
        } elseif ($value instanceof Filter) {
            return TableServiceFunctionalTestUtils::filtertoString($value);
        } else {
            return $value;
        }
    }

    public static function entityPropsToString($props)
    {
        $ret = '';
        foreach ($props as $k => $value) {
            $ret .= $k . ':';
            if (is_null($value)) {
                $ret .= 'NULL PROP!';
            } else {
                $ret .= $value->getEdmType() . ':' . self::tmptostring($value->getValue());
            }
            $ret .= "\n";
        }
        return $ret;
    }

    public static function entityToString($ent)
    {
        $ret = 'ETag=' . self::tmptostring($ent->getETag()) . "\n";
        $ret .= 'Props=' . self::entityPropsToString($ent->getProperties());
        return $ret;
    }
}
