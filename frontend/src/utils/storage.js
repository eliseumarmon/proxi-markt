const storageBaseUrl = (() => {
    const explicit = import.meta.env.VITE_STORAGE_URL;
    if (explicit) return explicit.replace(/\/+$/, "");

    const api = import.meta.env.VITE_API_URL || "";
    if (!api) return "";

    return api.replace(/\/api\/?$/, "");
})();

export const storageUrl = (path) => {
    if (!path || path === "0") return storageDefaultProductUrl();
    if (path.startsWith('http')) return path;
    if (path.startsWith('/storage/')) return path;

    const cleanPath = path.startsWith('productos/')
        ? path 
        : `productos/${path}`;

    return storageBaseUrl ? `${storageBaseUrl}/storage/${cleanPath}` : `/storage/${cleanPath}`;
};

export const storageDefaultProductUrl = () => {
    return storageBaseUrl
        ? `${storageBaseUrl}/storage/productos/default.png`
        : "/storage/productos/default.png";
};
