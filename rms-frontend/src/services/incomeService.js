import api from './api'

const incomeService = {

  getIncomes(params = {})
  {
    return api.get(
      '/other-incomes',
      {
        params,
      }
    )
  },


  getSummary(params = {})
  {
    return api.get(
      '/other-incomes/summary',
      {
        params,
      }
    )
  },


  getOptions()
  {
    return api.get(
      '/other-incomes/options'
    )
  },


  createIncome(data)
  {
    return api.post(
      '/other-incomes',
      data
    )
  },


  updateIncome(
    id,
    data
  ) {
    return api.put(
      `/other-incomes/${id}`,
      data
    )
  },


  deleteIncome(id)
  {
    return api.delete(
      `/other-incomes/${id}`
    )
  },


  createCategory(data)
  {
    return api.post(
      '/other-incomes/categories',
      data
    )
  },


  getErrorMessage(
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

export default incomeService
