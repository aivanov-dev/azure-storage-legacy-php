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
 * @package   MicrosoftAzureLegacy\Storage\Tests\Unit\Blob\Models
 * @author    Azure Storage PHP SDK <dmsh@microsoft.com>
 * @copyright 2016 Microsoft Corporation
 * @license   https://github.com/azure/azure-storage-php/LICENSE
 * @link      https://github.com/azure/azure-storage-php
 */
namespace MicrosoftAzureLegacy\Storage\Tests\Unit\Blob\Models;

use MicrosoftAzureLegacy\Storage\Blob\Models\GetContainerACLResult;
use MicrosoftAzureLegacy\Storage\Blob\Models\ContainerACL;
use MicrosoftAzureLegacy\Storage\Tests\Framework\TestResources;
use MicrosoftAzureLegacy\Storage\Common\Internal\Resources;
use MicrosoftAzureLegacy\Storage\Common\Internal\Utilities;

/**
 * Unit tests for class GetContainerACLResult
 *
 * @category  Microsoft
 * @package   MicrosoftAzureLegacy\Storage\Tests\Unit\Blob\Models
 * @author    Azure Storage PHP SDK <dmsh@microsoft.com>
 * @copyright 2016 Microsoft Corporation
 * @license   https://github.com/azure/azure-storage-php/LICENSE
 * @link      https://github.com/azure/azure-storage-php
 */
class GetContainerACLResultTest extends \PHPUnit\Framework\TestCase
{
    public function testCreate()
    {
        // Setup
        $sample = array();
        $expectedETag = '0x8CAFB82EFF70C46';
        $expectedDate = new \DateTime('Sun, 25 Sep 2011 19:42:18 GMT');
        $expectedPublicAccess = 'container';
        $expectedContainerACL = ContainerACL::create($expectedPublicAccess, $sample);

        // Test
        $result = GetContainerACLResult::create(
            $expectedPublicAccess,
            $expectedETag,
            $expectedDate,
            $sample
        );

        // Assert
        $this->assertEquals($expectedContainerACL, $result->getContainerAcl());
        $this->assertEquals($expectedDate, $result->getLastModified());
        $this->assertEquals($expectedETag, $result->getETag());
    }
}
