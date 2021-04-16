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

use MicrosoftAzureLegacy\Storage\File\Internal\FileResources as Resources;
use MicrosoftAzureLegacy\Storage\Common\Internal\Utilities;

/**
 * Represents windows azure directory object
 *
 * @category  Microsoft
 * @package   MicrosoftAzureLegacy\Storage\File\Models
 * @author    Azure Storage PHP SDK <dmsh@microsoft.com>
 * @copyright 2017 Microsoft Corporation
 * @license   https://github.com/azure/azure-storage-php/LICENSE
 * @link      https://github.com/azure/azure-storage-php
 */
class Directory
{
    private $name;

    /**
     * Gets directory name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets directory name.
     *
     * @param string $name value.
     *
     * @return void
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Creates a Directory object using the parsed array.
     *
     * @param  array $parsed The parsed array that contains the object information.
     *
     * @return Directory
     */
    public static function create(array $parsed)
    {
        $result = new Directory();
        $name = Utilities::tryGetValue($parsed, Resources::QP_NAME);
        $result->setName($name);

        return $result;
    }
}
