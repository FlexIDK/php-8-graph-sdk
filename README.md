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
  - FacebookResponse -> Response 
  - Url\
    - FacebookUrlManipulator -> Url\Manipulator
    - UrlDetectionInterface -> Url\DetectionInterface
  - Exceptions\
    - FacebookAuthenticationException -> Exceptions\AuthenticationException
    - FacebookAuthorizationException -> Exceptions\AuthorizationException
    - FacebookClientException -> Exceptions\ClientException
    - FacebookOtherException -> Exceptions\OtherException
    - FacebookResponseException -> Exceptions\ResponseException
    - FacebookResumableUploadException -> Exceptions\ResumableUploadException
    - FacebookSDKException -> Exceptions\SDKException
    - FacebookServerException -> Exceptions\ServerException
    - FacebookThrottleException -> Exceptions\ThrottleException
  - PseudoRandomString\
    - McryptPseudoRandomStringGenerator -> PseudoRandomString\Generators\McryptGenerator (deprecate)
    - OpenSslPseudoRandomStringGenerator -> PseudoRandomString\Generators\OpenSslGenerator
    - RandomBytesPseudoRandomStringGenerator -> PseudoRandomString\Generators\RandomBytesGenerator
    - UrandomPseudoRandomStringGenerator -> PseudoRandomString\Generators\UrandomGenerator
    - PseudoRandomStringGeneratorInterface -> PseudoRandomString\Generators\GeneratorInterface
    - PseudoRandomStringGeneratorFactory -> PseudoRandomString\GeneratorFactory
- **Deprecate** (remove in next version):
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
  - PseudoRandomString\
    - McryptPseudoRandomStringGenerator
    - OpenSslPseudoRandomStringGenerator
    - PseudoRandomStringGeneratorFactory
    - PseudoRandomStringGeneratorInterface
    - RandomBytesPseudoRandomStringGenerator
    - UrandomPseudoRandomStringGenerator
    - **Generators\McryptGenerator** 

## What's new

- Global exception: \One23\GraphSdk\Exception
- ...
  
## Usage

```php
use \One23\GraphSdk\Facebook;

$fb = new Facebook([
  'app_id' => '{app-id}',
  'app_secret' => '{app-secret}',
  'default_graph_version' => 'v15.0',
  //'default_access_token' => '{access-token}', // optional
]);

// Use one of the helper classes to get a Facebook\Authentication\AccessToken entity.
//   $helper = $fb->getRedirectLoginHelper();
//   $helper = $fb->getJavaScriptHelper();
//   $helper = $fb->getCanvasHelper();
//   $helper = $fb->getPageTabHelper();

try {
  // Get the \Facebook\GraphNodes\GraphUser object for the current user.
  // If you provided a 'default_access_token', the '{access-token}' is optional.
  $response = $fb->get('/me', '{access-token}');
} catch(\Facebook\Exceptions\FacebookResponseException $e) {
  // When Graph returns an error
  echo 'Graph returned an error: ' . $e->getMessage();
  exit;
} catch(\Facebook\Exceptions\FacebookSDKException $e) {
  // When validation fails or other local issues
  echo 'Facebook SDK returned an error: ' . $e->getMessage();
  exit;
}

$me = $response->getGraphUser();
echo 'Logged in as ' . $me->getName();
```

# Security

If you discover any security related issues, please email eugene@krivoruchko.info instead of using the issue tracker.