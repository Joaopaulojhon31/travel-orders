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
      <!-- Topbar -->
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h4 class="mb-0 fw-bold">Pedidos de Viagem</h4>
          <span class="text-muted small"
            >Gerencie suas solicitações corporativas</span
          >
        </div>

        <div class="d-flex align-items-center gap-3">
          <!-- Sino de notificações -->
          <div class="position-relative">
            <button
              class="btn btn-outline-secondary btn-sm position-relative"
              @click.stop="toggleNotifications"
            >
              🔔
              <span
                v-if="unreadCount > 0"
                class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                style="font-size: 0.6rem"
              >
                {{ unreadCount }}
              </span>
            </button>

            <!-- Dropdown de notificações -->
            <div
              v-if="showNotifications"
              class="position-absolute end-0 mt-2 bg-white border rounded shadow-sm"
              style="width: 340px; z-index: 1000"
              @click.stop
            >
              <div
                class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom"
              >
                <span class="fw-semibold small">Notificações</span>
                <button
                  v-if="unreadCount > 0"
                  class="btn btn-link btn-sm p-0 text-muted text-decoration-none"
                  :disabled="markingAll"
                  @click="markAllAsRead"
                >
                  <span
                    v-if="markingAll"
                    class="spinner-border spinner-border-sm me-1"
                  ></span>
                  {{ markingAll ? "Marcando..." : "Marcar todas como lidas" }}
                </button>
              </div>

              <div style="max-height: 300px; overflow-y: auto">
                <div
                  v-if="notifications.length === 0"
                  class="text-center text-muted py-4 small"
                >
                  Nenhuma notificação
                </div>
                <div
                  v-for="notification in notifications"
                  :key="notification.id"
                  class="px-3 py-2 border-bottom"
                  :class="!notification.read_at ? 'bg-primary-subtle' : ''"
                  style="cursor: pointer"
                  @click="goToOrder(notification)"
                >
                  <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                      <p class="mb-0 small">{{ notification.data.message }}</p>
                      <span class="text-muted" style="font-size: 0.7rem">
                        {{ formatDateTime(notification.created_at) }}
                      </span>
                    </div>
                    <div class="ms-2 d-flex align-items-center gap-1">
                      <span
                        v-if="!notification.read_at"
                        class="badge bg-primary rounded-pill"
                        style="
                          font-size: 0.6rem;
                          width: 8px;
                          height: 8px;
                          padding: 0;
                        "
                      ></span>
                      <button
                        v-if="!notification.read_at"
                        class="btn btn-link btn-sm p-0 text-muted text-decoration-none"
                        style="font-size: 0.7rem; white-space: nowrap"
                        :disabled="markingId === notification.id"
                        @click.stop="markAsRead(notification.id)"
                      >
                        <span
                          v-if="markingId === notification.id"
                          class="spinner-border spinner-border-sm"
                        ></span>
                        <span v-else>Lida</span>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <RouterLink to="/orders/new" class="btn btn-primary btn-sm"
            >Novo Pedido</RouterLink
          >
        </div>
      </div>

      <!-- Filtros -->
      <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body">
          <div class="row g-2 align-items-end">
            <div class="col-md-2">
              <label class="form-label small fw-semibold">Status</label>
              <select
                class="form-select form-select-sm"
                v-model="filters.status"
                @change="loadOrders"
              >
                <option value="">Todos</option>
                <option value="solicitado">Solicitado</option>
                <option value="aprovado">Aprovado</option>
                <option value="cancelado">Cancelado</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label small fw-semibold">Destino</label>
              <input
                class="form-control form-control-sm"
                v-model="filters.destination"
                placeholder="Buscar destino..."
                @input="onDestinationInput"
              />
            </div>
            <div class="col-md-2">
              <label class="form-label small fw-semibold">De</label>
              <input
                type="date"
                class="form-control form-control-sm"
                v-model="filters.date_from"
                @change="loadOrders"
              />
            </div>
            <div class="col-md-2">
              <label class="form-label small fw-semibold">Até</label>
              <input
                type="date"
                class="form-control form-control-sm"
                v-model="filters.date_to"
                @change="loadOrders"
              />
            </div>
            <div class="col-md-2">
              <button
                class="btn btn-outline-secondary btn-sm w-100"
                @click="clearFilters"
              >
                Limpar
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="text-center py-5">
        <div class="spinner-border text-primary" role="status"></div>
        <p class="mt-2 text-muted small">Carregando pedidos...</p>
      </div>

      <!-- Vazio -->
      <div v-else-if="orders.length === 0" class="text-center py-5 text-muted">
        <p class="mb-2">Nenhum pedido encontrado.</p>
        <RouterLink to="/orders/new" class="btn btn-primary btn-sm"
          >Criar pedido</RouterLink
        >
      </div>

      <!-- Tabela -->
      <div v-else class="card border-0 shadow-sm">
        <div class="table-responsive">
          <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
              <tr>
                <th class="text-muted fw-semibold small">#</th>
                <th class="text-muted fw-semibold small">Solicitante</th>
                <th class="text-muted fw-semibold small">Destino</th>
                <th class="text-muted fw-semibold small">Ida</th>
                <th class="text-muted fw-semibold small">Volta</th>
                <th class="text-muted fw-semibold small">Status</th>
                <th class="text-muted fw-semibold small">Ações</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="order in orders" :key="order.id">
                <td class="text-muted small">#{{ order.id }}</td>
                <td class="small">{{ order.requester_name }}</td>
                <td class="fw-semibold small">{{ order.destination }}</td>
                <td class="small">{{ formatDate(order.departure_date) }}</td>
                <td class="small">{{ formatDate(order.return_date) }}</td>
                <td>
                  <span :class="statusClass(order.status)">{{
                    order.status
                  }}</span>
                </td>
                <td>
                  <div class="d-flex gap-1">
                    <RouterLink
                      :to="`/orders/${order.id}`"
                      class="btn btn-outline-primary btn-sm"
                      >Ver</RouterLink
                    >
                    <template
                      v-if="auth.isAdmin && order.status === 'solicitado'"
                    >
                      <button
                        class="btn btn-outline-success btn-sm"
                        :disabled="updatingId === order.id"
                        @click="updateStatus(order.id, 'aprovado')"
                      >
                        <span
                          v-if="updatingId === order.id"
                          class="spinner-border spinner-border-sm"
                        ></span>
                        <span v-else>Aprovar</span>
                      </button>
                      <button
                        class="btn btn-outline-danger btn-sm"
                        :disabled="updatingId === order.id"
                        @click="updateStatus(order.id, 'cancelado')"
                      >
                        Cancelar
                      </button>
                    </template>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <ConfirmModal
      :show="confirmModal.show"
      :message="`Tem certeza que deseja ${
        confirmModal.status === 'aprovado' ? 'aprovar' : 'cancelar'
      } o pedido #${confirmModal.orderId}?`"
      :confirm-text="
        confirmModal.status === 'aprovado' ? 'Aprovar' : 'Cancelar'
      "
      :variant="confirmModal.status === 'aprovado' ? 'success' : 'danger'"
      :loading="!!updatingId"
      @confirm="confirmAction"
      @cancel="closeModal"
    />
  </div>
</template>
<script setup>
import ConfirmModal from "../components/ConfirmModal.vue";
import { ref, computed, onMounted, onUnmounted } from "vue";
import { RouterLink, useRouter } from "vue-router";
import { useAuthStore } from "../stores/auth";
import { useToast } from "vue-toastification";
import api from "../api/axios";

const auth = useAuthStore();
const toast = useToast();
const router = useRouter();
const orders = ref([]);
const loading = ref(false);
const loggingOut = ref(false);
const updatingId = ref(null);
const notifications = ref([]);
const showNotifications = ref(false);
const markingAll = ref(false);
const markingId = ref(null);
const confirmModal = ref({ show: false, orderId: null, status: null });
let debounceTimer = null;

const filters = ref({
  status: "",
  destination: "",
  date_from: "",
  date_to: "",
});

const unreadCount = computed(
  () => notifications.value.filter((n) => !n.read_at).length
);

async function loadOrders() {
  loading.value = true;
  try {
    const params = {};
    if (filters.value.status) params.status = filters.value.status;
    if (filters.value.destination)
      params.destination = filters.value.destination;
    if (filters.value.date_from) params.date_from = filters.value.date_from;
    if (filters.value.date_to) params.date_to = filters.value.date_to;
    const response = await api.get("/travel-orders", { params });
    orders.value = response.data;
  } catch {
    toast.error("Erro ao carregar pedidos.");
  } finally {
    loading.value = false;
  }
}

async function loadNotifications() {
  try {
    const response = await api.get("/notifications");
    notifications.value = response.data;
  } catch {}
}

async function markAsRead(id) {
  markingId.value = id;
  try {
    await api.patch(`/notifications/${id}/read`);
    await loadNotifications();
  } finally {
    markingId.value = null;
  }
}

async function markAllAsRead() {
  markingAll.value = true;
  try {
    const unread = notifications.value.filter((n) => !n.read_at);
    await Promise.all(
      unread.map((n) => api.patch(`/notifications/${n.id}/read`))
    );
    await loadNotifications();
    toast.success("Todas as notificações foram marcadas como lidas.");
  } finally {
    markingAll.value = false;
  }
}

async function goToOrder(notification) {
  if (!notification.read_at) {
    await markAsRead(notification.id);
  }
  showNotifications.value = false;
  router.push(`/orders/${notification.data.order_id}`);
}

function toggleNotifications() {
  showNotifications.value = !showNotifications.value;
}

function closeOnOutsideClick(e) {
  if (!e.target.closest(".position-relative")) {
    showNotifications.value = false;
  }
}

function onDestinationInput() {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => loadOrders(), 400);
}

function updateStatus(id, status) {
  confirmModal.value = { show: true, orderId: id, status };
}

function closeModal() {
  confirmModal.value = { show: false, orderId: null, status: null };
}

async function confirmAction() {
  const { orderId, status } = confirmModal.value;
  updatingId.value = orderId;
  try {
    await api.patch(`/travel-orders/${orderId}/status`, { status });
    toast.success(`Pedido ${status} com sucesso!`);
    closeModal();
    await loadOrders();
    await loadNotifications();
  } catch (error) {
    toast.error(error.response?.data?.message || "Erro ao atualizar status.");
  } finally {
    updatingId.value = null;
  }
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

function clearFilters() {
  filters.value = { status: "", destination: "", date_from: "", date_to: "" };
  loadOrders();
}

function formatDate(date) {
  return new Date(date).toLocaleDateString("pt-BR", { timeZone: "UTC" });
}

function formatDateTime(date) {
  return new Date(date).toLocaleString("pt-BR");
}

function statusClass(status) {
  const map = {
    solicitado: "badge bg-primary-subtle text-primary",
    aprovado: "badge bg-success-subtle text-success",
    cancelado: "badge bg-danger-subtle text-danger",
  };
  return map[status] || "badge bg-secondary";
}

onMounted(() => {
  loadOrders();
  loadNotifications();
  document.addEventListener("click", closeOnOutsideClick);
});

onUnmounted(() => {
  document.removeEventListener("click", closeOnOutsideClick);
});
</script>