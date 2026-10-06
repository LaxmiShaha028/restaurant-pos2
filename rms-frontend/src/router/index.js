 /*
|--------------------------------------------------------------------------
| Vue Router Imports
|--------------------------------------------------------------------------
*/

import {
  createRouter,
  createWebHistory,
} from 'vue-router'


/*
|--------------------------------------------------------------------------
| Authentication Store
|--------------------------------------------------------------------------
*/

import {
  useAuthStore,
} from '@/stores/auth'


/*
|--------------------------------------------------------------------------
| Tenant Entry View
|--------------------------------------------------------------------------
*/

import TenantEntryView
  from '@/views/tenant/TenantEntry.vue'


/*
|--------------------------------------------------------------------------
| Authentication and Error Views
|--------------------------------------------------------------------------
*/

import LoginView
  from '@/views/LoginView.vue'

import ForbiddenView
  from '@/views/ForbiddenView.vue'

import NotFoundView
  from '@/views/NotFoundView.vue'


/*
|--------------------------------------------------------------------------
| Accounting Report Components
|--------------------------------------------------------------------------
*/

import ProfitLossReport
  from '@/components/reports/accounting/ProfitLossReport.vue'

import BalanceSheetReport
  from '@/components/reports/accounting/BalanceSheetReport.vue'

import TrialBalanceReport
  from '@/components/reports/accounting/TrialBalanceReport.vue'

import GeneralLedgerReport
  from '@/components/reports/accounting/GeneralLedgerReport.vue'

import AccountingDashboard
from '@/components/reports/accounting/AccountingDashboard.vue'


/*
|--------------------------------------------------------------------------
| Main Application Layout
|--------------------------------------------------------------------------
*/

import DashboardLayout
  from '@/layouts/DashboardLayout.vue'


/*
|--------------------------------------------------------------------------
| Super Admin Layout
|--------------------------------------------------------------------------
*/

import SuperAdminLayout
  from '@/layouts/super-admin/SuperAdminLayout.vue'


/*
|--------------------------------------------------------------------------
| Dashboard Views
|--------------------------------------------------------------------------
*/

import DashboardView
  from '@/views/DashboardView.vue'

import AdminOverviewView
  from '@/views/AdminOverviewView.vue'

import ManagerOverviewView
  from '@/views/ManagerOverviewView.vue'


/*
|--------------------------------------------------------------------------
| Shared Placeholder View
|--------------------------------------------------------------------------
*/

import ModulePlaceholderView
  from '@/views/ModulePlaceholderView.vue'


/*
|--------------------------------------------------------------------------
| Order Management Views
|--------------------------------------------------------------------------
*/

import OrderManagement
  from '@/views/orders/OrderManagement.vue'

import CreateOrderView
  from '@/views/orders/CreateOrderView.vue'

import OrderDetailsView
  from '@/views/orders/OrderDetailsView.vue'


/*
|--------------------------------------------------------------------------
| Kitchen Management Views
|--------------------------------------------------------------------------
*/

import KitchenOverviewView
  from '@/views/kitchen/KitchenOverviewView.vue'

import KitchenOrderDetailsView
  from '@/views/kitchen/KitchenOrderDetailsView.vue'


/*
|--------------------------------------------------------------------------
| Billing Management View
|--------------------------------------------------------------------------
*/

import BillingOverviewView
  from '@/views/billing/BillingOverviewView.vue'


/*
|--------------------------------------------------------------------------
| Table Management View
|--------------------------------------------------------------------------
*/

import TableManagementView
  from '@/views/tables/TableManagement.vue'


/*
|--------------------------------------------------------------------------
| Menu Management View
|--------------------------------------------------------------------------
*/

import MenuManagementView
  from '@/views/menu/MenuManagementView.vue'


/*
|--------------------------------------------------------------------------
| Customer Management Views
|--------------------------------------------------------------------------
*/

import CustomerManagementView
  from '@/views/customers/CustomerManagementView.vue'

import CustomerEditView
  from '@/components/customers/CustomerEditView.vue'

import CustomerDetailsView
  from '@/views/customers/CustomerDetailsView.vue'


/*
|--------------------------------------------------------------------------
| Supplier Management View
|--------------------------------------------------------------------------
*/

import SupplierManagementView
  from '@/views/suppliers/SupplierManagementView.vue'


/*
|--------------------------------------------------------------------------
| Staff Management View
|--------------------------------------------------------------------------
*/

import StaffManagementView
  from '@/views/staff/StaffManagementView.vue'


/*
|--------------------------------------------------------------------------
| Salary Management View
|--------------------------------------------------------------------------
*/

import SalaryManagementView
  from '@/views/salary/SalaryManagementView.vue'


/*
|--------------------------------------------------------------------------
| Inventory Management View
|--------------------------------------------------------------------------
*/

import InventoryManagementView
  from '@/views/inventory/InventoryManagementView.vue'


/*
|--------------------------------------------------------------------------
| Reports View
|--------------------------------------------------------------------------
*/

import ReportsView
  from '@/views/reports/ReportsManagementView.vue'


/*
|--------------------------------------------------------------------------
| System Settings View
|--------------------------------------------------------------------------
*/

import SystemSettingsView
  from '@/views/settings/SystemSettingsView.vue'


/*
|--------------------------------------------------------------------------
| Super Admin Views
|--------------------------------------------------------------------------
*/

import SuperAdminLoginView
  from '@/views/super-admin/SuperAdminLoginView.vue'

import SuperAdminDashboardView
  from '@/views/super-admin/Dashboard.vue'


/*
|--------------------------------------------------------------------------
| Role Helper
|--------------------------------------------------------------------------
*/

function getNormalizedRoleName(
  authStore
) {

  return String(
    authStore.user?.role?.name
    ??
    authStore.user?.role_name
    ??
    ''
  )
    .trim()
    .toLowerCase()

}


/*
|--------------------------------------------------------------------------
| Super Admin Token Helper
|--------------------------------------------------------------------------
*/

function hasSuperAdminToken() {

  return Boolean(
    localStorage.getItem(
      'rms_central_access_token'
    )
  )

}


/*
|--------------------------------------------------------------------------
| Application Router
|--------------------------------------------------------------------------
*/

const router = createRouter({

  history: createWebHistory(
    import.meta.env.BASE_URL
  ),


  routes: [

    /*
    |--------------------------------------------------------------------------
    | Login Route
    |--------------------------------------------------------------------------
    */

    {
      path: '/login',

      name: 'login',

      component:
        LoginView,

      meta: {

        guestOnly: true,

        title: 'Login',

      },

    },


    /*
    |--------------------------------------------------------------------------
    | Super Admin Login
    |--------------------------------------------------------------------------
    */

    {
      path:
        '/super-admin/login',

      name:
        'super-admin-login',

      component:
        SuperAdminLoginView,

      meta: {

        guestSuperAdminOnly:
          true,

        title:
          'Super Admin Login',

      },

    },


    /*
    |--------------------------------------------------------------------------
    | Super Admin Protected Layout
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    | - /super-admin/login stays outside this layout.
    | - Protected Super Admin pages render inside SuperAdminLayout.
    | - Parent requiresSuperAdmin keeps the central-token guard active.
    |
    */

    {
      path:
        '/super-admin',

      component:
        SuperAdminLayout,

      meta: {

        requiresSuperAdmin:
          true,

      },

      children: [

        /*
        |--------------------------------------------------------------------------
        | Super Admin Root
        |--------------------------------------------------------------------------
        */

        {
          path: '',

          redirect: {

            name:
              'super-admin-dashboard',

          },

        },


        /*
        |--------------------------------------------------------------------------
        | Super Admin Dashboard
        |--------------------------------------------------------------------------
        */

        {
          path:
            'dashboard',

          name:
            'super-admin-dashboard',

          component:
            SuperAdminDashboardView,

          meta: {

            requiresSuperAdmin:
              true,

            title:
              'Super Admin Dashboard',

          },

        },


        /*
        |--------------------------------------------------------------------------
        | Restaurants — All
        |--------------------------------------------------------------------------
        */

        {
          path:
            'restaurants',

          name:
            'super-admin-restaurants',

          component: () =>
            import(
              '@/views/super-admin/Restaurants.vue'
            ),

          meta: {

            requiresSuperAdmin:
              true,

            title:
              'Restaurants',

          },

        },


        /*
        |--------------------------------------------------------------------------
        | Restaurants — Create
        |--------------------------------------------------------------------------
        */

        {
          path:
            'restaurants/create',

          name:
            'super-admin-create-restaurant',

          component: () =>
            import(
              '@/views/super-admin/CreateRestaurant.vue'
            ),

          meta: {

            requiresSuperAdmin:
              true,

            title:
              'Create Restaurant',

          },

        },


        /*
        |--------------------------------------------------------------------------
        | Restaurants — Suspended
        |--------------------------------------------------------------------------
        */

        {
          path:
            'restaurants/suspended',

          name:
            'super-admin-restaurants-suspended',

          component: () =>
            import(
              '@/views/super-admin/SuspendedRestaurants.vue'
            ),

          meta: {

            requiresSuperAdmin:
              true,

            title:
              'Suspended Restaurants',

          },

        },


        /*
        |--------------------------------------------------------------------------
        | Restaurant Details
        |--------------------------------------------------------------------------
        */

        {
          path:
            'restaurants/:restaurant',

          name:
            'super-admin-restaurant-details',

          component: () =>
            import(
              '@/views/super-admin/RestaurantDetails.vue'
            ),

          props:
            true,

          meta: {

            requiresSuperAdmin:
              true,

            title:
              'Restaurant Details',

          },

        },


        /*
        |--------------------------------------------------------------------------
        | Subscriptions — Plans
        |--------------------------------------------------------------------------
        */

        {
          path:
            'subscriptions/plans',

          name:
            'super-admin-subscription-plans',

          component: () =>
            import(
              '@/views/super-admin/subscriptions/SubscriptionPlans.vue'
            ),

          meta: {

            requiresSuperAdmin:
              true,

            title:
              'Subscription Plans',

          },

        },


        /*
        |--------------------------------------------------------------------------
        | Subscriptions — Active
        |--------------------------------------------------------------------------
        */

        {
          path:
            'subscriptions/active',

          name:
            'super-admin-subscriptions-active',

          component: () =>
            import(
              '@/views/super-admin/subscriptions/ActiveSubscriptions.vue'
            ),

          meta: {

            requiresSuperAdmin:
              true,

            title:
              'Active Subscriptions',

          },

        },


        /*
        |--------------------------------------------------------------------------
        | Subscriptions — Trial
        |--------------------------------------------------------------------------
        */

        {
          path:
            'subscriptions/trial',

          name:
            'super-admin-subscriptions-trial',

          component: () =>
            import(
              '@/views/super-admin/subscriptions/TrialSubscriptions.vue'
            ),

          meta: {

            requiresSuperAdmin:
              true,

            title:
              'Trial Subscriptions',

          },

        },


        /*
        |--------------------------------------------------------------------------
        | Subscriptions — Expired
        |--------------------------------------------------------------------------
        */

        {
          path:
            'subscriptions/expired',

          name:
            'super-admin-subscriptions-expired',

          component: () =>
            import(
              '@/views/super-admin/subscriptions/ExpiredSubscriptions.vue'
            ),

          meta: {

            requiresSuperAdmin:
              true,

            title:
              'Expired Subscriptions',

          },

        },


        /*
        |--------------------------------------------------------------------------
        | Subscriptions — Cancelled
        |--------------------------------------------------------------------------
        */

        {
          path:
            'subscriptions/cancelled',

          name:
            'super-admin-subscriptions-cancelled',

          component: () =>
            import(
              '@/views/super-admin/subscriptions/CancelledSubscriptions.vue'
            ),

          meta: {

            requiresSuperAdmin:
              true,

            title:
              'Cancelled Subscriptions',

          },

        },


        /*
        |--------------------------------------------------------------------------
        | Payments — Methods
        |--------------------------------------------------------------------------
        */

        {
          path:
            'payments/methods',

          name:
            'super-admin-payment-methods',

          component: () =>
            import(
              '@/views/super-admin/payments/PaymentMethods.vue'
            ),

          meta: {

            requiresSuperAdmin:
              true,

            title:
              'Payment Methods',

          },

        },


        /*
        |--------------------------------------------------------------------------
        | Payments — Transactions
        |--------------------------------------------------------------------------
        */

        {
          path:
            'payments/transactions',

          name:
            'super-admin-payments-transactions',

          component: () =>
            import(
              '@/views/super-admin/payments/Transactions.vue'
            ),

          meta: {

            requiresSuperAdmin:
              true,

            title:
              'Payment Transactions',

          },

        },


        /*
        |--------------------------------------------------------------------------
        | Payments — Revenue
        |--------------------------------------------------------------------------
        */

        {
          path:
            'payments/revenue',

          name:
            'super-admin-payments-revenue',

          component: () =>
            import(
              '@/views/super-admin/payments/Revenue.vue'
            ),

          meta: {

            requiresSuperAdmin:
              true,

            title:
              'Revenue',

          },

        },

      ],

    },


    /*
    |--------------------------------------------------------------------------
    | Tenant Entry
    |--------------------------------------------------------------------------
    */

    {
      path:
        '/tenant/:tenantKey',

      name:
        'tenant-entry',

      component:
        TenantEntryView,

      props:
        true,

      meta: {

        title:
          'Restaurant',

      },

    },


    /*
    |--------------------------------------------------------------------------
    | Protected Dashboard Layout
    |--------------------------------------------------------------------------
    */

    {

      path: '/',

      component:
        DashboardLayout,

      meta: {

        requiresAuth:
          true,

        tenantApp:
          true,

      },


      children: [

        /*
        |--------------------------------------------------------------------------
        | Root Dashboard Redirect
        |--------------------------------------------------------------------------
        */

        {
          path: '',

          redirect: {

            name:
              'dashboard',

          },

        },


        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */

        {
          path:
            'dashboard',

          name:
            'dashboard',

          component:
            DashboardView,

          meta: {

            permission:
              'dashboard.view',

            title:
              'Dashboard',

            description:
              'View restaurant operational overview',

            icon:
              'bi bi-speedometer2',

          },

        },


        /*
        |--------------------------------------------------------------------------
        | POS Order — Table Selection
        |--------------------------------------------------------------------------
        */

        {

          path:
            'pos-order',

          name:
            'pos-order-tables',

          component: () =>
            import(
              '@/views/pos/PosTableSelectionView.vue'
            ),

          meta: {

            permission:
              'orders.create',

            title:
              'POS Order',

            description:
              'Select dine-in tables for a fast POS order',

            icon:
              'bi bi-columns-gap',

          },

        },


        /*
        |--------------------------------------------------------------------------
        | POS Order — Main Order Screen
        |--------------------------------------------------------------------------
        */

        {

          path:
            'pos-order/create',

          name:
            'pos-order-create',

          component: () =>
            import(
              '@/views/pos/PosOrderView.vue'
            ),

          meta: {

            permission:
              'orders.create',

            title:
              'POS Order',

            description:
              'Create a dine-in order using the POS interface',

            icon:
              'bi bi-cart-plus',

          },

        },


        /*
        |--------------------------------------------------------------------------
        | Order Management
        |--------------------------------------------------------------------------
        */

        {
          path:
            'orders',

          name:
            'order-management',

          component:
            OrderManagement,

          meta: {

            permission:
              'orders.view',

            title:
              'Order Management',

            description:
              'Create and manage restaurant orders',

            icon:
              'bi bi-receipt',

          },

        },


        /*
        |--------------------------------------------------------------------------
        | Create Order
        |--------------------------------------------------------------------------
        */

        {

          path:
            'orders/create',

          name:
            'order-create',

          component:
            CreateOrderView,

          meta: {

            permission:
              'orders.create',

            title:
              'Create Order',

            description:
              'Create a new restaurant order',

            icon:
              'bi bi-plus-circle',

          },

        },


        /*
        |--------------------------------------------------------------------------
        | Edit Order
        |--------------------------------------------------------------------------
        */

        {

          path:
            'orders/:id/edit',

          name:
            'order-edit',

          component:
            CreateOrderView,

          props:
            true,

          meta: {

            permission:
              'orders.update',

            title:
              'Edit Order',

            description:
              'Update an existing restaurant order',

            icon:
              'bi bi-pencil-square',

          },

        },


        /*
        |--------------------------------------------------------------------------
        | Complete / Pay & Complete
        |--------------------------------------------------------------------------
        */

        {

          path:
            'orders/:id/complete',

          name:
            'order-completion',

          component:
            CreateOrderView,

          props:
            true,

          meta: {

            permission:
              'orders.create',

            title:
              'Complete Order',

            description:
              'Settle a served order and complete the bill',

            icon:
              'bi bi-check2-circle',

          },

        },


        /*
        |--------------------------------------------------------------------------
        | Add More Items To Served Order
        |--------------------------------------------------------------------------
        */

        {

          path:
            'orders/:id/add-more',

          name:
            'order-extension',

          component:
            CreateOrderView,

          props:
            true,

          meta: {

            permission:
              'orders.create',

            title:
              'Add More Items',

            description:
              'Add another kitchen batch to a served restaurant order',

            icon:
              'bi bi-plus-circle',

          },

        },


        /*
        |--------------------------------------------------------------------------
        | Order Details
        |--------------------------------------------------------------------------
        */

        {

          path:
            'orders/:id',

          name:
            'order-details',

          component:
            OrderDetailsView,

          props:
            true,

          meta: {

            permission:
              'orders.view',

            title:
              'Order Details',

            description:
              'View complete order information',

            icon:
              'bi bi-eye',

          },

        },


        /*
        |--------------------------------------------------------------------------
        | Kitchen Display
        |--------------------------------------------------------------------------
        */

        {

          path:
            'kitchen-display',

          name:
            'kitchen-display',

          component:
            KitchenOverviewView,

          meta: {

            permission:
              'kitchen.view',

            title:
              'Kitchen Display',

            description:
              'Manage kitchen orders, assigned chefs and preparation status',

            icon:
              'bi bi-fire',

          },

        },


        /*
        |--------------------------------------------------------------------------
        | Kitchen Order Details
        |--------------------------------------------------------------------------
        */

        {

          path:
            'kitchen-display/:id',

          name:
            'kitchen-order-details',

          component:
            KitchenOrderDetailsView,

          props:
            true,

          meta: {

            permission:
              'kitchen.view',

            title:
              'Kitchen Order Details',

            description:
              'Review menu items, ingredients, notes and kitchen progress',

            icon:
              'bi bi-receipt-cutoff',

          },

        },


        /*
        |--------------------------------------------------------------------------
        | Legacy Kitchen Redirect
        |--------------------------------------------------------------------------
        */

        {

          path:
            'kitchen',

          redirect: {

            name:
              'kitchen-display',

          },

        },


        /*
        |--------------------------------------------------------------------------
        | Table Management
        |--------------------------------------------------------------------------
        */

        {

          path:
            'tables',

          name:
            'tables',

          component:
            TableManagementView,

          meta: {

            permission:
              'tables.manage',

            title:
              'Table Management',

            description:
              'Manage restaurant tables, sections and availability',

            icon:
              'bi bi-grid-3x3-gap-fill',

          },

        },


        /*
        |--------------------------------------------------------------------------
        | Billing
        |--------------------------------------------------------------------------
        */

        {

          path:
            'billing',

          name:
            'billing',

          component:
            BillingOverviewView,

          meta: {

            permission:
              'billing.view',

            title:
              'Billing & Statement',

            description:
              'View sales, settlements, payment modes and staff payment activity',

            icon:
              'bi bi-credit-card',

          },

        },


        /*
        |--------------------------------------------------------------------------
        | Reservations
        |--------------------------------------------------------------------------
        */

        {

          path:
            'reservations',

          name:
            'reservations',

          component: () =>
            import(
              '@/views/reservation/ReservationList.vue'
            ),

          meta: {

            permission:
              'reservations.manage',

            title:
              'Reservations',

            description:
              'Manage restaurant reservations',

            icon:
              'bi bi-calendar2-check',

          },

        },


        {

          path:
            'reservations/create',

          name:
            'reservation-create',

          component: () =>
            import(
              '@/views/reservation/ReservationForm.vue'
            ),

          meta: {

            permission:
              'reservations.manage',

            title:
              'Create Reservation',

            description:
              'Create a restaurant reservation',

            icon:
              'bi bi-calendar-plus',

          },

        },


        /*
        |--------------------------------------------------------------------------
        | Customer Management
        |--------------------------------------------------------------------------
        */

        {

          path:
            'customers',

          name:
            'customers',

          component:
            CustomerManagementView,

          meta: {

            permission:
              'customers.manage',

            title:
              'Customers',

            description:
              'Manage guest and customer information',

            icon:
              'bi bi-people',

          },

        },


        /*
        |--------------------------------------------------------------------------
        | Customer Edit
        |--------------------------------------------------------------------------
        */

        {

          path:
            'customers/:id/edit',

          name:
            'customer-edit',

          component:
            CustomerEditView,

          props:
            true,

          meta: {

            permission:
              'customers.manage',

            title:
              'Edit Customer',

            description:
              'Update customer contact information and status',

            icon:
              'bi bi-pencil-square',

          },

        },


        /*
        |--------------------------------------------------------------------------
        | Customer Details
        |--------------------------------------------------------------------------
        */

        {

          path:
            'customers/:id',

          name:
            'customer-details',

          component:
            CustomerDetailsView,

          props:
            true,

          meta: {

            permission:
              'customers.manage',

            title:
              'Customer Details',

            description:
              'View customer profile and visit history',

            icon:
              'bi bi-person-lines-fill',

          },

        },


        /*
        |--------------------------------------------------------------------------
        | Menu Management
        |--------------------------------------------------------------------------
        */

        {

          path:
            'menu',

          name:
            'menu',

          component:
            MenuManagementView,

          meta: {

            permission:
              'menu.manage',

            title:
              'Menu Management',

            description:
              'Manage menu categories, items, variants and add-ons',

            icon:
              'bi bi-journal-text',

          },

        },


        /*
        |--------------------------------------------------------------------------
        | Inventory Management
        |--------------------------------------------------------------------------
        */

        {

          path:
            'inventory',

          name:
            'inventory',

          component:
            InventoryManagementView,

          meta: {

            permission:
              'inventory.view',

            title:
              'Inventory Management',

            description:
              'Manage recipes, warehouse stock and restaurant stock',

            icon:
              'bi bi-box-seam',

          },

        },


        /*
        |--------------------------------------------------------------------------
        | Suppliers
        |--------------------------------------------------------------------------
        */

        {

          path:
            'suppliers',

          name:
            'suppliers',

          component:
            SupplierManagementView,

          meta: {

            permission:
              'suppliers.manage',

            title:
              'Supplier Management',

            description:
              'Manage restaurant suppliers',

            icon:
              'bi bi-truck',

          },

        },


        /*
        |--------------------------------------------------------------------------
        | Staff Management
        |--------------------------------------------------------------------------
        */

        {

          path:
            'staff',

          name:
            'staff',

          component:
            StaffManagementView,

          meta: {

            permission:
              'staff.manage',

            title:
              'Staff',

            description:
              'Manage restaurant staff members',

            icon:
              'bi bi-person-badge',

          },

        },


        /*
        |--------------------------------------------------------------------------
        | Salary Management — Admin Only
        |--------------------------------------------------------------------------
        */

        {

          path:
            'salary-management',

          name:
            'salary-management',

          component:
            SalaryManagementView,

          meta: {

            adminOnly:
              true,

            title:
              'Salary Management',

            description:
              'Calculate, review and manage employee salaries',

            icon:
              'bi bi-cash-stack',

          },

        },


        /*
        |--------------------------------------------------------------------------
        | Other Income — Admin + Manager Only
        |--------------------------------------------------------------------------
        */

        {

          path:
            'other-income',

          name:
            'other-income',

          component: () =>
            import(
              '@/views/income/OtherIncomeManagementView.vue'
            ),

          meta: {

            allowedRoles: [
              'admin',
              'manager',
            ],

            title:
              'Other Income',

            description:
              'Manage non-POS restaurant income',

            icon:
              'bi bi-graph-up-arrow',

          },

        },


        /*
        |--------------------------------------------------------------------------
        | Chart of Accounts — Admin + Manager Only
        |--------------------------------------------------------------------------
        */

        {

          path:
            'chart-of-accounts',

          name:
            'chart-of-accounts',

          component: () =>
            import(
              '@/views/accounting/ChartOfAccountsView.vue'
            ),

          meta: {

            allowedRoles: [
              'admin',
              'manager',
            ],

            title:
              'Chart of Accounts',

            description:
              'Manage ledger accounts and accounting mappings',

            icon:
              'bi bi-diagram-3',

          },

        },


        /*
        |--------------------------------------------------------------------------
        | Journal Entries — Admin + Manager Only
        |--------------------------------------------------------------------------
        */

        {

          path:
            'journal-entries',

          name:
            'journal-entries',

          component: () =>
            import(
              '@/views/accounting/JournalEntriesView.vue'
            ),

          meta: {

            allowedRoles: [
              'admin',
              'manager',
            ],

            title:
              'Journal Entries',

            description:
              'Post and review double-entry accounting journals',

            icon:
              'bi bi-journal-text',

          },

        },
        


        /*
        |--------------------------------------------------------------------------
        | Expense Management
        |--------------------------------------------------------------------------
        */

        {

          path:
            'expenses',

          name:
            'expenses',

          component: () =>
            import(
              '@/views/expenses/ExpenseManagementView.vue'
            ),

          meta: {

            permission:
              'expenses.manage',

            title:
              'Expense Management',

            description:
              'Manage restaurant expenses',

            icon:
              'bi bi-wallet2',

          },

        },


        /*
        |--------------------------------------------------------------------------
        | Reports
        |--------------------------------------------------------------------------
        */

        {

          path:
            'reports',

          name:
            'reports',

          component:
            ReportsView,

          meta: {

            permission:
              'reports.view',

            title:
              'Reports',

            description:
              'Business performance reports',

            icon:
              'bi bi-bar-chart',

          },

          

          children: [

            {
                path: 'accounting-dashboard',
                name: 'accounting-dashboard',
                component: AccountingDashboard,
                meta: {
                    permission: 'reports.view',
                    title: 'Accounting Dashboard',
                },
            },
            {
    path:
        'business-overview',

    name:
        'business-overview',

    component: () =>
        import(
            '@/views/accounting/BusinessOverview.vue'
        ),

    meta: {

        permission:
            'reports.view',

        title:
            'Business Overview',

    },

},


            /*
            |--------------------------------------------------------------------------
            | Profit & Loss
            |--------------------------------------------------------------------------
            */

            {

              path:
                'profit-loss',

              name:
                'profit-loss',

              component:
                ProfitLossReport,

              meta: {

                permission:
                  'reports.view',

                title:
                  'Profit & Loss',

              },

            },


            /*
            |--------------------------------------------------------------------------
            | Balance Sheet
            |--------------------------------------------------------------------------
            */

            {

              path:
                'balance-sheet',

              name:
                'balance-sheet',

              component:
                BalanceSheetReport,

              meta: {

                permission:
                  'reports.view',

                title:
                  'Balance Sheet',

              },

            },


            /*
            |--------------------------------------------------------------------------
            | Trial Balance
            |--------------------------------------------------------------------------
            */

            {

              path:
                'trial-balance',

              name:
                'trial-balance',

              component:
                TrialBalanceReport,

              meta: {

                permission:
                  'reports.view',

                title:
                  'Trial Balance',

              },

            },


            /*
            |--------------------------------------------------------------------------
            | General Ledger
            |--------------------------------------------------------------------------
            */

            {

              path:
                'general-ledger',

              name:
                'general-ledger',

              component:
                GeneralLedgerReport,

              meta: {

                permission:
                  'reports.view',

                title:
                  'General Ledger',

              },

            },

          ],

        },


        /*
        |--------------------------------------------------------------------------
        | Settings
        |--------------------------------------------------------------------------
        */

        {

          path:
            'settings',

          name:
            'settings',

          component:
            SystemSettingsView,

          meta: {

            permission:
              'settings.manage',

            title:
              'Settings',

            description:
              'Configure restaurant and system settings',

            icon:
              'bi bi-gear',

          },

        },


        /*
        |--------------------------------------------------------------------------
        | Users
        |--------------------------------------------------------------------------
        */

        {

          path:
            'users',

          name:
            'users',

          component: () =>
            import(
              '@/views/users/UserManagement.vue'
            ),

          meta: {

            permission:
              'users.manage',

            title:
              'Users',

            description:
              'Manage system users and roles',

            icon:
              'bi bi-person-gear',

          },

        },


        /*
        |--------------------------------------------------------------------------
        | Audit Logs
        |--------------------------------------------------------------------------
        */

        {

          path:
            'audit-logs',

          name:
            'audit-logs',

          component: () =>
            import(
              '@/views/audit-logs/AuditLogsView.vue'
            ),

          meta: {

            permission:
              'audit_logs.view',

            title:
              'Audit Log',

            description:
              'Review important system activities',

            icon:
              'bi bi-clock-history',

          },

        },


        /*
        |--------------------------------------------------------------------------
        | Admin Overview
        |--------------------------------------------------------------------------
        */

        {

          path:
            'admin',

          name:
            'admin-overview',

          component:
            AdminOverviewView,

          meta: {

            permission:
              'users.manage',

            title:
              'Admin Overview',

            description:
              'View administrative system overview',

            icon:
              'bi bi-shield-check',

          },

        },


        /*
        |--------------------------------------------------------------------------
        | Manager Overview
        |--------------------------------------------------------------------------
        */

        {

          path:
            'manager',

          name:
            'manager-overview',

          component:
            ManagerOverviewView,

          meta: {

            permission:
              'orders.create',

            title:
              'Manager Overview',

            description:
              'View restaurant management overview',

            icon:
              'bi bi-briefcase',

          },

        },

      ],

    },


    /*
    |--------------------------------------------------------------------------
    | Access Denied
    |--------------------------------------------------------------------------
    */

    {

      path:
        '/forbidden',

      name:
        'forbidden',

      component:
        ForbiddenView,

      meta: {

        requiresAuth:
          true,

        title:
          'Access Denied',

      },

    },


    /*
    |--------------------------------------------------------------------------
    | Page Not Found
    |--------------------------------------------------------------------------
    */

    {

      path:
        '/:pathMatch(.*)*',

      name:
        'not-found',

      component:
        NotFoundView,

      meta: {

        title:
          'Page Not Found',

      },

    },

  ],

})


/*
|--------------------------------------------------------------------------
| Global Navigation Guard
|--------------------------------------------------------------------------
*/

router.beforeEach(
  async (to) => {

    /*
    |--------------------------------------------------------------------------
    | Page Title
    |--------------------------------------------------------------------------
    */

    const applicationName =
      import.meta.env.VITE_APP_NAME ||
      'RMS'


    const pageTitle =
      to.meta.title ||
      'Dashboard'


    document.title =
      `${pageTitle} | ${applicationName}`


    /*
    |--------------------------------------------------------------------------
    | Super Admin Detection
    |--------------------------------------------------------------------------
    */

    const isSuperAdminLogin =
      to.name === 'super-admin-login'


    const isSuperAdminRoute =
      to.matched.some(
        (record) =>
          Boolean(
            record.meta.requiresSuperAdmin
          )
      )


    const hasCentralToken =
      hasSuperAdminToken()


    /*
    |--------------------------------------------------------------------------
    | SUPER ADMIN LOGIN
    |--------------------------------------------------------------------------
    */

    if (
      isSuperAdminLogin
    ) {

      if (
        hasCentralToken
      ) {

        return {

          name:
            'super-admin-dashboard',

        }

      }


      return true

    }


    /*
    |--------------------------------------------------------------------------
    | SUPER ADMIN PROTECTED ROUTES
    |--------------------------------------------------------------------------
    */

    if (
      isSuperAdminRoute
    ) {

      if (
        !hasCentralToken
      ) {

        return {

          name:
            'super-admin-login',

          query: {

            redirect:
              to.fullPath,

          },

        }

      }


      return true

    }


    /*
    |--------------------------------------------------------------------------
    | TENANT AUTH STORE
    |--------------------------------------------------------------------------
    */

    const authStore =
      useAuthStore()


    try {

      await authStore.initializeAuth()

    }
    catch (error) {

      console.error(
        'Tenant authentication initialization failed:',
        error
      )

    }


    /*
    |--------------------------------------------------------------------------
    | Guest Tenant Login
    |--------------------------------------------------------------------------
    */

    if (
      to.name === 'login'
    ) {

      const tenantKey =
        String(

          to.query?.tenant_key ||
          authStore.tenantKey ||
          ''

        ).trim()


      if (
        !tenantKey
      ) {

        return {

          name:
            'not-found',

        }

      }


      if (
        authStore.isAuthenticated
      ) {

        return authStore
          .getDefaultRoute()

      }


      return true

    }


    /*
    |--------------------------------------------------------------------------
    | Guest-only Protection
    |--------------------------------------------------------------------------
    */

    if (
      to.meta.guestOnly
      &&
      authStore.isAuthenticated
    ) {

      return authStore
        .getDefaultRoute()

    }


    /*
    |--------------------------------------------------------------------------
    | Authentication Requirement
    |--------------------------------------------------------------------------
    */

    const requiresAuth =
      to.matched.some(
        (record) =>
          Boolean(
            record.meta.requiresAuth
          )
      )


    if (
      requiresAuth
      &&
      !authStore.isAuthenticated
    ) {

      const tenantKey =
        authStore.tenantKey


      return {

        name:
          'login',

        query: {

          redirect:
            to.fullPath,

          ...(tenantKey
            ? {

                tenant_key:
                  tenantKey,

              }
            : {}),

        },

      }

    }


    /*
    |--------------------------------------------------------------------------
    | Admin-only Route Protection
    |--------------------------------------------------------------------------
    */

    const adminOnly =
      to.matched.some(
        (record) =>
          Boolean(
            record.meta.adminOnly
          )
      )


    if (
      adminOnly
    ) {

      const roleName =
        getNormalizedRoleName(
          authStore
        )


      if (
        roleName !== 'admin'
      ) {

        return {

          name:
            'forbidden',

        }

      }

    }


    /*
    |--------------------------------------------------------------------------
    | Allowed Roles Protection
    |--------------------------------------------------------------------------
    */

    const allowedRolesRecord =
      [...to.matched]
        .reverse()
        .find(
          (record) =>

            Array.isArray(
              record.meta.allowedRoles
            )
            &&
            record.meta.allowedRoles.length > 0

        )


    const allowedRoles =
      allowedRolesRecord
        ?.meta
        ?.allowedRoles
      ??
      []


    const hasAllowedRolesRule =
      allowedRoles.length > 0


    if (
      hasAllowedRolesRule
    ) {

      const roleName =
        getNormalizedRoleName(
          authStore
        )


      const normalizedAllowedRoles =
        allowedRoles.map(
          (role) =>
            String(role)
              .trim()
              .toLowerCase()
        )


      if (
        !normalizedAllowedRoles.includes(
          roleName
        )
      ) {

        return {

          name:
            'forbidden',

        }

      }

    }


    /*
    |--------------------------------------------------------------------------
    | Permission Protection
    |--------------------------------------------------------------------------
    */

    const permissionRecord =
      [...to.matched]
        .reverse()
        .find(
          (record) =>
            Boolean(
              record.meta.permission
            )
        )


    const requiredPermission =
      permissionRecord
        ?.meta
        ?.permission


    if (
      requiredPermission
      &&
      !authStore.hasPermission(
        requiredPermission
      )
    ) {

      return {

        name:
          'forbidden',

      }

    }


    /*
    |--------------------------------------------------------------------------
    | Fail-Closed Tenant Route Protection
    |--------------------------------------------------------------------------
    */

    const isTenantApplicationRoute =
      to.matched.some(
        (record) =>
          record.meta.tenantApp === true
      )


    const allowsAnyAuthenticatedUser =
      to.matched.some(
        (record) =>
          record.meta.allowAuthenticatedOnly === true
      )


    const hasAuthorizationRule =
      Boolean(

        requiredPermission
        ||
        adminOnly
        ||
        hasAllowedRolesRule
        ||
        allowsAnyAuthenticatedUser

      )


    if (
      isTenantApplicationRoute
      &&
      authStore.isAuthenticated
      &&
      !hasAuthorizationRule
    ) {

      console.warn(

        'Blocked tenant route without an explicit authorization rule:',

        to.fullPath

      )


      return {

        name:
          'forbidden',

      }

    }


    /*
    |--------------------------------------------------------------------------
    | Allow Navigation
    |--------------------------------------------------------------------------
    */

    return true

  }
)


/*
|--------------------------------------------------------------------------
| Router Error Handler
|--------------------------------------------------------------------------
*/

router.onError(
  (error) => {

    console.error(
      'Router navigation error:',
      error
    )

  }
)


/*
|--------------------------------------------------------------------------
| Export Router
|--------------------------------------------------------------------------
*/

export default router