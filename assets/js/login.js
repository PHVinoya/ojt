function openRegisterModal() {
  document.getElementById("registerModal").style.display = "block"
  document.body.style.overflow = "hidden"
}

function closeRegisterModal() {
  document.getElementById("registerModal").style.display = "none"
  document.body.style.overflow = "auto"
}

function showRegistrationForm(role) {
  closeRegisterModal()

  if (role === "trainee") {
    document.getElementById("traineeFormModal").style.display = "block"
  } else if (role === "admin") {
    document.getElementById("adminFormModal").style.display = "block"
  }

  document.body.style.overflow = "hidden"
}

function closeRegistrationForm() {
  document.getElementById("traineeFormModal").style.display = "none"
  document.getElementById("adminFormModal").style.display = "none"
  document.body.style.overflow = "auto"
}

// Close modals when clicking outside
window.onclick = (event) => {
  const registerModal = document.getElementById("registerModal")
  const traineeModal = document.getElementById("traineeFormModal")
  const adminModal = document.getElementById("adminFormModal")

  if (event.target === registerModal) {
    closeRegisterModal()
  }
  if (event.target === traineeModal || event.target === adminModal) {
    closeRegistrationForm()
  }
}

// Form validation
document.addEventListener("DOMContentLoaded", () => {
  // Trainee form validation
  const traineeForm = document.querySelector("#traineeFormModal .registration-form")
  if (traineeForm) {
    traineeForm.addEventListener("submit", (e) => {
      const username = document.getElementById("trainee_username").value
      const password = document.getElementById("trainee_password").value
      const confirmPassword = document.getElementById("trainee_confirm_password").value

      if (username.length < 3) {
        e.preventDefault()
        alert("Username must be at least 3 characters long!")
        return false
      }

      if (password !== confirmPassword) {
        e.preventDefault()
        alert("Passwords do not match!")
        return false
      }

      if (password.length < 6) {
        e.preventDefault()
        alert("Password must be at least 6 characters long!")
        return false
      }
    })
  }

  // Admin form validation
  const adminForm = document.querySelector("#adminFormModal .registration-form")
  if (adminForm) {
    adminForm.addEventListener("submit", (e) => {
      const username = document.getElementById("admin_username").value
      const password = document.getElementById("admin_password").value
      const confirmPassword = document.getElementById("admin_confirm_password").value
      const adminCode = document.getElementById("admin_code").value

      if (username.length < 3) {
        e.preventDefault()
        alert("Username must be at least 3 characters long!")
        return false
      }

      if (password !== confirmPassword) {
        e.preventDefault()
        alert("Passwords do not match!")
        return false
      }

      if (password.length < 6) {
        e.preventDefault()
        alert("Password must be at least 6 characters long!")
        return false
      }

      if (adminCode !== "000-admin-000") {
        e.preventDefault()
        alert("Invalid admin code!")
        return false
      }
    })
  }

  // Phone number formatting
  const phoneInput = document.getElementById("trainee_phone")
  if (phoneInput) {
    phoneInput.addEventListener("input", (e) => {
      let value = e.target.value.replace(/\D/g, "")
      if (value.length >= 10) {
        value = value.substring(0, 11)
      }
      e.target.value = value
    })
  }
})
