<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/ads/googleads/v8/enums/resource_limit_type.proto

namespace GPBMetadata\Google\Ads\GoogleAds\V8\Enums;

class ResourceLimitType
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
�
7google/ads/googleads/v8/enums/resource_limit_type.protogoogle.ads.googleads.v8.enums"�
ResourceLimitTypeEnum"�
ResourceLimitType
UNSPECIFIED 
UNKNOWN
CAMPAIGNS_PER_CUSTOMER
BASE_CAMPAIGNS_PER_CUSTOMER%
!EXPERIMENT_CAMPAIGNS_PER_CUSTOMERi 
HOTEL_CAMPAIGNS_PER_CUSTOMER)
%SMART_SHOPPING_CAMPAIGNS_PER_CUSTOMER
AD_GROUPS_PER_CAMPAIGN#
AD_GROUPS_PER_SHOPPING_CAMPAIGN 
AD_GROUPS_PER_HOTEL_CAMPAIGN	*
&REPORTING_AD_GROUPS_PER_LOCAL_CAMPAIGN
(
$REPORTING_AD_GROUPS_PER_APP_CAMPAIGN(
$MANAGED_AD_GROUPS_PER_SMART_CAMPAIGN4"
AD_GROUP_CRITERIA_PER_CUSTOMER\'
#BASE_AD_GROUP_CRITERIA_PER_CUSTOMER-
)EXPERIMENT_AD_GROUP_CRITERIA_PER_CUSTOMERk"
AD_GROUP_CRITERIA_PER_CAMPAIGN"
CAMPAIGN_CRITERIA_PER_CUSTOMER\'
#BASE_CAMPAIGN_CRITERIA_PER_CUSTOMER-
)EXPERIMENT_CAMPAIGN_CRITERIA_PER_CUSTOMERl!
WEBPAGE_CRITERIA_PER_CUSTOMER&
"BASE_WEBPAGE_CRITERIA_PER_CUSTOMER,
(EXPERIMENT_WEBPAGE_CRITERIA_PER_CUSTOMER+
\'COMBINED_AUDIENCE_CRITERIA_PER_AD_GROUP5
1CUSTOMER_NEGATIVE_PLACEMENT_CRITERIA_PER_CUSTOMER;
7CUSTOMER_NEGATIVE_YOUTUBE_CHANNEL_CRITERIA_PER_CUSTOMER
CRITERIA_PER_AD_GROUP
LISTING_GROUPS_PER_AD_GROUP*
&EXPLICITLY_SHARED_BUDGETS_PER_CUSTOMER*
&IMPLICITLY_SHARED_BUDGETS_PER_CUSTOMER+
\'COMBINED_AUDIENCE_CRITERIA_PER_CAMPAIGN"
NEGATIVE_KEYWORDS_PER_CAMPAIGN$
 NEGATIVE_PLACEMENTS_PER_CAMPAIGN
GEO_TARGETS_PER_CAMPAIGN#
NEGATIVE_IP_BLOCKS_PER_CAMPAIGN 
PROXIMITIES_PER_CAMPAIGN!(
$LISTING_SCOPES_PER_SHOPPING_CAMPAIGN",
(LISTING_SCOPES_PER_NON_SHOPPING_CAMPAIGN#$
 NEGATIVE_KEYWORDS_PER_SHARED_SET$&
"NEGATIVE_PLACEMENTS_PER_SHARED_SET%-
)SHARED_SETS_PER_CUSTOMER_FOR_TYPE_DEFAULT(>
:SHARED_SETS_PER_CUSTOMER_FOR_NEGATIVE_PLACEMENT_LIST_LOWER);
7HOTEL_ADVANCE_BOOKING_WINDOW_BID_MODIFIERS_PER_AD_GROUP,#
BIDDING_STRATEGIES_PER_CUSTOMER-!
BASIC_USER_LISTS_PER_CUSTOMER/#
LOGICAL_USER_LISTS_PER_CUSTOMER0"
BASE_AD_GROUP_ADS_PER_CUSTOMER5(
$EXPERIMENT_AD_GROUP_ADS_PER_CUSTOMER6
AD_GROUP_ADS_PER_CAMPAIGN7#
TEXT_AND_OTHER_ADS_PER_AD_GROUP8
IMAGE_ADS_PER_AD_GROUP9#
SHOPPING_SMART_ADS_PER_AD_GROUP:&
"RESPONSIVE_SEARCH_ADS_PER_AD_GROUP;
APP_ADS_PER_AD_GROUP<#
APP_ENGAGEMENT_ADS_PER_AD_GROUP=
LOCAL_ADS_PER_AD_GROUP>
VIDEO_ADS_PER_AD_GROUP?&
"LEAD_FORM_ASSET_LINKS_PER_CAMPAIGND
VERSIONS_PER_ADR
USER_FEEDS_PER_CUSTOMERZ
SYSTEM_FEEDS_PER_CUSTOMER[
FEED_ATTRIBUTES_PER_FEED\\
FEED_ITEMS_PER_CUSTOMER^
CAMPAIGN_FEEDS_PER_CUSTOMER_$
 BASE_CAMPAIGN_FEEDS_PER_CUSTOMER`*
&EXPERIMENT_CAMPAIGN_FEEDS_PER_CUSTOMERm
AD_GROUP_FEEDS_PER_CUSTOMERa$
 BASE_AD_GROUP_FEEDS_PER_CUSTOMERb*
&EXPERIMENT_AD_GROUP_FEEDS_PER_CUSTOMERn
AD_GROUP_FEEDS_PER_CAMPAIGNc
FEED_ITEM_SETS_PER_CUSTOMERd 
FEED_ITEMS_PER_FEED_ITEM_SETe%
!CAMPAIGN_EXPERIMENTS_PER_CUSTOMERp(
$EXPERIMENT_ARMS_PER_VIDEO_EXPERIMENTq
OWNED_LABELS_PER_CUSTOMERs
LABELS_PER_CAMPAIGNu
LABELS_PER_AD_GROUPv
LABELS_PER_AD_GROUP_ADw!
LABELS_PER_AD_GROUP_CRITERIONx
TARGET_CUSTOMERS_PER_LABELy\'
#KEYWORD_PLANS_PER_USER_PER_CUSTOMERz3
/KEYWORD_PLAN_AD_GROUP_KEYWORDS_PER_KEYWORD_PLAN{+
\'KEYWORD_PLAN_AD_GROUPS_PER_KEYWORD_PLAN|3
/KEYWORD_PLAN_NEGATIVE_KEYWORDS_PER_KEYWORD_PLAN}+
\'KEYWORD_PLAN_CAMPAIGNS_PER_KEYWORD_PLAN~$
CONVERSION_ACTIONS_PER_CUSTOMER�!
BATCH_JOB_OPERATIONS_PER_JOB�
BATCH_JOBS_PER_CUSTOMER�9
4HOTEL_CHECK_IN_DATE_RANGE_BID_MODIFIERS_PER_AD_GROUP�B�
!com.google.ads.googleads.v8.enumsBResourceLimitTypeProtoPZBgoogle.golang.org/genproto/googleapis/ads/googleads/v8/enums;enums�GAA�Google.Ads.GoogleAds.V8.Enums�Google\\Ads\\GoogleAds\\V8\\Enums�!Google::Ads::GoogleAds::V8::Enumsbproto3'
        , true);
        static::$is_initialized = true;
    }
}

