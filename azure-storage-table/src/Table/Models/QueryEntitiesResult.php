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
 * @package   MicrosoftAzureLegacy\Storage\Table\Models
 * @author    Azure Storage PHP SDK <dmsh@microsoft.com>
 * @copyright 2016 Microsoft Corporation
 * @license   https://github.com/azure/azure-storage-php/LICENSE
 * @link      https://github.com/azure/azure-storage-php
 */

namespace MicrosoftAzureLegacy\Storage\Table\Models;

use MicrosoftAzureLegacy\Storage\Common\Internal\Utilities;
use MicrosoftAzureLegacy\Storage\Table\Internal\TableResources as Resources;

/**
 * Holds results of calling queryEntities API
 *
 * @category  Microsoft
 * @package   MicrosoftAzureLegacy\Storage\Table\Models
 * @author    Azure Storage PHP SDK <dmsh@microsoft.com>
 * @copyright 2016 Microsoft Corporation
 * @license   https://github.com/azure/azure-storage-php/LICENSE
 * @link      https://github.com/azure/azure-storage-php
 */
class QueryEntitiesResult
{
    use TableContinuationTokenTrait;

    private $_entities;

    /**
     * Creates new QueryEntitiesResult instance.
     *
     * @param array $headers  The HTTP response headers.
     * @param array $entities The entities.
     *
     * @internal
     *
     * @return QueryEntitiesResult
     */
    public static function create(array $headers, array $entities)
    {
        $result  = new QueryEntitiesResult();
        $headers = array_change_key_case($headers);
        $nextPK  = Utilities::tryGetValue(
            $headers,
            Resources::X_MS_CONTINUATION_NEXTPARTITIONKEY
        );
        $nextRK  = Utilities::tryGetValue(
            $headers,
            Resources::X_MS_CONTINUATION_NEXTROWKEY
        );

        if ($nextRK != null && $nextPK != null) {
            $result->setContinuationToken(
                new TableContinuationToken(
                    '',
                    $nextPK,
                    $nextRK,
                    Utilities::getLocationFromHeaders($headers)
                )
            );
        }

        $result->setEntities($entities);

        return $result;
    }

    /**
     * Gets entities.
     *
     * @return array
     */
    public function getEntities()
    {
        return $this->_entities;
    }

    /**
     * Sets entities.
     *
     * @param array $entities The entities array.
     *
     * @return void
     */
    protected function setEntities(array $entities)
    {
        $this->_entities = $entities;
    }
}
