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
 * @package   MicrosoftAzureLegacy\Storage\File\Models
 * @author    Azure Storage PHP SDK <dmsh@microsoft.com>
 * @copyright 2017 Microsoft Corporation
 * @license   https://github.com/azure/azure-storage-php/LICENSE
 * @link      https://github.com/azure/azure-storage-php
 */

namespace MicrosoftAzureLegacy\Storage\File\Models;

use MicrosoftAzureLegacy\Storage\Common\Internal\MetadataTrait;

/**
 * Holds result of getDirectoryMetadata.
 *
 * @category  Microsoft
 * @package   MicrosoftAzureLegacy\Storage\File\Models
 * @author    Azure Storage PHP SDK <dmsh@microsoft.com>
 * @copyright 2017 Microsoft Corporation
 * @license   https://github.com/azure/azure-storage-php/LICENSE
 * @link      https://github.com/azure/azure-storage-php
 */
class GetDirectoryMetadataResult
{
    use MetadataTrait;

    /**
     * Creates the instance from the parsed headers.
     *
     * @param  array $parsed Parsed headers
     *
     * @return GetDirectoryMetadataResult
     */
    public static function create(array $parsed)
    {
        return static::createMetadataResult($parsed);
    }
}
