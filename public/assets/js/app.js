(() => {
  const body = document.body;
  document.querySelectorAll('a, button').forEach((control) => {
    const icon = control.querySelector('[data-icon-label]');
    if (icon && !control.textContent.trim()) {
      control.setAttribute('aria-label', control.getAttribute('aria-label') || icon.dataset.iconLabel);
      control.setAttribute('title', control.getAttribute('aria-label'));
    }
  });

  document.querySelectorAll("[data-toggle-menu]").forEach((button) => {
    button.addEventListener("click", () => {
      const target = document.querySelector(button.getAttribute("data-toggle-menu"));
      if (target) button.setAttribute('aria-expanded', String(target.classList.toggle("is-open")));
    });
  });

  document.querySelectorAll("[data-confirm]").forEach((form) => {
    form.addEventListener("submit", (event) => {
      const message = form.getAttribute("data-confirm") || "Confirmer cette action ?";
      if (!window.confirm(message)) event.preventDefault();
    });
  });

  document.querySelectorAll("[data-slug-source]").forEach((input) => {
    const target = document.querySelector(input.getAttribute("data-slug-source"));
    if (!target) return;
    input.addEventListener("input", () => {
      if (target.dataset.touched === "true") return;
      target.value = input.value
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, "-")
        .replace(/^-|-$/g, "");
    });
    target.addEventListener("input", () => {
      target.dataset.touched = "true";
    });
  });

  document.querySelectorAll("[data-view]").forEach((button) => {
    button.addEventListener("click", () => {
      const view = button.getAttribute("data-view");
      const wrapper = document.querySelector("[data-view-wrapper]");
      if (!wrapper) return;
      wrapper.dataset.currentView = view;
      document.querySelectorAll("[data-view]").forEach((item) => {
        item.classList.remove("is-active");
        item.setAttribute('aria-pressed', 'false');
      });
      button.classList.add("is-active");
      button.setAttribute('aria-pressed', 'true');
    });
  });

  document.querySelectorAll("[data-preview-input]").forEach((input) => {
    const preview = document.querySelector(input.getAttribute("data-preview-input"));
    if (!preview) return;
    input.addEventListener("change", () => {
      const file = input.files && input.files[0];
      if (!file) return;
      if (!file.type.startsWith('image/')) return;
      if (preview.dataset.objectUrl) URL.revokeObjectURL(preview.dataset.objectUrl);
      preview.src = URL.createObjectURL(file);
      preview.dataset.objectUrl = preview.src;
      preview.hidden = false;
    });
  });

  const sidebarToggle = document.querySelector("[data-admin-sidebar-toggle]");
  const sidebar = document.querySelector('.admin-sidebar');
  const mobileSidebar = window.matchMedia('(max-width:1080px)');
  const syncSidebar = () => {
    if (sidebar) sidebar.inert = mobileSidebar.matches && !body.classList.contains('admin-sidebar-open');
  };
  syncSidebar();
  mobileSidebar.addEventListener('change', syncSidebar);
  if (sidebarToggle) {
    sidebarToggle.addEventListener("click", () => {
      sidebarToggle.setAttribute('aria-expanded', String(body.classList.toggle("admin-sidebar-open")));
      syncSidebar();
      if (body.classList.contains('admin-sidebar-open')) document.querySelector('.admin-sidebar-close')?.focus();
    });
  }
  const closeSidebar = () => {
    const wasOpen = body.classList.contains('admin-sidebar-open');
    body.classList.remove('admin-sidebar-open');
    sidebarToggle?.setAttribute('aria-expanded', 'false');
    syncSidebar();
    if (wasOpen) sidebarToggle?.focus();
  };
  document.querySelectorAll('[data-close-sidebar]').forEach(el => el.addEventListener('click', () => { closeSidebar(); sidebarToggle?.focus(); }));
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Tab' && mobileSidebar.matches && body.classList.contains('admin-sidebar-open') && sidebar) {
      const items = [...sidebar.querySelectorAll('a,button')].filter(el => el.getClientRects().length);
      const first = items[0], last = items[items.length - 1];
      if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last?.focus(); }
      if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first?.focus(); }
    }
    if (event.key === 'Escape') {
      closeSidebar();
      document.querySelectorAll('.nav-menu.is-open').forEach(el => el.classList.remove('is-open'));
      document.querySelectorAll('[data-toggle-menu]').forEach(el => el.setAttribute('aria-expanded', 'false'));
    }
  });
})();
