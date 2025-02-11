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
 * @package   MicrosoftAzureLegacy\Storage\Tests\Framework
 * @author    Azure Storage PHP SDK <dmsh@microsoft.com>
 * @copyright 2016 Microsoft Corporation
 * @license   https://github.com/azure/azure-storage-php/LICENSE
 * @link      https://github.com/azure/azure-storage-php
 */
namespace MicrosoftAzureLegacy\Storage\Tests\Framework;

use MicrosoftAzureLegacy\Storage\Queue\QueueRestProxy;
use MicrosoftAzureLegacy\Storage\Tests\Framework\ServiceRestProxyTestBase;
use MicrosoftAzureLegacy\Storage\Common\Models\ServiceProperties;
use MicrosoftAzureLegacy\Storage\Common\Middlewares\RetryMiddlewareFactory;

/**
 * TestBase class for each unit test class.
 *
 * @category  Microsoft
 * @package   MicrosoftAzureLegacy\Storage\Tests\Framework
 * @author    Azure Storage PHP SDK <dmsh@microsoft.com>
 * @copyright 2016 Microsoft Corporation
 * @license   https://github.com/azure/azure-storage-php/LICENSE
 * @link      https://github.com/azure/azure-storage-php
 */
class QueueServiceRestProxyTestBase extends ServiceRestProxyTestBase
{
    private $_createdQueues;

    public function setUp()
    {
        parent::setUp();
        $queueRestProxy = QueueRestProxy::createQueueService($this->connectionString);
        $queueRestProxy->pushMiddleware(RetryMiddlewareFactory::create());
        parent::setProxy($queueRestProxy);
        $this->_createdQueues = array();
    }

    public function createQueue($queueName, $options = null)
    {
        $this->restProxy->createQueue($queueName, $options);
        $this->_createdQueues[] = $queueName;
    }

    public function deleteQueue($queueName, $options = null)
    {
        $this->restProxy->deleteQueue($queueName, $options);
    }

    public function safeDeleteQueue($queueName)
    {
        try {
            $this->deleteQueue($queueName);
        } catch (\Exception $e) {
            // Ignore exception and continue if the error message shows that the
            // queue does not exist.
            if (strpos($e->getMessage(), 'specified queue does not exist') == false) {
                throw $e;
            };
        }
    }

    protected function tearDown()
    {
        parent::tearDown();

        foreach ($this->_createdQueues as $value) {
            $this->safeDeleteQueue($value);
        }
    }
}
