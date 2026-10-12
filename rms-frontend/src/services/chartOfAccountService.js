import api from './api'

const chartOfAccountService = {

  getAccounts(params = {})
  {
    return api.get(
      '/accounting/chart-of-accounts',
      {
        params,
      }
    )
  },


  getSummary()
  {
    return api.get(
      '/accounting/chart-of-accounts/summary'
    )
  },


  getOptions()
  {
    return api.get(
      '/accounting/chart-of-accounts/options'
    )
  },


  getMappings()
  {
    return api.get(
      '/accounting/chart-of-accounts/mappings'
    )
  },


  createAccount(data)
  {
    return api.post(
      '/accounting/chart-of-accounts',
      data
    )
  },


  updateAccount(
    id,
    data
  ) {
    return api.put(
      `/accounting/chart-of-accounts/${id}`,
      data
    )
  },


  deleteAccount(id)
  {
    return api.delete(
      `/accounting/chart-of-accounts/${id}`
    )
  },


  updateIncomeCategoryMapping(
    categoryId,
    ledgerAccountId
  ) {
    return api.put(
      `/accounting/chart-of-accounts/mappings/income-categories/${categoryId}`,
      {
        ledger_account_id:
          ledgerAccountId,
      }
    )
  },


  updateExpenseCategoryMapping(
    categoryId,
    ledgerAccountId
  ) {
    return api.put(
      `/accounting/chart-of-accounts/mappings/expense-categories/${categoryId}`,
      {
        ledger_account_id:
          ledgerAccountId,
      }
    )
  },


  updatePaymentMapping(
    paymentMethod,
    ledgerAccountId
  ) {
    return api.put(
      `/accounting/chart-of-accounts/mappings/payment-methods/${paymentMethod}`,
      {
        ledger_account_id:
          ledgerAccountId,
      }
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

export default chartOfAccountService
