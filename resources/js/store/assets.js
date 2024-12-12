export default {
    namespaced: true,
    
    state: () => ({
        assets: [],
        currentAsset: null,
        loading: false,
        error: null
    }),

    mutations: {
        SET_ASSETS(state, assets) {
            state.assets = assets;
        },
        SET_CURRENT_ASSET(state, asset) {
            state.currentAsset = asset;
        },
        SET_LOADING(state, loading) {
            state.loading = loading;
        },
        SET_ERROR(state, error) {
            state.error = error;
        }
    },

    actions: {
        async fetchAssets({ commit }, filters = {}) {
            commit('SET_LOADING', true);
            try {
                const response = await axios.get('/api/assets', { params: filters });
                commit('SET_ASSETS', response.data.data);
                return response;
            } catch (error) {
                commit('SET_ERROR', error.response?.data?.message || 'Failed to fetch assets');
                throw error;
            } finally {
                commit('SET_LOADING', false);
            }
        },

        async fetchAsset({ commit }, id) {
            commit('SET_LOADING', true);
            try {
                const response = await axios.get(`/api/assets/${id}`);
                commit('SET_CURRENT_ASSET', response.data.data);
                return response;
            } catch (error) {
                commit('SET_ERROR', error.response?.data?.message || 'Failed to fetch asset');
                throw error;
            } finally {
                commit('SET_LOADING', false);
            }
        }
    },

    getters: {
        getAssetById: state => id => state.assets.find(asset => asset.id === id),
        filteredAssets: state => type => state.assets.filter(asset => asset.type === type)
    }
};