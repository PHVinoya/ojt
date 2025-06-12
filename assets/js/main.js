// Enhanced sidebar functionality
let sidebarCollapsed = false

function toggleSidebar() {
  const sidebar = document.getElementById("sidebar")
  const mainContent = document.querySelector(".main-content")
  const toggleBtn = document.getElementById("sidebarToggle")

  sidebarCollapsed = !sidebarCollapsed

  if (sidebarCollapsed) {
    sidebar.classList.add("collapsed")
    mainContent.classList.add("expanded")
    toggleBtn.classList.remove("hidden")
  } else {
    sidebar.classList.remove("collapsed")
    mainContent.classList.remove("expanded")
    if (window.innerWidth > 768) {
      toggleBtn.classList.add("hidden")
    }
  }

  // Save state
  localStorage.setItem("sidebarCollapsed", sidebarCollapsed)
}

// Initialize sidebar state
document.addEventListener("DOMContentLoaded", () => {
  const savedState = localStorage.getItem("sidebarCollapsed")
  if (savedState === "true") {
    toggleSidebar()
  }

  // Add fade-in animation to content
  document.querySelector(".main-content").classList.add("fade-in")

  // Enhanced button loading states
  document.querySelectorAll("form").forEach((form) => {
    form.addEventListener("submit", () => {
      const submitBtn = form.querySelector('button[type="submit"]')
      if (submitBtn) {
        submitBtn.classList.add("loading")
        submitBtn.disabled = true
      }
    })
  })
})

// Auto-hide sidebar on mobile when clicking outside
document.addEventListener("click", (event) => {
  const sidebar = document.getElementById("sidebar")
  const toggle = document.getElementById("sidebarToggle")

  if (
    window.innerWidth <= 768 &&
    !sidebar.contains(event.target) &&
    !toggle.contains(event.target) &&
    !sidebar.classList.contains("collapsed")
  ) {
    toggleSidebar()
  }
})

// Responsive sidebar handling
window.addEventListener("resize", () => {
  const sidebar = document.getElementById("sidebar")
  const mainContent = document.querySelector(".main-content")
  const toggleBtn = document.getElementById("sidebarToggle")

  if (window.innerWidth <= 768) {
    toggleBtn.classList.remove("hidden")
  } else if (!sidebarCollapsed) {
    toggleBtn.classList.add("hidden")
  }
})

// Enhanced QR scanner with better error handling
function initQRScanner() {
  if (typeof Html5QrcodeScanner !== "undefined") {
    const html5QrcodeScanner = new Html5QrcodeScanner(
      "qr-reader",
      {
        fps: 10,
        qrbox: { width: 250, height: 250 },
        aspectRatio: 1.0,
        showTorchButtonIfSupported: true,
      },
      false,
    )

    function onScanSuccess(decodedText, decodedResult) {
      document.getElementById("scanner-result").innerHTML =
        '<div class="scanner-result success">✅ QR Code detected: ' + decodedText + "</div>"

      // Auto-submit with loading state
      const form = document.createElement("form")
      form.method = "POST"
      form.action = ""

      const input = document.createElement("input")
      input.type = "hidden"
      input.name = "qr_data"
      input.value = decodedText

      const submitInput = document.createElement("input")
      submitInput.type = "hidden"
      submitInput.name = "scan_qr"
      submitInput.value = "1"

      form.appendChild(input)
      form.appendChild(submitInput)
      document.body.appendChild(form)

      // Show loading
      document.getElementById("scanner-result").innerHTML += '<div class="scanner-result info">⏳ Processing...</div>'

      form.submit()
    }

    function onScanFailure(error) {
      // Handle scan failure silently
    }

    html5QrcodeScanner.render(onScanSuccess, onScanFailure)
  }
}

// Initialize QR scanner if on scanner page
if (document.getElementById("qr-reader")) {
  initQRScanner()
}
