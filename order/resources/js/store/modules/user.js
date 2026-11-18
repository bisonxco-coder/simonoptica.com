import ApiService from '@/utils/api';
import { VueCookieNext } from 'vue-cookie-next'

export default {
    state: {
        isAuthenticated: !!sessionStorage.getItem('lensware_uid') && !!sessionStorage.getItem('lensware_pwd'),
        user: localStorage.getItem('user') && JSON.parse(localStorage.getItem('user')) || {}
    },
    getters: {
        isAuthenticated: state => state.isAuthenticated,
        userUID: state => sessionStorage.getItem('lensware_uid'),
        user: state => state.user,
        isManager: state => {
            if (state.isAuthenticated === undefined || !state.isAuthenticated)
                return false

            if (state.user === undefined)
                return false

            if (Object.entries(state.user).length == 0)
                return false

            if (state.user.ALIAS2 === undefined)
                return false

            const alias2Value = state.user.ALIAS2.trim()
            if (alias2Value.length == 0)
                return false

            return true
        }
    },
    mutations: {
        setAuth (state, data) {
            state.isAuthenticated = true;
            // sessionStorage.setItem('lensware', data.token_type + ' ' + data.access_token);
            // state.user = data.user;
            // localStorage.setItem('user', JSON.stringify(data.user));

            sessionStorage.setItem('lensware_uid', data.UID);
            sessionStorage.setItem('lensware_pwd', data.PWD);

            var userData = {
                LANG: data.LANG,
                CUSTLIST: data.CUSTLIST,
                PRODLIST: data.PRODLIST,
                PRICELIST: data.PRICELIST,
                STATUS: data.STATUS,
                ALIAS2: data.ALIAS2
            };

            state.user = userData;
            localStorage.setItem('user', JSON.stringify(userData));
        },
        setUser (state, data) {
            // state.user = data;
            // localStorage.setItem('user', JSON.stringify(data));
        },
        purgeAuth (state) {
            state.isAuthenticated = false;
            sessionStorage.clear();
        },
        purgeUser (state) {
            sessionStorage.clear();
            localStorage.removeItem('user');
            state.user = {};
        }
    },
    actions: {
        login (context, data) {
            data = data || {};

            return new Promise((resolve, reject) => {
                ApiService.unsetHeader();
                ApiService.post('soap/login', data)
                    .then(({data}) => {
                        context.commit('setAuth', data);
                        if(data.RMB !== '')
                          VueCookieNext.setCookie('lensorderweb_token', data.RMB)
                        resolve(data);
                    })
                    .catch(({response}) => {
                        reject(response);
                    });
            });
        },
        logout (context, data) {
          return new Promise((resolve, reject) => {
            ApiService.unsetHeader();
            ApiService.post('soap/logout', data)
              .then(({data}) => {
                VueCookieNext.removeCookie('lensorderweb_token')
                context.commit('purgeAuth');
                context.commit('purgeUser');
                resolve(data);
              })
              .catch(({response}) => {
                VueCookieNext.removeCookie('lensorderweb_token')
                context.commit('purgeAuth');
                context.commit('purgeUser');
                reject(response);
              });
          });
            /*return new Promise((resolve, reject) => {
                ApiService.setHeader();
                ApiService.post('auth/logout')
                    .then(({data}) => {
                        ApiService.unsetHeader();
                        context.commit('purgeAuth');
                        context.commit('purgeUser');
                        resolve(data);
                    })
                    .catch(({response}) => {
                        reject(response);
                    });
            });*/
        },
        changePassword (context, data) {
            data = data || {};
            return new Promise((resolve, reject) => {
              data = ApiService.addAuthParameters(data);
                ApiService.post('soap/changePassword', data)
                    .then(({response}) => {
                        context.commit('setAuth', {UID: sessionStorage.getItem('lensware_uid'), PWD: data.pwdNew});
                        resolve(data);
                    })
                    .catch(({response}) => {
                        reject(response);
                    });
            });
        },
        forgotPassword (context, data) {
            return new Promise((resolve, reject) => {
                ApiService.unsetHeader();
                ApiService.post('auth/forgot', data)
                    .then(({data}) => {
                        resolve(data);
                    })
                    .catch(({response}) => {
                        reject(response);
                    });
            });
        },
        resetPassword (context, data) {
            return new Promise((resolve, reject) => {
                ApiService.unsetHeader();
                ApiService.post('auth/reset', data)
                    .then(({data}) => {
                        resolve(data);
                    })
                    .catch(({response}) => {
                        reject(response);
                    });
            });
        },
        register (context, data) {
            return new Promise((resolve, reject) => {
                ApiService.unsetHeader();
                ApiService.post('auth/register', data)
                    .then(({data}) => {
                        context.commit('setAuth', data);
                        resolve(data);
                    })
                    .catch(({response}) => {
                        reject(response);
                    });
            });
        },
        getUser (context) {
            return new Promise((resolve, reject) => {
                ApiService.setHeader();
                ApiService.get('users/user')
                    .then(({data}) => {
                        context.commit('setUser', data);
                        resolve(data);
                    })
                    .catch(({response}) => {
                        reject(response);
                    });
            });
        },
        updateUser (context, data) {
            return new Promise((resolve, reject) => {
                ApiService.setHeader();
                ApiService.put('users/user', data)
                    .then(({data}) => {
                        context.commit('setUser', data);
                        resolve(data);
                    })
                    .catch(({response}) => {
                        reject(response);
                    });
            });
        },
        updatePassword (context, data) {
            return new Promise((resolve, reject) => {
                ApiService.setHeader();
                ApiService.put('users/user/password', data)
                    .then(({data}) => {
                        resolve(data);
                    })
                    .catch(({response}) => {
                        reject(response);
                    });
            });
        },

        // Admin routes
        adminGetUsers (context, data) {
            return new Promise((resolve, reject) => {
                ApiService.setHeader();
                ApiService.query('users/all', data)
                    .then(({data}) => {
                        resolve(data);
                    })
                    .catch(({response}) => {
                        reject(response);
                    });
            });
        },
        adminAddUser (context, data) {
            return new Promise((resolve, reject) => {
                ApiService.setHeader();
                ApiService.post('users', data)
                    .then(({data}) => {
                        resolve(data);
                    })
                    .catch(({response}) => {
                        reject(response);
                    });
            });
        },
        adminGetUser (context, userId) {
            if (userId == null) return;

            return new Promise((resolve, reject) => {
                ApiService.setHeader();
                ApiService.get('users', userId)
                    .then(({data}) => {
                        resolve(data);
                    })
                    .catch(({response}) => {
                        reject(response);
                    });
            });
        },
        adminUpdateUser (context, data) {
            if (data.id == null) return;
            var userId = data.id;

            return new Promise((resolve, reject) => {
                ApiService.setHeader();
                ApiService.put('users/' + userId, data)
                    .then(({data}) => {
                        resolve(data);
                    })
                    .catch(({response}) => {
                        reject(response);
                    });
            });
        },
        adminDeleteUser (context, userId) {
            if (userId == null) return;

            return new Promise((resolve, reject) => {
                ApiService.setHeader();
                ApiService.delete('users', userId)
                    .then(({data}) => {
                        resolve(data);
                    })
                    .catch(({response}) => {
                        reject(response);
                    });
            });
        }
    }
}
