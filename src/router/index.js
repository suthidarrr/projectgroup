import { createRouter, createWebHistory } from 'vue-router'

const routes = [
  // 🏠 หน้าหลัก
  {
    path: '/',
    redirect: '/home'
  },
  {
    path: '/home',
    name: 'home',
    component: () => import('../views/Home.vue')
  },

  // 🔑 ส่วนการเข้าสู่ระบบและลงทะเบียน (Public)
  {
    path: '/clogin',
    name: 'clogin',
    component: () => import('../views/Clogin.vue')
  },
  {
    path: '/elogin',
    name: 'elogin',
    component: () => import('../views/Elogin.vue')
  },
  {
    path: '/add_customer',
    name: 'add_customer',
    component: () => import('../views/Add_customer.vue')
  },
  {
    path: '/add_employee',
    name: 'add_employee',
    component: () => import('../views/Add_employee.vue')
  },
  

  // 🌍 หน้าแสดงข้อมูลทั่วไป (Public - ใครก็เข้าได้)
  {
    path: '/contact',
    name: 'contact',
    component: () => import('../views/Contact.vue')
  },
  {
    path: '/attraction_detail',
    name: 'attraction_detail',
    component: () => import('../views/Attraction_detail.vue')
  },
  {
    path: '/attraction',
    name: 'attraction',
    component: () => import('../views/Attraction.vue')
  },
  {
    path: '/attraction_list',
    name: 'attraction_list',
    component: () => import('../views/Attraction_list.vue')
  },

  // 🛠️ ส่วนจัดการข้อมูล (Private - ต้อง Login และเป็นพนักงาน)
  {
    path: '/attraction_crud',
    name: 'attraction_crud',
    component: () => import('../views/Attraction_crud.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/employee_crud',
    name: 'employee_crud',
    component: () => import('../views/Employee_crud.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/customer_crud',
    name: 'customer_crud',
    component: () => import('../views/Customer_crud.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/categories_crud',
    name: 'categories_crud',
    component: () => import('../views/Categories_crud.vue'),
    meta: { requiresAuth: true }
  }
  ,
  {
    path: '/mybooking',
    name: 'mybooking',
    component: () => import('../views/Mybooking.vue'),
    meta: { requiresAuth: true }
  }
  ,
  {
    path: '/booking_crud',
    name: 'booking_crud',
    component: () => import('../views/Booking_crud.vue'),
    meta: { requiresAuth: true }
  }
]

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes
})

/* ✅ ระบบความปลอดภัย (ROUTE GUARD) */
router.beforeEach((to, from, next) => {
  const isLoggedIn = !!localStorage.getItem("user")

  // 1. เช็คว่าหน้านี้ต้องการการ Login หรือไม่ (ดูจาก meta: { requiresAuth: true })
  const authRequired = to.matched.some(record => record.meta.requiresAuth)

  // 🚩 กรณีที่ 1: หน้าที่ต้อง Login แต่ผู้ใช้ยังไม่ได้ Login
  if (authRequired && !isLoggedIn) {
    next('/clogin') 
  } 
  // 🚩 กรณีที่ 2: Login แล้ว แต่พยายามจะเข้าหน้า Login ซ้ำ (clogin/elogin)
  else if (isLoggedIn && (to.path === '/clogin' || to.path === '/elogin')) {
    next('/home')
  }
  // 🚩 กรณีอื่นๆ: หน้า Public (รวมถึง /attraction_list) ให้ผ่านไปได้เลย
  else {
    next()
  }
})

export default router