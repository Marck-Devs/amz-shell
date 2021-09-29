<?php
namespace MarckDevs\AmzShell;

use ClouSale\AmazonSellingPartnerAPI\Api\FeedsApi;
use ClouSale\AmazonSellingPartnerAPI\ApiException;
use ClouSale\AmazonSellingPartnerAPI\AssumeRole;
use ClouSale\AmazonSellingPartnerAPI\Configuration;
use ClouSale\AmazonSellingPartnerAPI\Models\Feeds\CreateFeedDocumentSpecification;
use ClouSale\AmazonSellingPartnerAPI\Models\Feeds\CreateFeedSpecification;
use ClouSale\AmazonSellingPartnerAPI\SellingPartnerOAuth;
use MarckDevs\SimpleLogger\SimpleLogger;
use Throwable;

class Controller{
    private $wrapper = array(); # wrapper for all properties
    private static $log;

    private function __construct($config){
        $this->wrapper["config"] = $config;
        if(!isset(self::$log)){
            self::$log = new SimpleLogger(get_class($this));
        }
    }
    
    public function __get($name){
        return $this->wrapper[$name];
    }
    
    public function __set($name, $value){
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
            throw new Throwable($msg);
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
        $config = $this->createConfig();
        $api = new FeedsApi($config);
        try{
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
                throw new Throwable($error);
            }
            $feed = new CreateFeedSpecification([
                "feed_type" => $this->config->feed_type,
                "marketplace_ids" => $this->config->marketplaces_ids,
                "input_feed_document_id" => $feedDoc->getFeedDocumentId()
            ]);
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
}
    
