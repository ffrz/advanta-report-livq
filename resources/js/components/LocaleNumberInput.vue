<template>
  <q-input
    :model-value="displayValue"
    :label="props.label"
    :outlined="props.outlined"
    @update:model-value="onInput"
    @focus="onFocus"
    @blur="onBlur"
    @keydown="filterInput"
    :lazy-rules="lazyRules"
    :disable="disable"
    :error="error"
    :rules="rules"
    :error-message="errorMessage"
  />
</template>

<script setup>
import { ref, watch } from "vue";

const props = defineProps({
  modelValue: { type: Number, required: true, default: 0 },
  label: { type: String, default: "" },
  locale: { type: String, default: "id-ID" },
  outlined: { type: Boolean, default: false },
  allowNegative: { type: Boolean, default: false },
  maxDecimals: { type: Number, default: 0 },
  lazyRules: { type: String },
  disable: { type: Boolean, default: false },
  error: { type: Boolean, default: false },
  errorMessage: { type: String, default: "" },
  rules: { type: Array, default: () => [] },
});

const emit = defineEmits(["update:modelValue"]);
const displayValue = ref("");
const isFocused = ref(false);

// Dapatkan separator berdasarkan locale
const getLocaleSeparators = (locale) => {
  const sampleNumber = 1234567.89;
  const formatted = new Intl.NumberFormat(locale).format(sampleNumber);

  let decimalSep = ".";
  let thousandSep = ",";

  if (formatted.includes(",") || formatted.includes(".")) {
    const lastComma = formatted.lastIndexOf(",");
    const lastDot = formatted.lastIndexOf(".");
    decimalSep = lastComma > lastDot ? "," : ".";
    thousandSep = decimalSep === "," ? "." : ",";
  }
  return { decimalSeparator: decimalSep, thousandSeparator: thousandSep };
};

const { decimalSeparator, thousandSeparator } = getLocaleSeparators(
  props.locale
);

// Format angka sesuai locale + maxDecimals
const formatNumber = (value) => {
  if (value === null || value === undefined || isNaN(value)) value = 0;
  return new Intl.NumberFormat(props.locale, {
    minimumFractionDigits: props.maxDecimals,
    maximumFractionDigits: props.maxDecimals,
  }).format(value);
};

// Hanya sinkronkan display ketika TIDAK sedang fokus (user mengetik)
watch(
  () => props.modelValue,
  (newVal) => {
    if (!isFocused.value) {
      displayValue.value = formatNumber(newVal);
    }
  },
  { immediate: true }
);

// Sanitasi input -> number. Jika round=false, tidak menggunakan toFixed (biar user bebas mengetik)
const sanitizeInput = (value, round = true) => {
  if (value === null || value === undefined) return NaN;
  const raw = String(value).trim();
  if (raw === "" || raw === "-" || raw === decimalSeparator) return NaN;

  // remove non numeric except ., - and ,
  let sanitized = raw
    .replace(/[^0-9.,-]+/g, "")
    .replace(new RegExp(`\\${thousandSeparator}`, "g"), "")
    .replace(new RegExp(`\\${decimalSeparator}`, "g"), ".");

  // if multiple dots - keep first dot, remove others
  const firstDotIndex = sanitized.indexOf(".");
  if (firstDotIndex !== -1) {
    sanitized =
      sanitized.slice(0, firstDotIndex + 1) +
      sanitized.slice(firstDotIndex + 1).replace(/\./g, "");
  }

  const parsed = parseFloat(sanitized);
  if (isNaN(parsed)) return NaN;

  return round ? parseFloat(parsed.toFixed(props.maxDecimals)) : parsed;
};

// Fokus / blur handlers
const onFocus = () => {
  isFocused.value = true;
  // jangan ubah displayValue di fokus — biarkan apa yang sedang diketik user
};

const onBlur = () => {
  isFocused.value = false;
  const rounded = sanitizeInput(displayValue.value, true);
  const finalValue = isNaN(rounded) ? 0 : rounded;
  emit("update:modelValue", finalValue);
  displayValue.value = formatNumber(finalValue);
};

// Input handler (saat user mengetik)
// IMPORTANT: jangan emit bila user baru mengetik tanda desimal terakhir (mis "1,"), atau input kosong / hanya "-"
const onInput = (val) => {
  displayValue.value = val;

  const str = String(val);
  if (str === "" || str === "-") {
    // tidak emit, biarkan user menyelesaikan
    return;
  }

  const lastChar = str.charAt(str.length - 1);
  // jika terakhir adalah pemisah desimal, jangan emit (user belum selesai mengetik bagian desimal)
  if (lastChar === decimalSeparator || lastChar === "." || lastChar === ",") {
    return;
  }

  // jika ada decimal separator dan user baru mengetik bagian desimal (tetapi masih lebih pendek dari maxDecimals), tetap boleh emit
  const numericValue = sanitizeInput(str, false);
  if (!isNaN(numericValue)) {
    emit("update:modelValue", numericValue);
  }
};

// Keyboard filter: izinkan digit, satu pemisah decimal, minus di awal, dan navigasi
const filterInput = (event) => {
  const allowedKeys = [
    "Backspace",
    "Delete",
    "Tab",
    "ArrowLeft",
    "ArrowRight",
    "Home",
    "End",
  ];
  if (event.ctrlKey || event.metaKey) return;
  if (allowedKeys.includes(event.key)) return;
  if (event.key >= "0" && event.key <= "9") return;

  // izinkan pemisah desimal (dot atau comma) jika belum ada
  const isDecimalKey =
    event.key === decimalSeparator || event.key === "." || event.key === ",";
  if (
    isDecimalKey &&
    !displayValue.value.includes(decimalSeparator) &&
    !displayValue.value.includes(".") &&
    !displayValue.value.includes(",")
  ) {
    return;
  }

  if (
    props.allowNegative &&
    event.key === "-" &&
    event.target.selectionStart === 0
  )
    return;

  event.preventDefault();
};
</script>
