<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/ads/googleads/v8/errors/ad_group_feed_error.proto

namespace GPBMetadata\Google\Ads\GoogleAds\V8\Errors;

class AdGroupFeedError
{
    public static $is_initialized = false;

    public static function initOnce() {
        $pool = \Google\Protobuf\Internal\DescriptorPool::getGeneratedPool();
        if (static::$is_initialized == true) {
          return;
        }
        \GPBMetadata\Google\Api\Http::initOnce();
        \GPBMetadata\Google\Api\Annotations::initOnce();
        $pool->internalAddGeneratedFile(
            '
�
8google/ads/googleads/v8/errors/ad_group_feed_error.protogoogle.ads.googleads.v8.errors"�
AdGroupFeedErrorEnum"�
AdGroupFeedError
UNSPECIFIED 
UNKNOWN,
(FEED_ALREADY_EXISTS_FOR_PLACEHOLDER_TYPE"
CANNOT_CREATE_FOR_REMOVED_FEED
ADGROUP_FEED_ALREADY_EXISTS*
&CANNOT_OPERATE_ON_REMOVED_ADGROUP_FEED
INVALID_PLACEHOLDER_TYPE,
(MISSING_FEEDMAPPING_FOR_PLACEHOLDER_TYPE&
"NO_EXISTING_LOCATION_CUSTOMER_FEEDB�
"com.google.ads.googleads.v8.errorsBAdGroupFeedErrorProtoPZDgoogle.golang.org/genproto/googleapis/ads/googleads/v8/errors;errors�GAA�Google.Ads.GoogleAds.V8.Errors�Google\\Ads\\GoogleAds\\V8\\Errors�"Google::Ads::GoogleAds::V8::Errorsbproto3'
        , true);
        static::$is_initialized = true;
    }
}

