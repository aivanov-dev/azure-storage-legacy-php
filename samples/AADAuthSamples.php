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
 * @category  Microsoft
 * @package   MicrosoftAzureLegacy\Storage\Samples
 * @author    Azure Storage PHP SDK <dmsh@microsoft.com>
 * @copyright 2019 Microsoft Corporation
 * @license   https://github.com/azure/azure-storage-php/LICENSE
 * @link      https://github.com/azure/azure-storage-php
 */

namespace MicrosoftAzureLegacy\Storage\Samples;

require_once "../vendor/autoload.php";

use MicrosoftAzureLegacy\Storage\Blob\BlobRestProxy;
use MicrosoftAzureLegacy\Storage\Blob\BlobSharedAccessSignatureHelper;
use MicrosoftAzureLegacy\Storage\Blob\Models\CreateBlockBlobOptions;
use MicrosoftAzureLegacy\Storage\Blob\Models\CreateContainerOptions;
use MicrosoftAzureLegacy\Storage\Blob\Models\ListBlobsOptions;
use MicrosoftAzureLegacy\Storage\Blob\Models\PublicAccessType;
use MicrosoftAzureLegacy\Storage\Blob\Models\DeleteBlobOptions;
use MicrosoftAzureLegacy\Storage\Blob\Models\CreateBlobOptions;
use MicrosoftAzureLegacy\Storage\Blob\Models\GetBlobOptions;
use MicrosoftAzureLegacy\Storage\Blob\Models\SetBlobPropertiesOptions;
use MicrosoftAzureLegacy\Storage\Blob\Models\ListPageBlobRangesOptions;
use MicrosoftAzureLegacy\Storage\Common\Exceptions\ServiceException;
use MicrosoftAzureLegacy\Storage\Common\Exceptions\InvalidArgumentTypeException;
use MicrosoftAzureLegacy\Storage\Common\Internal\Resources;
use MicrosoftAzureLegacy\Storage\Common\Internal\StorageServiceSettings;
use MicrosoftAzureLegacy\Storage\Common\Models\Range;
use MicrosoftAzureLegacy\Storage\Common\Models\Logging;
use MicrosoftAzureLegacy\Storage\Common\Models\Metrics;
use MicrosoftAzureLegacy\Storage\Common\Models\RetentionPolicy;
use MicrosoftAzureLegacy\Storage\Common\Models\ServiceProperties;

$connectionString = 'DefaultEndpointsProtocol=https;AccountName=<YOUR_ACCOUNT_NAME>;';
$bearerToken = 'INITIAL BEARER TOKEN THAT DOES NOT WORK';
$blobClient = BlobRestProxy::createBlobServiceWithTokenCredential($bearerToken, $connectionString);

// to refresh token, simply modify it will change the value of the stored token.
$bearerToken = '<YOUR_BEARER_TOKEN>';

// A temporary container created and used through this sample, and finally deleted
$myContainer = 'mycontainer' . generateRandomString();

// Get and Set Blob Service Properties
setBlobServiceProperties($blobClient);

// To create a container call createContainer.
createContainerSample($blobClient);

// To get/set container properties
containerProperties($blobClient);

// To get/set container metadata
containerMetadata($blobClient);

// To upload a file as a blob, use the BlobRestProxy->createBlockBlob method. This operation will
// create the blob if it doesn't exist, or overwrite it if it does. The code example below assumes
// that the container has already been created and uses fopen to open the file as a stream.
uploadBlobSample($blobClient);

// To download blob into a file, use the BlobRestProxy->getBlob method. The example below assumes
// the blob to download has been already created.
downloadBlobSample($blobClient);

// To list the blobs in a container, use the BlobRestProxy->listBlobs method with a foreach loop to loop
// through the result. The following code outputs the name and URI of each blob in a container.
listBlobsSample($blobClient);

// To get set blob properties
blobProperties($blobClient);

// To get set blob metadata
blobMetadata($blobClient);

// Basic operations for page blob.
pageBlobOperations($blobClient);

// Snap shot operation for blob service.
snapshotOperations($blobClient);

// Basic lease operations.
leaseOperations($blobClient);

//Or to leverage the asynchronous methods provided, the operation can be done in
//a promise pipeline.
$containerName = '';
try {
    $containerName = basicStorageBlobOperationAsync($blobClient)->wait();
} catch (ServiceException $e) {
    $code = $e->getCode();
    $error_message = $e->getMessage();
    echo $code.": ".$error_message.PHP_EOL;
} catch (InvalidArgumentTypeException $e) {
    echo $e->getMessage().PHP_EOL;
}

try {
    $blobClient->deleteContainerAsync($containerName)->wait();
    cleanUp($blobClient);
} catch (ServiceException $e) {
    $code = $e->getCode();
    $error_message = $e->getMessage();
    echo $code.": ".$error_message.PHP_EOL;
}

function setBlobServiceProperties($blobClient)
{
     // Get blob service properties
    echo "Get Blob Service properties" . PHP_EOL;
    $originalProperties = $blobClient->getServiceProperties();
    // Set blob service properties
    echo "Set Blob Service properties" . PHP_EOL;
    $retentionPolicy = new RetentionPolicy();
    $retentionPolicy->setEnabled(true);
    $retentionPolicy->setDays(10);

    $logging = new Logging();
    $logging->setRetentionPolicy($retentionPolicy);
    $logging->setVersion('1.0');
    $logging->setDelete(true);
    $logging->setRead(true);
    $logging->setWrite(true);

    $metrics = new Metrics();
    $metrics->setRetentionPolicy($retentionPolicy);
    $metrics->setVersion('1.0');
    $metrics->setEnabled(true);
    $metrics->setIncludeAPIs(true);
    $serviceProperties = new ServiceProperties();
    $serviceProperties->setLogging($logging);
    $serviceProperties->setHourMetrics($metrics);
    $blobClient->setServiceProperties($serviceProperties);

    // revert back to original properties
    echo "Revert back to original service properties" . PHP_EOL;
    $blobClient->setServiceProperties($originalProperties->getValue());
    echo "Service properties sample completed" . PHP_EOL;
}

function createContainerSample($blobClient)
{
    // OPTIONAL: Set public access policy and metadata.
    // Create container options object.
    $createContainerOptions = new CreateContainerOptions();

    // Set container metadata
    $createContainerOptions->addMetaData("key1", "value1");
    $createContainerOptions->addMetaData("key2", "value2");

    try {
        // Create container.
        global $myContainer;
        $blobClient->createContainer($myContainer, $createContainerOptions);
    } catch (ServiceException $e) {
        $code = $e->getCode();
        $error_message = $e->getMessage();
        echo $code.": ".$error_message.PHP_EOL;
    }
}

function containerProperties($blobClient)
{
    $containerName = "mycontainer" . generateRandomString();

    echo "Create container " . $containerName . PHP_EOL;
    // Create container options object.
    $createContainerOptions = new CreateContainerOptions();

    // Set container metadata
    $createContainerOptions->addMetaData("key1", "value1");
    $createContainerOptions->addMetaData("key2", "value2");
    // Create container.
    $blobClient->createContainer($containerName, $createContainerOptions);
    echo "Get container properties:" . PHP_EOL;
    // Get container properties
    $properties = $blobClient->getContainerProperties($containerName);
    echo 'Last modified: ' . $properties->getLastModified()->format('Y-m-d H:i:s') . PHP_EOL;
    echo 'ETAG: ' . $properties->getETag() . PHP_EOL;
    echo "Delete container" . PHP_EOL;
    $blobClient->deleteContainer($containerName) . PHP_EOL;
}

function containerMetadata($blobClient)
{
    $containerName = "mycontainer" . generateRandomString();

    echo "Create container " . $containerName . PHP_EOL;
    // Create container options object.
    $createContainerOptions = new CreateContainerOptions();
    // Set container metadata
    $createContainerOptions->addMetaData("key1", "value1");
    $createContainerOptions->addMetaData("key2", "value2");
    // Create container.
    $blobClient->createContainer($containerName, $createContainerOptions);
    echo "Get container metadata" . PHP_EOL;
    // Get container properties
    $properties = $blobClient->getContainerProperties($containerName);
    foreach ($properties->getMetadata() as $key => $value) {
        echo $key . ": " . $value . PHP_EOL;
    }
    echo "Delete container" . PHP_EOL;
    $blobClient->deleteContainer($containerName);
}

function blobProperties($blobClient)
{
    // Create container
    $container = "mycontainer" . generateRandomString();
    echo "Create container " . $container . PHP_EOL;
    $blobClient->createContainer($container);
    // Create blob
    $blob = 'blob' . generateRandomString();
    echo "Create blob " . PHP_EOL;
    $blobClient->createPageBlob($container, $blob, 4096);
    // Set blob properties
    echo "Set blob properties" . PHP_EOL;
    $opts = new SetBlobPropertiesOptions();
    $opts->setCacheControl('test');
    $opts->setContentEncoding('UTF-8');
    $opts->setContentLanguage('en-us');
    $opts->setContentLength(512);
    $opts->setContentMD5(null);
    $opts->setContentType('text/plain');
    $opts->setSequenceNumberAction('increment');
    $blobClient->setBlobProperties($container, $blob, $opts);
    // Get blob properties
    echo "Get blob properties" . PHP_EOL;
    $result = $blobClient->getBlobProperties($container, $blob);

    $props = $result->getProperties();
    echo 'Cache control: ' . $props->getCacheControl() . PHP_EOL;
    echo 'Content encoding: ' . $props->getContentEncoding() . PHP_EOL;
    echo 'Content language: ' . $props->getContentLanguage() . PHP_EOL;
    echo 'Content type: ' . $props->getContentType() . PHP_EOL;
    echo 'Content length: ' . $props->getContentLength() . PHP_EOL;
    echo 'Content MD5: ' . $props->getContentMD5() . PHP_EOL;
    echo 'Last modified: ' . $props->getLastModified()->format('Y-m-d H:i:s') . PHP_EOL;
    echo 'Blob type: ' . $props->getBlobType() . PHP_EOL;
    echo 'Lease status: ' . $props->getLeaseStatus() . PHP_EOL;
    echo 'Sequence number: ' . $props->getSequenceNumber() . PHP_EOL;
    echo "Delete blob" . PHP_EOL;
    $blobClient->deleteBlob($container, $blob);
    echo "Delete container" . PHP_EOL;
    $blobClient->deleteContainer($container);
}

function blobMetadata($blobClient)
{
    // Create container
    $container = "mycontainer" . generateRandomString();
    echo "Create container " . $container . PHP_EOL;
    $blobClient->createContainer($container);
    // Create blob
    $blob = 'blob' . generateRandomString();
    echo "Create blob " . PHP_EOL;
    $blobClient->createPageBlob($container, $blob, 4096);
    // Set blob metadata
    echo "Set blob metadata" . PHP_EOL;
    $metadata = array(
        'key' => 'value',
        'foo' => 'bar',
        'baz' => 'boo');
    $blobClient->setBlobMetadata($container, $blob, $metadata);
    // Get blob metadata
    echo "Get blob metadata" . PHP_EOL;
    $result = $blobClient->getBlobMetadata($container, $blob);

    $retMetadata = $result->getMetadata();
    foreach ($retMetadata as $key => $value) {
        echo $key . ': ' . $value . PHP_EOL;
    }
    echo "Delete blob" . PHP_EOL;
    $blobClient->deleteBlob($container, $blob);
    echo "Delete container" . PHP_EOL;
    $blobClient->deleteContainer($container);
}

function uploadBlobSample($blobClient)
{
    if (!file_exists("myfile.txt")) {
        $file = fopen("myfile.txt", 'w');
        fwrite($file, 'Hello World!');
        fclose($file);
    }

    $content = fopen("myfile.txt", "r");
    $blob_name = "myblob";

    $content2 = "string content";
    $blob_name2 = "myblob2";

    try {
        //Upload blob
        global $myContainer;
        $blobClient->createBlockBlob($myContainer, $blob_name, $content);
        $blobClient->createBlockBlob($myContainer, $blob_name2, $content2);
    } catch (ServiceException $e) {
        $code = $e->getCode();
        $error_message = $e->getMessage();
        echo $code.": ".$error_message.PHP_EOL;
    }
}

function downloadBlobSample($blobClient)
{
    try {
        global $myContainer;
        $getBlobResult = $blobClient->getBlob($myContainer, "myblob");
    } catch (ServiceException $e) {
        $code = $e->getCode();
        $error_message = $e->getMessage();
        echo $code.": ".$error_message.PHP_EOL;
    }

    file_put_contents("output.txt", $getBlobResult->getContentStream());
}

function listBlobsSample($blobClient)
{
    try {
        // List blobs.
        $listBlobsOptions = new ListBlobsOptions();
        $listBlobsOptions->setPrefix("myblob");

        // Setting max result to 1 is just to demonstrate the continuation token.
        // It is not the recommended value in a product environment.
        $listBlobsOptions->setMaxResults(1);

        do {
            global $myContainer;
            $blob_list = $blobClient->listBlobs($myContainer, $listBlobsOptions);
            foreach ($blob_list->getBlobs() as $blob) {
                echo $blob->getName().": ".$blob->getUrl().PHP_EOL;
            }

            $listBlobsOptions->setContinuationToken($blob_list->getContinuationToken());
        } while ($blob_list->getContinuationToken());

    } catch (ServiceException $e) {
        $code = $e->getCode();
        $error_message = $e->getMessage();
        echo $code.": ".$error_message.PHP_EOL;
    }
}

function pageBlobOperations($blobClient)
{
    global $myContainer;

    $blobName = "HelloPageBlobWorld";
    $containerName = $myContainer;

    # Create a page blob
    echo "Create Page Blob with name {$blobName}".PHP_EOL;
    $blobClient->createPageBlob($containerName, $blobName, 2560);
    # Create pages in a page blob
    echo "Create pages in a page blob".PHP_EOL;

    $blobClient->createBlobPages(
        $containerName,
        $blobName,
        new Range(0, 511),
        generateRandomString(512)
    );
    $blobClient->createBlobPages(
        $containerName,
        $blobName,
        new Range(512, 1023),
        generateRandomString(512)
    );
    # List page blob ranges
    $listPageBlobRangesOptions = new ListPageBlobRangesOptions();
    $listPageBlobRangesOptions->setRange(new Range(0, 1023));
    echo "List Page Blob Ranges".PHP_EOL;
    $listPageBlobRangesResult = $blobClient->listPageBlobRanges(
        $containerName,
        $blobName,
        $listPageBlobRangesOptions
    );

    foreach ($listPageBlobRangesResult->getRanges() as $range) {
        echo "Range:".$range->getStart()."-".$range->getEnd().PHP_EOL;
        $getBlobOptions = new GetBlobOptions();
        $getBlobOptions->setRange($range);
        $getBlobResult = $blobClient->getBlob($containerName, $blobName, $getBlobOptions);
        file_put_contents("PageContent.txt", $getBlobResult->getContentStream());
    }
    # Clean up after the sample
      echo "Delete Blob".PHP_EOL;
    $blobClient->deleteBlob($containerName, $blobName);
}

function snapshotOperations($blobClient)
{
    global $myContainer;

    $blobName = "HelloWorld";
    $containerName = $myContainer;

    # Upload file as a block blob
    echo "Uploading BlockBlob".PHP_EOL;

    $content = 'test content hello hello world';
    $blobClient->createBlockBlob($containerName, $blobName, $content);
    # Create a snapshot
    echo "Create a Snapshot".PHP_EOL;
    $snapshotResult = $blobClient->createBlobSnapshot($containerName, $blobName);
    # Retrieve snapshot
    echo "Retrieve Snapshot".PHP_EOL;
    $getBlobOptions = new GetBlobOptions();
    $getBlobOptions->setSnapshot($snapshotResult->getSnapshot());
    $getBlobResult = $blobClient->getBlob($containerName, $blobName, $getBlobOptions);
    file_put_contents("HelloWorldSnapshotCopy.png", $getBlobResult->getContentStream());
    # Clean up after the sample
    echo "Delete Blob and snapshot".PHP_EOL;
    $deleteBlobOptions = new DeleteBlobOptions();
    $deleteBlobOptions->setDeleteSnaphotsOnly(false);
    $blobClient->deleteBlob($containerName, $blobName, $deleteBlobOptions);
}

function cleanUp($blobClient)
{
    if (file_exists('output.txt')) {
        unlink('output.txt');
    }
    if (file_exists('myfile.txt')) {
        unlink('myfile.txt');
    }
    if (file_exists('outputBySAS.txt')) {
        unlink('outputBySAS.txt');
    }
    if (file_exists('myblob.txt')) {
        unlink('myblob.txt');
    }
    if (file_exists('PageContent.txt')) {
        unlink('PageContent.txt');
    }
    if (file_exists('HelloWorldSnapshotCopy.png')) {
        unlink('HelloWorldSnapshotCopy.png');
    }

    global $myContainer;
    $blobClient->deleteContainer($myContainer);

    echo "Successfully cleaned up\n";
}

function leaseOperations($blobClient)
{
    // Create container
    $container = "mycontainer" . generateRandomString();
    echo "Create container " . $container . PHP_EOL;
    $blobClient->createContainer($container);
    // Create Blob
    $blob = 'Blob' . generateRandomString();
    echo "Create blob " . $blob . PHP_EOL;
    $contentType = 'text/plain; charset=UTF-8';
    $options = new CreateBlockBlobOptions();
    $options->setContentType($contentType);
    $blobClient->createBlockBlob($container, $blob, 'Hello world', $options);

    // Acquire lease
    $result = $blobClient->acquireLease($container, $blob);
    try {
        echo "Try delete blob without lease" . PHP_EOL;
        $blobClient->deleteBlob($container, $blob);
    } catch (ServiceException $e) {
        echo "Delete blob with lease" . PHP_EOL;
        $blobOptions = new DeleteBlobOptions();
        $blobOptions->setLeaseId($result->getLeaseId());
        $blobClient->deleteBlob($container, $blob, $blobOptions);
    }
    echo "Delete container" . PHP_EOL;
    $blobClient->deleteContainer($container);
}

function generateRandomString($length = 6)
{
    $characters = 'abcdefghijklmnopqrstuvwxyz';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}
