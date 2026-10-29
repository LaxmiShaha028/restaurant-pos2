import api from "@/services/api";

/*
|--------------------------------------------------------------------------
| API URLs
|--------------------------------------------------------------------------
|
| /settings
|   Admin-only settings management endpoint.
|
| /system-settings
|   Authenticated tenant operational/read-only endpoint.
|   Order create/edit screens must use this endpoint so Waiter/Manager
|   can read VAT and Service Charge without settings.manage permission.
|
*/

const SETTINGS_BASE_URL =
    "/settings";

const OPERATIONAL_SETTINGS_URL =
    "/system-settings";

/*
|--------------------------------------------------------------------------
| Response Helper
|--------------------------------------------------------------------------
*/

function getResponseBody(
    response
) {
    return (
        response?.data ??
        response ??
        {}
    );
}

/*
|--------------------------------------------------------------------------
| Error Helper
|--------------------------------------------------------------------------
*/

function getErrorMessage(
    error,
    fallback =
        "Unable to process system settings request."
) {
    const validationErrors =
        error?.response?.data?.errors;

    if (
        validationErrors &&
        typeof validationErrors === "object"
    ) {
        const firstError =
            Object.values(
                validationErrors
            )
                .flat()
                .find(Boolean);

        if (firstError) {
            return String(
                firstError
            );
        }
    }

    return String(
        error?.response?.data?.message ||
        error?.message ||
        fallback
    );
}

/*
|--------------------------------------------------------------------------
| System Setting Service
|--------------------------------------------------------------------------
*/

const systemSettingService = {

    /*
    |--------------------------------------------------------------------------
    | Admin Settings
    |--------------------------------------------------------------------------
    |
    | GET /api/settings
    |
    | Keep this for the System Settings admin page.
    |
    */

    async getSettings() {
        const response =
            await api.get(
                SETTINGS_BASE_URL
            );

        return getResponseBody(
            response
        );
    },

    /*
    |--------------------------------------------------------------------------
    | Operational / Order Billing Settings
    |--------------------------------------------------------------------------
    |
    | GET /api/system-settings
    |
    | This endpoint is available to authenticated tenant users and returns only
    | fields safe/necessary for restaurant operation:
    |
    | - restaurant branding
    | - VAT type/rate
    | - Service Charge type/rate
    |
    */

    async getOperationalSettings() {
        const response =
            await api.get(
                OPERATIONAL_SETTINGS_URL
            );

        return getResponseBody(
            response
        );
    },

    /*
    |--------------------------------------------------------------------------
    | Update Settings
    |--------------------------------------------------------------------------
    |
    | Admin-only.
    |
    */

    async updateSettings(
        payload
    ) {
        const isFormData =
            payload instanceof FormData;

        if (isFormData) {
            payload.append("_method", "PUT");

            const response =
                await api.post(
                    SETTINGS_BASE_URL,
                    payload
                );

            return getResponseBody(
                response
            );
        }

        const response =
            await api.put(
                SETTINGS_BASE_URL,
                payload
            );

        return getResponseBody(
            response
        );
    },

    getErrorMessage,
};

export default systemSettingService;
