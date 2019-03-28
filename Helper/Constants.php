<?php

namespace ReversIo\RMA\Helper;

class Constants
{
    const CACHE_KEY_REVERSIO_API_JWT_TOKEN = 'reversio_api_jwt_token';
    const CACHE_KEY_REVERSIO_MODELTYPES = 'reversio_modeltypes';
    const CACHE_KEY_REVERSIO_BRANDS = 'reversio_brands';

    const UNKNOWN_BRAND_NAME = 'Unknown Brand';

    const REVERSIO_MODEL_TYPE_ATTRIBUTE_CODE = 'reversio_modeltype';

    const REVERSIO_RMA_ATTRIBUTE_GROUP_NAME = 'ReversIo RMA';
    
    const REVERSIO_SYNC_STATUS_NOT_SYNC = 'not_sync';
    const REVERSIO_SYNC_STATUS_SYNC_ERROR = 'sync_error';
    const REVERSIO_SYNC_STATUS_SYNC_SUCCESS = 'sync_success';
    
    const REVERSIO_ENVIRONMENT_PROD = 'prod';
    const REVERSIO_ENVIRONMENT_TEST = 'test';
    const REVERSIO_ENVIRONMENT_CUSTOM = 'custom';
    
    const REVERSIO_TEST_API_URL = 'https://demo-customer-api.revers.io/api/v1/';
    const REVERSIO_PROD_API_URL = 'https://customer-api.revers.io/api/v1/';
}
