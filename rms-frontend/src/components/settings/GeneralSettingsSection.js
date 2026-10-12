export default {
    name: "GeneralSettingsSection",

    props: {
        modelValue: {
            type: Object,
            required: true,
        },

        isSaving: {
            type: Boolean,
            default: false,
        },

        validationErrors: {
            type: Object,
            default: () => ({}),
        },
    },

    emits: ["update:modelValue"],

    data() {
        return {
            localSettings: {
                restaurant_name: "",
                restaurant_logo: null,
                country: "",
                branch_address: "",

                vat_type: "percentage",
                vat_rate: 0,

                service_charge_type: "percentage",
                service_charge_rate: 0,

                owner_nid: null,
                owner_trade_license: null,
            },

            restaurantLogoPreview: null,

            ownerNidPreview: null,
            ownerTradeLicensePreview: null,

            ownerNidName: "",
            ownerTradeLicenseName: "",

            // Prevent parent -> child sync from immediately
            // triggering child -> parent emit.
            isSyncingFromParent: false,
        };
    },

    created() {
        this.syncFromParent(this.modelValue);
    },

    watch: {
        /*
        |--------------------------------------------------------------------------
        | Parent -> Child
        |--------------------------------------------------------------------------
        */
        modelValue: {
            deep: true,

            handler(value) {
                this.syncFromParent(value);
            },
        },

        /*
        |--------------------------------------------------------------------------
        | Child -> Parent
        |--------------------------------------------------------------------------
        */
        localSettings: {
            deep: true,

            handler(value) {
                if (this.isSyncingFromParent) {
                    return;
                }

                this.$emit("update:modelValue", {
                    ...value,
                });
            },
        },
    },

    methods: {
        /*
        |--------------------------------------------------------------------------
        | Sync Parent Data
        |--------------------------------------------------------------------------
        */
        syncFromParent(value = {}) {
            this.isSyncingFromParent = true;

            /*
            |--------------------------------------------------------------------------
            | Restaurant Logo Preview
            |--------------------------------------------------------------------------
            */
            if (value?.restaurant_logo) {
                this.restaurantLogoPreview =
                    this.normalizeFileUrl(
                        value.restaurant_logo
                    );
            } else {
                this.restaurantLogoPreview = null;
            }

            /*
            |--------------------------------------------------------------------------
            | Owner NID Preview
            |--------------------------------------------------------------------------
            */
            if (value?.owner_nid) {
                this.ownerNidPreview =
                    this.normalizeFileUrl(
                        value.owner_nid
                    );

                this.ownerNidName =
                    "Existing NID Document";
            } else {
                this.ownerNidPreview = null;
                this.ownerNidName = "";
            }

            /*
            |--------------------------------------------------------------------------
            | Trade License Preview
            |--------------------------------------------------------------------------
            */
            if (value?.owner_trade_license) {
                this.ownerTradeLicensePreview =
                    this.normalizeFileUrl(
                        value.owner_trade_license
                    );

                this.ownerTradeLicenseName =
                    "Existing Trade License";
            } else {
                this.ownerTradeLicensePreview = null;
                this.ownerTradeLicenseName = "";
            }

            /*
            |--------------------------------------------------------------------------
            | Local Settings Sync
            |--------------------------------------------------------------------------
            */
            this.localSettings = {
                restaurant_name:
                    value?.restaurant_name ?? "",

                restaurant_logo:
                    value?.restaurant_logo ??
                    null,

                country:
                    value?.country ?? "",

                branch_address:
                    value?.branch_address ?? "",

                vat_type:
                    value?.vat_type ??
                    "percentage",

                vat_rate:
                    Number(
                        value?.vat_rate ?? 0
                    ),

                service_charge_type:
                    value?.service_charge_type ??
                    "percentage",

                service_charge_rate:
                    Number(
                        value?.service_charge_rate ?? 0
                    ),

                owner_nid:
                    value?.owner_nid ??
                    null,

                owner_trade_license:
                    value?.owner_trade_license ??
                    null,
            };

            this.$nextTick(() => {
                this.isSyncingFromParent = false;
            });
        },

        /*
        |--------------------------------------------------------------------------
        | Normalize File URL
        |--------------------------------------------------------------------------
        */
        normalizeFileUrl(file) {
            if (!file) {
                return null;
            }

            if (
                file.startsWith("http") ||
                file.startsWith("blob:")
            ) {
                return file;
            }

            return `/storage/${file.replace(/^storage\//, "")}`;
        },

        /*
        |--------------------------------------------------------------------------
        | Restaurant Logo
        |--------------------------------------------------------------------------
        */
        handleRestaurantLogoChange(event) {
            const file =
                event?.target?.files?.[0] ?? null;

            if (!file) {
                return;
            }

            this.localSettings.restaurant_logo =
                file;

            this.restaurantLogoPreview =
                URL.createObjectURL(file);

            this.clearValidationField(
                "restaurant_logo"
            );
        },

        /*
        |--------------------------------------------------------------------------
        | Owner NID
        |--------------------------------------------------------------------------
        */
        handleOwnerNidChange(event) {
            const file =
                event?.target?.files?.[0] ?? null;

            if (!file) {
                return;
            }

            this.localSettings.owner_nid =
                file;

            this.ownerNidName =
                file.name;

            this.ownerNidPreview =
                URL.createObjectURL(file);

            this.clearValidationField(
                "owner_nid"
            );
        },

        /*
        |--------------------------------------------------------------------------
        | Trade License
        |--------------------------------------------------------------------------
        */
        handleOwnerTradeLicenseChange(event) {
            const file =
                event?.target?.files?.[0] ?? null;

            if (!file) {
                return;
            }

            this.localSettings.owner_trade_license =
                file;

            this.ownerTradeLicenseName =
                file.name;

            this.ownerTradeLicensePreview =
                URL.createObjectURL(file);

            this.clearValidationField(
                "owner_trade_license"
            );
        },

        /*
        |--------------------------------------------------------------------------
        | Open Document
        |--------------------------------------------------------------------------
        */
        openDocument(url) {
            if (!url) {
                return;
            }

            window.open(
                url,
                "_blank",
                "noopener,noreferrer"
            );
        },

        /*
        |--------------------------------------------------------------------------
        | Validation Error
        |--------------------------------------------------------------------------
        */
        getFieldError(field) {
            const error =
                this.validationErrors?.[field];

            if (Array.isArray(error)) {
                return error[0] || "";
            }

            return error || "";
        },

        /*
        |--------------------------------------------------------------------------
        | Validation Clear
        |--------------------------------------------------------------------------
        */
        clearValidationField(field) {
            /*
            |--------------------------------------------------------------------------
            | IMPORTANT:
            | Do NOT mutate this.validationErrors because it is a prop.
            |--------------------------------------------------------------------------
            */

            if (
                !this.validationErrors ||
                !Object.prototype.hasOwnProperty.call(
                    this.validationErrors,
                    field
                )
            ) {
                return;
            }

            // Parent owns validationErrors.
            // We simply avoid mutating it here.
        },
    },
};