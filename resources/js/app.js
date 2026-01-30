// Alpine.data para o form de edição
Alpine.data('editClient', (id) => ({
    clientId: id,
    form: {
        name: '',
        email: '',
        cpf: '',
        phone: '',
        birth_date: '',
        is_active: true
    },
    errors: {},
    loading: false,
    loadingData: true,

    async init() {
        await this.fetchClient();
    },

    async fetchClient() {
        this.loadingData = true;
        try {
            const response = await fetch(`/api/clients/${this.clientId}`, {
                headers: {
                    'Authorization': `Bearer ${this.$store.auth.token}`,
                    'Accept': 'application/json'
                }
            });
            if (response.ok) {
                const result = await response.json();
                const data = result.data || result;
                this.form = {
                    name: data.name,
                    email: data.email,
                    cpf: data.cpf,
                    phone: data.phone || '',
                    birth_date: data.birth_date || '',
                    is_active: data.is_active
                };
            } else {
                this.$dispatch('notify', { message: 'Cliente não encontrado', type: 'error' });
                setTimeout(() => window.location.href = '/dashboard', 1500);
            }
        } catch (error) {
            this.$dispatch('notify', { message: 'Erro ao carregar cliente', type: 'error' });
        } finally {
            this.loadingData = false;
        }
    },

    async submit() {
        this.loading = true;
        this.errors = {};
        try {
            const formData = {
                ...this.form,
                cpf: this.form.cpf.replace(/\D/g, ''),
                phone: this.form.phone.replace(/\D/g, '')
            };
            const response = await fetch(`/api/clients/${this.clientId}`, {
                method: 'PUT',
                headers: {
                    'Authorization': `Bearer ${this.$store.auth.token}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(formData)
            });
            const data = await response.json();
            if (response.ok) {
                this.$dispatch('notify', { message: 'Cliente atualizado com sucesso!', type: 'success' });
                setTimeout(() => window.location.href = '/dashboard', 1000);
            } else {
                if (data.errors) {
                    this.errors = data.errors;
                } else {
                    this.$dispatch('notify', { message: data.message || 'Erro ao atualizar cliente', type: 'error' });
                }
            }
        } catch (error) {
            this.$dispatch('notify', { message: 'Erro ao atualizar cliente', type: 'error' });
        } finally {
            this.loading = false;
        }
    },

    formatCPF() {
        let value = this.form.cpf.replace(/\D/g, '');
        if (value.length > 11) value = value.slice(0, 11);
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
        this.form.cpf = value;
    },
    formatPhone() {
        let value = this.form.phone.replace(/\D/g, '');
        if (value.length > 11) value = value.slice(0, 11);
        if (value.length > 10) {
            value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
        } else if (value.length > 5) {
            value = value.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
        } else if (value.length > 2) {
            value = value.replace(/(\d{2})(\d{0,5})/, '($1) $2');
        }
        this.form.phone = value;
    }
}));
import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

// Estado global da aplicação
Alpine.store('auth', {
    token: localStorage.getItem('auth_token'),
    user: null,

    setToken(token) {
        this.token = token;
        localStorage.setItem('auth_token', token);
    },

    clearToken() {
        this.token = null;
        this.user = null;
        localStorage.removeItem('auth_token');
    },

    async fetchUser() {
        if (!this.token) return;

        try {
            const response = await fetch('/api/me', {
                headers: {
                    'Authorization': `Bearer ${this.token}`,
                    'Accept': 'application/json'
                }
            });

            if (response.ok) {
                this.user = await response.json();
            } else {
                this.clearToken();
            }
        } catch (error) {
            this.clearToken();
        }
    },

    isAuthenticated() {
        return !!this.token;
    }
});

// Componente de notificações
Alpine.data('notifications', () => ({
    items: [],

    add(message, type = 'success') {
        const id = Date.now();
        this.items.push({ id, message, type });

        setTimeout(() => {
            this.remove(id);
        }, 5000);
    },

    remove(id) {
        this.items = this.items.filter(item => item.id !== id);
    }
}));

// Componente de clientes
Alpine.data('clientsManager', () => ({
    init() {
        // ...nenhum log ou debug...
    },
    clients: [],
    filters: {
        name: '',
        email: '',
        cpf: '',
        phone: '',
        is_active: '',
        sort_by: 'created_at',
        sort_order: 'desc',
        per_page: 20
    },
    pagination: {
        current_page: 1,
        total: 0,
        per_page: 20,
        last_page: 1
    },
    loading: false,
    selectedIds: [],
    showDeleteModal: false,
    deleteTarget: null,

    async init() {
        await this.fetchClients();
    },

    async fetchClients(page = 1) {
        this.loading = true;

        const params = new URLSearchParams({
            ...this.filters,
            page
        });

        // Remove parâmetros vazios
        for (let [key, value] of params.entries()) {
            if (!value) params.delete(key);
        }

        try {
            const response = await fetch(`/api/clients?${params}`, {
                headers: {
                    'Authorization': `Bearer ${this.$store.auth.token}`,
                    'Accept': 'application/json'
                }
            });

            if (response.ok) {
                const data = await response.json();
                this.clients = data.data;
                this.pagination = {
                    current_page: data.meta.current_page,
                    total: data.meta.total,
                    per_page: data.meta.per_page,
                    last_page: data.meta.last_page
                };
            } else {
                throw new Error('Erro ao carregar clientes');
            }
        } catch (error) {
            this.$dispatch('notify', { message: 'Erro ao carregar clientes', type: 'error' });
        } finally {
            this.loading = false;
        }
    },

    async deleteClient(id) {
        try {
            const response = await fetch(`/api/clients/${id}`, {
                method: 'DELETE',
                headers: {
                    'Authorization': `Bearer ${this.$store.auth.token}`,
                    'Accept': 'application/json'
                }
            });

            if (response.ok) {
                this.$dispatch('notify', { message: 'Cliente deletado com sucesso!', type: 'success' });
                await this.fetchClients(this.pagination.current_page);
            } else {
                throw new Error('Erro ao deletar cliente');
            }
        } catch (error) {
            this.$dispatch('notify', { message: error.message, type: 'error' });
        }
    },

    async deleteSelected() {
        if (this.selectedIds.length === 0) return;

        try {
            const response = await fetch('/api/clients', {
                method: 'DELETE',
                headers: {
                    'Authorization': `Bearer ${this.$store.auth.token}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ ids: this.selectedIds })
            });

            if (response.ok) {
                const data = await response.json();
                this.$dispatch('notify', {
                    message: `${data.deleted_count} cliente(s) deletado(s) com sucesso!`,
                    type: 'success'
                });
                this.selectedIds = [];
                await this.fetchClients(this.pagination.current_page);
            } else {
                throw new Error('Erro ao deletar clientes');
            }
        } catch (error) {
            this.$dispatch('notify', { message: error.message, type: 'error' });
        }
    },

    toggleSort(field) {
        if (this.filters.sort_by === field) {
            this.filters.sort_order = this.filters.sort_order === 'asc' ? 'desc' : 'asc';
        } else {
            this.filters.sort_by = field;
            this.filters.sort_order = 'asc';
        }
        this.fetchClients(1);
    },

    toggleSelectAll() {
        if (this.selectedIds.length === this.clients.length) {
            this.selectedIds = [];
        } else {
            this.selectedIds = this.clients.map(c => c.id);
        }
    },

    confirmDelete(id = null) {
        this.deleteTarget = id;
        this.showDeleteModal = true;
    },

    async executeDelete() {
        if (this.deleteTarget) {
            await this.deleteClient(this.deleteTarget);
        } else if (this.selectedIds.length > 0) {
            await this.deleteSelected();
        }
        this.showDeleteModal = false;
        this.deleteTarget = null;
    }
}));

Alpine.start();
