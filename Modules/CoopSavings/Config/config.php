<?php

return [
    'name' => 'CoopSavings',

    'alias' => 'coopsavings',

    'logo' => 'Modules/CoopSavings/Resources/assets/coop.png',

    'options' => [
        ['label' => __('Settings'), 'type' => 'modal', 'url' => 'paystack.edit'],
        ['label' => __('Savings Documentation'), 'target' => '_blank', 'url' => '#']
    ],

    'validation' => [
        'rules' => [
            'secretKey' => 'required',
            'publicKey' => 'required',
            'sandbox' => 'required',
        ],
        'attributes' => [
            'secretKey' => __('Secret Key'),
            'publicKey' => __('Public Key'),
            'sandbox' => __('Please specify sandbox enabled/disabled.')
        ]
    ],
    'fields' => [
        'secretKey' => [
            'label' => __('Secret Key'),
            'type' => 'text',
            'required' => true
        ],
        'publicKey' => [
            'label' => __('Public Key'),
            'type' => 'text',
            'required' => true
        ],
        'instruction' => [
            'label' => __('Instruction'),
            'type' => 'textarea',
        ],
        'sandbox' => [
            'label' => __('Sandbox'),
            'type' => 'select',
            'required' => true,
            'options' => [
                'Enabled' => 1,
                'Disabled' =>  0
            ]
        ],
        'status' => [
            'label' => 'Status',
            'type' => 'select',
            'required' => true,
            'options' => [
                'Active' => 1,
                'Inactive' =>  0
            ],
            'note' => __("We cannot process you payment if savings balance is less than amount required 'GHS'"),
        ]
    ],

    'ssl_verify_host' => false,

    'ssl_verify_peer' => false,


    'store_route' => 'coopsavings.store',
];
