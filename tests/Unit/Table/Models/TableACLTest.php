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
 * @package   MicrosoftAzureLegacy\Storage\Tests\Unit\Table\Models
 * @author    Azure Storage PHP SDK <dmsh@microsoft.com>
 * @copyright 2017 Microsoft Corporation
 * @license   https://github.com/azure/azure-storage-php/LICENSE
 * @link      https://github.com/azure/azure-storage-php
 */
namespace MicrosoftAzureLegacy\Storage\Tests\Unit\Table\Models;

use MicrosoftAzureLegacy\Storage\Table\Models\TableACL;
use MicrosoftAzureLegacy\Storage\Tests\Framework\TestResources;
use MicrosoftAzureLegacy\Storage\Common\Internal\Resources;
use MicrosoftAzureLegacy\Storage\Common\Internal\Utilities;
use MicrosoftAzureLegacy\Storage\Common\Internal\Serialization\XmlSerializer;

/**
 * Unit tests for class TableACL
 *
 * @category  Microsoft
 * @package   MicrosoftAzureLegacy\Storage\Tests\Unit\Table\Models
 * @author    Azure Storage PHP SDK <dmsh@microsoft.com>
 * @copyright 2017 Microsoft Corporation
 * @license   https://github.com/azure/azure-storage-php/LICENSE
 * @link      https://github.com/azure/azure-storage-php
 */
class TableACLTest extends \PHPUnit\Framework\TestCase
{
    public function testCreateEmpty()
    {
        // Setup
        $sample = array();

        // Test
        $acl = TableACL::create($sample);

        // Assert
        $this->assertCount(0, $acl->getSignedIdentifiers());
    }

    public function testCreateOneEntry()
    {
        // Setup
        $sample = TestResources::getTableACLOneEntrySample();

        // Test
        $acl = TableACL::create($sample['SignedIdentifiers']);

        // Assert
        $this->assertCount(1, $acl->getSignedIdentifiers());
    }

    public function testCreateMultipleEntries()
    {
        // Setup
        $sample = TestResources::getTableACLMultipleEntriesSample();

        // Test
        $acl = TableACL::create($sample['SignedIdentifiers']);

        // Assert
        $this->assertCount(2, $acl->getSignedIdentifiers());

        return $acl;
    }
}
