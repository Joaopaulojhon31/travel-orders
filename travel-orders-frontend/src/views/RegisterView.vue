<template>
  <div
    class="min-vh-100 d-flex align-items-center justify-content-center bg-light"
  >
    <div class="card border-0 shadow-sm" style="width: 100%; max-width: 400px">
      <div class="card-body p-5">
        <div class="text-center mb-4">
          <h4 class="fw-bold mb-1">Travel Orders</h4>
          <p class="text-muted small">Crie sua conta para começar</p>
        </div>

        <form @submit.prevent="handleRegister">
          <div class="mb-3">
            <label class="form-label fw-semibold">Nome</label>
            <input
              v-model="form.name"
              type="text"
              class="form-control"
              placeholder="Seu nome completo"
              required
            />
          </div>

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

          <div class="mb-3">
            <label class="form-label fw-semibold">Senha</label>
            <input
              v-model="form.password"
              type="password"
              class="form-control"
              placeholder="Mínimo 6 caracteres"
              required
            />
          </div>

          <div class="mb-4">
            <label class="form-label fw-semibold">Confirmar Senha</label>
            <input
              v-model="form.password_confirmation"
              type="password"
              class="form-control"
              placeholder="Repita a senha"
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
            {{ loading ? "Criando conta..." : "Criar conta" }}
          </button>
        </form>

        <p class="text-center text-muted small mt-4 mb-0">
          Já tem uma conta?
          <RouterLink to="/login" class="text-primary">Entrar</RouterLink>
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
import api from "../api/axios";

const router = useRouter();
const auth = useAuthStore();
const toast = useToast();
const loading = ref(false);

const form = ref({
  name: "",
  email: "",
  password: "",
  password_confirmation: "",
});

async function handleRegister() {
  if (form.value.password !== form.value.password_confirmation) {
    toast.error("As senhas não coincidem.");
    return;
  }

  loading.value = true;
  try {
    const response = await api.post("/auth/register", form.value);
    auth.token = response.data.token;
    auth.user = response.data.user;
    localStorage.setItem("token", response.data.token);
    localStorage.setItem("user", JSON.stringify(response.data.user));
    toast.success("Conta criada com sucesso!");
    router.push("/");
  } catch (error) {
    const errors = error.response?.data?.errors;
    if (errors) {
      toast.error(Object.values(errors)[0][0]);
    } else {
      toast.error(error.response?.data?.message || "Erro ao criar conta.");
    }
  } finally {
    loading.value = false;
  }
}
</script>