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

use MicrosoftAzureLegacy\Storage\Queue\Internal\QueueResources;
use MicrosoftAzureLegacy\Storage\Queue\Models\UpdateMessageResult;
use MicrosoftAzureLegacy\Storage\Tests\Framework\TestResources;
use MicrosoftAzureLegacy\Storage\Common\Internal\Utilities;
use MicrosoftAzureLegacy\Storage\Common\Internal\Resources;

/**
 * Unit tests for class UpdateMessageResult
 *
 * @category  Microsoft
 * @package   MicrosoftAzureLegacy\Storage\Tests\Unit\Queue\Models
 * @author    Azure Storage PHP SDK <dmsh@microsoft.com>
 * @copyright 2016 Microsoft Corporation
 * @license   https://github.com/azure/azure-storage-php/LICENSE
 * @link      https://github.com/azure/azure-storage-php
 */
class UpdateMessageResultTest extends \PHPUnit\Framework\TestCase
{
    /**
     *
     */
    public function testCreate()
    {
        // Setup
        $sample = TestResources::getUpdateMessageResultSampleHeaders();
        $expectedDate = Utilities::rfc1123ToDateTime(
            $sample[QueueResources::X_MS_TIME_NEXT_VISIBLE]
        );

        // Test
        $result = UpdateMessageResult::create($sample);

        // Assert
        $this->assertEquals(
            $sample[QueueResources::X_MS_POPRECEIPT],
            $result->getPopReceipt()
        );
        $this->assertEquals($expectedDate, $result->getTimeNextVisible());
    }
}
