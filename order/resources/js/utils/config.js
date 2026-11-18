export const appUrl = window.location.origin || 'http://127.0.0.1:8000/';
export const apiUrl = window.location.origin + '/api' || 'http://127.0.0.1:8000/api/';

export const USER_ROLE_USER = 'user';
export const USER_ROLE_ADMIN = 'admin';
export const availableUserRoles = [
    {name: 'User', code: USER_ROLE_USER},
    {name: 'Admin', code: USER_ROLE_ADMIN}
];