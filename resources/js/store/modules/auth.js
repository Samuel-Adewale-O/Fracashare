import axios from 'axios';

export default {
    namespaced: true,
    
    state: {
        user: null,
        token: localStorage.getItem('token'),
        loading: false,
        error: null
    },

    mutations: {
        SET_USER(state, user) {
            state.user = user;
        },
        SET_TOKEN(state, token) {
            state.token = token;
            if (token) {
                localStorage.setItem('token', token);
                axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
            } else {
                localStorage.removeItem('token');
                delete axios.defaults.headers.common['Authorization'];
            }
        },
        SET_LOADING(state, loading) {
            state.loading = loading;
        },
        SET_ERROR(state, error) {
            state.error = error;
        }
    },

    actions: {
        async login({ commit }, credentials) {
            commit('SET_LOADING', true);
            commit('SET_ERROR', null);
            try {
                const response = await axios.post('/api/auth/login', credentials);
                const { user, token } = response.data;
                commit('SET_USER', user);
                commit('SET_TOKEN', token);
                return response;
            } catch (error) {
                commit('SET_ERROR', error.response?.data?.message || 'Login failed');
                throw error;
            } finally {
                commit('SET_LOADING', false);
            }
        },

        async register({ commit }, userData) {
            commit('SET_LOADING', true);
            commit('SET_ERROR', null);
            try {
                const response = await axios.post('/api/auth/register', userData);
                return response;
            } catch (error) {
                commit('SET_ERROR', error.response?.data?.message || 'Registration failed');
                throw error;
            } finally {
                commit('SET_LOADING', false);
            }
        },

        async logout({ commit }) {
            try {
                await axios.post('/api/auth/logout');
            } finally {
                commit('SET_USER', null);
                commit('SET_TOKEN', null);
            }
        },

        async fetchUser({ commit }) {
            commit('SET_LOADING', true);
            try {
                const response = await axios.get('/api/auth/user');
                commit('SET_USER', response.data);
                return response;
            } catch (error) {
                commit('SET_USER', null);
                commit('SET_TOKEN', null);
                throw error;
            } finally {
                commit('SET_LOADING', false);
            }
        },

        async verifyEmail({ commit }, token) {
            commit('SET_LOADING', true);
            try {
                const response = await axios.post(`/api/auth/email/verify/${token}`);
                return response;
            } catch (error) {
                commit('SET_ERROR', error.response?.data?.message || 'Email verification failed');
                throw error;
            } finally {
                commit('SET_LOADING', false);
            }
        },

        async sendPasswordResetLink({ commit }, email) {
            commit('SET_LOADING', true);
            try {
                const response = await axios.post('/api/auth/password/email', { email });
                return response;
            } catch (error) {
                commit('SET_ERROR', error.response?.data?.message || 'Failed to send reset link');
                throw error;
            } finally {
                commit('SET_LOADING', false);
            }
        },

        async resetPassword({ commit }, data) {
            commit('SET_LOADING', true);
            try {
                const response = await axios.post('/api/auth/password/reset', data);
                return response;
            } catch (error) {
                commit('SET_ERROR', error.response?.data?.message || 'Password reset failed');
                throw error;
            } finally {
                commit('SET_LOADING', false);
            }
        }
    },

    getters: {
        isAuthenticated: state => !!state.token,
        user: state => state.user,
        loading: state => state.loading,
        error: state => state.error
    }
};