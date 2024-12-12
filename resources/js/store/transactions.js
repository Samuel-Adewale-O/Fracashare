export default {
    namespaced: true,
    
    state: () => ({
        transactions: [],
        currentTransaction: null,
        loading: false,
        error: null
    }),

    mutations: {
        SET_TRANSACTIONS(state, transactions) {
            state.transactions = transactions;
        },
        SET_CURRENT_TRANSACTION(state, transaction) {
            state.currentTransaction = transaction;
        },
        SET_LOADING(state, loading) {
            state.loading = loading;
        },
        SET_ERROR(state, error) {
            state.error = error;
        }
    },

    actions: {
        async fetchTransactions({ commit }) {
            commit('SET_LOADING', true);
            try {
                const response = await axios.get('/api/transactions');
                commit('SET_TRANSACTIONS', response.data.data);
                return response;
            } catch (error) {
                commit('SET_ERROR', error.response?.data?.message || 'Failed to fetch transactions');
                throw error;
            } finally {
                commit('SET_LOADING', false);
            }
        },

        async initiateInvestment({ commit }, { assetId, shares }) {
            commit('SET_LOADING', true);
            try {
                const response = await axios.post(`/api/investments/assets/${assetId}/invest`, { shares });
                return response;
            } catch (error) {
                commit('SET_ERROR', error.response?.data?.message || 'Failed to initiate investment');
                throw error;
            } finally {
                commit('SET_LOADING', false);
            }
        }
    },

    getters: {
        getTransactionById: state => id => state.transactions.find(tx => tx.id === id),
        pendingTransactions: state => state.transactions.filter(tx => tx.status === 'pending')
    }
};