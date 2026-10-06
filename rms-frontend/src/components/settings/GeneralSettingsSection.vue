<template>

<section class="general-settings-section">


    <!-- =====================================================
       HEADER
  ====================================================== -->
  <div class="general-settings-header">
    <div class="general-settings-title-wrapper">
      <span class="general-settings-icon">
        <i class="bi bi-building"></i>
      </span>

      <div>
        <p class="general-settings-eyebrow">
          Configuration
        </p>

        <h2>
          General
        </h2>

        <p>
          Restaurant identity, billing and business information
        </p>
      </div>
    </div>
  </div>





  <!-- =====================================================
       BODY
  ====================================================== -->

  <div class="general-settings-body">





    <!-- =====================================================
         RESTAURANT NAME
    ====================================================== -->


    <div class="settings-form-group">


      <label
        for="restaurant_name"
      >

        Restaurant Name

      </label>



      <input

        id="restaurant_name"

        v-model.trim="
          localSettings.restaurant_name
        "

        type="text"

        class="settings-input"

        placeholder="Enter restaurant name"

        maxlength="190"

        :disabled="isSaving"

      />



      <small

        v-if="
          getFieldError(
            'restaurant_name'
          )
        "

        class="settings-field-error"

      >

        {{
          getFieldError(
            "restaurant_name"
          )
        }}


      </small>



    </div>





    <!-- =====================================================
         RESTAURANT LOGO
    ====================================================== -->


    <div class="settings-logo-section">


      <div
        class="settings-logo-preview"
      >


        <img

          v-if="
            restaurantLogoPreview
          "

          :src="
            restaurantLogoPreview
          "

          alt="Restaurant Logo"

        />


        <span v-else>

          <i class="bi bi-image"></i>

        </span>


      </div>





      <div
        class="settings-logo-content"
      >


        <label
          for="restaurant_logo"
        >

          Restaurant Logo

        </label>



        <p>

          Upload JPG, JPEG, PNG or WEBP.
          Maximum size 2 MB.

        </p>



        <input

          id="restaurant_logo"

          type="file"

          accept="
            .jpg,
            .jpeg,
            .png,
            .webp,
            image/jpeg,
            image/png,
            image/webp
          "

          class="settings-file-input"

          :disabled="isSaving"

          @change="
            handleRestaurantLogoChange
          "

        />



        <small

          v-if="
            getFieldError(
              'restaurant_logo'
            )
          "

          class="settings-field-error"

        >

          {{
            getFieldError(
              "restaurant_logo"
            )
          }}

        </small>



      </div>


    </div>





    <!-- =====================================================
         COUNTRY
    ====================================================== -->


    <div class="settings-form-group">


      <label
        for="country"
      >

        Country

      </label>



      <select

        id="country"

        v-model="
          localSettings.country
        "

        class="settings-input"

        :disabled="isSaving"

      >


        <option value="">

          Select country

        </option>



        <option value="Bangladesh">

          Bangladesh

        </option>



        <option value="India">

          India

        </option>



        <option value="Pakistan">

          Pakistan

        </option>



        <option value="Nepal">

          Nepal

        </option>



        <option value="Sri Lanka">

          Sri Lanka

        </option>



        <option value="United Arab Emirates">

          United Arab Emirates

        </option>



        <option value="Saudi Arabia">

          Saudi Arabia

        </option>



        <option value="United Kingdom">

          United Kingdom

        </option>



        <option value="United States">

          United States

        </option>



      </select>



    </div>





    <!-- =====================================================
         BRANCH ADDRESS
    ====================================================== -->


    <div class="settings-form-group">


      <label
        for="branch_address"
      >

        Branch Address

      </label>



      <textarea

        id="branch_address"

        v-model.trim="
          localSettings.branch_address
        "

        class="settings-input settings-textarea"

        placeholder="Enter branch address"

        maxlength="500"

        rows="3"

        :disabled="isSaving"

      ></textarea>



    </div>
    <!-- =====================================================
     VAT
====================================================== -->


<div class="settings-charge-box">


  <div class="settings-charge-heading">


    <div>

      <h3>
        VAT
      </h3>


      <p>
        Configure how VAT is applied
      </p>


    </div>


    <span class="settings-charge-icon">

      <i class="bi bi-percent"></i>

    </span>


  </div>





  <div class="settings-charge-grid">


    <!-- TYPE -->


    <div class="settings-form-group">


      <label
        for="vat_type"
      >

        Type

      </label>



      <select

        id="vat_type"

        v-model="
          localSettings.vat_type
        "

        class="settings-input"

        :disabled="isSaving"

      >


        <option value="percentage">

          Percentage

        </option>



        <option value="fixed">

          Fixed Amount

        </option>


      </select>



    </div>





    <!-- VALUE -->


    <div class="settings-form-group">


      <label
        for="vat_rate"
      >


        {{
          localSettings.vat_type === "percentage"
            ? "VAT Rate (%)"
            : "VAT Amount"
        }}


      </label>




      <div class="settings-number-input">


        <span>


          {{
            localSettings.vat_type === "percentage"
              ? "%"
              : "৳"
          }}



        </span>



        <input


          id="vat_rate"


          v-model.number="
            localSettings.vat_rate
          "


          type="number"


          min="0"


          step="0.01"


          class="settings-input"


          :disabled="isSaving"



        />


      </div>



    </div>



  </div>


</div>








<!-- =====================================================
     SERVICE CHARGE
====================================================== -->


<div class="settings-charge-box">


  <div class="settings-charge-heading">


    <div>


      <h3>

        Service Charge

      </h3>



      <p>

        Configure restaurant service charge

      </p>



    </div>



    <span class="settings-charge-icon">

      <i class="bi bi-receipt"></i>

    </span>



  </div>





  <div class="settings-charge-grid">



    <!-- TYPE -->


    <div class="settings-form-group">


      <label
        for="service_charge_type"
      >

        Type

      </label>



      <select


        id="service_charge_type"


        v-model="
          localSettings.service_charge_type
        "


        class="settings-input"


        :disabled="isSaving"


      >


        <option value="percentage">

          Percentage

        </option>



        <option value="fixed">

          Fixed Amount

        </option>


      </select>



    </div>





    <!-- VALUE -->


    <div class="settings-form-group">


      <label
        for="service_charge_rate"
      >


        {{
          localSettings.service_charge_type === "percentage"
            ? "Service Charge (%)"
            : "Service Charge Amount"
        }}


      </label>



      <div class="settings-number-input">


        <span>


          {{
            localSettings.service_charge_type === "percentage"
              ? "%"
              : "৳"
          }}



        </span>




        <input


          id="service_charge_rate"


          v-model.number="
            localSettings.service_charge_rate
          "


          type="number"


          min="0"


          step="0.01"


          class="settings-input"


          :disabled="isSaving"


        />


      </div>



    </div>



  </div>


</div>









<!-- =====================================================
     BUSINESS VERIFICATION
====================================================== -->


<div class="settings-business-box">


  <div class="settings-charge-heading">


    <div>


      <h3>

        Business Verification

      </h3>



      <p>

        Upload owner verification documents

      </p>


    </div>



    <span class="settings-charge-icon">

      <i class="bi bi-shield-check"></i>


    </span>


  </div>





  <div class="settings-two-column">



    <!-- OWNER NID START -->


    <div class="settings-form-group">


      <label
        for="owner_nid"
      >

        Owner NID

      </label>



      <input


        id="owner_nid"


        type="file"


        accept="
          .jpg,
          .jpeg,
          .png,
          .webp,
          .pdf,
          image/jpeg,
          image/png,
          image/webp,
          application/pdf
        "


        class="settings-file-input"


        :disabled="isSaving"


        @change="
          handleOwnerNidChange
        "


      />



    </div>





    <!-- TRADE LICENSE START -->


    <div class="settings-form-group">


      <label
        for="owner_trade_license"
      >

        Owner Trade License

      </label>



      <input


        id="owner_trade_license"


        type="file"


        accept="
          .jpg,
          .jpeg,
          .png,
          .webp,
          .pdf,
          image/jpeg,
          image/png,
          image/webp,
          application/pdf
        "


        class="settings-file-input"


        :disabled="isSaving"


        @change="
          handleOwnerTradeLicenseChange
        "


      />



    </div>



  </div>


</div>
<!-- =====================================================
     DOCUMENT PREVIEW
====================================================== -->


<div
  class="settings-document-preview-wrapper"
>


  <div
    v-if="ownerNidName"
    class="settings-document-info"
  >


    <span
      class="settings-document-name"
    >

      <i class="bi bi-file-earmark-check"></i>


      {{ ownerNidName }}


    </span>



    <button

      v-if="ownerNidPreview"

      type="button"

      class="settings-document-view-button"

      @click="
        openDocument(ownerNidPreview)
      "

    >

      <i class="bi bi-eye"></i>


    </button>



  </div>






  <div
    v-if="ownerTradeLicenseName"
    class="settings-document-info"
  >


    <span
      class="settings-document-name"
    >

      <i class="bi bi-file-earmark-check"></i>


      {{ ownerTradeLicenseName }}


    </span>




    <button

      v-if="ownerTradeLicensePreview"

      type="button"

      class="settings-document-view-button"

      @click="
        openDocument(ownerTradeLicensePreview)
      "

    >

      <i class="bi bi-eye"></i>


    </button>



  </div>



</div>



</div>


</section>


</template>





<script src="./GeneralSettingsSection.js"></script>




<style

scoped

src="./GeneralSettingsSection.css"

></style>
<style

scoped

src="./GeneralSettings-compact.css"

></style>