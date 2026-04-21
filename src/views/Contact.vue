<template>
  <div class="contact-page animate-fade-in">
    <section class="contact-header py-5 text-center bg-white border-bottom shadow-sm">
      <div class="container py-4">
        <h1 class="display-5 fw-bold text-primary">GET IN TOUCH</h1>
        <p class="lead text-muted">Have questions about our tours? We're here to help you.</p>
      </div>
    </section>

    <div class="container py-5">
      <div class="row g-5">
        
        <div class="col-lg-5">
          <div class="contact-info-card p-4 rounded-4 shadow-sm bg-white h-100">
            <h3 class="fw-bold mb-4 text-dark">Contact Information</h3>
            
            <div class="d-flex align-items-start mb-4">
              <div class="icon-box me-3">📞</div>
              <div>
                <h6 class="fw-bold mb-1">Phone Number</h6>
                <p class="text-secondary mb-0">123-456-7890</p>
              </div>
            </div>

            <div class="d-flex align-items-start mb-4">
              <div class="icon-box me-3">📧</div>
              <div>
                <h6 class="fw-bold mb-1">Email Address</h6>
                <p class="text-secondary mb-0">support@attraction-tour.com</p>
              </div>
            </div>

            <div class="d-flex align-items-start mb-4">
              <div class="icon-box me-3">📍</div>
              <div>
                <h6 class="fw-bold mb-1">Our Office</h6>
                <p class="text-secondary mb-0">
                  123 Travel Street, ICT District,<br>
                  Bangkok, Thailand 10110
                </p>
              </div>
            </div>

            <hr class="my-4">

            <div class="promo-img rounded-3 overflow-hidden">
              <img src="https://images.unsplash.com/photo-1488646953014-85cb44e25828?auto=format&fit=crop&w=600&q=80" 
                   alt="Travel Info" class="img-fluid w-100 shadow-sm" />
            </div>
          </div>
        </div>

        <div class="col-lg-7">
          <div class="form-card p-4 p-md-5 rounded-4 shadow-sm bg-white">
            <h3 class="fw-bold mb-4 text-dark">Send us a Message</h3>
            
            <form @submit.prevent="handleSubmit">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label small fw-bold text-secondary">FULL NAME</label>
                  <input type="text" class="form-control form-control-lg rounded-3 border-light-subtle bg-light" placeholder="John Snow">
                </div>
                <div class="col-md-6">
                  <label class="form-label small fw-bold text-secondary">EMAIL ADDRESS</label>
                  <input type="email" class="form-control form-control-lg rounded-3 border-light-subtle bg-light" placeholder="name@example.com">
                </div>
                <div class="col-12 mt-4">
                  <label class="form-label small fw-bold text-secondary">YOUR MESSAGE</label>
                  <textarea class="form-control rounded-3 border-light-subtle bg-light" rows="5" placeholder="Tell us what you need..."></textarea>
                </div>
                
                <div class="col-12 mt-4 pt-2">
                  <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-lg px-5 rounded-pill fw-bold shadow-sm">
                      Submit Message
                    </button>
                    <button type="reset" class="btn btn-light btn-lg px-4 rounded-pill text-secondary">
                      Cancel
                    </button>
                  </div>
                </div>
              </div>
            </form>
          </div>
        </div>

      </div>
    </div>

    <footer class="footer mt-5 py-4 bg-white border-top">
      <div class="container text-center">
        <p class="mb-0 fw-medium text-secondary small">
          © 2026 Domestic Tour Booking System. All Rights Reserved.
        </p>
      </div>
    </footer>
  </div>
</template>

<style scoped>
.contact-page {
  background-color: #f8fafc;
  min-height: 100vh;
}

/* ไอคอนในกรอบวงกลม */
.icon-box {
  width: 45px;
  height: 45px;
  background-color: #eef2ff;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
}

/* ปรับแต่งช่องกรอกข้อมูลให้ดูนวลขึ้น */
.form-control:focus {
  background-color: #fff;
  border-color: #0d6efd;
  box-shadow: 0 0 0 0.25 dark;
}

/* รูปภาพตกแต่ง */
.promo-img img {
  transition: transform 0.5s ease;
}
.promo-img:hover img {
  transform: scale(1.05);
}

.animate-fade-in {
  animation: fadeIn 0.8s ease-in-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>