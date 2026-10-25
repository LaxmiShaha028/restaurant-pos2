import api from './api'


/*
|--------------------------------------------------------------------------
| Report Service
|--------------------------------------------------------------------------
|
| Centralized API service for all business and accounting reports.
|
*/


const reportService = {


    /*
    |--------------------------------------------------------------------------
    | Dashboard Summary
    |--------------------------------------------------------------------------
    */

    async getSummary(
        params = {}
    ) {

        const response =
            await api.get(
                '/billing/summary',
                {
                    params,
                }
            )


        return response.data

    },


    /*
    |--------------------------------------------------------------------------
    | Orders Report
    |--------------------------------------------------------------------------
    */

    async getOrderReport(
        params = {}
    ) {

        const response =
            await api.get(
                '/reports/orders',
                {
                    params,
                }
            )


        return response.data

    },


    async getDemandForecast(
        params = {}
    ) {

        const response =
            await api.get(
                '/reports/demand-forecast',
                { params }
            )


        return response.data

    },


    /*
    |--------------------------------------------------------------------------
    | Sales Report
    |--------------------------------------------------------------------------
    */

    async getSalesReport(
        params = {}
    ) {

        const response =
            await api.get(
                '/billing/settlements',
                {
                    params,
                }
            )


        return response.data

    },


    /*
    |--------------------------------------------------------------------------
    | Payment Report
    |--------------------------------------------------------------------------
    */

    async getPaymentReport(
        params = {}
    ) {

        const response =
            await api.get(
                '/billing/payment-modes',
                {
                    params,
                }
            )


        return response.data

    },


    /*
    |--------------------------------------------------------------------------
    | Expense Report
    |--------------------------------------------------------------------------
    */

    async getExpenseReport(
        params = {}
    ) {

        const response =
            await api.get(
                '/expenses',
                {
                    params,
                }
            )


        return response.data

    },


    /*
    |--------------------------------------------------------------------------
    | Expense Summary
    |--------------------------------------------------------------------------
    */

    async getExpenseSummary(
        params = {}
    ) {

        const response =
            await api.get(
                '/expenses/summary',
                {
                    params,
                }
            )


        return response.data

    },


    /*
    |--------------------------------------------------------------------------
    | Purchase Report
    |--------------------------------------------------------------------------
    */

    async getPurchaseReport(
        params = {}
    ) {

        const response =
            await api.get(
                '/purchase-orders',
                {
                    params,
                }
            )


        return response.data

    },


    /*
    |--------------------------------------------------------------------------
    | Purchase Payment History
    |--------------------------------------------------------------------------
    */

    async getPurchasePayments(
        id
    ) {

        const response =
            await api.get(
                `/purchase-orders/${id}/payments`
            )


        return response.data

    },


    /*
    |--------------------------------------------------------------------------
    | Warehouse Stock
    |--------------------------------------------------------------------------
    */

    async getWarehouseStock(
        params = {}
    ) {

        const response =
            await api.get(
                '/inventory/warehouse-stocks',
                {
                    params,
                }
            )


        return response.data

    },


    /*
    |--------------------------------------------------------------------------
    | Restaurant Stock
    |--------------------------------------------------------------------------
    */

    async getRestaurantStock(
        params = {}
    ) {

        const response =
            await api.get(
                '/inventory/restaurant-stocks',
                {
                    params,
                }
            )


        return response.data

    },


    /*
    |--------------------------------------------------------------------------
    | Stock Transfers
    |--------------------------------------------------------------------------
    */

    async getStockTransfers(
        params = {}
    ) {

        const response =
            await api.get(
                '/inventory/stock-transfers',
                {
                    params,
                }
            )


        return response.data

    },


    /*
    |--------------------------------------------------------------------------
    | Attendance Report
    |--------------------------------------------------------------------------
    */

    async getAttendanceReport(
        params = {}
    ) {

        const response =
            await api.get(
                '/reports/attendance',
                {
                    params,
                }
            )


        return response.data

    },


    /*
    |--------------------------------------------------------------------------
    | Salary Report
    |--------------------------------------------------------------------------
    */

    async getSalaryReport(
        params = {}
    ) {

        const response =
            await api.get(
                '/admin/salaries',
                {
                    params,
                }
            )


        return response.data

    },


    /*
    |--------------------------------------------------------------------------
    | Accounting Reports
    |--------------------------------------------------------------------------
    */


    /*
    |--------------------------------------------------------------------------
    | Profit & Loss
    |--------------------------------------------------------------------------
    */

    async getProfitLoss(
        params = {}
    ) {

        const response =
            await api.get(
                '/reports/profit-loss',
                {
                    params,
                }
            )


        return response.data

    },


    /*
    |--------------------------------------------------------------------------
    | Balance Sheet
    |--------------------------------------------------------------------------
    */

    async getBalanceSheet(
        params = {}
    ) {

        const response =
            await api.get(
                '/reports/balance-sheet',
                {
                    params,
                }
            )


        return response.data

    },


    /*
    |--------------------------------------------------------------------------
    | Trial Balance
    |--------------------------------------------------------------------------
    */

    async getTrialBalance(
        params = {}
    ) {

        const response =
            await api.get(
                '/reports/trial-balance',
                {
                    params,
                }
            )


        return response.data

    },


    /*
    |--------------------------------------------------------------------------
    | Ledger Accounts
    |--------------------------------------------------------------------------
    |
    | Used by General Ledger for account selection/filtering.
    |
    */

    async getLedgerAccounts() {

        const response =
            await api.get(
                '/reports/ledger/accounts'
            )


        return response.data

    },


    /*
    |--------------------------------------------------------------------------
    | General Ledger
    |--------------------------------------------------------------------------
    */

    async getGeneralLedger(
        params = {}
    ) {

        const response =
            await api.get(
                '/reports/general-ledger',
                {
                    params,
                }
            )


        return response.data

    },


    /*
    |--------------------------------------------------------------------------
    | Export CSV
    |--------------------------------------------------------------------------
    */

    async exportCSV(
        type,
        params = {}
    ) {

        return await api.get(
            `/reports/${type}/export/csv`,
            {
                params,

                responseType:
                    'blob',
            }
        )

    },


    /*
    |--------------------------------------------------------------------------
    | Export PDF
    |--------------------------------------------------------------------------
    */

    async exportPDF(
        type,
        params = {}
    ) {

        return await api.get(
            `/reports/${type}/export/pdf`,
            {
                params,

                responseType:
                    'blob',
            }
        )

    },


    /*
    |--------------------------------------------------------------------------
    | Download File Helper
    |--------------------------------------------------------------------------
    */

    downloadFile(
        response,
        filename
    ) {

        const blob =
            new Blob(
                [
                    response.data,
                ]
            )


        const url =
            window.URL.createObjectURL(
                blob
            )


        const link =
            document.createElement(
                'a'
            )


        link.href =
            url


        link.download =
            filename


        document.body.appendChild(
            link
        )


        link.click()


        link.remove()


        window.URL.revokeObjectURL(
            url
        )

    },
    async getAccountingDashboard(
    params = {}
){

    const response =

        await api.get(

            '/reports/accounting-dashboard',

            {
                params
            }

        )


    return response.data

},
async downloadOwnerLedgerPdf(
    params={}
){

    return await api.get(

        '/reports/owner-ledger/pdf',

        {

            params,

            responseType:'blob'

        }

    )

},
async getOwnerLedger(params={}){

    const response =
        await api.get(
            '/reports/owner-ledger',
            {
                params
            }
        )


    return response.data

},

getBusinessOverview(params){

    return api.get(
    '/reports/business-overview',
    {
        params
    }
)

},

}


export default reportService
