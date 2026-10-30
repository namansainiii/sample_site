<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Store Identity
    |--------------------------------------------------------------------------
    |
    | Everything customer-facing (logo text, invoices, emails) reads from here,
    | so rebranding the shop is a matter of editing the .env file.
    |
    */

    'name' => env('STORE_NAME', 'BoltMart Hardware'),
    'tagline' => env('STORE_TAGLINE', 'Industrial fasteners & hardware, delivered.'),
    'phone' => env('STORE_PHONE', '+91 98765 43210'),
    'email' => env('STORE_EMAIL', 'sales@boltmart.test'),
    'whatsapp' => env('STORE_WHATSAPP', '919876543210'),
    'address' => env('STORE_ADDRESS', 'Shop 12, Industrial Area Phase 2, Ludhiana, Punjab 141003'),
    'state' => env('STORE_STATE', 'Punjab'),
    'gstin' => env('STORE_GSTIN', '03ABCDE1234F1Z5'),

    /*
    |--------------------------------------------------------------------------
    | Pricing & Shipping
    |--------------------------------------------------------------------------
    |
    | Catalogue prices are stored excluding GST. Shipping is a flat fee that is
    | waived once the pre-tax subtotal reaches the free shipping threshold.
    |
    */

    'default_gst_rate' => 18,
    'shipping_fee' => (float) env('STORE_SHIPPING_FEE', 99),
    'free_shipping_threshold' => (float) env('STORE_FREE_SHIPPING_ABOVE', 1999),
    'low_stock_threshold' => 10,

    'states' => [
        'Andhra Pradesh', 'Arunachal Pradesh', 'Assam', 'Bihar', 'Chhattisgarh', 'Goa', 'Gujarat',
        'Haryana', 'Himachal Pradesh', 'Jharkhand', 'Karnataka', 'Kerala', 'Madhya Pradesh',
        'Maharashtra', 'Manipur', 'Meghalaya', 'Mizoram', 'Nagaland', 'Odisha', 'Punjab',
        'Rajasthan', 'Sikkim', 'Tamil Nadu', 'Telangana', 'Tripura', 'Uttar Pradesh',
        'Uttarakhand', 'West Bengal', 'Andaman and Nicobar Islands', 'Chandigarh',
        'Dadra and Nagar Haveli and Daman and Diu', 'Delhi', 'Jammu and Kashmir', 'Ladakh',
        'Lakshadweep', 'Puducherry',
    ],

];
