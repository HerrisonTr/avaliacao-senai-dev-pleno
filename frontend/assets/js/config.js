export const API_BASE_URL = 'http://localhost:8080/api';
export const BASE_URL = 'http://localhost:8090';

export function base_url(page, extension = '.php') {
    return `${BASE_URL}/${page}${extension}`;
}
