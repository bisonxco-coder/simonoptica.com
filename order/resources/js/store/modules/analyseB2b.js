import ApiService from '@/utils/api';

export default {
    state() {
    },
    getters: {},
    mutations: {},
    actions: {
        uploadedAnalyseB2bSave (context, formData) {
            return new Promise((resolve, reject) => {
                const auth = ApiService.addAuthParameters({});
                Object.entries(auth).forEach(([key,val]) => formData.append(key,val))
                ApiService.post('/uploaded-analyse-b2b', formData)
                    .then(({data}) => {
                        resolve(data);
                    })
                    .catch(({response}) => {
                        reject(response);
                    });
            });
        },
    }
}