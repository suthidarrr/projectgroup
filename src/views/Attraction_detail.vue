<template>
  <div class="container my-5 animate-fade-in">
    <div v-if="loading" class="text-center my-5 py-5">
      <div class="spinner-border text-primary opacity-50" role="status"></div>
      <p class="mt-3 text-muted" style="font-size: 0.9rem;">กำลังดึงข้อมูลทัวร์สักครู่นะคะ...</p>
    </div>

    <div v-else-if="error" class="alert alert-danger text-center shadow-sm rounded-4 p-5">
      <i class="bi bi-exclamation-triangle-fill fs-1 d-block mb-3 text-danger opacity-75"></i> 
      <h5 class="fw-bold text-dark">{{ error }}</h5>
      <div class="mt-4">
        <router-link to="/attraction_list" class="btn btn-outline-danger rounded-pill px-4 fw-medium">กลับหน้าหลัก</router-link>
      </div>
    </div>

    <div v-else-if="attraction" class="row g-5">
      <div class="col-lg-6">
        <div class="sticky-top" style="top: 100px;">
          <div class="image-container shadow-sm overflow-hidden">
            <img :src="'http://localhost/projectgroup/php_api/uploads/' + attraction.image" class="img-fluid attraction-img" :alt="attraction.att_name" />
            <div class="modern-glass-badge glass-category shadow-sm">
              <i class="bi bi-tag-fill me-1"></i> {{ attraction.category_name }}
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-6">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb small" style="font-size: 0.85rem;">
            <li class="breadcrumb-item"><router-link to="/home" class="text-decoration-none text-primary">Home</router-link></li>
            <li class="breadcrumb-item"><router-link to="/attraction_list" class="text-decoration-none text-primary">Tours</router-link></li>
            <li class="breadcrumb-item active text-muted">{{ attraction.att_name }}</li>
          </ol>
        </nav>

        <h2 class="display-6 fw-bold text-dark mb-2 tracking-tight">{{ attraction.att_name }}</h2>
        
        <div class="d-flex align-items-center mb-5 gap-3 p-3 bg-light rounded-4 border">
          <div class="price-tag">
            <span class="fs-3 fw-bold text-primary">{{ parseFloat(attraction.price).toLocaleString() }}</span>
            <span class="ms-1 text-muted fw-medium" style="font-size: 0.85rem;">฿ / ท่าน</span>
          </div>
          <div class="v-line"></div>
          <div class="seats-info">
            <div class="small text-muted text-uppercase fw-semibold" style="font-size: 0.7rem; letter-spacing: 0.5px;">สถานะที่นั่ง</div>
            <div class="fw-bold" :class="attraction.Seat > 0 ? 'text-success' : 'text-danger'">
              <i :class="attraction.Seat > 0 ? 'bi bi-person-check-fill' : 'bi bi-x-circle-fill'" class="me-1"></i> 
              {{ attraction.Seat > 0 ? `ว่าง ${attraction.Seat} ที่` : 'เต็มแล้ว' }}
            </div>
          </div>
        </div>

        <div class="card border-0 bg-white mb-5">
          <h6 class="fw-bold mb-3 text-dark"><i class="bi bi-info-circle text-primary me-2"></i>รายละเอียดโปรแกรมทัวร์</h6>
          <p class="text-muted lh-lg mb-0" style="white-space: pre-line; font-size: 0.95rem;">{{ attraction.description }}</p>
        </div>

        <div class="card border border-light shadow-sm rounded-4 booking-card overflow-hidden">
          <div class="card-body p-4 bg-white">
            <h6 class="fw-bold mb-4 text-dark border-bottom pb-3"><i class="bi bi-calendar2-check text-primary me-2"></i>ดำเนินการจอง</h6>
            
            <div class="row mb-4">
              <div class="col-12">
                <label class="form-label small fw-bold text-muted text-uppercase mb-2" style="font-size: 0.75rem;">1. เลือกวันเดินทาง</label>
                <input type="date" v-model="travelDate" class="form-control form-control-lg rounded-3 shadow-none bg-light border-0" :min="minDate" style="font-size: 0.95rem;">
              </div>
            </div>

            <div class="row align-items-center">
              <div class="col-md-5 mb-3 mb-md-0">
                <label class="form-label small fw-bold text-muted text-uppercase mb-2" style="font-size: 0.75rem;">2. จำนวนผู้เดินทาง</label>
                <div class="input-group">
                  <button class="btn btn-light border px-3" @click="bookingCount > 1 ? bookingCount-- : null">-</button>
                  <input type="number" v-model="bookingCount" class="form-control text-center bg-white shadow-none fw-bold text-primary" readonly>
                  <button class="btn btn-light border px-3" @click="bookingCount < attraction.Seat ? bookingCount++ : null">+</button>
                </div>
              </div>
              <div class="col-md-7 mt-3 mt-md-0 pt-md-4">
                <div class="d-flex justify-content-between align-items-end mb-2 px-1">
                  <span class="small text-muted fw-medium">ยอดชำระรวม:</span>
                  <span class="fw-bold text-dark fs-5">{{ (attraction.price * bookingCount).toLocaleString() }} ฿</span>
                </div>
                <button class="btn btn-primary w-100 py-3 rounded-pill fw-bold shadow-sm d-flex justify-content-center align-items-center gap-2 transition-btn" @click="handleBooking" :disabled="attraction.Seat <= 0">
                  จองทัวร์นี้เลย <i class="bi bi-arrow-right"></i>
                </button>
              </div>
            </div>
          </div>
        </div>

        <div class="mt-4">
          <router-link to="/attraction_list" class="text-decoration-none text-muted fw-medium back-link d-inline-flex align-items-center gap-1" style="font-size: 0.9rem;">
            <i class="bi bi-arrow-left"></i> กลับไปเลือกดูทัวร์อื่น
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue"
import { useRoute, useRouter } from "vue-router"

const route = useRoute()
const router = useRouter()
const attraction = ref(null)
const loading = ref(true)
const error = ref(null)
const bookingCount = ref(1)
const travelDate = ref("")
const minDate = new Date().toISOString().split('T')[0]

const fetchAttractionDetail = async () => {
  loading.value = true
  try {
    const id = route.query.id 
    const response = await fetch(`http://localhost/projectgroup/php_api/show_attraction_detail.php?id=${id}`)
    const result = await response.json()
    if (result.success) { attraction.value = result.data }
    else { error.value = result.message }
  } catch (err) { error.value = "ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้" }
  finally { loading.value = false }
}

const handleBooking = async () => {
  const userJSON = localStorage.getItem("user");
  if (!userJSON) {
    alert("กรุณาเข้าสู่ระบบก่อนทำการจองนะคะ");
    router.push("/clogin");
    return;
  }
  if (!travelDate.value) {
    alert("กรุณาเลือกวันเดินทางก่อนนะคะ");
    return;
  }
  
  if (attraction.value.Seat <= 0) {
    alert("ขออภัยค่ะ ทัวร์นี้ที่นั่งเต็มแล้ว");
    return;
  }

  const user = JSON.parse(userJSON);
  if (confirm(`ยืนยันการจองจำนวน ${bookingCount.value} ท่าน?`)) {
    try {
      const bookingData = {
        att_id: attraction.value.att_id,
        cust_id: user.id || user.cust_id, 
        num_people: bookingCount.value,
        total_price: attraction.value.price * bookingCount.value,
        travel_date: travelDate.value
      };

      const response = await fetch("http://localhost/projectgroup/php_api/save_booking.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(bookingData)
      });

      const result = await response.json();
      if (result.success) {
        // 🚩 นำ Emoji ออก
        alert("จองสำเร็จแล้วค่ะ ระบบได้บันทึกข้อมูลการจองของคุณเรียบร้อยแล้ว");
        router.push("/mybooking"); 
      } else {
        alert("ผิดพลาด: " + result.message);
      }
    } catch (err) { alert("เกิดข้อผิดพลาดในการเชื่อมต่อ"); }
  }
}

onMounted(fetchAttractionDetail)
</script>

<style scoped>
/* Typography */
.tracking-tight { letter-spacing: -0.5px; }

/* Image Section */
.image-container { position: relative; border-radius: 24px; border: 1px solid #f1f5f9; }
.attraction-img { width: 100%; height: 500px; object-fit: cover; }

/* Modern Glass Badge */
.modern-glass-badge {
  position: absolute;
  background: rgba(255, 255, 255, 0.85);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  padding: 8px 16px;
  border-radius: 50px;
  font-size: 0.85rem;
  font-weight: 700;
  color: #0f172a;
}
.glass-category { top: 20px; left: 20px; }

/* Price & Seats Info */
.price-tag { display: flex; align-items: baseline; }
.v-line { width: 1px; height: 35px; background: #e2e8f0; margin: 0 15px; }

/* Booking Card */
.booking-card { border-top: 4px solid #0d6efd !important; }
.transition-btn { transition: all 0.3s ease; }
.transition-btn:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 8px 15px rgba(13, 110, 253, 0.2) !important; }

/* Animations & Links */
.back-link { transition: color 0.2s; }
.back-link:hover { color: #0d6efd !important; }

.animate-fade-in { animation: fadeIn 0.5s ease-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
</style>