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
 * @package   MicrosoftAzureLegacy\Storage\Tests\Unit\Queue\Models
 * @author    Azure Storage PHP SDK <dmsh@microsoft.com>
 * @copyright 2016 Microsoft Corporation
 * @license   https://github.com/azure/azure-storage-php/LICENSE
 * @link      https://github.com/azure/azure-storage-php
 */

namespace MicrosoftAzureLegacy\Storage\Tests\Unit\Queue\Models;

use MicrosoftAzureLegacy\Storage\Queue\Models\GetQueueMetadataResult;

/**
 * Unit tests for class GetQueueMetadataResult
 *
 * @category  Microsoft
 * @package   MicrosoftAzureLegacy\Storage\Tests\Unit\Queue\Models
 * @author    Azure Storage PHP SDK <dmsh@microsoft.com>
 * @copyright 2016 Microsoft Corporation
 * @license   https://github.com/azure/azure-storage-php/LICENSE
 * @link      https://github.com/azure/azure-storage-php
 */
class GetQueueMetadataResultTest extends \PHPUnit\Framework\TestCase
{
    public function testConstruct()
    {
        // Setup
        $count = 10;
        $metadata = array('key1' => 'value1', 'key2' => 'value2');

        // Test
        $actual = new GetQueueMetadataResult($count, $metadata);

        // Assert
        $this->assertEquals($count, $actual->getApproximateMessageCount());
        $this->assertEquals($metadata, $actual->getMetadata());
    }
}
