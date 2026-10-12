import api from './api'


const auditLogService = {


    /*
    |--------------------------------------------------------------------------
    | Audit Log List
    |--------------------------------------------------------------------------
    */


    getLogs(params = {})
    {

        return api.get(

            '/admin/audit-logs',

            {
                params
            }

        )

    },





    /*
    |--------------------------------------------------------------------------
    | Single Audit Log
    |--------------------------------------------------------------------------
    */


    getLog(id)
    {

        return api.get(

            `/admin/audit-logs/${id}`

        )

    }



}


export default auditLogService