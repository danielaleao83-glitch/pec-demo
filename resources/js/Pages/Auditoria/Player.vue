<script setup>
import { ref, onMounted, computed, watch } from 'vue'

const props = defineProps({
  timeline: { type: Array, default: () => [] },
  alertas: { type: Array, default: () => [] },
  score: { type: Number, default: 0 }
})

const index = ref(0)
const playing = ref(false)
const speed = ref(1500)
let interval = null

const current = computed(() => props.timeline[index.value] ?? null)

const progress = computed(() => {
  if (!props.timeline.length) return 0
  return Math.round((index.value / (props.timeline.length - 1)) * 100)
})

function play() {
  if (playing.value || props.timeline.length === 0) return

  playing.value = true

  interval = setInterval(() => {
    if (index.value < props.timeline.length - 1) {
      index.value++
    } else {
      pause()
    }
  }, speed.value)
}

function pause() {
  playing.value = false
  clearInterval(interval)
}

function next() {
  if (index.value < props.timeline.length - 1) index.value++
}

function prev() {
  if (index.value > 0) index.value--
}

function goTo(i) {
  index.value = i
}

function reset() {
  pause()
  index.value = 0
}

watch(speed, () => {
  if (playing.value) {
    pause()
    play()
  }
})

onMounted(() => {
  index.value = 0
})
</script>

<template>
  <div class="p-6 max-w-4xl mx-auto">

    <!-- HEADER -->
    <h1 class="text-2xl font-bold mb-4">
      🎬 Replay de Navegação Clínica
    </h1>

    <!-- 🚨 ALERTA DE RISCO -->
    <div
      v-if="score >= 50"
      class="bg-red-100 border border-red-500 p-4 mb-4 rounded"
    >
      <div class="font-bold">
        🚨 RISCO DETECTADO (Score: {{ score }})
      </div>

      <ul class="mt-2 text-sm">
        <li v-for="a in alertas" :key="a">
          ⚠️ {{ a }}
        </li>
      </ul>
    </div>

    <!-- 🎮 CONTROLES -->
    <div class="flex items-center gap-2 mb-4 flex-wrap">
      <button @click="play" class="px-3 py-1 bg-green-600 text-white rounded">▶️</button>
      <button @click="pause" class="px-3 py-1 bg-yellow-500 text-white rounded">⏸</button>
      <button @click="prev" class="px-3 py-1 bg-gray-500 text-white rounded">⏮</button>
      <button @click="next" class="px-3 py-1 bg-gray-700 text-white rounded">⏭</button>
      <button @click="reset" class="px-3 py-1 bg-black text-white rounded">⏹</button>

      <!-- velocidade -->
      <select v-model="speed" class="ml-2 border px-2 py-1 rounded">
        <option :value="2000">Lento</option>
        <option :value="1500">Normal</option>
        <option :value="800">Rápido</option>
        <option :value="300">Turbo</option>
      </select>
    </div>

    <!-- 📊 PROGRESSO -->
    <div class="w-full bg-gray-200 h-2 rounded mb-4">
      <div
        class="bg-blue-500 h-2 rounded transition-all"
        :style="{ width: progress + '%' }"
      ></div>
    </div>

    <!-- 🔍 EVENTO ATUAL -->
    <div v-if="current" class="border p-4 rounded shadow mb-6 bg-white">
      <p><strong>🕒</strong> {{ current.timestamp }}</p>
      <p><strong>📍</strong> {{ current.rota }}</p>
      <p><strong>👤 Paciente:</strong> {{ current.paciente_id ?? 'N/A' }}</p>
      <p><strong>🧠 Ação:</strong> {{ current.descricao }}</p>
    </div>

    <!-- 📜 TIMELINE -->
    <div class="space-y-2 max-h-[400px] overflow-y-auto">

      <div
        v-for="(item, i) in timeline"
        :key="item.id"
        @click="goTo(i)"
        :class="[
          'p-3 rounded cursor-pointer transition',
          
          // evento atual
          i === index
            ? 'bg-blue-100 border border-blue-500'
            : '',

          // suspeito
          score >= 50 && item.paciente_id
            ? 'bg-red-50 border border-red-300'
            : 'bg-gray-100'
        ]"
      >
        <div class="text-xs text-gray-500">
          {{ item.timestamp }}
        </div>

        <div class="font-medium">
          {{ item.descricao }}
        </div>

        <div class="text-sm text-gray-600">
          {{ item.rota }}
        </div>

      </div>

    </div>

  </div>
</template>