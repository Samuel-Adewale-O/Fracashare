export default {
    namespaced: true,
    
    state: () => ({
        user: null,
        token: localStorage.getItem('token'),
        isAuthenticated: false,
        kycStatus: null
    }),

    mutations: {
        SET_USER(state, user) {
            state.user = user;
            state.isAuthenticated = !!user;
            state.kycStatus = user?.kyc_status || null;
        },
        SET_TOKEN(state, token) {
            state.token = token;
            if (token) {
                localStorage.setItem('token', token);
            } else {
                localStorage.removeItem('token');
            }
        },
        SET_KYC_STATUS(state, status) {
            state.kycStatus = status;
            if (state.user) {
                state.user.kyc_status = status;
            }
        }
    },

    actions: {
        async register({ commit }, userData) {
            try {
                const response = await axios.post('/api/register', userData);
                const { user, token } = response.data;
                
                commit('SET_USER', user);
                commit('SET_TOKEN', token);
                
                return response;
            } catch (error) {
                throw error;
            }
        },

        async login({ commit }, credentials) {
            try {
                const response = await axios.post('/api/login', credentials);
                const { user, token } = response.data;
                
                commit('SET_USER', user);
                commit('SET_TOKEN', token);
                
                return response;
            } catch (error) {
                throw error;
            }
        },

        async logout({ commit }) {
            try {
                await axios.post('/api/logout');
                commit('SET_USER', null);
                commit('SET_TOKEN', null);
            } catch (error) {
                throw error;
            }
        },

        async fetchUser({ commit }) {
            try {
                const response = await axios.get('/api/user');
                commit('SET_USER', response.data);
                return response;
            } catch (error) {
                commit('SET_USER', null);
                commit('SET_TOKEN', null);
                throw error;
            }
        },

        async verifyKyc({ commit }, data) {
            try {
                const response = await axios.post('/api/kyc/verify', data);
                commit('SET_KYC_STATUS', response.data.data.kyc_status);
                return response;
            } catch (error) {
                throw error;
            }
        },

        async checkKycStatus({ commit }) {
            try {
                const response = await axios.get('/api/kyc/status');
                commit('SET_KYC_STATUS', response.data.data.kyc_status);
                return response;
            } catch (error) {
                throw error;
            }
        }
    },

    getters: {
        isAuthenticated: state => state.isAuthenticated,
        user: state => state.user,
        token: state => state.token,
        kycStatus: state => state.kycStatus,
        isKycVerified: state => state.kycStatus === 'verified'
    }
};