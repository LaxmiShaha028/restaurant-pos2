import api from './api'

const journalService = {

  getEntries(params = {})
  {
    return api.get(
      '/accounting/journals',
      {
        params,
      }
    )
  },


  getOptions()
  {
    return api.get(
      '/accounting/journals/options'
    )
  },


  getEntry(id)
  {
    return api.get(
      `/accounting/journals/${id}`
    )
  },


  createManual(data)
  {
    return api.post(
      '/accounting/journals',
      data
    )
  },


  reverse(
    id,
    data
  ) {
    return api.post(
      `/accounting/journals/${id}/reverse`,
      data
    )
  },


  errorMessage(
    error,
    fallback =
      'Something went wrong.'
  ) {

    const errors =
      error
        ?.response
        ?.data
        ?.errors


    if (errors) {

      return Object
        .values(errors)
        .flat()
        .join(' ')
    }


    return (
      error
        ?.response
        ?.data
        ?.message

      ??

      error?.message

      ??

      fallback
    )
  },

}

export default journalService
