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
 * @package   MicrosoftAzureLegacy\Storage\Tests\Unit\Queue\Models
 * @author    Azure Storage PHP SDK <dmsh@microsoft.com>
 * @copyright 2016 Microsoft Corporation
 * @license   https://github.com/azure/azure-storage-php/LICENSE
 * @link      https://github.com/azure/azure-storage-php
 */

namespace MicrosoftAzureLegacy\Storage\Tests\Unit\Queue\Models;

use MicrosoftAzureLegacy\Storage\Queue\Models\Queue;
use MicrosoftAzureLegacy\Storage\Tests\Framework\TestResources;

/**
 * Unit tests for class Queue
 *
 * @category  Microsoft
 * @package   MicrosoftAzureLegacy\Storage\Tests\Unit\Queue\Models
 * @author    Azure Storage PHP SDK <dmsh@microsoft.com>
 * @copyright 2016 Microsoft Corporation
 * @license   https://github.com/azure/azure-storage-php/LICENSE
 * @link      https://github.com/azure/azure-storage-php
 */
class QueueTest extends \PHPUnit\Framework\TestCase
{
    public function testConstruct()
    {
        // Setup
        $expectedName = TestResources::QUEUE1_NAME;
        $expectedUrl = TestResources::QUEUE_URI;

        // Test
        $queue = new Queue($expectedName, $expectedUrl);

        // Assert
        $this->assertEquals($expectedName, $queue->getName());
        $this->assertEquals($expectedUrl, $queue->getUrl());
    }

    public function testSetName()
    {
        // Setup
        $queue = new Queue('myqueue', 'myurl');
        $expected = TestResources::QUEUE1_NAME;

        // Test
        $queue->setName($expected);

        // Assert
        $this->assertEquals($expected, $queue->getName());
    }

    public function testGetName()
    {
        // Setup
        $queue = new Queue('myqueue', 'myurl');
        $expected = TestResources::QUEUE1_NAME;
        $queue->setName($expected);

        // Test
        $actual = $queue->getName();

        // Assert
        $this->assertEquals($expected, $actual);
    }

    public function testSetUrl()
    {
        // Setup
        $queue = new Queue('myqueue', 'myurl');
        $expected = TestResources::QUEUE1_NAME;

        // Test
        $queue->setUrl($expected);

        // Assert
        $this->assertEquals($expected, $queue->getUrl());
    }

    public function testGetUrl()
    {
        // Setup
        $queue = new Queue('myqueue', 'myurl');
        $expected = TestResources::QUEUE_URI;
        $queue->setUrl($expected);

        // Test
        $actual = $queue->getUrl();

        // Assert
        $this->assertEquals($expected, $actual);
    }

    public function testSetMetadata()
    {
        // Setup
        $queue = new Queue('myqueue', 'myurl');
        $expected = array('key1' => 'value1', 'key2' => 'value2');

        // Test
        $queue->setMetadata($expected);

        // Assert
        $this->assertEquals($expected, $queue->getMetadata());
    }

    public function testGetMetadata()
    {
        // Setup
        $queue = new Queue('myqueue', 'myurl');
        $expected = array('key1' => 'value1', 'key2' => 'value2');
        $queue->setMetadata($expected);

        // Test
        $actual = $queue->getMetadata();

        // Assert
        $this->assertEquals($expected, $actual);
    }
}
