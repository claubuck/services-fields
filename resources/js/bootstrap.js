import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.withCredentials = true;

const token = document.cookie.split('; ').find(row => row.startsWith('XSRF-TOKEN='));
if (token) {
    window.axios.defaults.headers.common['X-XSRF-TOKEN'] = decodeURIComponent(token.split('=')[1]);
}
