document.addEventListener("DOMContentLoaded", () => {
  // Sidebar Toggle
  const body = document.body
  const sidebarToggle = document.getElementById("sidebarToggle")
  const sidebar = document.querySelector(".sidebar")
  const backdrop = document.querySelector(".sidebar-backdrop")

  // Toggle sidebar
  function toggleSidebar() {
    body.classList.toggle("sidebar-open")
  }

  // Close sidebar
  function closeSidebar() {
    body.classList.remove("sidebar-open")
  }

  // Event listeners
  if (sidebarToggle) {
    sidebarToggle.addEventListener("click", (e) => {
      e.preventDefault()
      toggleSidebar()
    })
  }

  // Close sidebar when clicking backdrop
  if (backdrop) {
    backdrop.addEventListener("click", closeSidebar)
  }

  // Close sidebar when clicking nav links on mobile
  const navLinks = document.querySelectorAll(".sidebar .nav-link")
  navLinks.forEach((link) => {
    link.addEventListener("click", () => {
      if (window.innerWidth < 992) {
        closeSidebar()
      }
    })
  })

  // Handle window resize
  function handleResize() {
    if (window.innerWidth >= 992) {
      closeSidebar()
    }
  }

  // Initial call and event listener for resize
  handleResize()
  window.addEventListener("resize", handleResize)

  // Initialize Bootstrap components
  if (typeof bootstrap !== "undefined") {
    // Initialize dropdowns
    const dropdowns = document.querySelectorAll(".dropdown-toggle")
    dropdowns.forEach((dropdown) => {
      new bootstrap.Dropdown(dropdown)
    })

    // Auto-dismiss alerts after 5 seconds
    const alerts = document.querySelectorAll(".alert")
    alerts.forEach((alert) => {
      setTimeout(() => {
        try {
          const bsAlert = bootstrap.Alert.getOrCreateInstance(alert)
          if (bsAlert) {
            bsAlert.close()
          }
        } catch (error) {
          console.error("Error closing alert:", error)
        }
      }, 5000)
    })
  } else {
    console.warn("Bootstrap is not defined. Ensure Bootstrap is properly loaded.")
  }

  // Pagination scroll position management
  // Check if we should restore scroll position
  if (sessionStorage.getItem("scrollPosition")) {
    // Restore the scroll position
    window.scrollTo(0, Number.parseInt(sessionStorage.getItem("scrollPosition")))
    // Clear the stored position
    sessionStorage.removeItem("scrollPosition")
  }

  // Add click event listeners to pagination links
  const paginationLinks = document.querySelectorAll(".pagination .page-link")
  paginationLinks.forEach((link) => {
    link.addEventListener("click", (e) => {
      // Store current scroll position
      sessionStorage.setItem("scrollPosition", window.scrollY)
    })
  })

  // AJAX Pagination for customer view page
  const setupAjaxPagination = () => {
    // Only run on customer view page
    if (!document.querySelector(".pagination")) return

    // Get all pagination links
    const paginationLinks = document.querySelectorAll(".pagination .page-link")

    paginationLinks.forEach((link) => {
      link.addEventListener("click", function (e) {
        e.preventDefault()

        const url = this.getAttribute("href")
        const targetSection = this.closest(".card").id

        // Show loading indicator
        const tableBody = this.closest(".card").querySelector("tbody")
        if (tableBody) {
          tableBody.innerHTML = `
            <tr>
              <td colspan="6" class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                  <span class="visually-hidden">Loading...</span>
                </div>
              </td>
            </tr>
          `
        }

        // Fetch the new page content
        fetch(url)
          .then((response) => response.text())
          .then((html) => {
            const parser = new DOMParser()
            const doc = parser.parseFromString(html, "text/html")

            // Find the corresponding section in the fetched page
            const newSection = doc.getElementById(targetSection)

            if (newSection) {
              // Replace only the content of the section
              document.getElementById(targetSection).innerHTML = newSection.innerHTML

              // Re-attach event listeners to the new pagination links
              setupAjaxPagination()

              // Update the URL without reloading the page
              window.history.pushState({}, "", url)
            }
          })
          .catch((error) => {
            console.error("Error fetching page:", error)
            // Show error message
            if (tableBody) {
              tableBody.innerHTML = `
                <tr>
                  <td colspan="6" class="text-center py-4 text-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Error loading data. Please try again.
                  </td>
                </tr>
              `
            }
          })
      })
    })
  }

  // Initialize AJAX pagination
  setupAjaxPagination()
})

