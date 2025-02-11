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
 * @package   MicrosoftAzureLegacy\Storage\Common\Models
 * @author    Azure Storage PHP SDK <dmsh@microsoft.com>
 * @copyright 2017 Microsoft Corporation
 * @license   https://github.com/azure/azure-storage-php/LICENSE
 * @link      https://github.com/azure/azure-storage-php
 */

namespace MicrosoftAzureLegacy\Storage\Common\Models;

/**
 * Holds info about page blob range diffs
 *
 * @category  Microsoft
 * @package   MicrosoftAzureLegacy\Storage\Common\Models
 * @author    Azure Storage PHP SDK <dmsh@microsoft.com>
 * @copyright 2017 Microsoft Corporation
 * @license   https://github.com/azure/azure-storage-php/LICENSE
 * @link      https://github.com/azure/azure-storage-php
 */
class RangeDiff extends Range
{
    private $isClearedPageRange;

    /**
     * Constructor
     *
     * @param integer $start              the resource start value
     * @param integer $end                the resource end value
     * @param bool    $isClearedPageRange true if the page range is a cleared range, false otherwise.
     */
    public function __construct($start, $end = null, $isClearedPageRange = false)
    {
        parent::__construct($start, $end);
        $this->isClearedPageRange = $isClearedPageRange;
    }

    /**
     * True if the page range is a cleared range, false otherwise
     *
     * @return bool
     */
    public function isClearedPageRange()
    {
        return $this->isClearedPageRange;
    }

    /**
     * Sets the isClearedPageRange property
     *
     * @param bool $isClearedPageRange
     *
     * @return bool
     */
    public function setIsClearedPageRange($isClearedPageRange)
    {
        $this->isClearedPageRange = $isClearedPageRange;
    }
}
