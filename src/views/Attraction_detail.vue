<template>
  <div class="container my-5 animate-fade-in">
    <div v-if="loading" class="text-center my-5 py-5">
      <div class="spinner-border text-primary" role="status"></div>
      <p class="mt-3 text-muted">กำลังดึงข้อมูลทัวร์สักครู่นะคะ...</p>
    </div>

    <div v-else-if="error" class="alert alert-danger text-center shadow-sm rounded-4">
      <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ error }}
      <div class="mt-3">
        <router-link to="/attraction_list" class="btn btn-outline-danger rounded-pill px-4">กลับหน้าหลัก</router-link>
      </div>
    </div>

    <div v-else-if="attraction" class="row g-4">
      
      <div class="col-lg-6">
        <div class="sticky-top" style="top: 100px;">
          <div class="image-container shadow-lg rounded-4 overflow-hidden">
            <img 
              :src="'http://localhost/projectgroup/php_api/uploads/' + attraction.image" 
              class="img-fluid attraction-img" 
            />
            <div class="category-badge shadow-sm">{{ attraction.category_name }}</div>
          </div>
        </div>
      </div>

      <div class="col-lg-6">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb small">
            <li class="breadcrumb-item"><router-link to="/home">Home</router-link></li>
            <li class="breadcrumb-item active">{{ attraction.att_name }}</li>
          </ol>
        </nav>

        <h1 class="display-5 fw-bold text-dark mb-3">{{ attraction.att_name }}</h1>
        
        <div class="d-flex align-items-center mb-4 gap-3">
          <div class="price-tag">
            <span class="fs-2 fw-bold text-primary">{{ parseFloat(attraction.price).toLocaleString() }}</span>
            <span class="ms-1 text-muted">฿ / ท่าน</span>
          </div>
          <div class="v-line"></div>
          <div class="seats-info">
            <div class="small text-muted text-uppercase fw-bold">ว่างสำหรับคุณ</div>
            <div class="fw-bold text-dark">
              <i class="bi bi-people-fill me-1"></i> {{ attraction.Seat }} ที่นั่ง
            </div>
          </div>
        </div>

        <div class="card border-0 bg-white shadow-sm rounded-4 mb-4">
          <div class="card-body p-4">
            <h5 class="fw-bold mb-3"><i class="bi bi-info-circle-fill text-primary me-2"></i>รายละเอียดโปรแกรมทัวร์</h5>
            <p class="text-secondary lh-lg" style="white-space: pre-line;">{{ attraction.description }}</p>
          </div>
        </div>

        <div class="card border-0 shadow rounded-4 booking-card overflow-hidden">
          <div class="card-body p-4 bg-light">
            <div class="row align-items-center">
              <div class="col-md-6 mb-3 mb-md-0">
                <label class="form-label small fw-bold text-muted">จำนวนผู้เดินทาง</label>
                <div class="input-group">
                  <button class="btn btn-white border px-3" @click="bookingCount > 1 ? bookingCount-- : null">-</button>
                  <input type="number" v-model="bookingCount" class="form-control text-center bg-white shadow-none" readonly>
                  <button class="btn btn-white border px-3" @click="bookingCount < attraction.Seat ? bookingCount++ : null">+</button>
                </div>
              </div>
              <div class="col-md-6">
                <button class="btn btn-primary w-100 py-3 rounded-pill fw-bold shadow" @click="handleBooking">
                  <i class="bi bi-calendar-check me-2"></i>จองตอนนี้เลย
                </button>
              </div>
            </div>
            <div class="text-center mt-3 small text-muted">
              ราคารวม: <span class="fw-bold text-dark fs-5">{{ (attraction.price * bookingCount).toLocaleString() }} ฿</span>
            </div>
          </div>
        </div>

        <div class="mt-4 text-center text-md-start">
          <router-link to="/attraction_list" class="text-decoration-none text-muted fw-bold">
            <i class="bi bi-arrow-left me-1"></i> กลับไปเลือกดูทัวร์อื่น
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

// ✅ ฟังก์ชันจัดการการจอง (เช็ค Login ตรงนี้)
const handleBooking = async () => {
  const userJSON = localStorage.getItem("user");
  
  // 1. ถ้ายังไม่ได้ Login ให้เด้งไปหน้า Login ทันที
  if (!userJSON) {
    alert("กรุณาเข้าสู่ระบบก่อนทำการจองนะคะ 😊");
    router.push("/clogin");
    return; // หยุดการทำงานทันที
  }

  // 2. ถ้า Login แล้ว ทำงานต่อตามปกติ
  const user = JSON.parse(userJSON);
  
  if (confirm(`ยืนยันการจองจำนวน ${bookingCount.value} ท่าน?`)) {
    try {
      const bookingData = {
        att_id: attraction.value.att_id,
        cust_id: user.id || user.cust_id, 
        num_people: bookingCount.value,
        total_price: attraction.value.price * bookingCount.value
      };

      const response = await fetch("http://localhost/projectgroup/php_api/save_booking.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(bookingData)
      });

      const result = await response.json();
      if (result.success) {
        alert("จองสำเร็จแล้วค่ะ!");
        router.push("/my-booking"); 
      } else {
        alert("ผิดพลาด: " + result.message);
      }
    } catch (err) { alert("เกิดข้อผิดพลาดในการเชื่อมต่อ"); }
  }
}

// ✅ เมื่อเปิดหน้ามา ให้ดึงข้อมูลทันทีโดยไม่ต้องรอ Login
onMounted(fetchAttractionDetail)
</script>

<style scoped>
.image-container { position: relative; border-radius: 20px; }
.attraction-img { width: 100%; height: 550px; object-fit: cover; }
.category-badge { position: absolute; top: 20px; left: 20px; background: white; padding: 8px 20px; border-radius: 50px; font-weight: bold; color: #0d6efd; }
.price-tag { display: flex; align-items: baseline; }
.v-line { width: 1px; height: 40px; background: #eee; margin: 0 15px; }
.booking-card { border-left: 6px solid #0d6efd !important; background: #f8f9fa; }
.btn-white { background: white; color: #6c757d; }
.animate-fade-in { animation: fadeIn 0.6s ease-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
</style>