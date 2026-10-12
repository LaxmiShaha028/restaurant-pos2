import systemSettingService 
from "@/services/systemSettingService";


import GeneralSettingsSection
from "@/components/settings/GeneralSettingsSection.vue";


import {
    useSystemSettingStore
} from '@/stores/systemSetting'

export default {


  name: "SystemSettingsView",

  components: {


    GeneralSettingsSection,

  },







  data(){



    return {



      /*
      |--------------------------------------------------------------------------
      | Active Section
      |--------------------------------------------------------------------------
      */


      activeSection: "general",





      /*
      |--------------------------------------------------------------------------
      | Status
      |--------------------------------------------------------------------------
      */


      isLoading: true,


      isSaving: false,



      generalError: "",


      successMessage: "",



      validationErrors: {},






      /*
      |--------------------------------------------------------------------------
      | Main Form
      |--------------------------------------------------------------------------
      */


      form:{





        /*
        |--------------------------------------------------------------------------
        | General
        |--------------------------------------------------------------------------
        */


        restaurant_name: "",


        country: "",


        branch_address: "",






        /*
        |--------------------------------------------------------------------------
        | Invoice
        |--------------------------------------------------------------------------
        */


        invoice_subtitle: "",


        bin_number: "",


        invoice_currency: "BDT",



        invoice_show_logo: true,


        invoice_show_company_name: true,


        invoice_show_branch_address: true,


        invoice_show_token_number: true,


        invoice_show_table_number: true,






        /*
        |--------------------------------------------------------------------------
        | VAT
        |--------------------------------------------------------------------------
        */


        vat_type: "percentage",


        vat_rate: 0,






        /*
        |--------------------------------------------------------------------------
        | Service Charge
        |--------------------------------------------------------------------------
        */


        service_charge_type: "percentage",


        service_charge_rate: 0,

      },


    };


  },







  async created(){


    await this.loadSettings();


  },








  methods:{





    /*
    |--------------------------------------------------------------------------
    | Load Settings
    |--------------------------------------------------------------------------
    */


    async loadSettings(){


      this.isLoading = true;


      this.generalError = "";



      try{



        const response =

          await systemSettingService

            .getSettings();




        const data =

          response?.data ?? {};





        /*
        |--------------------------------------------------------------------------
        | General
        |--------------------------------------------------------------------------
        */


        this.form.restaurant_name =

          data.restaurant_name ?? "";



        this.form.country =

          data.country ?? "";



        this.form.branch_address =

          data.branch_address ?? "";





        /*
        |--------------------------------------------------------------------------
        | Invoice
        |--------------------------------------------------------------------------
        */


        this.form.invoice_subtitle =

          data.invoice_subtitle ?? "";



        this.form.bin_number =

          data.bin_number ?? "";



        this.form.invoice_currency =

          data.invoice_currency ?? "BDT";



        this.form.invoice_show_logo =

          Boolean(

            data.invoice_show_logo

          );



        this.form.invoice_show_company_name =

          Boolean(

            data.invoice_show_company_name

          );



        this.form.invoice_show_branch_address =

          Boolean(

            data.invoice_show_branch_address

          );



        this.form.invoice_show_token_number =

          Boolean(

            data.invoice_show_token_number

          );



        this.form.invoice_show_table_number =

          Boolean(

            data.invoice_show_table_number

          );








        /*
        |--------------------------------------------------------------------------
        | VAT
        |--------------------------------------------------------------------------
        */


        this.form.vat_type =

          data.vat_type ??

          "percentage";



        this.form.vat_rate =

          Number(

            data.vat_rate ?? 0

          );








        /*
        |--------------------------------------------------------------------------
        | Service Charge
        |--------------------------------------------------------------------------
        */


        this.form.service_charge_type =

          data.service_charge_type ??

          "percentage";



        this.form.service_charge_rate =

          Number(

            data.service_charge_rate ?? 0

          );







        /*
        |--------------------------------------------------------------------------
        | Existing Uploaded Files
        |--------------------------------------------------------------------------
        */


        this.form.restaurant_logo =

            data.restaurant_logo ?? null;


        this.form.owner_nid =

            data.owner_nid ?? null;


        this.form.owner_trade_license =

            data.owner_trade_license ?? null;





      }



      catch(error){



        console.error(

          "System settings load failed:",

          error

        );



        this.generalError =

          systemSettingService

            .getErrorMessage(

              error,

              "Unable to load system settings."

            );



      }



      finally{


        this.isLoading = false;


      }



    },












    /*
    |--------------------------------------------------------------------------
    | Save Settings
    |--------------------------------------------------------------------------
    */


    async saveSettings(){



      if(this.isSaving){


        return;


      }





      this.isSaving = true;


      this.generalError = "";


      this.successMessage = "";


      this.validationErrors = {};





      try{



        const formData =

          new FormData();








        /*
        |--------------------------------------------------------------------------
        | General
        |--------------------------------------------------------------------------
        */


        formData.append(

          "restaurant_name",

          this.form.restaurant_name || ""

        );



        formData.append(

          "country",

          this.form.country || ""

        );



        formData.append(

          "branch_address",

          this.form.branch_address || ""

        );









        /*
        |--------------------------------------------------------------------------
        | Invoice
        |--------------------------------------------------------------------------
        */


        formData.append(

          "invoice_subtitle",

          this.form.invoice_subtitle || ""

        );



        formData.append(

          "bin_number",

          this.form.bin_number || ""

        );



        formData.append(

          "invoice_currency",

          this.form.invoice_currency || "BDT"

        );



        formData.append(

          "invoice_show_logo",

          this.form.invoice_show_logo ? 1 : 0

        );



        formData.append(

          "invoice_show_company_name",

          this.form.invoice_show_company_name ? 1 : 0

        );



        formData.append(

          "invoice_show_branch_address",

          this.form.invoice_show_branch_address ? 1 : 0

        );



        formData.append(

          "invoice_show_token_number",

          this.form.invoice_show_token_number ? 1 : 0

        );



        formData.append(

          "invoice_show_table_number",

          this.form.invoice_show_table_number ? 1 : 0

        );









        /*
        |--------------------------------------------------------------------------
        | VAT
        |--------------------------------------------------------------------------
        */


        if(this.activeSection === "general"){

    formData.append(
        "vat_type",
        this.form.vat_type
    );


    formData.append(
        "vat_rate",
        Number(this.form.vat_rate || 0)
    );


    formData.append(
        "service_charge_type",
        this.form.service_charge_type
    );


    formData.append(
        "service_charge_rate",
        Number(this.form.service_charge_rate || 0)
    );

}

        /*
        |--------------------------------------------------------------------------
        | File Uploads
        |--------------------------------------------------------------------------
        */


        if(

          this.form.restaurant_logo

          instanceof File

        ){


          formData.append(

            "restaurant_logo",

            this.form.restaurant_logo

          );


        }





        if(

          this.form.owner_nid

          instanceof File

        ){


          formData.append(

            "owner_nid",

            this.form.owner_nid

          );


        }






        if(

          this.form.owner_trade_license

          instanceof File

        ){


          formData.append(

            "owner_trade_license",

            this.form.owner_trade_license

          );


        }








        /*
        |--------------------------------------------------------------------------
        | Laravel Method Override
        |--------------------------------------------------------------------------
        */


        formData.append(

          "_method",

          "PUT"

        );









        /*
        |--------------------------------------------------------------------------
        | API Update
        |--------------------------------------------------------------------------
        */


        const response =

          await systemSettingService

            .updateSettings(

              formData

            );





        const data =

          response?.data ?? {};








        /*
        |--------------------------------------------------------------------------
        | Update Local Form
        |--------------------------------------------------------------------------
        */


        this.form.restaurant_name =

          data.restaurant_name ??

          this.form.restaurant_name;



        this.form.country =

          data.country ??

          this.form.country;



        this.form.branch_address =

          data.branch_address ??

          this.form.branch_address;








        /*
        |--------------------------------------------------------------------------
        | Success
        |--------------------------------------------------------------------------
        */


        this.successMessage =

          "System settings updated successfully.";



        const systemSettingStore =
            useSystemSettingStore()


        await systemSettingStore.fetchSettings(true)

        window.dispatchEvent(

          new CustomEvent(

            "restaurant-settings-updated"

          )

        );




      }





      catch(error){



        console.error(

          "System settings update failed:",

          error

        );





        if(

          error?.response?.status === 422

        ){



          this.validationErrors =

            error?.response?.data?.errors || {};



        }






        this.generalError =

          systemSettingService

            .getErrorMessage(

              error,

              "Unable to update system settings."

            );



      }






      finally{


        this.isSaving = false;


      }






    },



  },



};