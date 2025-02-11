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
 * @package   MicrosoftAzureLegacy\Storage\Table\Internal
 * @author    Azure Storage PHP SDK <dmsh@microsoft.com>
 * @copyright 2016 Microsoft Corporation
 * @license   https://github.com/azure/azure-storage-php/LICENSE
 * @link      https://github.com/azure/azure-storage-php
 */

namespace MicrosoftAzureLegacy\Storage\Table\Internal;

/**
 * Defines how to serialize and unserialize table wrapper JSON
 *
 * @ignore
 * @category  Microsoft
 * @package   MicrosoftAzureLegacy\Storage\Table\Internal
 * @author    Azure Storage PHP SDK <dmsh@microsoft.com>
 * @copyright 2016 Microsoft Corporation
 * @license   https://github.com/azure/azure-storage-php/LICENSE
 * @link      https://github.com/azure/azure-storage-php
 */
interface IODataReaderWriter
{
    /**
     * Constructs JSON representation for table entry.
     *
     * @param string $name The name of the table.
     *
     * @return string
     */
    public function getTable($name);

    /**
     * Parses one table entry.
     *
     * @param string $body The HTTP response body.
     *
     * @return string
     */
    public function parseTable($body);

    /**
     * Constructs array of tables from HTTP response body.
     *
     * @param string $body The HTTP response body.
     *
     * @return array
     */
    public function parseTableEntries($body);

    /**
     * Constructs JSON representation for entity.
     *
     * @param \MicrosoftAzureLegacy\Storage\Table\Models\Entity $entity The entity instance.
     *
     * @return string
     */
    public function getEntity(\MicrosoftAzureLegacy\Storage\Table\Models\Entity $entity);

    /**
     * Constructs entity from HTTP response body.
     *
     * @param string $body The HTTP response body.
     *
     * @return \MicrosoftAzureLegacy\Storage\Table\Models\Entity
     */
    public function parseEntity($body);

    /**
     * Constructs array of entities from HTTP response body.
     *
     * @param string $body The HTTP response body.
     *
     * @return array
     */
    public function parseEntities($body);
}
