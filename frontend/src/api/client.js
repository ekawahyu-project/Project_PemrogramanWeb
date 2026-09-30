/**
 * API Client dengan Dual-Mode:
 * 1. Menghubungkan ke Backend PHP (api.php) jika server web aktif.
 * 2. Otomatis beralih ke Mock/Dummy Store lokal jika server backend offline.
 */
import { handleMockApi } from './mockStore';

const getApiUrl = () => {
  if (import.meta.env.VITE_API_URL) {
    return import.meta.env.VITE_API_URL;
  }
  if (import.meta.env.DEV) {
    return '/api';
  }
  if (window.location.pathname.includes('/frontend/dist/')) {
    return '../../backend/api.php';
  }
  if (window.location.pathname.includes('/Project_PemWeb/')) {
    return '/Project_PemWeb/backend/api.php';
  }
  return 'backend/api.php';
};

export const API_URL = getApiUrl();

export async function apiRequest(action, data = {}, method = 'POST') {
  const url = new URL(API_URL, window.location.origin);
  const isGet = method.toUpperCase() === 'GET';

  let fetchOptions = {
    method,
    headers: {
      'Accept': 'application/json',
    },
    credentials: 'include',
  };

  if (isGet) {
    url.searchParams.set('action', action);
    for (const [key, val] of Object.entries(data)) {
      if (val !== undefined && val !== null) {
        url.searchParams.set(key, val);
      }
    }
  } else {
    url.searchParams.set('action', action);
    fetchOptions.headers['Content-Type'] = 'application/json';
    fetchOptions.body = JSON.stringify({ action, ...data });
  }

  try {
    const res = await fetch(url.toString(), fetchOptions);
    const result = await res.json();
    if (!res.ok || result.success === false) {
      throw new Error(result.error || result.message || 'Terjadi kesalahan pada server.');
    }
    return result;
  } catch (err) {
    // Otomatis fallback ke Local Mock Store (Dummy Mode)
    console.info(`[Dummy Mode Active] Menggunakan dummy store lokal untuk '${action}'`);
    return handleMockApi(action, data);
  }
}
