<template>
  <div class="d-flex" style="min-height: 100vh">
    <!-- Sidebar -->
    <nav
      class="d-flex flex-column p-3 bg-dark text-white"
      style="width: 220px; min-height: 100vh"
    >
      <h6 class="fw-bold mb-4 mt-2">Travel Orders</h6>

      <ul class="nav nav-pills flex-column flex-grow-1 gap-1">
        <li class="nav-item">
          <RouterLink to="/" class="nav-link text-white">Pedidos</RouterLink>
        </li>
        <li class="nav-item">
          <RouterLink to="/orders/new" class="nav-link text-white"
            >Novo Pedido</RouterLink
          >
        </li>
      </ul>

      <div class="border-top border-secondary pt-3 mt-3">
        <p class="mb-1 small">{{ auth.user?.name }}</p>
        <p class="mb-2 text-secondary" style="font-size: 0.75rem">
          {{ auth.isAdmin ? "Administrador" : "Usuário" }}
        </p>
        <button
          class="btn btn-sm btn-outline-secondary w-100"
          :disabled="loggingOut"
          @click="handleLogout"
        >
          {{ loggingOut ? "Saindo..." : "Sair" }}
        </button>
      </div>
    </nav>

    <!-- Content -->
    <div class="flex-grow-1 p-4 bg-light">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h4 class="mb-0 fw-bold">Detalhes do Pedido</h4>
          <span class="text-muted small"
            >Informações da solicitação de viagem</span
          >
        </div>
        <RouterLink to="/" class="btn btn-sm btn-outline-secondary"
          >Voltar</RouterLink
        >
      </div>

      <!-- Loading -->
      <div v-if="loading" class="text-center py-5">
        <div class="spinner-border text-primary" role="status"></div>
        <p class="mt-2 text-muted small">Carregando pedido...</p>
      </div>

      <div v-else-if="order">
        <!-- Card principal -->
        <div class="card border-0 shadow-sm mb-4">
          <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-start mb-4">
              <div>
                <h5 class="fw-bold mb-1">{{ order.destination }}</h5>
                <span class="text-muted small">Pedido #{{ order.id }}</span>
              </div>
              <span :class="statusClass(order.status)">{{ order.status }}</span>
            </div>

            <div class="row g-3">
              <div class="col-md-4">
                <p class="text-muted small fw-semibold mb-1 text-uppercase">
                  Solicitante
                </p>
                <p class="mb-0">{{ order.requester_name }}</p>
              </div>
              <div class="col-md-4">
                <p class="text-muted small fw-semibold mb-1 text-uppercase">
                  Data de Ida
                </p>
                <p class="mb-0">{{ formatDate(order.departure_date) }}</p>
              </div>
              <div class="col-md-4">
                <p class="text-muted small fw-semibold mb-1 text-uppercase">
                  Data de Volta
                </p>
                <p class="mb-0">{{ formatDate(order.return_date) }}</p>
              </div>
              <div class="col-md-4">
                <p class="text-muted small fw-semibold mb-1 text-uppercase">
                  Criado em
                </p>
                <p class="mb-0">{{ formatDate(order.created_at) }}</p>
              </div>
              <div class="col-md-4">
                <p class="text-muted small fw-semibold mb-1 text-uppercase">
                  Atualizado em
                </p>
                <p class="mb-0">{{ formatDate(order.updated_at) }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Ações admin -->
        <div
          v-if="auth.isAdmin && order.status === 'solicitado'"
          class="card border-0 shadow-sm mb-4"
        >
          <div class="card-body p-4">
            <h6 class="fw-semibold mb-3">Atualizar Status</h6>
            <div class="d-flex gap-2">
              <button
                class="btn btn-outline-success"
                :disabled="!!updating"
                @click="askConfirmation('aprovado')"
              >
                Aprovar Pedido
              </button>
              <button
                class="btn btn-outline-danger"
                :disabled="!!updating"
                @click="askConfirmation('cancelado')"
              >
                Cancelar Pedido
              </button>
            </div>
          </div>
        </div>

        <!-- Notificações -->
        <div v-if="notifications.length > 0" class="card border-0 shadow-sm">
          <div class="card-body p-4">
            <h6 class="fw-semibold mb-3">Notificações</h6>
            <div
              v-for="notification in notifications"
              :key="notification.id"
              class="d-flex justify-content-between align-items-center p-3 rounded mb-2"
              :class="notification.read_at ? 'bg-light' : 'bg-primary-subtle'"
            >
              <span class="small">{{ notification.data.message }}</span>
              <button
                v-if="!notification.read_at"
                class="btn btn-sm btn-outline-primary"
                @click="markAsRead(notification.id)"
              >
                Marcar como lida
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
    <ConfirmModal
      :show="confirmModal.show"
      :message="`Tem certeza que deseja ${
        confirmModal.status === 'aprovado' ? 'aprovar' : 'cancelar'
      } o pedido #${order?.id}?`"
      :confirm-text="
        confirmModal.status === 'aprovado' ? 'Aprovar' : 'Cancelar'
      "
      :variant="confirmModal.status === 'aprovado' ? 'success' : 'danger'"
      :loading="!!updating"
      @confirm="confirmAction"
      @cancel="closeModal"
    />
  </div>
</template>

<script setup>
import ConfirmModal from "../components/ConfirmModal.vue";
import { ref, onMounted } from "vue";
import { RouterLink, useRoute, useRouter } from "vue-router";
import { useAuthStore } from "../stores/auth";
import { useToast } from "vue-toastification";
import api from "../api/axios";

const auth = useAuthStore();
const route = useRoute();
const router = useRouter();
const toast = useToast();
const order = ref(null);
const loading = ref(false);
const updating = ref(null);
const loggingOut = ref(false);
const notifications = ref([]);
const confirmModal = ref({ show: false, status: null });

async function loadOrder() {
  loading.value = true;
  try {
    const response = await api.get(`/travel-orders/${route.params.id}`);
    order.value = response.data;
  } catch {
    toast.error("Pedido não encontrado.");
    router.push("/");
  } finally {
    loading.value = false;
  }
}

async function loadNotifications() {
  try {
    const response = await api.get("/notifications");
    notifications.value = response.data.filter(
      (n) => n.data.order_id === order.value?.id
    );
  } catch {}
}

function askConfirmation(status) {
  confirmModal.value = { show: true, status };
}

function closeModal() {
  confirmModal.value = { show: false, status: null };
}

async function confirmAction() {
  updating.value = confirmModal.value.status;
  try {
    await api.patch(`/travel-orders/${order.value.id}/status`, {
      status: confirmModal.value.status,
    });
    toast.success(`Pedido ${confirmModal.value.status} com sucesso!`);
    closeModal();
    await loadOrder();
    await loadNotifications();
  } catch (error) {
    toast.error(error.response?.data?.message || "Erro ao atualizar status.");
  } finally {
    updating.value = null;
  }
}

async function markAsRead(id) {
  try {
    await api.patch(`/notifications/${id}/read`);
    await loadNotifications();
  } catch {}
}

async function handleLogout() {
  loggingOut.value = true;
  try {
    await auth.logout();
    router.push("/login");
  } finally {
    loggingOut.value = false;
  }
}

function formatDate(date) {
  return new Date(date).toLocaleDateString("pt-BR", { timeZone: "UTC" });
}

function statusClass(status) {
  const map = {
    solicitado: "badge bg-primary-subtle text-primary",
    aprovado: "badge bg-success-subtle text-success",
    cancelado: "badge bg-danger-subtle text-danger",
  };
  return map[status] || "badge bg-secondary";
}

onMounted(async () => {
  await loadOrder();
  await loadNotifications();
});
</script>