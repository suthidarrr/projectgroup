<template>
  <div class="container mt-4">
    <h2 class="mb-3">รายชื่อพนักงาน</h2>

     <!--ปุ่มเพิ่มหน้า-->
    <div class="mb-3 text-end">
      <a class="btn btn-success" href="/add_employee" role="button">Add <i class="bi bi-plus-circle"></i></a>
    </div>
    
    <!-- ตารางแสดงข้อมูลลูกค้า -->
    <table class="table table-bordered table-striped">
      <thead class="table-dark">
        <tr>
          <th>รหัสพนักงาน</th>
          <th>ชื่อ-นามสกุุล</th>
          <th>แผนก</th>
          <th>เงินเดือน</th>
          <th>สถานะ</th>
          <!--<th>เริ่มงานวันที่</th>-->
        </tr>
      </thead>
      <tbody>
        <tr v-for="employee in employees" :key="employee.emp_id">
          <td>{{ employee.emp_id }}</td>
          <td>{{ employee.full_name }}</td>
          <td>{{ employee.department }}</td>
          <td>{{ employee.salary }}</td>
          <td>
            <span v-if="employee.active == 1">ปกติ</span>
            <span v-else>ลาออก</span>
          </td>
          <!--<td>{{ employee.created_at }}</td>-->
        </tr>
      </tbody>
    </table>

    <!-- Loading -->
    <div v-if="loading" class="text-center">
      <p>กำลังโหลดข้อมูล...</p>
    </div>

    <!-- Error -->
    <div v-if="error" class="alert alert-danger">
      {{ error }}
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from "vue";

export default {
  name: "EmployeeList",
  setup() {
    const employees = ref([]);
    const loading = ref(true);
    const error = ref(null);

    // ฟังก์ชันดึงข้อมูลจาก API
    const fetchEmployees = async () => {
      try {
        const response = await fetch("http://localhost/App-vue01/php_api/show_employee.php");
        if (!response.ok) {
          throw new Error("ไม่สามารถดึงข้อมูลได้");
        }
        employees.value = await response.json();
      } catch (err) {
        error.value = err.message;
      } finally {
        loading.value = false;
      }
    };

    onMounted(() => {
      fetchEmployees();
    });

    return {
      employees,
      loading,
      error
    };
  }
};
</script>

