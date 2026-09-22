/**
 * AlumTrace – Shared Frontend UI Scripts
 * Sidebar toggle, mobile nav, localStorage preference
 */
(function () {
  "use strict";

  const body = document.body;
  const STORAGE_KEY = "alumtrace_sidebar";

  function isMobile() {
    return window.innerWidth <= 900;
  }

  function applyStoredState() {
    if (isMobile()) {
      body.classList.remove("sidebar-collapsed");
      body.classList.remove("sidebar-open-mobile");
      return;
    }
    const state = localStorage.getItem(STORAGE_KEY);
    if (state === "collapsed") {
      body.classList.add("sidebar-collapsed");
    } else {
      body.classList.remove("sidebar-collapsed");
    }
  }

  function toggleSidebar() {
    if (isMobile()) {
      body.classList.toggle("sidebar-open-mobile");
      return;
    }
    body.classList.toggle("sidebar-collapsed");
    localStorage.setItem(
      STORAGE_KEY,
      body.classList.contains("sidebar-collapsed") ? "collapsed" : "expanded"
    );
  }

  function closeMobileSidebar() {
    body.classList.remove("sidebar-open-mobile");
  }

  function init() {
    applyStoredState();

    document.querySelectorAll("[data-sidebar-toggle], #sidebarToggle, #headerToggle").forEach(function (btn) {
      btn.addEventListener("click", function (e) {
        e.preventDefault();
        toggleSidebar();
      });
    });

    const backdrop = document.getElementById("sidebarBackdrop");
    if (backdrop) {
      backdrop.addEventListener("click", closeMobileSidebar);
    }

    window.addEventListener("resize", function () {
      if (!isMobile()) {
        body.classList.remove("sidebar-open-mobile");
        applyStoredState();
      } else {
        body.classList.remove("sidebar-collapsed");
      }
    });

    // Wrap nav link text in span.nav-label for collapsed mode if not already
    document.querySelectorAll(".sidebar-nav a, .sidebar-menu a").forEach(function (link) {
      if (link.querySelector(".nav-label")) return;
      const icon = link.querySelector("i");
      const nodes = Array.from(link.childNodes).filter(function (n) {
        return n.nodeType === 3 && n.textContent.trim();
      });
      nodes.forEach(function (textNode) {
        const span = document.createElement("span");
        span.className = "nav-label";
        span.textContent = textNode.textContent;
        textNode.parentNode.replaceChild(span, textNode);
      });
    });
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();