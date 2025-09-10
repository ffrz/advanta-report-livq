<script setup>
import { ref, watch, onMounted } from 'vue';

const props = defineProps({
  modelValue: {
    type: [File, String, null],
    default: null
  },
  initialImagePath: {
    type: String,
    default: null
  },
  label: {
    type: String,
    default: 'Foto Lampiran:'
  },
  disabled: {
    type: Boolean,
    default: false
  },
  error: {
    type: Boolean,
    default: false
  },
  errorMessage: {
    type: String,
    default: ''
  }
});

const emit = defineEmits(['update:modelValue']);

const fileInput = ref(null);
const imagePreview = ref(null);

// Watch untuk memperbarui pratinjau saat modelValue berubah dari luar
watch(() => props.modelValue, (newFile) => {
  if (newFile instanceof File) {
    imagePreview.value = URL.createObjectURL(newFile);
  } else if (!newFile) {
    imagePreview.value = null;
  }
});

onMounted(() => {
  if (props.initialImagePath) {
    imagePreview.value = `/${props.initialImagePath}`;
  }
});

function triggerInput() {
  if (!props.disabled) {
    fileInput.value.click();
  }
}

function onFileChange(event) {
  const file = event.target.files[0];
  if (file) {
    emit('update:modelValue', file);
  }
}

function clearImage() {
  emit('update:modelValue', null);
  imagePreview.value = null;
  fileInput.value.value = null;
}
</script>

<template>
  <div>
    <div class="text-subtitle2 text-bold text-grey-9">
      {{ label }}
    </div>
    <div class="q-gutter-x-sm q-mt-sm">
      <q-btn
        label="Pilih Foto"
        size="sm"
        @click.prevent="triggerInput"
        color="secondary"
        icon="add_a_photo"
        :disable="disabled"
      />
      <q-btn
        size="sm"
        icon="close"
        label="Buang"
        :disable="disabled || !imagePreview"
        color="red"
        @click.prevent="clearImage"
      />
      <input
        type="file"
        ref="fileInput"
        accept="image/*"
        style="display: none"
        @change="onFileChange"
      />
    </div>

    <div v-if="error" class="text-negative q-mt-sm">
      {{ errorMessage }}
    </div>

    <div class="q-mt-md">
      <q-img
        v-if="imagePreview"
        :src="imagePreview"
        style="max-width: 500px; border: 1px solid #ddd; border-radius: 4px;"
      >
        <template v-slot:error>
          <div class="text-negative text-center q-pa-md">
            Gambar tidak tersedia
          </div>
        </template>
      </q-img>
    </div>
  </div>
</template>