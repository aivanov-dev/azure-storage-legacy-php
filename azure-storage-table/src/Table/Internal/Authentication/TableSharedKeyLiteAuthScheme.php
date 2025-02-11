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
 * @package   MicrosoftAzureLegacy\Storage\Common\Internal\Authentication
 * @author    Azure Storage PHP SDK <dmsh@microsoft.com>
 * @copyright 2016 Microsoft Corporation
 * @license   https://github.com/azure/azure-storage-php/LICENSE
 * @link      http://github.com/windowsazure/azure-sdk-for-php
 */

namespace MicrosoftAzureLegacy\Storage\Table\Internal\Authentication;

use MicrosoftAzureLegacy\Storage\Common\Internal\Authentication\SharedKeyAuthScheme;
use MicrosoftAzureLegacy\Storage\Common\Internal\Utilities;
use MicrosoftAzureLegacy\Storage\Table\Internal\TableResources as Resources;

/**
 * Provides shared key authentication scheme for blob and queue. For more info
 * check: http://msdn.microsoft.com/en-us/library/windowsazure/dd179428.aspx
 *
 * @ignore
 * @category  Microsoft
 * @package   MicrosoftAzureLegacy\Storage\Common\Internal\Authentication
 * @author    Azure Storage PHP SDK <dmsh@microsoft.com>
 * @copyright 2016 Microsoft Corporation
 * @license   https://github.com/azure/azure-storage-php/LICENSE
 * @link      http://github.com/windowsazure/azure-sdk-for-php
 */
class TableSharedKeyLiteAuthScheme extends SharedKeyAuthScheme
{
    /**
     * The invaluded headers
     */
    protected $includedHeaders;

    /**
     * Constructor.
     *
     * @param string $accountName storage account name.
     * @param string $accountKey  storage account primary or secondary key.
     *
     * @return TableSharedKeyLiteAuthScheme
     */
    public function __construct($accountName, $accountKey)
    {
        $this->accountKey  = $accountKey;
        $this->accountName = $accountName;

        $this->includedHeaders   = array();
        $this->includedHeaders[] = Resources::DATE;
    }

    /**
     * Computes the authorization signature for blob and queue shared key.
     *
     * @param array  $headers     request headers.
     * @param string $url         reuqest url.
     * @param array  $queryParams query variables.
     * @param string $httpMethod  request http method.
     *
     * @see Blob and Queue Services (Shared Key Authentication) at
     *      http://msdn.microsoft.com/en-us/library/windowsazure/dd179428.aspx
     *
     * @return string
     */
    protected function computeSignature(
        array $headers,
        $url,
        array $queryParams,
        $httpMethod
    ) {
        $canonicalizedResource = $this->computeCanonicalizedResourceForTable(
            $url,
            $queryParams
        );

        $stringToSign = array();

        foreach ($this->includedHeaders as $header) {
            $stringToSign[] = Utilities::tryGetValue($headers, $header);
        }

        $stringToSign[] = $canonicalizedResource;
        $stringToSign   = implode("\n", $stringToSign);

        return $stringToSign;
    }

    /**
     * Returns authorization header to be included in the request.
     *
     * @param array  $headers     request headers.
     * @param string $url         reuqest url.
     * @param array  $queryParams query variables.
     * @param string $httpMethod  request http method.
     *
     * @see Specifying the Authorization Header section at
     *      http://msdn.microsoft.com/en-us/library/windowsazure/dd179428.aspx
     *
     * @return string
     */
    public function getAuthorizationHeader(
        array $headers,
        $url,
        array $queryParams,
        $httpMethod
    ) {
        $signature = $this->computeSignature(
            $headers,
            $url,
            $queryParams,
            $httpMethod
        );

        return 'SharedKeyLite ' . $this->accountName . ':' . base64_encode(
            hash_hmac('sha256', $signature, base64_decode($this->accountKey), true)
        );
    }
}
