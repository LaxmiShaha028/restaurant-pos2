import api from '@/services/api'


const BASE_URL =
  '/inventory/transfer-templates'


async function getTemplates(
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


async function getTemplate(
  templateId,
)
{
  const response =
    await api.get(
      `${BASE_URL}/${templateId}`,
    )

  return response.data
}


async function createTemplate(
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


async function updateTemplate(
  templateId,
  payload,
)
{
  const response =
    await api.put(
      `${BASE_URL}/${templateId}`,
      payload,
    )

  return response.data
}


async function updateStatus(
  templateId,
  isActive,
)
{
  const response =
    await api.patch(
      `${BASE_URL}/${templateId}/status`,
      {
        is_active:
          Boolean(isActive),
      },
    )

  return response.data
}


async function deleteTemplate(
  templateId,
)
{
  const response =
    await api.delete(
      `${BASE_URL}/${templateId}`,
    )

  return response.data
}


function getErrorMessage(
  error,
  fallback =
    'Unable to process the stock transfer template.',
)
{
  const errors =
    error?.response?.data?.errors

  if (
    errors
    &&
    typeof errors === 'object'
  ) {
    const validationMessage =
      Object.values(errors)
        .flat()
        .filter(
          (message) =>
            typeof message ===
            'string',
        )
        .join(' ')
        .trim()

    if (
      validationMessage
    ) {
      return validationMessage
    }
  }

  return (
    error?.response?.data?.message
    ||
    error?.message
    ||
    fallback
  )
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


export default {
  getTemplates,
  getTemplate,
  createTemplate,
  updateTemplate,
  updateStatus,
  deleteTemplate,
  getErrorMessage,
  getValidationErrors,
}
