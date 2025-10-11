<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Navbar -->
    <nav class="bg-white shadow-sm">
      <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex items-center">
            <h1 class="text-2xl font-bold text-gray-900">藏琢診所班表系統</h1>
          </div>
          <div class="flex items-center space-x-4">
            <template v-if="!isAdmin">
              <button @click="showLoginModal = true" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                登入
              </button>
            </template>
            <template v-else>
              <span class="text-gray-700">{{ adminName }}</span>
              <button @click="confirmSchedule" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                確認該月班表
              </button>
              <button @click="exportSchedule" class="px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700">
                匯出班表
              </button>
              <button @click="logout" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
                登出
              </button>
            </template>
          </div>
        </div>
      </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="flex gap-6">
        <!-- Employee List Sidebar -->
        <div class="w-64 bg-white rounded-lg shadow p-4">
          <h2 class="text-lg font-semibold mb-4">員工列表</h2>
          <div v-for="dept in departments" :key="dept.id" class="mb-4">
            <h3 class="font-medium text-gray-700 mb-2">{{ dept.name }}</h3>
            <div class="space-y-2">
              <button
                v-for="employee in dept.employees"
                :key="employee.id"
                @click="selectEmployee(employee)"
                :class="[
                  'w-full px-3 py-2 rounded text-left transition-colors',
                  selectedEmployee?.id === employee.id
                    ? 'ring-2 ring-offset-2'
                    : 'hover:bg-gray-100'
                ]"
                :style="{
                  backgroundColor: selectedEmployee?.id === employee.id ? employee.color + '20' : 'white',
                  borderLeft: `4px solid ${employee.color}`,
                  ringColor: employee.color
                }"
              >
                {{ employee.name }}
              </button>
            </div>
          </div>
        </div>

        <!-- Schedule Table -->
        <div class="flex-1 bg-white rounded-lg shadow overflow-x-auto">
          <div class="p-4">
            <!-- Year/Month Selector -->
            <div class="flex items-center gap-4 mb-4">
              <label class="font-medium">選擇年月：</label>
              <select v-model="selectedYear" @change="loadSchedule" class="border rounded px-3 py-1">
                <option v-for="y in years" :key="y" :value="y">{{ y }}年</option>
              </select>
              <select v-model="selectedMonth" @change="loadSchedule" class="border rounded px-3 py-1">
                <option v-for="m in 12" :key="m" :value="m">{{ m }}月</option>
              </select>
              <span v-if="schedule?.is_confirmed" class="ml-4 px-3 py-1 bg-green-100 text-green-800 rounded">
                ✓ 已確認
              </span>
            </div>

            <!-- Schedule Grid -->
            <div class="overflow-x-auto">
              <table class="border-collapse border border-gray-300 text-sm">
                <thead>
                  <!-- Row 1: Year.Month and Days -->
                  <tr>
                    <th class="border border-gray-300 px-3 py-2 bg-gray-100 sticky left-0 z-10">
                      {{ selectedYear }}.{{ selectedMonth }}
                    </th>
                    <th
                      v-for="day in daysInMonth"
                      :key="`day-${day}`"
                      :class="[
                        'border border-gray-300 px-3 py-2 min-w-[40px]',
                        getDayOfWeek(day) === '日' ? 'bg-orange-200' : 'bg-gray-50'
                      ]"
                    >
                      {{ day }}
                    </th>
                  </tr>
                  <!-- Row 2: Day of Week -->
                  <tr>
                    <th class="border border-gray-300 px-3 py-2 bg-gray-100 sticky left-0 z-10"></th>
                    <th
                      v-for="day in daysInMonth"
                      :key="`dow-${day}`"
                      :class="[
                        'border border-gray-300 px-3 py-2',
                        getDayOfWeek(day) === '日' ? 'bg-orange-200' : 'bg-gray-50'
                      ]"
                    >
                      {{ getDayOfWeek(day) }}
                    </th>
                  </tr>
                </thead>
                <tbody>
                  <!-- Department and Employee Rows -->
                  <template v-for="dept in departments" :key="dept.id">
                    <tr>
                      <td class="border border-gray-300 px-3 py-2 font-semibold bg-gray-100 sticky left-0 z-10">
                        {{ dept.name }}
                      </td>
                      <td
                        v-for="day in daysInMonth"
                        :key="`dept-${dept.id}-${day}`"
                        :class="[
                          'border border-gray-300',
                          getDayOfWeek(day) === '日' ? 'bg-orange-100' : ''
                        ]"
                      ></td>
                    </tr>
                    <tr v-for="employee in dept.employees" :key="employee.id">
                      <td
                        class="border border-gray-300 px-3 py-2 sticky left-0 z-10 bg-white"
                        :style="{
                          backgroundColor: selectedEmployee?.id === employee.id ? employee.color + '20' : 'white'
                        }"
                      >
                        {{ employee.name }}
                      </td>
                      <td
                        v-for="day in daysInMonth"
                        :key="`emp-${employee.id}-${day}`"
                        @click="toggleDayOff(employee, day)"
                        :class="[
                          'border border-gray-300 cursor-pointer hover:opacity-80 transition-opacity',
                          getCellClass(employee, day)
                        ]"
                        :style="getCellStyle(employee, day)"
                      ></td>
                    </tr>
                  </template>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Login Modal -->
    <div v-if="showLoginModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-96">
        <h2 class="text-xl font-bold mb-4">管理員登入</h2>
        <form @submit.prevent="login">
          <div class="mb-4">
            <label class="block text-sm font-medium mb-1">帳號</label>
            <input
              v-model="loginForm.username"
              type="text"
              class="w-full border rounded px-3 py-2"
              required
            >
          </div>
          <div class="mb-4">
            <label class="block text-sm font-medium mb-1">密碼</label>
            <input
              v-model="loginForm.password"
              type="password"
              class="w-full border rounded px-3 py-2"
              required
            >
          </div>
          <div class="flex justify-end gap-2">
            <button
              type="button"
              @click="showLoginModal = false"
              class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400"
            >
              取消
            </button>
            <button
              type="submit"
              class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
            >
              登入
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

// State
const departments = ref([]);
const schedule = ref(null);
const selectedEmployee = ref(null);
const selectedYear = ref(114);
const selectedMonth = ref(11);
const isAdmin = ref(false);
const adminName = ref('');
const showLoginModal = ref(false);
const loginForm = ref({
  username: '',
  password: ''
});

let clearEmployeeTimer = null;

// Computed
const years = computed(() => {
  const currentYear = new Date().getFullYear() - 1911;
  return Array.from({ length: 5 }, (_, i) => currentYear - 2 + i);
});

const daysInMonth = computed(() => {
  const westernYear = selectedYear.value + 1911;
  return new Date(westernYear, selectedMonth.value, 0).getDate();
});

// Methods
const getDayOfWeek = (day) => {
  const westernYear = selectedYear.value + 1911;
  const date = new Date(westernYear, selectedMonth.value - 1, day);
  const days = ['日', '一', '二', '三', '四', '五', '六'];
  return days[date.getDay()];
};

const selectEmployee = (employee) => {
  if (!selectedEmployee.value || selectedEmployee.value.id !== employee.id) {
    selectedEmployee.value = employee;

    // Log employee selection
    axios.post(`/api/employees/${employee.id}/select`, {
      schedule_id: schedule.value?.id
    });

    // Clear selection after 3 minutes
    if (clearEmployeeTimer) {
      clearTimeout(clearEmployeeTimer);
    }
    clearEmployeeTimer = setTimeout(() => {
      selectedEmployee.value = null;
    }, 3 * 60 * 1000);
  } else {
    selectedEmployee.value = null;
    if (clearEmployeeTimer) {
      clearTimeout(clearEmployeeTimer);
    }
  }
};

const isEmployeeDayOff = (employee, day) => {
  if (!employee.schedule_records) return false;
  const record = employee.schedule_records.find(r => r.day === day);
  return record?.is_off || false;
};

const getCellClass = (employee, day) => {
  const dayOfWeek = getDayOfWeek(day);
  if (dayOfWeek === '日') return '';
  return '';
};

const getCellStyle = (employee, day) => {
  const dayOfWeek = getDayOfWeek(day);
  const isOff = isEmployeeDayOff(employee, day);

  if (dayOfWeek === '日') {
    return { backgroundColor: '#FFA500' }; // 橘色店休
  }

  if (isOff) {
    return { backgroundColor: '#FF0000' }; // 紅色休假
  }

  return {};
};

const toggleDayOff = async (employee, day) => {
  if (!selectedEmployee.value) {
    alert('請先選擇員工');
    return;
  }

  if (selectedEmployee.value.id !== employee.id) {
    alert('請先選擇員工');
    return;
  }

  const dayOfWeek = getDayOfWeek(day);
  if (dayOfWeek === '日') {
    return; // 不能點選店休日
  }

  try {
    const response = await axios.post('/api/schedules/records', {
      schedule_id: schedule.value.id,
      employee_id: employee.id,
      day: day
    });

    // Update local state
    if (!employee.schedule_records) {
      employee.schedule_records = [];
    }

    const existingRecord = employee.schedule_records.find(r => r.day === day);
    if (existingRecord) {
      existingRecord.is_off = response.data.record.is_off;
    } else {
      employee.schedule_records.push(response.data.record);
    }
  } catch (error) {
    if (error.response?.status === 403) {
      alert(error.response.data.message);
    } else {
      alert('操作失敗');
    }
  }
};

const login = async () => {
  try {
    const response = await axios.post('/api/auth/login', loginForm.value);
    if (response.data.success) {
      isAdmin.value = true;
      adminName.value = response.data.admin.name;
      showLoginModal.value = false;
      loginForm.value = { username: '', password: '' };
    }
  } catch (error) {
    alert(error.response?.data?.message || '登入失敗');
  }
};

const logout = async () => {
  try {
    await axios.post('/api/auth/logout');
    isAdmin.value = false;
    adminName.value = '';
  } catch (error) {
    console.error('Logout error:', error);
  }
};

const confirmSchedule = async () => {
  if (!confirm('確定要確認本月班表嗎？確認後非管理員將無法編輯。')) {
    return;
  }

  try {
    const response = await axios.post(`/api/schedules/${schedule.value.id}/confirm`);
    if (response.data.success) {
      alert('班表已確認');
      schedule.value.is_confirmed = true;
    }
  } catch (error) {
    alert(error.response?.data?.message || '確認失敗');
  }
};

const exportSchedule = () => {
  window.open(`/api/schedules/${selectedYear.value}/${selectedMonth.value}/export`, '_blank');
};

const loadEmployees = async () => {
  try {
    const response = await axios.get('/api/employees');
    departments.value = response.data.departments;
  } catch (error) {
    console.error('Load employees error:', error);
  }
};

const loadSchedule = async () => {
  try {
    const response = await axios.get(`/api/schedules/${selectedYear.value}/${selectedMonth.value}`);
    schedule.value = response.data.schedule;

    // Merge schedule records into employees
    const employeeMap = {};
    Object.values(response.data.employees).forEach(deptEmployees => {
      deptEmployees.forEach(emp => {
        employeeMap[emp.id] = emp;
      });
    });

    departments.value.forEach(dept => {
      dept.employees.forEach(emp => {
        const fetchedEmp = employeeMap[emp.id];
        if (fetchedEmp) {
          emp.schedule_records = fetchedEmp.schedule_records || [];
        }
      });
    });
  } catch (error) {
    console.error('Load schedule error:', error);
  }
};

const checkAuth = async () => {
  try {
    const response = await axios.get('/api/auth/check');
    if (response.data.isAuthenticated) {
      isAdmin.value = true;
      adminName.value = response.data.admin.name;
    }
  } catch (error) {
    console.error('Check auth error:', error);
  }
};

// Lifecycle
onMounted(async () => {
  await checkAuth();
  await loadEmployees();
  await loadSchedule();
});

onUnmounted(() => {
  if (clearEmployeeTimer) {
    clearTimeout(clearEmployeeTimer);
  }
});
</script>

<style scoped>
table {
  border-collapse: collapse;
}

.sticky {
  position: sticky;
}
</style>
