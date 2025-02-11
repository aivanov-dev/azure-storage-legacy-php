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

use MicrosoftAzureLegacy\Storage\Common\MarkerContinuationTokenTrait;
use MicrosoftAzureLegacy\Storage\Common\Internal\Validate;

/**
 * Options for listFiles API.
 *
 * @category  Microsoft
 * @package   MicrosoftAzureLegacy\Storage\File\Models
 * @author    Azure Storage PHP SDK <dmsh@microsoft.com>
 * @copyright 2017 Microsoft Corporation
 * @license   https://github.com/azure/azure-storage-php/LICENSE
 * @link      https://github.com/azure/azure-storage-php
 */
class ListSharesOptions extends FileServiceOptions
{
    use MarkerContinuationTokenTrait;

    private $prefix;
    private $maxResults;
    private $includeMetadata;

    /**
     * Gets prefix - filters the results to return only Shares whose name
     * begins with the specified prefix.
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * Sets prefix - filters the results to return only Shares whose name
     * begins with the specified prefix.
     *
     * @param string $prefix value.
     *
     * @return void
     */
    public function setPrefix($prefix)
    {
        Validate::canCastAsString($prefix, 'prefix');
        $this->prefix = $prefix;
    }

    /**
     * Gets max results which specifies the maximum number of Shares to return.
     * If the request does not specify maxresults, or specifies a value
     * greater than 5,000, the server will return up to 5,000 items.
     * If the parameter is set to a value less than or equal to zero,
     * the server will return status code 400 (Bad Request).
     *
     * @return string
     */
    public function getMaxResults()
    {
        return $this->maxResults;
    }

    /**
     * Sets max results which specifies the maximum number of Shares to return.
     * If the request does not specify maxresults, or specifies a value
     * greater than 5,000, the server will return up to 5,000 items.
     * If the parameter is set to a value less than or equal to zero,
     * the server will return status code 400 (Bad Request).
     *
     * @param string $maxResults value.
     *
     * @return void
     */
    public function setMaxResults($maxResults)
    {
        Validate::canCastAsString($maxResults, 'maxResults');
        $this->maxResults = $maxResults;
    }

    /**
     * Indicates if metadata is included or not.
     *
     * @return string
     */
    public function getIncludeMetadata()
    {
        return $this->includeMetadata;
    }

    /**
     * Sets the include metadata flag.
     *
     * @param bool $includeMetadata value.
     *
     * @return void
     */
    public function setIncludeMetadata($includeMetadata)
    {
        Validate::isBoolean($includeMetadata);
        $this->includeMetadata = $includeMetadata;
    }
}
