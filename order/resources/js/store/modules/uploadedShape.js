import ApiService from '@/utils/api';

export default {
    state () {},
    getters: {},
    mutations: {},
    actions: {
        uploadedShapeGet (context, data) {
            return new Promise((resolve, reject) => {
                data = ApiService.addAuthParameters(data);
                ApiService.post('/uploaded-shapes', data)
                    .then(({data}) => {
                        resolve(data);
                    })
                    .catch(({response}) => {
                        reject(response);
                    });
            });
        },
        uploadedShapeSave (context, formData) {
            return new Promise((resolve, reject) => {
                const auth = ApiService.addAuthParameters({});
                Object.entries(auth).forEach(([key,val]) => formData.append(key,val))
                ApiService.post('/uploaded-shapes/save', formData)
                    .then(({data}) => {
                        resolve(data);
                    })
                    .catch(({response}) => {
                        reject(response);
                    });
            });
        },
        uploadedShapeDelete (context, data) {
            return new Promise((resolve, reject) => {
                data = ApiService.addAuthParameters(data)
                ApiService.post('/uploaded-shapes/delete', data)
                    .then(({data}) => {
                        resolve(data);
                    })
                    .catch(({response}) => {
                        reject(response);
                    });
            });
        },
        getStandardShapesImages (context, data) {
            return new Promise((resolve, reject) => {
                data = ApiService.addAuthParameters(data);
                ApiService.post('/uploaded-shapes/standard-shapes-images', data)
                    .then(({data}) => {
                        resolve(data);
                    })
                    .catch(({response}) => {
                        reject(response);
                    });
            });
        },
        getStandardShapeForm (context, data) {
            return new Promise((resolve, reject) => {
                data = ApiService.addAuthParameters(data);
                ApiService.post('/uploaded-shapes/standard-shape-form', data)
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