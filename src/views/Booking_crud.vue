<template>
  <div class="container mt-4 animate-fade-in">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
      <div>
        <h4 class="fw-bold text-dark mb-1">Manage Bookings</h4>
        <p class="text-muted mb-0" style="font-size: 0.85rem;">จัดการและตรวจสอบรายการจองทัวร์ของลูกค้าทั้งหมด</p>
      </div>
      
      <div class="search-container shadow-sm">
        <i class="bi bi-search text-muted ms-3"></i>
        <input 
          v-model="searchQuery" 
          type="text" 
          class="search-input" 
          placeholder="ค้นหาด้วยรหัส (ID), ชื่อลูกค้า หรือทัวร์..." 
        />
      </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="bg-light">
            <tr style="font-size: 0.85rem;">
              <th class="ps-4" style="width: 5%;">No.</th>
              <th style="width: 15%;">Booking ID</th>
              <th style="width: 20%;">Customer</th>
              <th style="width: 20%;">Destination</th>
              <th style="width: 15%;">Travel Date</th>
              <th class="text-center" style="width: 5%;">People</th>
              <th style="width: 10%;">Total Price</th>
              <th class="text-center" style="width: 10%;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(booking, index) in filteredBookings" :key="booking.booking_id">
              <td class="ps-4 text-muted small">{{ index + 1 }}</td>
              <td class="fw-bold text-primary font-monospace small">#BK-{{ booking.booking_id }}</td>
              <td class="fw-medium text-dark" style="font-size: 0.9rem;">{{ booking.cust_name }}</td>
              <td style="font-size: 0.9rem;">{{ booking.att_name }}</td>
              <td>
                <span class="badge bg-info-subtle text-info rounded-pill px-3 py-2 fw-medium" style="font-size: 0.75rem;">
                  <i class="bi bi-calendar-event me-1"></i> {{ formatDate(booking.travel_date) }}
                </span>
              </td>
              <td class="text-center">
                <span class="badge bg-light text-dark border px-2 py-1">{{ booking.num_people }}</span>
              </td>
              <td class="fw-bold text-success" style="font-size: 0.95rem;">
                {{ parseFloat(booking.total_price).toLocaleString() }} ฿
              </td>
              <td class="text-center">
                <div class="d-flex justify-content-center gap-2">
                  <button class="btn btn-light btn-sm px-3 rounded-pill text-primary fw-medium border shadow-sm btn-action" @click="openEditModal(booking)">
                    <i class="bi bi-pencil-square"></i>
                  </button>
                  <button class="btn btn-light btn-sm px-3 rounded-pill text-danger fw-medium border shadow-sm btn-action" @click="deleteBooking(booking.booking_id)">
                    <i class="bi bi-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="filteredBookings.length === 0 && !loading">
              <td colspan="8" class="text-center py-5 text-muted">
                <i class="bi bi-search fs-3 d-block mb-2 opacity-50"></i>
                ไม่พบข้อมูลการจองในระบบ
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div v-if="loading" class="text-center my-5">
      <div class="spinner-border text-primary" role="status"></div>
    </div>

    <div class="modal fade" id="editBookingModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
          <div class="modal-header border-0 pb-0 mt-2 mx-2">
            <h5 class="fw-bold mb-0 text-dark d-flex align-items-center">
              <i class="bi bi-pencil-square text-warning me-2 fs-4"></i> Edit Booking
            </h5>
            <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body p-4">
            <form @submit.prevent="updateBooking">
              <div class="row g-3">
                <div class="col-md-12">
                  <label class="form-label fw-bold small text-muted">Customer Name</label>
                  <input :value="editForm.cust_name" type="text" class="form-control rounded-3 bg-light text-muted" disabled />
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-bold small text-muted">Travel Date</label>
                  <input v-model="editForm.travel_date" type="date" class="form-control rounded-3" required />
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-bold small text-muted">Number of People</label>
                  <input v-model="editForm.num_people" type="number" class="form-control rounded-3" @input="calculateTotal" required min="1" />
                </div>
                <div class="col-12 mt-4 text-center border-top pt-4">
                  <div class="small text-muted mb-1 fw-medium">ยอดชำระรวม (อัตโนมัติ)</div>
                  <h3 class="fw-bold text-success mb-0">{{ parseFloat(editForm.total_price).toLocaleString() }} <span class="fs-5 text-muted fw-normal">฿</span></h3>
                </div>
              </div>
              <div class="d-grid mt-4">
                <button type="submit" class="btn btn-primary btn-lg rounded-pill fw-bold shadow-sm">
                  <i class="bi bi-save me-2"></i> บันทึกการแก้ไข
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";

const bookings = ref([]);
const loading = ref(true);
const searchQuery = ref("");
const editForm = ref({ booking_id: null, travel_date: "", num_people: 0, total_price: 0, unit_price: 0, cust_name: "" });
let modalInstance = null;

const API_URL = "http://localhost/projectgroup/php_api/booking_crud.php";

const fetchData = async () => {
  loading.value = true;
  try {
    const res = await fetch(API_URL);
    const result = await res.json();
    if (result.success) {
      bookings.value = result.data; 
    }
  } catch (err) { console.error("หน้าบ้านเชื่อมต่อหลังบ้านไม่ได้ค่ะ:", err); }
  loading.value = false;
};

// ✅ ปรับปรุงให้ค้นหาได้ครอบคลุม ID, ชื่อลูกค้า, และชื่อทัวร์ พร้อมดักค่า Null
const filteredBookings = computed(() => {
  if (!searchQuery.value) return bookings.value;

  const query = searchQuery.value.toLowerCase().trim();

  return bookings.value.filter(b => {
    const idMatch = b.booking_id ? String(b.booking_id).includes(query) : false;
    const nameMatch = b.cust_name ? b.cust_name.toLowerCase().includes(query) : false;
    const tourMatch = b.att_name ? b.att_name.toLowerCase().includes(query) : false;
    
    return idMatch || nameMatch || tourMatch;
  });
});

const calculateTotal = () => {
  editForm.value.total_price = editForm.value.num_people * editForm.value.unit_price;
};

const openEditModal = (booking) => {
  const date = booking.travel_date ? booking.travel_date.split(' ')[0] : "";
  editForm.value = { ...booking, travel_date: date };
  modalInstance.show();
};

const updateBooking = async () => {
  const formData = new FormData();
  formData.append("action", "update");
  formData.append("booking_id", editForm.value.booking_id);
  formData.append("num_people", editForm.value.num_people);
  formData.append("travel_date", editForm.value.travel_date);
  formData.append("total_price", editForm.value.total_price);

  try {
    const res = await fetch(API_URL, { method: "POST", body: formData });
    const result = await res.json();
    if (result.success) {
      // alert("อัปเดตข้อมูลสำเร็จแล้วค่ะ 🎉"); // ลบอิโมจิใน Alert ออกเพื่อความสุภาพ
      alert("อัปเดตข้อมูลสำเร็จแล้วค่ะ");
      fetchData();
      modalInstance.hide();
    }
  } catch (err) { alert("Error connecting to server"); }
};

const deleteBooking = async (id) => {
  if (!confirm("คุณแน่ใจหรือไม่ว่าต้องการลบรายการจองนี้?")) return;
  const formData = new FormData();
  formData.append("action", "delete");
  formData.append("booking_id", id);
  try {
    const res = await fetch(API_URL, { method: "POST", body: formData });
    const result = await res.json();
    if (result.success) {
      fetchData();
    }
  } catch (err) { alert("Error deleting booking"); }
};

const formatDate = (dateStr) => {
  if (!dateStr || dateStr.startsWith("0000")) return "-";
  return new Date(dateStr).toLocaleDateString('th-TH', { year: 'numeric', month: 'short', day: 'numeric' });
};

onMounted(() => {
  fetchData();
  modalInstance = new window.bootstrap.Modal(document.getElementById("editBookingModal"));
});
</script>

<style scoped>
/* Typography */
h4 { letter-spacing: -0.5px; }

/* ช่องค้นหา */
.search-container { 
  display: flex; align-items: center; background: white; 
  border: 1px solid #e2e8f0; border-radius: 50px; 
  min-width: 280px; height: 40px; 
  transition: all 0.3s ease;
}
.search-container:focus-within {
  border-color: #0d6efd;
  box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.1) !important;
}
.search-input { 
  border: none; outline: none; width: 100%; padding: 0 15px; 
  font-size: 0.85rem; color: #334155; background: transparent; 
}

/* ปุ่มและฟอร์ม */
.btn-action {
  transition: all 0.2s ease;
  background-color: #ffffff;
}
.btn-action:hover {
  transform: translateY(-2px);
  background-color: #f8fafc;
}
.form-control:focus {
  border-color: #0d6efd;
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1);
}

/* ตาราง */
.table thead th { font-weight: 700; text-transform: uppercase; color: #64748b; padding: 1rem; border-bottom: 2px solid #e2e8f0; }

.animate-fade-in { animation: fadeIn 0.4s ease-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
</style>