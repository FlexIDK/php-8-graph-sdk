# Facebook SDK for PHP 8

This repository contains the open source PHP SDK that allows you to access the Facebook Platform from your PHP app.

## Installation

```shell
composer require one23/php-8-graph-sdk
```

## Fork

This repository fork from deprecate [facebook/graph-sdk](https://github.com/facebookarchive/php-graph-sdk).

## What's different

- Namespace: \Facebook\ -> \One23\GraphSdk
- Facebook::__construct require 'default_graph_version'
- **Remove deprecate class:**
  - GraphNodes\GraphList -> GraphEdge
  - GraphNodes\GraphObject -> GraphNode
  - GraphNodes\GraphObjectFactory -> GraphNodeFactory
- **Remove traits:**
  - PseudoRandomStringGeneratorTrait
- **Replace deprecate methods:**
  - FacebookResponse
    - getGraphObject() -> getGraphNode()
    - getGraphList() -> getGraphEdge()
  - Authentication\AccessTokenMetadata
    - getProperty() -> getField()
  - GraphNodes\Collection
    - getProperty() -> getField()
- Rename && refactoring class:
  - FacebookApp -> App
  - FacebookBatchRequest -> BatchRequest
  - FacebookBatchResponse -> BatchResponse
  - FacebookClient -> Client
  - FacebookRequest -> Request
  - FacebookResponse -> Response 
  - Exceptions\
    - FacebookAuthenticationException -> AuthenticationException
    - FacebookAuthorizationException -> AuthorizationException
    - FacebookClientException -> ClientException
    - FacebookOtherException -> OtherException
    - FacebookResponseException -> ResponseException
    - FacebookResumableUploadException -> ResumableUploadException
    - FacebookSDKException -> SDKException
    - FacebookServerException -> ServerException
    - FacebookThrottleException -> ThrottleException
  - FileUpload\
    - FacebookFile -> File
    - FacebookResumableUploader -> ResumableUploader
    - FacebookTransferChunk -> TransferChunk
    - FacebookVideo -> Video
  - Helpers\
    - FacebookSignedRequestFromInputHelper -> AbstractSignedRequestFromInput
  - HttpClients\
    - FacebookCurl -> Curl
    - FacebookStream - Stream
    - FacebookHttpClientInterface -> Clients\ClientInterface
    - FacebookCurlHttpClient -> Clients\Curl
    - FacebookGuzzleHttpClient -> Clients\Guzzle
    - FacebookStreamHttpClient -> Clients\Stream
  - PersistentData\
    - PersistentDataInterface -> Handlers\PersistentDataInterface
    - FacebookMemoryPersistentDataHandler -> Handlers\Memory
    - FacebookSessionPersistentDataHandler -> Handlers\Session
  - PseudoRandomString\
    - McryptPseudoRandomStringGenerator -> Generators\Mcrypt (deprecate)
    - OpenSslPseudoRandomStringGenerator -> Generators\OpenSsl
    - RandomBytesPseudoRandomStringGenerator -> Generators\RandomBytes
    - UrandomPseudoRandomStringGenerator -> Generators\Urandom
    - PseudoRandomStringGeneratorInterface -> Generators\GeneratorInterface
    - PseudoRandomStringGeneratorFactory -> GeneratorFactory
  - Url\
    - FacebookUrlManipulator -> Url\Manipulator
    - UrlDetectionInterface -> Url\DetectionInterface
- **Deprecate** (remove in next version):
  - FacebookApp
  - FacebookBatchRequest
  - FacebookBatchResponse
  - FacebookClient
  - FacebookRequest
  - FacebookResponse
  - Exceptions\
    - FacebookAuthenticationException
    - FacebookAuthorizationException
    - FacebookClientException
    - FacebookOtherException
    - FacebookResponseException
    - FacebookResumableUploadException
    - FacebookSDKException
    - FacebookServerException
    - FacebookThrottleException
  - FileUpload\
    - FacebookFile
    - FacebookResumableUploader
    - FacebookTransferChunk
    - FacebookVideo
  - Helpers\
    - FacebookSignedRequestFromInputHelper
  - HttpClients\
    - FacebookCurl
    - FacebookCurlHttpClient
    - FacebookGuzzleHttpClient
    - FacebookHttpClientInterface
    - FacebookStream
    - FacebookStreamHttpClient
  - PersistentData\
    - PersistentDataInterface
    - FacebookMemoryPersistentDataHandler
    - FacebookSessionPersistentDataHandler
  - PseudoRandomString\
    - McryptPseudoRandomStringGenerator
    - OpenSslPseudoRandomStringGenerator
    - PseudoRandomStringGeneratorFactory
    - PseudoRandomStringGeneratorInterface
    - RandomBytesPseudoRandomStringGenerator
    - UrandomPseudoRandomStringGenerator
    - **Generators\Mcrypt** 

## What's new

- Support PHP 8.1
- Fix guzzle request
- Global exception: \One23\GraphSdk\Exception
- FileUpload\Mimetypes - replace to \GuzzleHttp\Psr7\MimeType
- PersistentData\Handlers\SessionLaravel
- PersistentData\Handlers\SessionPhalcon
  
## Usage

```php
use \One23\GraphSdk;

$fb = new GraphSdk\Facebook([
  'app_id' => '{app-id}',
  'app_secret' => '{app-secret}',
  'default_graph_version' => 'v15.0',
  //'default_access_token' => '{access-token}', // optional
]);

// Use one of the helper classes to get a GraphSdk\Authentication\AccessToken entity.
//   $helper = $fb->getRedirectLoginHelper();
//   $helper = $fb->getJavaScriptHelper();
//   $helper = $fb->getCanvasHelper();
//   $helper = $fb->getPageTabHelper();

try {
  // Get the GraphSdk\GraphNodes\GraphUser object for the current user.
  // If you provided a 'default_access_token', the '{access-token}' is optional.
  $response = $fb->get('/me', '{access-token}');
} 
catch(GraphSdk\Exceptions\ResponseException $e) {
  // When Graph returns an error
  echo 'Graph returned an error: ' . $e->getMessage();
  exit;
} 
catch(GraphSdk\Exceptions\SDKException $e) {
  // When validation fails or other local issues
  echo 'Facebook SDK returned an error: ' . $e->getMessage();
  exit;
}

$me = $response->getGraphUser();
echo 'Logged in as ' . $me->getName();
```

# Security

If you discover any security related issues, please email eugene@krivoruchko.info instead of using the issue tracker.

## License

[MIT](https://github.com/FlexIDK/php-8-graph-sdk/blob/master/LICENSE)
