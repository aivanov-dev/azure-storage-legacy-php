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

use MicrosoftAzureLegacy\Storage\Common\Internal\Utilities;
use MicrosoftAzureLegacy\Storage\Queue\Internal\QueueResources as Resources;

/**
 * Holds results of listMessages wrapper.
 *
 * @category  Microsoft
 * @package   MicrosoftAzureLegacy\Storage\Queue\Models
 * @author    Azure Storage PHP SDK <dmsh@microsoft.com>
 * @copyright 2016 Microsoft Corporation
 * @license   https://github.com/azure/azure-storage-php/LICENSE
 * @link      https://github.com/azure/azure-storage-php
 */
class PeekMessagesResult
{
    private $queueMessages;

    /**
     * Creates PeekMessagesResult object from parsed XML response.
     *
     * @param array $parsedResponse XML response parsed into array.
     *
     * @internal
     *
     * @return PeekMessagesResult
     */
    public static function create($parsedResponse)
    {
        $result        = new PeekMessagesResult();
        $queueMessages = array();

        if (!empty($parsedResponse)) {
            $rawMessages = Utilities::getArray(
                $parsedResponse[Resources::QP_QUEUE_MESSAGE]
            );
            foreach ($rawMessages as $value) {
                $message = QueueMessage::createFromPeekMessages($value);

                $queueMessages[] = $message;
            }
        }
        $result->setQueueMessages($queueMessages);

        return $result;
    }

    /**
     * Gets queueMessages field.
     *
     * @return QueueMessage[]
     */
    public function getQueueMessages()
    {
        $clonedMessages = array();

        foreach ($this->queueMessages as $value) {
            $clonedMessages[] = clone $value;
        }

        return $clonedMessages;
    }

    /**
     * Sets queueMessages field.
     *
     * @param QueueMessage[] $queueMessages value to use.
     *
     * @internal
     *
     * @return void
     */
    protected function setQueueMessages($queueMessages)
    {
        $this->queueMessages = $queueMessages;
    }
}
