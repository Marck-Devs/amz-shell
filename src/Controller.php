<?php
namespace MarckDevs\AmzShell;

use ClouSale\AmazonSellingPartnerAPI\Api\FeedsApi;
use ClouSale\AmazonSellingPartnerAPI\ApiException;
use ClouSale\AmazonSellingPartnerAPI\AssumeRole;
use ClouSale\AmazonSellingPartnerAPI\Configuration;
use ClouSale\AmazonSellingPartnerAPI\Models\Feeds\CreateFeedDocumentSpecification;
use ClouSale\AmazonSellingPartnerAPI\Models\Feeds\CreateFeedSpecification;
use ClouSale\AmazonSellingPartnerAPI\SellingPartnerOAuth;
use Exception;
use MarckDevs\SimpleLogger\SimpleLogger;
use Throwable;

class Controller{
    private $wrapper = array(); # wrapper for all properties
    private static $log;

    public function __construct($config){
        $this->wrapper["config"] = $config;
        if(!isset(self::$log)){
            self::$log = new SimpleLogger(get_class($this));
        }
    }
    
    public function __get($name){
        self::$log->debug("GET $name");
        return $this->wrapper[$name];
    }
    
    public function __set($name, $value){
        self::$log->debug("SET $name = $value");
        $this->wrapper[$name] = $value;
    }

    private function createConfig(){
        try{
            $accessToken = SellingPartnerOAuth::getAccessTokenFromRefreshToken(
                $this->config->refresh_token,
                $this->config->client_id,
                $this->config->client_secret
            );
    
            $assumeRole = AssumeRole::assume(
                $this->config->region,
                $this->config->access_key,
                $this->config->secret_key,
                $this->config->role_arn
            );

            $configuration = Configuration::getDefaultConfiguration();
            $configuration->setHost($this->config->endpoint);
            $configuration->setAccessToken($accessToken);
            $configuration->setAccessKey($assumeRole->getAccessKeyId());
            $configuration->setSecretKey($assumeRole->getSecretAccessKey());
            $configuration->setRegion($this->config->region);
            $configuration->setSecurityToken($assumeRole->getSessionToken());
            return $configuration;
        }catch(Throwable $e){
            self::$log->info($e->getMessage());
        }
        return false;
    }

    private function createFDoc(){
        $config = $this->createConfig();
        if(!$config){
            $time = time();
            $msg = <<<JSON
            {
                "error": "Error with configuration, enable verbose option to see error",
                "time": $time
            }
            JSON;
            echo $msg;
            exit(1);
        }
        $api = new FeedsApi($config);
        $requesBody = new CreateFeedDocumentSpecification([
            'content_type' => $this->config->content_type
        ]);
        self::$log->info("Request body create for feed document");
        try{
            $response = $api->createFeedDocument($requesBody);
            return $response->getPayload();
        }catch(ApiException $e){
            self::$log->error($e->getResponseBody());
        }
        return false;
    }
    
    public function uploadXML(){
        self::$log->info("Create the feed document to the upload");
        $feedDoc = $this->createFDoc();
        self::$log->info("Getting access conf");
        $config = $this->createConfig();
        $api = new FeedsApi($config);
        try{
            self::$log->info("Uploading the xml");
            $response = $api->uploadFeedDocument($feedDoc, $this->config->content_type, $this->config->file);
            if(strtolower($response) != 'done'){
                $time = time();
                $error = <<<JSON
                    {
                        "error": "document upload error",
                        "body": $response,
                        "time": $time
                    }
                JSON;
                echo $error;
                exit(1);
            }
            $feed = new CreateFeedSpecification([
                "feed_type" => $this->config->feed_type,
                "marketplace_ids" => $this->config->marketplaces_ids,
                "input_feed_document_id" => $feedDoc->getFeedDocumentId()
            ]);
            self::$log->info("Creating the feed");
            sleep(1);
            $feedResponse =  $api->createFeed($feed);
            echo $feedResponse->getPayload() . "\n";
        } catch (ApiException $e){
            $time = time();
            $error = <<<JSON
                {
                    "error": "document upload error",
                    "body": $response,
                    "time": $time
                }
            JSON;
            echo $error;
        }
    }

    public function getFeed($id){
        self::$log->info("Getting access conf");
        $config = $this->createConfig();
        $api = new FeedsApi($config);
        try{
            self::$log->info("Getting feed info");
            $feed = $api->getFeed($id);
            $response = $feed->getPayload();
            echo json_encode($response, JSON_PRETTY_PRINT);
        }catch(ApiException $e){
            $time = time();
            $error = <<<JSON
                {
                    "error": "Error while getting feed data",
                    "body": $response,
                    "time": $time
                }
            JSON;
            echo $error;
        }
    }

    public function getReport($docId){
        self::$log->info("Getting access conf");
        $config = $this->createConfig();
        $api = new FeedsApi($config);
        try{
            self::$log->info("Getting feed info");
            $docResponse = $api->getFeedDocument($docId);
            $file = $api->downloadFeedProcessingReport($docResponse->getPayload());
            echo json_encode($file, JSON_PRETTY_PRINT);
        }catch(ApiException $e){
            $time = time();
            $error = <<<JSON
                {
                    "error": "Error while getting feed data",
                    "body": $docResponse,
                    "time": $time
                }
            JSON;
            echo $error;
        }
    }

}
    
