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
          <h4 class="mb-0 fw-bold">Novo Pedido de Viagem</h4>
          <span class="text-muted small">Preencha os dados da solicitação</span>
        </div>
        <RouterLink to="/" class="btn btn-sm btn-outline-secondary"
          >Voltar</RouterLink
        >
      </div>

      <div class="card border-0 shadow-sm" style="max-width: 600px">
        <div class="card-body p-4">
          <form @submit.prevent="handleSubmit">
            <div class="mb-3">
              <label class="form-label fw-semibold">Destino</label>
              <input
                v-model="form.destination"
                type="text"
                class="form-control"
                placeholder="Ex: São Paulo, SP"
                required
              />
            </div>

            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <label class="form-label fw-semibold">Data de Ida</label>
                <input
                  v-model="form.departure_date"
                  type="date"
                  class="form-control"
                  :min="today"
                  required
                />
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Data de Volta</label>
                <input
                  v-model="form.return_date"
                  type="date"
                  class="form-control"
                  :min="form.departure_date || today"
                  required
                />
              </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
              <RouterLink to="/" class="btn btn-outline-secondary"
                >Cancelar</RouterLink
              >
              <button type="submit" class="btn btn-primary" :disabled="loading">
                <span
                  v-if="loading"
                  class="spinner-border spinner-border-sm me-2"
                ></span>
                {{ loading ? "Criando..." : "Criar Pedido" }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import { RouterLink, useRouter } from "vue-router";
import { useAuthStore } from "../stores/auth";
import { useToast } from "vue-toastification";
import api from "../api/axios";

const auth = useAuthStore();
const router = useRouter();
const toast = useToast();
const loading = ref(false);
const loggingOut = ref(false);
const today = new Date().toISOString().split("T")[0];

const form = ref({
  destination: "",
  departure_date: "",
  return_date: "",
});

async function handleSubmit() {
  loading.value = true;
  try {
    await api.post("/travel-orders", form.value);
    toast.success("Pedido criado com sucesso!");
    router.push("/");
  } catch (error) {
    const errors = error.response?.data?.errors;
    if (errors) {
      toast.error(Object.values(errors)[0][0]);
    } else {
      toast.error(error.response?.data?.message || "Erro ao criar pedido.");
    }
  } finally {
    loading.value = false;
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
</script>