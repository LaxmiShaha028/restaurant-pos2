<template>

<section class="system-settings-page">


    <!-- HEADER -->

    <header class="system-settings-header">
      <div class="system-settings-title-row">
        <span class="system-settings-title-icon">
          <i class="bi bi-gear-wide-connected"></i>
        </span>

        <div>
          <p class="system-settings-eyebrow">
            Configuration
          </p>

          <h1>
            System Settings
          </h1>

          <p>
            Manage restaurant information,
            VAT, service charge and business documents
          </p>
        </div>
      </div>
    </header>


    <!-- SUCCESS -->

    <div

        v-if="successMessage"

        class="settings-alert settings-alert-success"

    >

        {{ successMessage }}

    </div>


    <!-- LOADING -->

    <div

        v-if="isLoading"

        class="settings-loading"

    >

        Loading system settings...

    </div>



    <!-- SETTINGS -->

    <form

        v-else

        class="system-settings-form"

        @submit.prevent="saveSettings"

    >



        <!-- GENERAL COMPONENT -->

        <GeneralSettingsSection


            v-if="
                activeSection === 'general'
            "


            v-model="form"


            :is-saving="isSaving"


            :validation-errors="
                validationErrors
            "


        />

        <section class="pickup-sound-settings" aria-labelledby="pickup-sound-title">
          <div class="pickup-sound-copy">
            <span class="pickup-sound-icon"><i class="bi bi-volume-up"></i></span>
            <div>
              <h2 id="pickup-sound-title">Order Pickup Audio</h2>
              <p>Play a chime and announce the order number when it becomes ready. Applies to pickup displays in this browser.</p>
            </div>
          </div>
          <label class="pickup-sound-toggle">
            <span>{{ pickupSoundEnabled ? 'On' : 'Off' }}</span>
            <input
              type="checkbox"
              :checked="pickupSoundEnabled"
              aria-label="Enable order pickup sound"
              @change="setPickupSoundEnabled($event.target.checked)"
            />
          </label>
        </section>
        <!-- SAVE -->


        <div class="settings-form-actions">


            <button

                type="submit"

                class="settings-save-button"

                :disabled="isSaving"

            >


                <span v-if="isSaving">

                    Saving...

                </span>


                <span v-else>


                    {{
                        activeSection === 'invoice'
                        ?
                        'Save Invoice Settings'
                        :
                        'Save General Settings'
                    }}


                </span>



            </button>



        </div>




    </form>





</section>


</template>





<script src="./SystemSettingsView.js"></script>


<style

scoped

src="./SystemSettingsView.css"

></style>
<style

scoped

src="./systemsettings-compact.css"

></style>
