import './bootstrap'
import Alpine from 'alpinejs'

window.Alpine = Alpine

Alpine.store('sidebar', {
  collapsed: false,

  init() {
    const saved = localStorage.getItem('sidebar') || 'expanded'
    this.collapsed = saved === 'collapsed'
  },

  setCollapsed(value) {
    this.collapsed = !!value
    localStorage.setItem('sidebar', this.collapsed ? 'collapsed' : 'expanded')
  },

  toggle() {
    this.setCollapsed(!this.collapsed)
  },
})

Alpine.start()

// ============================
// ===== GLOBAL THEME (DARK DEFAULT) =====
// ============================
const themeToggleBtn = document.getElementById('theme-toggle')
const iconMoon = document.getElementById('icon-moon')
const iconSun = document.getElementById('icon-sun')

function applyTheme(mode) {
  const isDark = mode === 'dark'
  document.documentElement.classList.toggle('dark', isDark)
  localStorage.setItem('theme', isDark ? 'dark' : 'light')
  iconSun?.classList.toggle('hidden', isDark)
  iconMoon?.classList.toggle('hidden', !isDark)
}

const savedTheme = localStorage.getItem('theme')
applyTheme(savedTheme ? savedTheme : 'dark')

themeToggleBtn?.addEventListener('click', () => {
  const isDark = document.documentElement.classList.contains('dark')
  applyTheme(isDark ? 'light' : 'dark')
})

// ============================
// ===== AVATAR MENU =====
// ============================
const avatarBtn = document.getElementById('avatar-btn')
const avatarMenu = document.getElementById('avatar-menu')

avatarBtn?.addEventListener('click', (e) => {
  e.stopPropagation()
  avatarMenu?.classList.toggle('hidden')
})
document.addEventListener('click', () => avatarMenu?.classList.add('hidden'))

// ============================
// ===== SIDEBAR =====
// ============================
const sidebar = document.getElementById('sidebar')
const overlay = document.getElementById('sidebar-overlay')
const toggleBtn = document.getElementById('sidebar-toggle')
const closeBtn = document.getElementById('sidebar-close')
const navbar = document.getElementById('navbar')
const shell = document.getElementById('appShell')

function isDesktop() {
  return window.matchMedia('(min-width: 1024px)').matches
}

function setDataState(state) {
  sidebar?.setAttribute('data-sidebar', state)
  navbar?.setAttribute('data-sidebar', state)
  shell?.setAttribute('data-sidebar', state)
}

function openDrawer() {
  sidebar?.classList.remove('-translate-x-full')
  overlay?.classList.remove('hidden')
  document.documentElement.classList.add('overflow-hidden')
}

function closeDrawer() {
  sidebar?.classList.add('-translate-x-full')
  overlay?.classList.add('hidden')
  document.documentElement.classList.remove('overflow-hidden')
}

function initSidebar() {
  const saved = localStorage.getItem('sidebar') || 'expanded'
  setDataState(saved)
  Alpine.store('sidebar')?.setCollapsed(saved === 'collapsed')

  if (isDesktop()) {
    overlay?.classList.add('hidden')
    sidebar?.classList.remove('-translate-x-full')
    document.documentElement.classList.remove('overflow-hidden')
  } else {
    closeDrawer()
  }
}

function toggleSidebar() {
  if (!sidebar) return

  if (isDesktop()) {
    const current = sidebar.getAttribute('data-sidebar') || 'expanded'
    const next = current === 'collapsed' ? 'expanded' : 'collapsed'
    localStorage.setItem('sidebar', next)
    setDataState(next)
    Alpine.store('sidebar')?.setCollapsed(next === 'collapsed')
  } else {
    const closed = sidebar.classList.contains('-translate-x-full')
    closed ? openDrawer() : closeDrawer()
  }
}

toggleBtn?.addEventListener('click', toggleSidebar)
closeBtn?.addEventListener('click', closeDrawer)
overlay?.addEventListener('click', closeDrawer)

document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape' && !isDesktop()) closeDrawer()
})

window.addEventListener('resize', initSidebar)
initSidebar()
