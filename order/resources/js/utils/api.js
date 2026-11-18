import axios from 'axios';
import {apiUrl} from './config';
import store from '@/store'


axios.defaults.baseURL = apiUrl;
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

let token = document.head.querySelector('meta[name="csrf-token"]');

if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}

export default {
    setHeader () {
        axios.defaults.headers.common['Authorization'] = sessionStorage.getItem('lensware');
    },

    unsetHeader () {
        delete axios.defaults.headers.common['Authorization'];
    },

    addAuthParameters (data) {
        data.uid = sessionStorage.getItem('lensware_uid');
        data.pwd = sessionStorage.getItem('lensware_pwd');
        data = {
            ...data,
            ...store.getters.user,
            'langCode': store.getters.getLanguageCode
        }
        return data;
    },

    query (resource, params) {
        params = params || {};
        params._ = new Date().getTime();
        return axios.get(resource, {params: params});
    },

    get (resource, slug = '') {
        var path = (slug != null && slug !== '') ? resource + '/' + slug : resource;
        path += '?_=' + new Date().getTime();
        return axios.get(path);
    },

    post (resource, params) {
        return axios.post(resource, params);
    },

    put (resource, params) {
        return axios.put(resource, params);
    },

    update (resource, slug, params) {
        var path = (slug != null && slug !== '') ? resource + '/' + slug : resource;
        return axios.put(path, params);
    },

    delete (resource, slug) {
        var path = (slug != null && slug !== '') ? resource + '/' + slug : resource;
        return axios.delete(path);
    }
};