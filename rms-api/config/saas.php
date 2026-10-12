<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Local / Production Tenant Base Domain
    |--------------------------------------------------------------------------
    */

    'tenant_base_domain' =>
        env(
            'TENANT_BASE_DOMAIN',
            'local'
        ),

];