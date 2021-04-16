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
 * @package   MicrosoftAzureLegacy\Storage\Tests\Unit\File\Models
 * @author    Azure Storage PHP SDK <dmsh@microsoft.com>
 * @copyright 2017 Microsoft Corporation
 * @license   https://github.com/azure/azure-storage-php/LICENSE
 * @link      https://github.com/azure/azure-storage-php
 */

namespace MicrosoftAzureLegacy\Storage\Tests\Unit\File\Models;

use MicrosoftAzureLegacy\Storage\File\Models\GetDirectoryPropertiesResult;
use MicrosoftAzureLegacy\Storage\Common\Internal\Resources;
use MicrosoftAzureLegacy\Storage\Common\Internal\Utilities;
use MicrosoftAzureLegacy\Storage\Tests\Framework\TestResources;

/**
 * Unit tests for class GetDirectoryPropertiesResult
 *
 * @category  Microsoft
 * @package   MicrosoftAzureLegacy\Storage\Tests\Unit\File\Models
 * @author    Azure Storage PHP SDK <dmsh@microsoft.com>
 * @copyright 2017 Microsoft Corporation
 * @license   https://github.com/azure/azure-storage-php/LICENSE
 * @link      https://github.com/azure/azure-storage-php
 */
class GetDirectoryPropertiesResultTest extends \PHPUnit\Framework\TestCase
{
    public function testCreate()
    {
        $sample = TestResources::getInterestingPropertiesArray();

        $directoryPropertiesResult = GetDirectoryPropertiesResult::create($sample);
        $expectedLastModified = Utilities::rfc1123ToDateTime($sample[Resources::QP_LAST_MODIFIED]);
        $expectedEtag = $sample[Resources::QP_ETAG];
        $expectedMeta = Utilities::getMetadataArray($sample);

        $this->assertEquals($expectedLastModified, $directoryPropertiesResult->getLastModified());
        $this->assertEquals($expectedEtag, $directoryPropertiesResult->getETag());
        $actualMeta = $directoryPropertiesResult->getMetadata();
        foreach ($expectedMeta as $key => $value) {
            $this->assertArrayHasKey($key, $actualMeta);
            $this->assertEquals($value, $actualMeta[$key]);
        }
    }
}
