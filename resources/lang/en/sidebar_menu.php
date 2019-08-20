<?php
/**
 * Created by PhpStorm.
 * User: benjaminmanford
 * Date: 1/30/17
 * Time: 3:38 PM
 */

return [

    'callens' => [
        'links' => [
            [
                'title' => 'Partners',
                'route' => 'callens.partners.index',
                'active_link' => 'callens.partners',
                'roles' => ['app-owner'],
                'permissions' => [],
                'icon' => 'fa fa-building'
            ],
            [
                'title' => 'Employers',
                'route' => 'callens.employers.index',
                'active_link' => 'callens.employers',
                'roles' => ['app-owner'],
                'permissions' => ['add-employer', 'edit-employer', 'delete-employer'],
                'icon' => 'fa fa-suitcase'
            ],
            [
                'title' => 'Roles',
                'route' => 'roles.index',
                'active_link' => 'roles',
                'roles' => ['app-owner'],
                'permissions' => ['add-role', 'edit-role', 'delete-role'],
                'icon' => 'fa fa-list'
            ],
            [
                'title' => 'Users',
                'route' => 'users.index',
                'active_link' => 'users',
                'roles' => ['app-owner'],
                'permissions' => ['add-user', 'edit-user', 'delete-user'],
                'icon' => 'font-icon font-icon-users'
            ],
            [
                'title' => 'Loan Types',
                'route' => 'loan_products.types.index',
                'active_link' => 'loan_products',
                'roles' => ['app-owner'],
                'permissions' => [],
                'icon' => 'font-icon font-icon-users'
            ]
        ]
    ],

    'partner' => [
        'links' => [
            [
                'title' => 'Roles',
                'route' => 'roles.index',
                'active_link' => 'roles',
                'roles' => ['account-owner'],
                'permissions' => ['add-role', 'edit-role', 'delete-role'],
                'icon' => 'fa fa-list'
            ],
            [
                'title' => 'Users',
                'route' => 'users.index',
                'active_link' => 'users',
                'roles' => ['account-owner'],
                'permissions' => ['add-user', 'edit-user', 'delete-user'],
                'icon' => 'font-icon font-icon-users'
            ],
            [
                'title' => 'Employers',
                'route' => 'partner.employers.index',
                'active_link' => 'partner.employers',
                'roles' => ['account-owner'],
                'permissions' => ['add-employer', 'edit-employer', 'delete-employer'],
                'icon' => 'fa fa-suitcase'
            ],
            [
                'title' => 'Approvals',
                'route' => 'approval_levels.index',
                'active_link' => 'approval_levels',
                'roles' => ['account-owner'],
                'permissions' => [
                    'add-approval-level', 'edit-approval-level', 'delete-approval-level'
                ],
                'icon' => 'fa fa-check-circle'
            ],
            [
                'title' => 'Products',
                'route' => 'loan_products.index',
                'active_link' => 'loan_products',
                'roles' => ['account-owner'],
                'permissions' => ['add-loan-product', 'edit-loan-product', 'delete-loan-product'],
                'icon' => 'font-icon font-icon-post'
            ],
            [
                'title' => 'Loan Requests',
                'route' => 'loan_applications.index',
                'active_link' => 'loan_applications',
                'roles' => ['account-owner'],
                'permissions' => [],
                'icon' => 'font-icon font-icon-revers'
            ]
        ]
    ],

    'employer' => [
        'links' => [
            [
                'title' => 'Roles',
                'route' => 'roles.index',
                'active_link' => 'roles',
                'roles' => [],
                'permissions' => ['add-role', 'edit-role', 'delete-role'],
                'icon' => 'fa fa-list'
            ],
            [
                'title' => 'Users',
                'route' => 'users.index',
                'active_link' => 'users',
                'roles' => [],
                'permissions' => ['add-user', 'edit-user', 'delete-user'],
                'icon' => 'font-icon font-icon-users'
            ],
            [
                'title' => 'Approvals',
                'route' => 'approval_levels.index',
                'active_link' => 'approval_levels',
                'roles' => [],
                'permissions' => [
                    'add-approval-level', 'edit-approval-level', 'delete-approval-level'
                ],
                'icon' => 'fa fa-check-circle'
            ],
            [
                'title' => 'Loan Requests',
                'route' => 'loan_applications.index',
                'active_link' => 'loan_applications',
                'roles' => [],
                'permissions' => [],
                'icon' => 'font-icon font-icon-revers'
            ]
        ]
    ]
];