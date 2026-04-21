<template>
  <div class="container my-5 animate-fade-in">
    <div class="mb-5 text-center text-md-start">
      <h2 class="fw-bold text-dark"><i class="bi bi-journal-bookmark-fill text-primary me-2"></i>My Bookings</h2>
      <p class="text-muted">รายการจองทัวร์ปัจจุบันของคุณ</p>
    </div>

    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status"></div>
      <p class="mt-3 text-muted">กำลังโหลดข้อมูล...</p>
    </div>

    <div v-else-if="bookings.length === 0" class="text-center py-5 shadow-sm rounded-4 bg-white border">
      <div class="py-5">
        <i class="bi bi-calendar-x fs-1 text-muted opacity-50"></i>
        <h4 class="mt-3 fw-bold text-secondary">ไม่มีรายการจองค่ะ</h4>
        <router-link to="/attraction_list" class="btn btn-primary px-4 rounded-pill mt-2 shadow">ไปดูทัวร์เพิ่มเติม</router-link>
      </div>
    </div>

    <div v-else class="row g-4">
      <div v-for="item in bookings" :key="item.booking_id" class="col-12">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden booking-item-card">
          <div class="row g-0">
            <div class="col-md-3">
              <img :src="'http://localhost/projectgroup/php_api/uploads/' + item.image" class="img-fluid h-100 object-fit-cover" style="min-height: 200px;">
            </div>
            <div class="col-md-9">
              <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                  <div>
                    <h5 class="fw-bold text-dark mb-1">{{ item.att_name }}</h5>
                    <span class="text-muted small">จองเมื่อ: {{ formatDate(item.booking_date) }}</span>
                  </div>
                  <span class="badge rounded-pill px-3 py-2 bg-success text-white">ชำระเงินแล้ว</span>
                </div>

                <div class="row g-3 py-3 border-top border-bottom bg-light">
                  <div class="col-6 col-md-3 border-end">
                    <div class="small text-muted mb-1 fw-bold">Booking ID</div>
                    <div class="fw-bold">#BK-{{ item.booking_id }}</div>
                  </div>
                  <div class="col-6 col-md-3 border-end">
                    <div class="small text-muted mb-1 fw-bold">จำนวน</div>
                    <div class="fw-bold">{{ item.num_people }} ท่าน</div>
                  </div>
                  <div class="col-6 col-md-3 border-end">
                    <div class="small text-muted mb-1 fw-bold">รวมยอดเงิน</div>
                    <div class="fw-bold text-primary">{{ parseFloat(item.total_price).toLocaleString() }} ฿</div>
                  </div>
                  <div class="col-6 col-md-3">
                    <div class="small text-muted mb-1 fw-bold">สถานะทริป</div>
                    <div class="fw-bold text-success">เตรียมเดินทาง</div>
                  </div>
                </div>

                <div class="mt-4 d-flex justify-content-end">
                  <button 
                    @click="handleCancel(item.booking_id)"
                    class="btn btn-outline-danger rounded-pill px-4 btn-sm fw-bold shadow-sm"
                  >
                    ยกเลิกการจอง
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';

const bookings = ref([]);
const loading = ref(true);
const router = useRouter();

const fetchMyBookings = async () => {
  const userJSON = localStorage.getItem("user");
  if (!userJSON) { router.push("/clogin"); return; }
  const user = JSON.parse(userJSON);
  const cust_id = user.id || user.cust_id;

  try {
    const res = await fetch(`http://localhost/projectgroup/php_api/show_my_booking.php?cust_id=${cust_id}`);
    const result = await res.json();
    if (result.success) { 
      bookings.value = result.data; 
    }
  } catch (err) { console.error(err); } 
  finally { loading.value = false; }
};

const handleCancel = async (bookingId) => {
  if (confirm("ยืนยันการยกเลิก? รายการนี้จะถูกลบออกจากระบบและหน้าจอของคุณทันทีค่ะ")) {
    try {
      const response = await fetch("http://localhost/projectgroup/php_api/cancel_booking.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ booking_id: bookingId })
      });
      const result = await response.json();
      
      if (result.success) {
        // ✅ วิธีทำให้หายไปทันที: กรองออกในตัวแปร bookings ในเครื่องเราเลย
        bookings.value = bookings.value.filter(item => item.booking_id !== bookingId);
        alert("ยกเลิกรายการจองสำเร็จแล้วค่ะ");
      }
    } catch (err) { alert("Error connecting to server"); }
  }
};

const formatDate = (dateStr) => {
  return new Date(dateStr).toLocaleDateString('th-TH', {
    year: 'numeric', month: 'short', day: 'numeric'
  });
};

onMounted(fetchMyBookings);
</script>

<style scoped>
.booking-item-card { border: 1px solid #e2e8f0 !important; transition: 0.3s; }
.object-fit-cover { object-fit: cover; }
.animate-fade-in { animation: fadeIn 0.5s ease-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>