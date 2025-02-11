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
 * @package   MicrosoftAzureLegacy\Storage\Queue\Models
 * @author    Azure Storage PHP SDK <dmsh@microsoft.com>
 * @copyright 2016 Microsoft Corporation
 * @license   https://github.com/azure/azure-storage-php/LICENSE
 * @link      https://github.com/azure/azure-storage-php
 */

namespace MicrosoftAzureLegacy\Storage\Queue\Models;

/**
 * Holds optional parameters for createMessage wrapper.
 *
 * @category  Microsoft
 * @package   MicrosoftAzureLegacy\Storage\Queue\Models
 * @author    Azure Storage PHP SDK <dmsh@microsoft.com>
 * @copyright 2016 Microsoft Corporation
 * @license   https://github.com/azure/azure-storage-php/LICENSE
 * @link      https://github.com/azure/azure-storage-php
 */
class CreateMessageOptions extends QueueServiceOptions
{
    private $_visibilityTimeoutInSeconds;
    private $_timeToLiveInSeconds;

    /**
     * Gets visibilityTimeoutInSeconds field.
     *
     * @return integer
     */
    public function getVisibilityTimeoutInSeconds()
    {
        return $this->_visibilityTimeoutInSeconds;
    }

    /**
     * Sets visibilityTimeoutInSeconds field.
     *
     * @param integer $visibilityTimeoutInSeconds value to use.
     *
     * @return void
     */
    public function setVisibilityTimeoutInSeconds($visibilityTimeoutInSeconds)
    {
        $this->_visibilityTimeoutInSeconds = $visibilityTimeoutInSeconds;
    }

    /**
     * Gets timeToLiveInSeconds field.
     *
     * @return integer
     */
    public function getTimeToLiveInSeconds()
    {
        return $this->_timeToLiveInSeconds;
    }

    /**
     * Sets timeToLiveInSeconds field.
     *
     * @param integer $timeToLiveInSeconds value to use.
     *
     * @return void
     */
    public function setTimeToLiveInSeconds($timeToLiveInSeconds)
    {
        $this->_timeToLiveInSeconds = $timeToLiveInSeconds;
    }
}
