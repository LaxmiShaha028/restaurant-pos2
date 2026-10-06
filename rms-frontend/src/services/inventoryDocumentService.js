import api from '@/services/api'


async function openPdf(
  endpoint,
  filename = 'document.pdf',
)
{
  let previewWindow = null

  try {
    /*
    |--------------------------------------------------------------------------
    | Open a blank tab immediately from the user's click
    |--------------------------------------------------------------------------
    |
    | This reduces browser popup-blocker problems while the authenticated PDF
    | request is still loading.
    |
    */

    previewWindow =
      window.open(
        '',
        '_blank',
      )

    if (
      previewWindow
    ) {
      previewWindow.document.title =
        'Preparing PDF...'

      previewWindow.document.body.innerHTML =
        '<p style="font-family:Arial,sans-serif;padding:24px;">Preparing PDF...</p>'
    }


    const response =
      await api.get(
        endpoint,
        {
          responseType: 'blob',

          headers: {
            Accept: 'application/pdf',
          },
        },
      )


    const blob =
      response.data instanceof Blob
        ? response.data
        : new Blob(
            [
              response.data,
            ],
            {
              type: 'application/pdf',
            },
          )


    const url =
      URL.createObjectURL(
        blob,
      )


    if (
      previewWindow
      &&
      !previewWindow.closed
    ) {
      previewWindow.location.href =
        url
    } else {
      /*
      |--------------------------------------------------------------------------
      | Popup-blocker fallback
      |--------------------------------------------------------------------------
      */

      const anchor =
        document.createElement(
          'a',
        )

      anchor.href =
        url

      anchor.download =
        filename

      anchor.rel =
        'noopener'

      document.body.appendChild(
        anchor,
      )

      anchor.click()

      anchor.remove()
    }


    /*
    |--------------------------------------------------------------------------
    | Keep URL long enough for the browser PDF viewer to finish loading.
    |--------------------------------------------------------------------------
    */

    window.setTimeout(
      () => {
        URL.revokeObjectURL(
          url,
        )
      },
      120000,
    )


    return true
  } catch (error) {
    if (
      previewWindow
      &&
      !previewWindow.closed
    ) {
      previewWindow.close()
    }

    let readableMessage = ''

    const errorBlob =
      error?.response?.data

    if (
      errorBlob instanceof Blob
    ) {
      try {
        const errorText =
          await errorBlob.text()

        try {
          const parsed =
            JSON.parse(errorText)

          readableMessage =
            parsed?.message
            ||
            ''
        } catch {
          readableMessage =
            errorText
            ||
            ''
        }
      } catch {
        // Keep normal fallback below.
      }
    }

    if (
      readableMessage
      &&
      readableMessage.trim() !== ''
    ) {
      const readableError =
        new Error(
          readableMessage.trim(),
        )

      readableError.response =
        error?.response

      throw readableError
    }

    throw error
  }
}


function getErrorMessage(
  error,
  fallback =
    'Unable to open PDF document.',
)
{
  /*
  |--------------------------------------------------------------------------
  | Blob Error Responses
  |--------------------------------------------------------------------------
  |
  | Axios may return the server JSON error as a Blob because responseType=blob.
  | The simple fallback remains safe here; detailed dev errors are available in
  | the network tab / backend log.
  |
  */

  return (
    error?.response?.data?.message
    ||
    error?.message
    ||
    fallback
  )
}


function openPurchaseReturnPdf(
  purchaseReturnId,
  returnNo = null,
)
{
  return openPdf(
    `/inventory/documents/purchase-returns/${purchaseReturnId}/pdf`,
    `${returnNo || `purchase-return-${purchaseReturnId}`}.pdf`,
  )
}


function openPurchaseOrderPdf(
  purchaseOrderId,
)
{
  return openPdf(
    `/inventory/documents/purchase-orders/${purchaseOrderId}/pdf`,
    `purchase-order-${purchaseOrderId}.pdf`,
  )
}


function openPurchaseReceiptPdf(
  purchaseOrderId,
  receiptId,
  receiptNo = null,
)
{
  return openPdf(
    `/inventory/documents/purchase-orders/${purchaseOrderId}/receipts/${receiptId}/pdf`,
    `${receiptNo || `grn-${receiptId}`}.pdf`,
  )
}


export default {
  openPdf,
  openPurchaseReturnPdf,
  openPurchaseOrderPdf,
  openPurchaseReceiptPdf,
  getErrorMessage,
}
