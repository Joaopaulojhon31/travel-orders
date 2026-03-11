<template>
  <div v-if="show" class="modal d-block" style="background: rgba(0, 0, 0, 0.5)">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow">
        <div class="modal-header border-0 pb-0">
          <h6 class="modal-title fw-bold">{{ title }}</h6>
          <button
            class="btn-close"
            :disabled="loading"
            @click="$emit('cancel')"
          ></button>
        </div>
        <div class="modal-body">
          <p class="mb-0">{{ message }}</p>
          <p v-if="subMessage" class="text-muted small mt-1 mb-0">
            {{ subMessage }}
          </p>
        </div>
        <div class="modal-footer border-0 pt-0">
          <button
            class="btn btn-outline-secondary btn-sm"
            :disabled="loading"
            @click="$emit('cancel')"
          >
            Voltar
          </button>
          <button
            :class="`btn btn-sm btn-${variant}`"
            :disabled="loading"
            @click="$emit('confirm')"
          >
            <span
              v-if="loading"
              class="spinner-border spinner-border-sm me-1"
            ></span>
            {{ loading ? "Aguarde..." : confirmText }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  show: { type: Boolean, default: false },
  title: { type: String, default: "Confirmar ação" },
  message: { type: String, required: true },
  subMessage: { type: String, default: "Esta ação não poderá ser desfeita." },
  confirmText: { type: String, default: "Confirmar" },
  variant: { type: String, default: "primary" },
  loading: { type: Boolean, default: false },
});

defineEmits(["confirm", "cancel"]);
</script>