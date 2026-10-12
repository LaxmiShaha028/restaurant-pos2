import { defineStore } from 'pinia'
import api from '@/services/api'


export const useSystemSettingStore = defineStore(
    'systemSetting',
    {

        state: () => ({

            restaurantName: '',

            restaurantLogo: null,

            loaded: false,

            loading: false,

            error: null,

        }),


        getters: {


            name: (state) =>
                state.restaurantName,


            logo: (state) =>
                state.restaurantLogo,


        },


        actions: {


            async fetchSettings(
                force = false
            ) {


                if (
                    this.loaded &&
                    !force
                ) {
                    return
                }


                this.loading = true

                this.error = null


                try {


                    const response =
                        await api.get(
                            '/system-settings'
                        )


                    const data =
                        response.data.data


                    this.restaurantName =
                        data.restaurant_name ?? ''


                    this.restaurantLogo =
                        data.restaurant_logo ?? null


                    this.loaded = true



                } catch(error) {


                    this.error =
                        error.response?.data?.message ||
                        'Unable to load restaurant settings.'


                } finally {


                    this.loading = false

                }

            },


            clearSettings(){


                this.restaurantName = ''

                this.restaurantLogo = null

                this.loaded = false


            },


        },


    }
)