import { API_BASE_URL } from './config.js';
import { getToken, handleUnauthorized } from './auth.js';

const DEFAULT_TIMEOUT_MS = 10000;

class HttpError extends Error {
  constructor(message, { status, data } = {}) {
    super(message);
    this.name = 'HttpError';
    this.status = status;
    this.data = data;
  }
}

async function request(endpoint, options = {}) {
  const { timeoutMs = DEFAULT_TIMEOUT_MS, headers: customHeaders, ...fetchOptions } = options;
  
  const token = getToken();
  const controller = new AbortController();
  const timeoutId = window.setTimeout(() => controller.abort(), timeoutMs);

  const headers = {
    'Content-Type': 'application/json',
    ...customHeaders,
  };

  // Anexa o token automaticamente quando o usuário estiver autenticado.
  if (token) {
    headers.Authorization = `Bearer ${token}`;
  }

  try {
    const response = await fetch(`${API_BASE_URL}${endpoint}`, {
      ...fetchOptions,
      headers,
      signal: controller.signal,
    });

    const data = await parseResponse(response);

    if (response.status === 401) {
      handleUnauthorized();
      throw new HttpError('Sua sessão expirou. Faça login novamente.', {
        status: response.status,
        data,
      });
    }

    if (!response.ok) {
      const message = data?.message || data?.error || 'Ocorreu um erro inesperado.';
      throw new HttpError(message, {
        status: response.status,
        data,
      });
    }

    return data;
  } catch (error) {
    if (error.name === 'AbortError') {
      throw new HttpError('A requisição demorou demais para responder.', {
        status: 408,
      });
    }

    if (error instanceof HttpError) {
      throw error;
    }

    throw new HttpError('Não foi possível se comunicar com o servidor.', {
      status: 0,
    });
  } finally {
    window.clearTimeout(timeoutId);
  }
}

async function parseResponse(response) {
  const contentType = response.headers.get('content-type') || '';

  if (contentType.includes('application/json')) {
    try {
      return await response.json();
    } catch {
      return null;
    }
  }

  const text = await response.text();
  return text || null;
}

export const http = {
  get(endpoint) {
    return request(endpoint);
  },

  post(endpoint, body, options = {}) {
    return request(endpoint, {
      method: 'POST',
      body: JSON.stringify(body),
      ...options,
    });
  },

  put(endpoint, body, options = {}) {
    return request(endpoint, {
      method: 'PUT',
      body: JSON.stringify(body),
      ...options,
    });
  },

  delete(endpoint, options = {}) {
    return request(endpoint, {
      method: 'DELETE',
      ...options,
    });
  },
};

export { HttpError };
