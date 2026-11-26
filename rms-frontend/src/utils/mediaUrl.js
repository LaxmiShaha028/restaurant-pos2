const DEFAULT_API_BASE_URL =
  import.meta.env.VITE_API_BASE_URL ||
  (import.meta.env.VITE_API_ORIGIN
    ? `${import.meta.env.VITE_API_ORIGIN.replace(/\/+$/, '')}/api`
    : import.meta.env.PROD
      ? `${window.location.origin}/api`
      : "http://127.0.0.1:8000/api");

const DEFAULT_MEDIA_ORIGIN = import.meta.env.PROD
  ? window.location.origin
  : "http://127.0.0.1:8000";

export const getApiOrigin = () => {
  const apiBase = String(
    import.meta.env.VITE_API_BASE_URL ||
      DEFAULT_API_BASE_URL
  ).trim();

  if (!apiBase) {
    return "";
  }

  try {
    const parsedUrl = new URL(apiBase);

    return `${parsedUrl.protocol}//${parsedUrl.host}`;
  } catch {
    if (
      typeof window !== "undefined" &&
      apiBase.startsWith("/")
    ) {
      return window.location.origin;
    }

    return apiBase
      .replace(/\/api\/?$/i, "")
      .replace(/\/+$/, "");
  }
};

const normalizeStoragePath = (value) => {
  return String(value || "")
    .trim()
    .replace(/\\/g, "/")
    .replace(/^\/+/, "")
    .replace(/^public\//i, "")
    .replace(/^storage\//i, "");
};

export const resolveMediaUrl = (value) => {
  if (
    value === null ||
    value === undefined ||
    value === ""
  ) {
    return "";
  }

  const rawValue = String(value).trim();

  if (!rawValue) {
    return "";
  }

  if (
    rawValue.startsWith("blob:") ||
    rawValue.startsWith("data:")
  ) {
    return rawValue;
  }

  // API requests can be proxied through the frontend host while uploaded
  // media still lives on the Laravel host. Prefer an explicit media/backend
  // origin when configured, then fall back to the API origin.
  const apiOrigin = String(
    import.meta.env.VITE_BACKEND_URL ||
      import.meta.env.VITE_API_ORIGIN ||
      getApiOrigin() ||
      DEFAULT_MEDIA_ORIGIN
  ).trim().replace(/\/+$/, "");

  /*
   * Absolute URL
   */
  if (
    /^https?:\/\//i.test(rawValue)
  ) {
    try {
      const parsedUrl =
        new URL(rawValue);

      const storagePosition =
        parsedUrl.pathname.indexOf(
          "/storage/"
        );

      /*
       * Laravel storage image.
       *
       * Backend APP_URL wrong হলেও
       * current API origin ব্যবহার করবে।
       */
      if (storagePosition !== -1) {
        const storagePath =
          parsedUrl.pathname.slice(
            storagePosition
          );

        return (
          `${apiOrigin}${storagePath}` +
          `${parsedUrl.search}` +
          `${parsedUrl.hash}`
        );
      }

      /*
       * External URL
       */
      return rawValue;
    } catch {
      return rawValue;
    }
  }

  /*
   * /storage/menu-items/file.jpg
   */
  if (
    rawValue.startsWith("/storage/")
  ) {
    return `${apiOrigin}${rawValue}`;
  }

  /*
   * storage/menu-items/file.jpg
   */
  if (
    rawValue.startsWith("storage/")
  ) {
    return `${apiOrigin}/${rawValue}`;
  }

  /*
   * menu-items/file.jpg
   */
  const storagePath =
    normalizeStoragePath(rawValue);

  if (!storagePath) {
    return "";
  }

  return `${apiOrigin}/storage/${storagePath}`;
};
