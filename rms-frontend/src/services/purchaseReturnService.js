import api from '@/services/api'


const BASE_URL =
  '/inventory/purchase-returns'


async function createPurchaseReturn(
  payload,
)
{
  const response =
    await api.post(
      BASE_URL,
      payload,
    )

  return response.data
}


async function getPurchaseReturns(
  params = {},
)
{
  const response =
    await api.get(
      BASE_URL,
      {
        params,
      },
    )

  return response.data
}


async function getPurchaseReturn(
  returnId,
)
{
  const response =
    await api.get(
      `${BASE_URL}/${returnId}`,
    )

  return response.data
}


async function getSettlements(
  returnId,
)
{
  const response =
    await api.get(
      `${BASE_URL}/${returnId}/settlements`,
    )

  return response.data
}


async function createSettlement(
  returnId,
  payload,
)
{
  const response =
    await api.post(
      `${BASE_URL}/${returnId}/settlements`,
      payload,
    )

  return response.data
}


async function collectRefund(
  returnId,
  payload,
)
{
  const response =
    await api.post(
      `${BASE_URL}/${returnId}/settlements/collect-refund`,
      payload,
    )

  return response.data
}


function getValidationErrors(
  error,
)
{
  const errors =
    error?.response?.data?.errors

  if (
    !errors
    ||
    typeof errors !== 'object'
    ||
    Array.isArray(errors)
  ) {
    return {}
  }

  return errors
}


function getErrorMessage(
  error,
  fallback =
    'Unable to record Purchase Return.',
)
{
  return (
    error?.response?.data?.message
    ||
    error?.message
    ||
    fallback
  )
}


const reasons =
  Object.freeze([
    { value: 'near_expiry', label: 'Near Expiry' },
    { value: 'expired', label: 'Expired' },
    { value: 'quality_rejected', label: 'Quality Rejected' },
    { value: 'damaged_packaging', label: 'Damaged Packaging' },
    { value: 'excess_stock', label: 'Excess Stock' },
    { value: 'wrong_item', label: 'Wrong Item' },
    { value: 'supplier_recall', label: 'Supplier Recall' },
    { value: 'other', label: 'Other' },
  ])


const settlementTypes =
  Object.freeze([
    {
      value: 'payable_offset',
      label: 'Reduce Supplier Payable',
    },
    {
      value: 'supplier_credit',
      label: 'Supplier Credit',
    },
    {
      value: 'refund_receivable',
      label: 'Refund Expected / Receivable',
    },
    {
      value: 'direct_refund',
      label: 'Refund Received Now',
    },
  ])


const refundMethods =
  Object.freeze([
    {
      value: 'cash',
      label: 'Cash',
    },
    {
      value: 'bank_transfer',
      label: 'Bank Transfer',
    },
    {
      value: 'bkash',
      label: 'bKash',
    },
    {
      value: 'nagad',
      label: 'Nagad',
    },
    {
      value: 'card',
      label: 'Card',
    },
    {
      value: 'other',
      label: 'Other',
    },
  ])


export default {
  createPurchaseReturn,
  getPurchaseReturns,
  getPurchaseReturn,
  getSettlements,
  createSettlement,
  collectRefund,
  getValidationErrors,
  getErrorMessage,
  reasons,
  settlementTypes,
  refundMethods,
}
