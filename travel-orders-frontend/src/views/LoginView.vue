<template>
  <div
    class="min-vh-100 d-flex align-items-center justify-content-center bg-light"
  >
    <div class="card border-0 shadow-sm" style="width: 100%; max-width: 400px">
      <div class="card-body p-5">
        <div class="text-center mb-4">
          <h4 class="fw-bold mb-1">Travel Orders</h4>
          <p class="text-muted small">Entre com sua conta para continuar</p>
        </div>

        <div v-if="errorMessage" class="alert alert-danger py-2 small mb-3">
          {{ errorMessage }}
        </div>

        <form @submit.prevent="handleLogin">
          <div class="mb-3">
            <label class="form-label fw-semibold">E-mail</label>
            <input
              v-model="form.email"
              type="email"
              class="form-control"
              placeholder="seu@email.com"
              required
            />
          </div>

          <div class="mb-4">
            <label class="form-label fw-semibold">Senha</label>
            <input
              v-model="form.password"
              type="password"
              class="form-control"
              placeholder="••••••••"
              required
            />
          </div>

          <button
            type="submit"
            class="btn btn-primary w-100"
            :disabled="loading"
          >
            <span
              v-if="loading"
              class="spinner-border spinner-border-sm me-2"
            ></span>
            {{ loading ? "Entrando..." : "Entrar" }}
          </button>
        </form>

        <p class="text-center text-muted small mt-4 mb-0">
          Não tem uma conta?
          <RouterLink to="/register" class="text-primary"
            >Criar conta</RouterLink
          >
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import { RouterLink, useRouter } from "vue-router";
import { useAuthStore } from "../stores/auth";
import { useToast } from "vue-toastification";

const router = useRouter();
const auth = useAuthStore();
const toast = useToast();
const loading = ref(false);
const errorMessage = ref("");

const form = ref({
  email: "",
  password: "",
});

async function handleLogin() {
  loading.value = true;
  errorMessage.value = "";
  try {
    await auth.login(form.value.email, form.value.password);
    toast.success("Login realizado com sucesso!");
    router.push("/");
  } catch (error) {
    const message =
      error.response?.status === 401
        ? "E-mail ou senha inválidos."
        : error.response?.data?.message || "Erro ao fazer login.";
    errorMessage.value = message;
    toast.error(message);
  } finally {
    loading.value = false;
  }
}
</script>